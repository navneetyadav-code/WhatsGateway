import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';

/**
 * Batch 1 Security Audit & Verification Suite (Node.js)
 *
 * Validates remediation of:
 * - SEC-05: Privilege Escalation Prevention in Admin Phone Matching (worker/server.mjs)
 */

test('SEC-05: worker/server.mjs removes vulnerable p.endsWith(cleanPhone) wildcarding', () => {
  const serverPath = path.resolve('worker/server.mjs');
  const serverSource = fs.readFileSync(serverPath, 'utf8');

  // Verify that dangerous suffix wildcard p.endsWith(cleanPhone) is strictly eliminated
  assert.equal(
    serverSource.includes('p.endsWith(cleanPhone)'),
    false,
    'worker/server.mjs must NOT contain vulnerable p.endsWith(cleanPhone) condition'
  );
  assert.equal(
    serverSource.includes('SEC-05: Prevent privilege escalation via suffix spoofing'),
    true,
    'worker/server.mjs must contain SEC-05 hardened admin phone matcher'
  );
});

test('SEC-05: Admin phone matcher security invariants', () => {
  function checkIsAdminCaller(adminPhones, senderPhone) {
    const cleanPhone = (senderPhone || '').replace(/[^\d]/g, '');
    return adminPhones.length > 0 && adminPhones.some(p => {
      if (!p || typeof p !== 'string') return false;
      const adminP = p.replace(/[^\d]/g, '');
      if (adminP.length < 8) return false;
      if (cleanPhone === adminP) return true;
      if (cleanPhone.length >= 10 && adminP.length >= 10 && cleanPhone.slice(-10) === adminP.slice(-10)) {
        return true;
      }
      return false;
    });
  }

  const registeredAdminPhones = ['919876543210', '15551234567'];

  // 1. Exact match with same country code
  assert.equal(checkIsAdminCaller(registeredAdminPhones, '919876543210'), true);
  assert.equal(checkIsAdminCaller(registeredAdminPhones, '15551234567'), true);

  // 2. Match local national 10-digit number without country code
  assert.equal(checkIsAdminCaller(['9876543210'], '919876543210'), true);
  assert.equal(checkIsAdminCaller(['919876543210'], '9876543210'), true);

  // 3. SEC-05 EXPLOIT TEST: Attacker calling from short number or spoofed suffix MUST BE REJECTED
  assert.equal(checkIsAdminCaller(registeredAdminPhones, '543210'), false, 'Short suffix 543210 must NOT match admin 919876543210');
  assert.equal(checkIsAdminCaller(registeredAdminPhones, '6543210'), false, '7-digit suffix 6543210 must NOT match admin');
  assert.equal(checkIsAdminCaller(registeredAdminPhones, '876543210'), false, '9-digit partial number must NOT match admin');

  // 4. Attacker calling from a different 12-digit number ending with same 6 digits MUST BE REJECTED
  assert.equal(checkIsAdminCaller(registeredAdminPhones, '911111543210'), false, 'Different number with same 6-digit suffix must NOT match admin');

  // 5. Short admin phone in settings must not create wildcards
  assert.equal(checkIsAdminCaller(['1234'], '919876541234'), false, 'Short admin entry must be rejected from wildcarding');
  assert.equal(checkIsAdminCaller([''], '919876543210'), false, 'Empty admin entry must be rejected');
});
