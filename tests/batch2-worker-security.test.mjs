import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';

/**
 * Batch 2 Security Audit & Verification Suite (Node.js)
 *
 * Validates remediation of:
 * - SEC-10: Restriction of Shared Server AI Key Quota in Multi-Tenant Environments (worker/server.mjs)
 */

test('SEC-10: worker/server.mjs gates environment AI keys behind canUseGlobalAiFallback', () => {
  const serverPath = path.resolve('worker/server.mjs');
  const serverSource = fs.readFileSync(serverPath, 'utf8');

  // Verify that SEC-10 comment and logic are present in worker/server.mjs
  assert.equal(
    serverSource.includes('SEC-10: Restrict shared server AI credentials'),
    true,
    'worker/server.mjs must contain SEC-10 hardening comment'
  );
  assert.equal(
    serverSource.includes('const canUseGlobalAiFallback = process.env.ALLOW_GLOBAL_AI_FALLBACK === \'true\' || Number(userId) === 1;'),
    true,
    'worker/server.mjs must restrict fallback to admin (userId === 1) or ALLOW_GLOBAL_AI_FALLBACK'
  );

  // Verify all 3 environment variables are guarded by canUseGlobalAiFallback
  assert.equal(
    serverSource.includes('if (canUseGlobalAiFallback && !aiSettings.gemini_api_key && process.env.GEMINI_API_KEY)'),
    true,
    'GEMINI_API_KEY fallback must be guarded by canUseGlobalAiFallback'
  );
  assert.equal(
    serverSource.includes('if (canUseGlobalAiFallback && !aiSettings.groq_api_key && process.env.GROQ_API_KEY)'),
    true,
    'GROQ_API_KEY fallback must be guarded by canUseGlobalAiFallback'
  );
  assert.equal(
    serverSource.includes('if (canUseGlobalAiFallback && !aiSettings.openrouter_api_key && process.env.OPENROUTER_API_KEY)'),
    true,
    'OPENROUTER_API_KEY fallback must be guarded by canUseGlobalAiFallback'
  );
});

test('SEC-10: AI global key fallback resolution unit tests', () => {
  function resolveFallbackKeys({ userId, userSettings, env }) {
    const aiSettings = { ...userSettings };
    const canUseGlobalAiFallback = env.ALLOW_GLOBAL_AI_FALLBACK === 'true' || Number(userId) === 1;

    if (canUseGlobalAiFallback && !aiSettings.gemini_api_key && env.GEMINI_API_KEY) {
      aiSettings.gemini_api_key = env.GEMINI_API_KEY;
    }
    if (canUseGlobalAiFallback && !aiSettings.groq_api_key && env.GROQ_API_KEY) {
      aiSettings.groq_api_key = env.GROQ_API_KEY;
    }
    if (canUseGlobalAiFallback && !aiSettings.openrouter_api_key && env.OPENROUTER_API_KEY) {
      aiSettings.openrouter_api_key = env.OPENROUTER_API_KEY;
    }

    return aiSettings;
  }

  const hostEnv = {
    GEMINI_API_KEY: 'host-secret-gemini-key',
    GROQ_API_KEY: 'host-secret-groq-key',
    OPENROUTER_API_KEY: 'host-secret-openrouter-key'
  };

  // 1. Instance owner / Admin (userId === 1): Has access to host fallback keys when no personal key is configured
  const adminResolved = resolveFallbackKeys({
    userId: 1,
    userSettings: {},
    env: { ...hostEnv }
  });
  assert.equal(adminResolved.gemini_api_key, 'host-secret-gemini-key');
  assert.equal(adminResolved.groq_api_key, 'host-secret-groq-key');
  assert.equal(adminResolved.openrouter_api_key, 'host-secret-openrouter-key');

  // 2. Non-admin Tenant (userId === 2): MUST NOT get host keys by default (quota theft prevention)
  const tenantResolved = resolveFallbackKeys({
    userId: 2,
    userSettings: {},
    env: { ...hostEnv }
  });
  assert.equal(tenantResolved.gemini_api_key, undefined, 'Tenant must not receive host GEMINI_API_KEY');
  assert.equal(tenantResolved.groq_api_key, undefined, 'Tenant must not receive host GROQ_API_KEY');
  assert.equal(tenantResolved.openrouter_api_key, undefined, 'Tenant must not receive host OPENROUTER_API_KEY');

  // 3. Non-admin Tenant with their own configured key: Keeps their own key
  const tenantWithOwnKey = resolveFallbackKeys({
    userId: 5,
    userSettings: { gemini_api_key: 'tenant-own-key' },
    env: { ...hostEnv }
  });
  assert.equal(tenantWithOwnKey.gemini_api_key, 'tenant-own-key');
  assert.equal(tenantWithOwnKey.groq_api_key, undefined);

  // 4. Host explicitly opts into global sharing via ALLOW_GLOBAL_AI_FALLBACK=true (e.g. dedicated single-user deploy)
  const sharedResolved = resolveFallbackKeys({
    userId: 42,
    userSettings: {},
    env: { ...hostEnv, ALLOW_GLOBAL_AI_FALLBACK: 'true' }
  });
  assert.equal(sharedResolved.gemini_api_key, 'host-secret-gemini-key');
  assert.equal(sharedResolved.groq_api_key, 'host-secret-groq-key');
  assert.equal(sharedResolved.openrouter_api_key, 'host-secret-openrouter-key');
});
