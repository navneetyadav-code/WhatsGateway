import { test, describe, before, after } from 'node:test';
import assert from 'node:assert/strict';
import http from 'node:http';
import crypto from 'node:crypto';
import { spawnSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import {
  isAntiBotChallenge,
  solveAntiBotChallenge,
  injectOrReplaceTestCookie,
  resolveProxyCookie,
  sharedCookieJar,
  executeCustomRestApi,
  executeSharedEnvApi,
  BROWSER_USER_AGENT
} from '../worker/tools-engine.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT_DIR = path.resolve(__dirname, '..');

describe('Free Shared Hosting Anti-Bot Bypass & Autonomous 24h Rotation Suite', () => {

  // Generate deterministic test challenge vectors
  const testKey = crypto.randomBytes(16);
  const testIv = crypto.randomBytes(16);
  const testExpectedCookie = crypto.randomBytes(16).toString('hex');

  const cipher = crypto.createCipheriv('aes-128-cbc', testKey, testIv);
  cipher.setAutoPadding(false);
  const testCiphertext = Buffer.concat([cipher.update(Buffer.from(testExpectedCookie, 'hex')), cipher.final()]);

  const sampleChallengeHtml = `
    <html>
      <head>
        <script type="text/javascript" src="/aes.js"></script>
        <script>
          function toNumbers(d){var e=[];d.replace(/(..)/g,function(d){e.push(parseInt(d,16))});return e}
          var a=toNumbers("${testKey.toString('hex')}"),
              b=toNumbers("${testIv.toString('hex')}"),
              c=toNumbers("${testCiphertext.toString('hex')}");
          document.cookie="__test="+toHex(slowAES.decrypt(c,2,a,b))+"; expires=Thu, 31-Dec-37 23:55:55 GMT; path=/";
          location.href="https://freehost.example.com/api.php?i=1";
        </script>
      </head>
      <body>Please wait while your request is processed...</body>
    </html>
  `;

  test('1. Challenge Detection in Node.js & PHP', () => {
    // 1.1 Node.js detection
    assert.equal(isAntiBotChallenge(sampleChallengeHtml), true, 'Node.js should identify aes.js challenge HTML');
    assert.equal(isAntiBotChallenge('{"status":"ok","items":[]}'), false, 'Normal JSON should not be flagged as challenge');
    assert.equal(isAntiBotChallenge('<html><body>Welcome to my website</body></html>'), false, 'Standard HTML without toNumbers should not be flagged');

    // Variations: single quotes and varying whitespace
    const variantHtml = `var a = toNumbers('${testKey.toString('hex')}'), b = toNumbers('${testIv.toString('hex')}'), c = toNumbers('${testCiphertext.toString('hex')}');`;
    assert.equal(isAntiBotChallenge(variantHtml), true, 'Node.js should identify single-quote formatted challenge');

    // 1.2 PHP detection via AntiBotBypass::isChallenge()
    const phpCheckScript = `
      require_once 'src/lib/AntiBotBypass.php';
      $html = ${JSON.stringify(sampleChallengeHtml)};
      $isCh = AntiBotBypass::isChallenge($html);
      $isNotCh = AntiBotBypass::isChallenge('{"status":"ok"}');
      echo json_encode(['challenge' => $isCh, 'nonChallenge' => $isNotCh]);
    `;
    const proc = spawnSync('php', ['-r', phpCheckScript], { cwd: ROOT_DIR, encoding: 'utf8' });
    assert.equal(proc.status, 0, `PHP run error: ${proc.stderr}`);
    const phpOutput = JSON.parse(proc.stdout.trim());
    assert.equal(phpOutput.challenge, true, 'PHP AntiBotBypass should detect challenge HTML');
    assert.equal(phpOutput.nonChallenge, false, 'PHP AntiBotBypass should ignore normal JSON');
  });

  test('2. Cryptographic AES-128-CBC Challenge Solving (Node.js & PHP parity)', () => {
    // 2.1 Node.js solver
    const nodeSolved = solveAntiBotChallenge(sampleChallengeHtml);
    assert.equal(nodeSolved, testExpectedCookie, 'Node.js solver should accurately decrypt the __test cookie value');

    // 2.2 PHP solver via AntiBotBypass::solveChallenge()
    const phpSolveScript = `
      require_once 'src/lib/AntiBotBypass.php';
      $html = ${JSON.stringify(sampleChallengeHtml)};
      $solved = AntiBotBypass::solveChallenge($html);
      echo json_encode(['solved' => $solved]);
    `;
    const proc = spawnSync('php', ['-r', phpSolveScript], { cwd: ROOT_DIR, encoding: 'utf8' });
    assert.equal(proc.status, 0, `PHP run error: ${proc.stderr}`);
    const phpSolveOutput = JSON.parse(proc.stdout.trim());
    assert.equal(phpSolveOutput.solved, testExpectedCookie, 'PHP AntiBotBypass solver should produce identical decrypted hex');
    assert.equal(nodeSolved, phpSolveOutput.solved, 'Cross-platform parity between Node.js and PHP challenge solvers');
  });

  test('3. Clean Cookie Header Injection & Replacement (Zero duplicate __test headers)', () => {
    // 3.1 Node.js injection into fresh headers
    const headers1 = { 'Accept': 'application/json' };
    const res1 = injectOrReplaceTestCookie(headers1, 'abc123');
    assert.equal(res1['Cookie'], '__test=abc123');

    // 3.2 Node.js replacement of existing __test cookie
    const headers2 = { 'Cookie': 'PHPSESSID=session999; __test=oldcookie; other=1' };
    const res2 = injectOrReplaceTestCookie(headers2, 'newcookie');
    assert.equal(res2['Cookie'], 'PHPSESSID=session999; __test=newcookie; other=1', 'Should replace __test in place without altering other cookies');

    // 3.3 Node.js appending to existing cookie header lacking __test
    const headers3 = { 'Cookie': 'auth_token=xyz' };
    const res3 = injectOrReplaceTestCookie(headers3, 'val99');
    assert.equal(res3['Cookie'], 'auth_token=xyz; __test=val99');

    // 3.4 PHP AntiBotBypass::injectOrReplaceCookie()
    const phpCookieScript = `
      require_once 'src/lib/AntiBotBypass.php';
      $c1 = AntiBotBypass::injectOrReplaceCookie('', 'val1');
      $c2 = AntiBotBypass::injectOrReplaceCookie('__test=oldval', 'val2');
      $c3 = AntiBotBypass::injectOrReplaceCookie('user=admin; __test=old; role=dev', 'val3');
      echo json_encode(['c1' => $c1, 'c2' => $c2, 'c3' => $c3]);
    `;
    const proc = spawnSync('php', ['-r', phpCookieScript], { cwd: ROOT_DIR, encoding: 'utf8' });
    assert.equal(proc.status, 0, `PHP run error: ${proc.stderr}`);
    const phpCookieOutput = JSON.parse(proc.stdout.trim());
    assert.equal(phpCookieOutput.c1, '__test=val1');
    assert.equal(phpCookieOutput.c2, '__test=val2');
    assert.equal(phpCookieOutput.c3, 'user=admin; __test=val3; role=dev');
  });

  describe('4. Autonomous Free Hosting Mock HTTP Simulation & Auto-Retry', () => {
    let mockServer;
    let mockPort;
    let mockHostUrl;
    let activationCount = 0;
    let challengeIssuedCount = 0;
    let successfulCallsCount = 0;
    let currentValidCookie = testExpectedCookie;

    before(async () => {
      await new Promise((resolve) => {
        mockServer = http.createServer((req, res) => {
          const urlObj = new URL(req.url, `http://${req.headers.host}`);
          const cookies = req.headers['cookie'] || '';
          const hasValidCookie = cookies.includes(`__test=${currentValidCookie}`);

          // Activation ping (host?i=1 or endpoint?i=1)
          if (urlObj.searchParams.get('i') === '1') {
            activationCount++;
            res.writeHead(200, { 'Content-Type': 'text/html' });
            res.end('<html><body>Activated</body></html>');
            return;
          }

          // If client does not present the current valid cookie, issue anti-bot challenge
          if (!hasValidCookie) {
            challengeIssuedCount++;
            res.writeHead(200, { 'Content-Type': 'text/html' });
            res.end(sampleChallengeHtml);
            return;
          }

          // Otherwise return the desired API response
          successfulCallsCount++;
          res.writeHead(200, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({
            ok: true,
            status: 'success',
            endpoint: urlObj.pathname,
            items: [{ id: 101, name: 'Free Tier Item' }]
          }));
        });

        mockServer.listen(0, '127.0.0.1', () => {
          mockPort = mockServer.address().port;
          mockHostUrl = `http://127.0.0.1:${mockPort}`;
          resolve();
        });
      });
    });

    after(async () => {
      if (mockServer) {
        await new Promise((resolve) => mockServer.close(resolve));
      }
    });

    test('4.1 executeCustomRestApi automatically solves challenge, activates session, and returns data', async () => {
      // Clear cookie jar for clean state
      sharedCookieJar.clear();
      activationCount = 0;
      challengeIssuedCount = 0;
      successfulCallsCount = 0;

      const config = {
        method: 'GET',
        url: `${mockHostUrl}/api/inventory`,
        headers: {
          'Accept': 'application/json'
        },
        __allow_test_mock: true
      };

      const result = await executeCustomRestApi(config);

      assert.equal(result.success, true, 'Execution should succeed transparently');
      assert.equal(result.status, 200, 'HTTP status should be 200');
      assert.equal(result.data.status, 'success');
      assert.equal(result.data.items[0].name, 'Free Tier Item');
      assert.ok(challengeIssuedCount >= 1, 'Mock server should have issued at least 1 challenge');
      assert.ok(activationCount >= 1, 'Engine should have triggered ?i=1 activation');
      assert.ok(successfulCallsCount >= 1, 'API should have succeeded on retry');

      // Verify the cookie is now cached in sharedCookieJar
      const cached = sharedCookieJar.get('127.0.0.1');
      assert.ok(cached, 'Cookie jar should contain entry for 127.0.0.1');
      assert.equal(cached.cookie, currentValidCookie);
    });

    test('4.2 24-Hour Daily Salt Rotation Resilience: Automatically updates stale cookie on remote challenge', async () => {
      // Simulate that the host rotated its secret 24h salt (new cookie required)
      const newExpectedCookie = crypto.randomBytes(16).toString('hex');
      const cipher2 = crypto.createCipheriv('aes-128-cbc', testKey, testIv);
      cipher2.setAutoPadding(false);
      const newCiphertext = Buffer.concat([cipher2.update(Buffer.from(newExpectedCookie, 'hex')), cipher2.final()]);

      // Update mock server's expected cookie
      currentValidCookie = newExpectedCookie;

      // Update mock server challenge with new ciphertext
      const newChallengeHtml = `
        <script type="text/javascript" src="/aes.js"></script>
        <script>
          var a=toNumbers("${testKey.toString('hex')}"),
              b=toNumbers("${testIv.toString('hex')}"),
              c=toNumbers("${newCiphertext.toString('hex')}");
        </script>
      `;

      // Temporarily overwrite mockServer behavior for challenges
      mockServer.removeAllListeners('request');
      mockServer.on('request', (req, res) => {
        const urlObj = new URL(req.url, `http://${req.headers.host}`);
        const cookies = req.headers['cookie'] || '';

        if (urlObj.searchParams.get('i') === '1') {
          res.writeHead(200, { 'Content-Type': 'text/html' });
          res.end('Activated');
          return;
        }

        if (!cookies.includes(`__test=${newExpectedCookie}`)) {
          res.writeHead(200, { 'Content-Type': 'text/html' });
          res.end(newChallengeHtml);
          return;
        }

        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: true, saltRotated: true }));
      });

      // The cookie jar still holds the OLD expired cookie from test 4.1
      assert.notEqual(sharedCookieJar.get('127.0.0.1').cookie, newExpectedCookie);

      const config = {
        method: 'GET',
        url: `${mockHostUrl}/api/status`,
        __allow_test_mock: true
      };

      const result = await executeCustomRestApi(config);
      assert.equal(result.success, true);
      assert.equal(result.data.saltRotated, true, 'Engine must recover from salt rotation challenge');

      // Check that cookie jar now contains the NEW cookie
      assert.equal(sharedCookieJar.get('127.0.0.1').cookie, newExpectedCookie, 'Cookie jar must store fresh cookie after salt rotation');
    });

    test('4.3 executeSharedEnvApi handles anti-bot challenge and retries cleanly', async () => {
      const config = {
        base_url: mockHostUrl,
        bearer_token: 'test_token',
        __allow_test_mock: true,
        action_routes: {
          test_action: { path: '/api/action', method: 'GET' }
        }
      };

      const result = await executeSharedEnvApi(config, { action: 'test_action' });
      assert.equal(result.success, true);
      assert.equal(result.environment, 'production');
    });
  });

  test('5. CurlImporter provisions Browser Persona & Anti-Bot Compatible Headers', () => {
    const rawCurl = 'curl -X POST "https://myfreehost.epizy.com/api.php" -d "action=check&device_id=99"';
    const phpImportScript = `
      require_once 'src/lib/AntiBotBypass.php';
      require_once 'src/lib/CurlImporter.php';
      $parsed = CurlImporter::parse(${JSON.stringify(rawCurl)});
      echo json_encode($parsed);
    `;
    const proc = spawnSync('php', ['-r', phpImportScript], { cwd: ROOT_DIR, encoding: 'utf8' });
    assert.equal(proc.status, 0, `PHP run error: ${proc.stderr}`);
    const parsed = JSON.parse(proc.stdout.trim());

    assert.equal(parsed.category, 'rest_api');
    assert.ok(parsed.execution_config.headers['User-Agent'], 'Should contain a User-Agent header');
    assert.equal(parsed.execution_config.headers['User-Agent'], BROWSER_USER_AGENT, 'User-Agent must match BROWSER_USER_AGENT');
    assert.ok(parsed.execution_config.headers['Accept'].includes('application/json'), 'Accept header must accept JSON');
  });

});
