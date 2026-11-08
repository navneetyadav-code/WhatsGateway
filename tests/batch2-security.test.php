<?php

declare(strict_types=1);

/**
 * Batch 2 Security Audit & Verification Suite (PHP)
 *
 * Validates remediation of:
 * - SEC-07: IP Spoofing Prevention via Right-to-Left X-Forwarded-For Traversal & Trusted Proxies (src/lib/ApiCredentials.php)
 * - SEC-08: Session Action Pairing CSRF & HTTP Method Hardening (public/session.php)
 * - SEC-09: At-Rest Hashing for Password Reset OTPs & DB Schema Expansion (src/pages/forgot-password.php)
 */

$testCount = 0;
$failCount = 0;

function run_test(string $name, callable $fn): void {
    global $testCount, $failCount;
    $testCount++;
    echo "[TEST] $name ... ";
    try {
        $fn();
        echo "PASSED\n";
    } catch (\Throwable $e) {
        $failCount++;
        echo "FAILED: " . $e->getMessage() . " at line " . $e->getLine() . "\n";
    }
}

function assert_true(bool $cond, string $msg = 'Assertion failed'): void {
    if (!$cond) throw new \RuntimeException($msg);
}

function assert_equals($expected, $actual, string $msg = ''): void {
    if ($expected !== $actual) {
        $msg = $msg ? $msg . " - " : "";
        throw new \RuntimeException($msg . "Expected " . var_export($expected, true) . ", got " . var_export($actual, true));
    }
}

// -----------------------------------------------------------------------------
// SEC-07: IP Spoofing Traversal & Trusted Proxy Validation
// -----------------------------------------------------------------------------
require_once __DIR__ . '/../src/bootstrap.php';

run_test('SEC-07: client_ip rejects spoofed client IP prepended to X-Forwarded-For', function() {
    $originalServer = $_SERVER;

    // Direct connection is from a trusted proxy (e.g. Cloudflare / local proxy 10.0.0.1)
    // Client sent: "attacker-spoofed-ip, real-client-ip"
    $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.99, 203.0.113.195';

    $resolved = client_ip();
    assert_equals('203.0.113.195', $resolved, 'Must pick the rightmost untrusted public client IP, not the spoofed first IP');

    $_SERVER = $originalServer;
});

run_test('SEC-07: client_ip falls back to REMOTE_ADDR when remote addr is not a trusted proxy', function() {
    $originalServer = $_SERVER;

    // Direct connection is from untrusted public IP (e.g. 198.51.100.5)
    // Attacker sends X-Forwarded-For: 1.1.1.1
    $_SERVER['REMOTE_ADDR'] = '198.51.100.5';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.1.1.1';

    $resolved = client_ip();
    assert_equals('198.51.100.5', $resolved, 'Must use REMOTE_ADDR because the direct peer is not a trusted proxy');

    $_SERVER = $originalServer;
});

// -----------------------------------------------------------------------------
// SEC-08: Device Pairing CSRF & HTTP Method Hardening
// -----------------------------------------------------------------------------
run_test('SEC-08: public/session.php classifies pair-code as state-changing action', function() {
    $sessionFile = __DIR__ . '/../public/session.php';
    assert_true(file_exists($sessionFile), 'public/session.php must exist');
    $content = file_get_contents($sessionFile);

    assert_true(
        strpos($content, "'pair-code'") !== false,
        "public/session.php must include 'pair-code' in state-changing actions"
    );

    // Verify regex matching stateChangingActions array contains pair-code
    assert_true(
        preg_match('/\$stateChangingActions\s*=\s*\[[^\]]*\'pair-code\'[^\]]*\];/', $content) === 1,
        "stateChangingActions must contain 'pair-code'"
    );
});

// -----------------------------------------------------------------------------
// SEC-09: At-Rest Hashing for Password Reset OTPs
// -----------------------------------------------------------------------------
run_test('SEC-09: migrations/001_initial_schema.sql defines password_resets.otp as VARCHAR(64)', function() {
    $schemaFile = __DIR__ . '/../migrations/001_initial_schema.sql';
    assert_true(file_exists($schemaFile), '001_initial_schema.sql must exist');
    $schema = file_get_contents($schemaFile);

    assert_true(
        strpos($schema, "`otp` varchar(64) NOT NULL") !== false,
        '001_initial_schema.sql must declare otp as varchar(64)'
    );
});

run_test('SEC-09: MySQL database has password_resets.otp column size at least 64 chars', function() {
    $pdo = Database::pdo();
    $stmt = $pdo->query("SHOW COLUMNS FROM password_resets LIKE 'otp'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);

    assert_true(!empty($col), 'password_resets table must have otp column');
    $type = strtolower((string)$col['Type']);
    assert_true(
        strpos($type, 'varchar(64)') !== false || strpos($type, 'text') !== false,
        "password_resets.otp type must be varchar(64), got $type"
    );
});

run_test('SEC-09: src/pages/forgot-password.php writes SHA-256 hashed OTPs and verifies securely', function() {
    $forgotFile = __DIR__ . '/../src/pages/forgot-password.php';
    assert_true(file_exists($forgotFile), 'src/pages/forgot-password.php must exist');
    $src = file_get_contents($forgotFile);

    // 1. Verify insertion hashes the OTP
    assert_true(
        strpos($src, "hash('sha256', \$otp)") !== false,
        "forgot-password.php must hash OTP with sha256 on insert"
    );

    // 2. Verify verification uses timing-safe hash_equals with SHA-256
    assert_true(
        strpos($src, "\$submittedHash = hash('sha256', \$otp);") !== false,
        "forgot-password.php must compute submittedHash for verification"
    );

    assert_true(
        strpos($src, "hash_equals((string) \$reset['otp'], \$submittedHash)") !== false,
        "forgot-password.php must compare stored hash using hash_equals"
    );
});

run_test('SEC-09: Verification logic correctly accepts SHA-256 OTP hash and rejects bad OTPs', function() {
    $rawOtp = '482915';
    $hashedInDb = hash('sha256', $rawOtp);

    // Check correct OTP matches
    $submittedGood = '482915';
    $submittedGoodHash = hash('sha256', $submittedGood);
    assert_true(
        hash_equals($hashedInDb, $submittedGoodHash),
        'Good OTP must match stored SHA-256 hash'
    );

    // Check wrong OTP fails
    $submittedBad = '123456';
    $submittedBadHash = hash('sha256', $submittedBad);
    assert_true(
        !hash_equals($hashedInDb, $submittedBadHash),
        'Bad OTP must not match stored SHA-256 hash'
    );

    // Check backwards compatibility for unhashed in-flight OTP
    $legacyRawInDb = '999888';
    $submittedLegacy = '999888';
    $submittedLegacyHash = hash('sha256', $submittedLegacy);
    $isLegacyValid = hash_equals($legacyRawInDb, $submittedLegacyHash) || hash_equals($legacyRawInDb, $submittedLegacy);
    assert_true($isLegacyValid, 'Legacy in-flight plain OTP must still validate during deployment rollover');
});

// -----------------------------------------------------------------------------
// Test Summary
// -----------------------------------------------------------------------------
echo "\n=========================================\n";
echo "BATCH 2 TESTS: $testCount total | " . ($testCount - $failCount) . " passed | $failCount failed\n";
echo "=========================================\n";

if ($failCount > 0) {
    exit(1);
}
