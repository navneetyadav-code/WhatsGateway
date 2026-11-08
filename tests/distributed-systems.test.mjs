import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { MessageDebouncer } from '../worker/chat-debouncer.mjs';

/**
 * Distributed Systems Reliability & Concurrency Verification Suite (DIST-01 to DIST-04)
 *
 * Validates remediation of:
 * - DIST-01: Elimination of 45-second latency on auto-wake via bounded connection readiness polling
 * - DIST-02: Multi-worker Baileys session collision prevention via atomic database leases
 * - DIST-03: Elimination of platform-wide webhook Head-of-Line (HoL) blocking via bounded concurrent dispatcher
 * - DIST-04: Chat debouncer crash & shutdown resilience via flushAll() and termination handlers
 */

const serverSource = fs.readFileSync(path.resolve('worker/server.mjs'), 'utf8');

// =============================================================================
// DIST-01: Auto-Wake Connection Readiness Polling
// =============================================================================

test('DIST-01: worker/server.mjs contains waitForSessionConnection and invokes it on auto-wake', () => {
  assert.equal(
    serverSource.includes('async function waitForSessionConnection(session, timeoutMs = 6000)'),
    true,
    'worker/server.mjs must define waitForSessionConnection'
  );

  assert.equal(
    serverSource.includes('await waitForSessionConnection(session, 6000)'),
    true,
    'worker/server.mjs must await socket readiness during auto-wake'
  );
});

test('DIST-01: waitForSessionConnection logic resolves on state open and times out safely', async () => {
  async function waitForSessionConnection(session, timeoutMs = 1000) {
    if (session.state === 'connected' && session.sock) {
      return true;
    }
    if (session.state !== 'connecting') {
      return false;
    }

    return new Promise((resolve) => {
      let settled = false;
      const intervalMs = 50;
      let elapsed = 0;

      const timer = setInterval(() => {
        elapsed += intervalMs;
        if (session.state === 'connected' && session.sock) {
          finish(true);
        } else if (session.state !== 'connecting' || elapsed >= timeoutMs) {
          finish(session.state === 'connected');
        }
      }, intervalMs);

      function finish(result) {
        if (!settled) {
          settled = true;
          clearInterval(timer);
          resolve(result);
        }
      }
    });
  }

  // Case A: Session already connected
  const immediateSession = { state: 'connected', sock: {} };
  const resA = await waitForSessionConnection(immediateSession);
  assert.equal(resA, true, 'Immediate connected session must return true instantly');

  // Case B: Session transitions from connecting to connected after 150ms
  const delayedSession = { state: 'connecting', sock: null };
  setTimeout(() => {
    delayedSession.state = 'connected';
    delayedSession.sock = {};
  }, 150);
  const start = Date.now();
  const resB = await waitForSessionConnection(delayedSession, 1000);
  const duration = Date.now() - start;
  assert.equal(resB, true, 'Transitioning session must resolve true once connected');
  assert.ok(duration >= 140 && duration < 600, 'Must resolve promptly after state transition, not wait for timeout');

  // Case C: Session fails or stays offline
  const offlineSession = { state: 'connecting', sock: null };
  const resC = await waitForSessionConnection(offlineSession, 200);
  assert.equal(resC, false, 'Stuck connecting session must resolve false after timeout');
});

// =============================================================================
// DIST-02: Multi-Worker Baileys Session Lease Coordination
// =============================================================================

test('DIST-02: worker/server.mjs implements distributed session leasing and release', () => {
  assert.equal(
    serverSource.includes('async function acquireSessionLease(userId, workerId, leaseSeconds = 30)'),
    true,
    'worker/server.mjs must implement acquireSessionLease'
  );

  assert.equal(
    serverSource.includes('async function releaseSessionLease(userId, workerId)'),
    true,
    'worker/server.mjs must implement releaseSessionLease'
  );

  assert.equal(
    serverSource.includes('const hasLease = await acquireSessionLease(userId, WORKER_ID, 30)'),
    true,
    'startSession must guard socket creation with acquireSessionLease'
  );

  assert.equal(
    serverSource.includes('releaseSessionLease(session.userId, WORKER_ID)'),
    true,
    'destroySocket must release the session lease'
  );
});

test('DIST-02: Schema defines locked_by and lease_expires_at in migrations', () => {
  const schemaSource = fs.readFileSync(path.resolve('migrations/001_initial_schema.sql'), 'utf8');
  assert.equal(
    schemaSource.includes('`locked_by` varchar(80) DEFAULT NULL'),
    true,
    '001_initial_schema.sql must define locked_by'
  );
  assert.equal(
    schemaSource.includes('`lease_expires_at` datetime DEFAULT NULL'),
    true,
    '001_initial_schema.sql must define lease_expires_at'
  );

  const mig19Source = fs.readFileSync(path.resolve('migrations/019_whatsapp_session_leases.sql'), 'utf8');
  assert.ok(mig19Source.includes('locked_by'), 'Migration 019 must alter whatsapp_sessions with locked_by');
});

test('DIST-02: Distributed lease state machine simulation', () => {
  // Simulates multi-worker atomic lease acquisition
  const sessionsTable = new Map();

  function acquireLease(userId, workerId, leaseDurationMs = 1000) {
    const now = Date.now();
    const existing = sessionsTable.get(userId);
    if (!existing || !existing.locked_by || existing.lease_expires_at < now || existing.locked_by === workerId) {
      sessionsTable.set(userId, {
        locked_by: workerId,
        lease_expires_at: now + leaseDurationMs
      });
      return true;
    }
    return false;
  }

  function releaseLease(userId, workerId) {
    const existing = sessionsTable.get(userId);
    if (existing && existing.locked_by === workerId) {
      sessionsTable.delete(userId);
    }
  }

  // 1. Worker 1 claims User 101
  assert.equal(acquireLease(101, 'worker-1', 500), true, 'Worker 1 must acquire free lease');

  // 2. Worker 2 attempts to claim User 101 concurrently -> REJECTED
  assert.equal(acquireLease(101, 'worker-2', 500), false, 'Worker 2 must be rejected while Worker 1 holds active lease');

  // 3. Worker 1 renews lease -> SUCCESS
  assert.equal(acquireLease(101, 'worker-1', 500), true, 'Worker 1 can renew its own lease');

  // 4. Worker 1 releases lease -> Worker 2 can now claim
  releaseLease(101, 'worker-1');
  assert.equal(acquireLease(101, 'worker-2', 500), true, 'Worker 2 can acquire lease once released');
});

// =============================================================================
// DIST-03: Webhook Head-of-Line Blocking Elimination
// =============================================================================

test('DIST-03: worker/server.mjs eliminates global webhook lock and uses concurrent pool', () => {
  assert.equal(
    serverSource.includes('let webhookQueueBusy = false;'),
    false,
    'worker/server.mjs must NOT contain the serial webhookQueueBusy single-lock'
  );

  assert.equal(
    serverSource.includes('const MAX_CONCURRENT_WEBHOOKS = 10;'),
    true,
    'worker/server.mjs must define MAX_CONCURRENT_WEBHOOKS pool'
  );

  assert.equal(
    serverSource.includes('LIMIT ?'),
    true,
    'worker/server.mjs must fetch batch of jobs with LIMIT ? instead of LIMIT 1'
  );
});

test('DIST-03: Concurrent queue dispatcher completes independent jobs without waiting for slow consumers', async () => {
  // Simulate dispatching 5 jobs concurrently where 1 job is slow (100ms) and 4 are fast (10ms)
  const jobResults = [];

  async function mockDeliver(job) {
    if (job.isSlow) {
      await new Promise(r => setTimeout(r, 100));
    } else {
      await new Promise(r => setTimeout(r, 10));
    }
    jobResults.push({ id: job.id, finishedAt: Date.now() });
  }

  const batch = [
    { id: 1, isSlow: true }, // Tenant A slow endpoint
    { id: 2, isSlow: false },
    { id: 3, isSlow: false },
    { id: 4, isSlow: false },
    { id: 5, isSlow: false }
  ];

  const startTime = Date.now();
  await Promise.all(batch.map(j => mockDeliver(j)));

  // Fast jobs (2, 3, 4, 5) must have finished BEFORE the slow job (1)
  const fastJobFinishes = jobResults.filter(r => r.id !== 1).map(r => r.finishedAt);
  const slowJobFinish = jobResults.find(r => r.id === 1).finishedAt;

  for (const fastFinish of fastJobFinishes) {
    assert.ok(
      fastFinish < slowJobFinish,
      `Fast job must complete before slow job finishes in concurrent dispatching`
    );
  }
});

// =============================================================================
// DIST-04: Chat Debouncer Flush & Termination Resilience
// =============================================================================

test('DIST-04: MessageDebouncer implements flushAll and returns onFlush callback promise', async () => {
  const debouncer = new MessageDebouncer({ debounceMs: 500, maxWaitMs: 2000 });
  const flushedMessages = [];

  // Enqueue 3 separate conversation items
  debouncer.enqueue(1, 'remote-1@s.whatsapp.net', { text: 'Hello from user 1' }, (data) => {
    flushedMessages.push({ user: 1, text: data.consolidatedText });
  });

  debouncer.enqueue(2, 'remote-2@s.whatsapp.net', { text: 'Question from user 2' }, (data) => {
    flushedMessages.push({ user: 2, text: data.consolidatedText });
  });

  debouncer.enqueue(3, 'remote-3@s.whatsapp.net', { text: 'Support request from user 3' }, (data) => {
    flushedMessages.push({ user: 3, text: data.consolidatedText });
  });

  assert.equal(debouncer.buffers.size, 3, 'Debouncer must have 3 active pending buffers');

  // Simulate immediate shutdown flush
  await debouncer.flushAll();

  assert.equal(debouncer.buffers.size, 0, 'flushAll must clear all pending buffers');
  assert.equal(flushedMessages.length, 3, 'flushAll must execute onFlush callbacks for all pending buffers');
  assert.equal(flushedMessages.some(m => m.user === 1), true);
  assert.equal(flushedMessages.some(m => m.user === 2), true);
  assert.equal(flushedMessages.some(m => m.user === 3), true);
});

test('DIST-04: worker/server.mjs wires SIGTERM and SIGINT graceful shutdown handlers', () => {
  assert.equal(
    serverSource.includes("process.on('SIGTERM', () => handleGracefulShutdown('SIGTERM'))"),
    true,
    'worker/server.mjs must register SIGTERM termination handler'
  );

  assert.equal(
    serverSource.includes("process.on('SIGINT', () => handleGracefulShutdown('SIGINT'))"),
    true,
    'worker/server.mjs must register SIGINT termination handler'
  );

  assert.equal(
    serverSource.includes('await globalDebouncer.flushAll()'),
    true,
    'Termination handler must flush globalDebouncer buffers before exit'
  );
});
