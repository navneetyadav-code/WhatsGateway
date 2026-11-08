<?php

declare(strict_types=1);

/**
 * Performance, API & Operations Verification Suite (PERF-01 to OPS-01)
 *
 * Validates remediation of:
 * - PERF-01: OPcache bytecode caching preserved (opcache_reset() removed from public/index.php)
 * - PERF-02: Non-blocking async disk I/O in worker/server.mjs (fs/promises)
 * - API-01: Chat send API lifecycle correctness (queued status & schema enum)
 * - OPS-01: Lightweight health checks with SELECT 1 and cached schema validation
 */

require_once __DIR__ . '/../src/bootstrap.php';

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

$pdo = Database::pdo();

// -----------------------------------------------------------------------------
// PERF-01: OPcache bytecode caching preserved
// -----------------------------------------------------------------------------
run_test("PERF-01: public/index.php does not invoke opcache_reset()", function() {
    $indexPath = __DIR__ . '/../public/index.php';
    assert_true(file_exists($indexPath), "public/index.php must exist");
    $content = file_get_contents($indexPath);
    assert_true(
        !preg_match('/\bopcache_reset\s*\(/i', $content),
        "public/index.php must not call opcache_reset() on incoming requests"
    );
});

// -----------------------------------------------------------------------------
// PERF-02: Non-blocking async disk I/O in worker/server.mjs
// -----------------------------------------------------------------------------
run_test("PERF-02: worker/server.mjs imports fs/promises readFile and readdir", function() {
    $workerPath = __DIR__ . '/../worker/server.mjs';
    assert_true(file_exists($workerPath), "worker/server.mjs must exist");
    $content = file_get_contents($workerPath);
    assert_true(
        preg_match("/from\s+['\"](?:node:)?fs\/promises['\"]/", $content) === 1,
        "worker/server.mjs must import from fs/promises"
    );
    assert_true(
        str_contains($content, 'readFile') && str_contains($content, 'readdir'),
        "worker/server.mjs must import readFile and readdir functions"
    );
});

run_test("PERF-02: resolveJidToPhone in worker/server.mjs uses asynchronous disk I/O", function() {
    $workerPath = __DIR__ . '/../worker/server.mjs';
    $content = file_get_contents($workerPath);

    // Extract resolveJidToPhone function body
    $startPos = strpos($content, 'async function resolveJidToPhone');
    assert_true($startPos !== false, "resolveJidToPhone function must exist in worker/server.mjs");
    $endPos = strpos($content, 'function isPrivateIp', $startPos);
    assert_true($endPos !== false, "isPrivateIp marker found");
    $fnBody = substr($content, $startPos, $endPos - $startPos);

    assert_true(
        !str_contains($fnBody, 'readFileSync'),
        "resolveJidToPhone must not call synchronous readFileSync"
    );
    assert_true(
        !str_contains($fnBody, 'readdirSync'),
        "resolveJidToPhone must not call synchronous readdirSync"
    );
    assert_true(
        str_contains($fnBody, 'await readdir('),
        "resolveJidToPhone must use await readdir for directory traversal"
    );
    assert_true(
        str_contains($fnBody, 'await readFile('),
        "resolveJidToPhone must use await readFile for file reading"
    );
});

// -----------------------------------------------------------------------------
// API-01: Chat send API lifecycle correctness
// -----------------------------------------------------------------------------
run_test("API-01: chat_messages table schema supports 'queued' and 'pending' statuses", function() use ($pdo) {
    $stmt = $pdo->query("SHOW COLUMNS FROM chat_messages LIKE 'status'");
    $col = $stmt->fetch(\PDO::FETCH_ASSOC);
    assert_true(!empty($col), "chat_messages.status column must exist");
    $type = strtolower($col['Type'] ?? '');
    assert_true(str_contains($type, "'queued'"), "chat_messages.status enum must include 'queued'");
    assert_true(str_contains($type, "'pending'"), "chat_messages.status enum must include 'pending'");
    assert_true(str_contains($type, "'sent'"), "chat_messages.status enum must include 'sent'");
});

run_test("API-01: public/api/chat/send.php persists and reports status 'queued'", function() use ($pdo) {
    $sendPath = __DIR__ . '/../public/api/chat/send.php';
    assert_true(file_exists($sendPath), "public/api/chat/send.php must exist");
    $content = file_get_contents($sendPath);

    assert_true(
        str_contains($content, "'queued'"),
        "send.php must reference 'queued' status"
    );
    assert_true(
        str_contains($content, "'status' => 'queued'"),
        "send.php response payload must return 'status' => 'queued'"
    );

    // Find or create conversation for testing
    $convStmt = $pdo->query("SELECT id, user_id FROM chat_conversations LIMIT 1");
    $conv = $convStmt->fetch(\PDO::FETCH_ASSOC);
    $cleanupConv = false;
    if ($conv) {
        $convId = (int) $conv['id'];
        $userId = (int) $conv['user_id'];
    } else {
        $userStmt = $pdo->query("SELECT id FROM users LIMIT 1");
        $user = $userStmt->fetch(\PDO::FETCH_ASSOC);
        $userId = $user ? (int) $user['id'] : 1;
        $pdo->prepare("INSERT INTO chat_conversations (user_id, phone, contact_name) VALUES (?, '1234567890', 'Test')")->execute([$userId]);
        $convId = (int) $pdo->lastInsertId();
        $cleanupConv = true;
    }

    // Verify insert into chat_messages with 'queued' succeeds
    $testBody = 'Test API-01 ' . bin2hex(random_bytes(4));
    $stmt = $pdo->prepare("
        INSERT INTO chat_messages (user_id, conversation_id, sender_type, message_type, body, status)
        VALUES (?, ?, 'agent', 'text', ?, 'queued')
    ");
    $stmt->execute([$userId, $convId, $testBody]);
    $insertedId = (int) $pdo->lastInsertId();

    $checkStmt = $pdo->prepare("SELECT status FROM chat_messages WHERE id = ?");
    $checkStmt->execute([$insertedId]);
    $row = $checkStmt->fetch(\PDO::FETCH_ASSOC);
    assert_equals('queued', $row['status'] ?? null, "Inserted row status should be 'queued'");

    // Cleanup
    $pdo->prepare("DELETE FROM chat_messages WHERE id = ?")->execute([$insertedId]);
    if ($cleanupConv) {
        $pdo->prepare("DELETE FROM chat_conversations WHERE id = ?")->execute([$convId]);
    }
});

// -----------------------------------------------------------------------------
// OPS-01: Heavy health checks optimized with SELECT 1 and cached schema
// -----------------------------------------------------------------------------
run_test("OPS-01: public/api/health.php uses lightweight SELECT 1 ping", function() {
    $healthPath = __DIR__ . '/../public/api/health.php';
    assert_true(file_exists($healthPath), "public/api/health.php must exist");
    $content = file_get_contents($healthPath);

    assert_true(
        str_contains($content, "SELECT 1"),
        "health.php must execute lightweight 'SELECT 1' for database connectivity check"
    );
    assert_true(
        str_contains($content, "whats_schema_health.json"),
        "health.php must cache schema verification results"
    );
});

run_test("OPS-01: health check execution succeeds and uses schema caching", function() {
    $schemaCacheFile = sys_get_temp_dir() . '/whats_schema_health.json';
    if (file_exists($schemaCacheFile)) {
        @unlink($schemaCacheFile);
    }

    // Execute health endpoint in separate process via CLI
    $output = shell_exec('php ' . escapeshellarg(__DIR__ . '/../public/api/health.php'));
    assert_true(!empty($output), "Health endpoint must produce output");
    $json = json_decode($output, true);
    assert_true(is_array($json), "Health endpoint must return valid JSON: " . substr($output, 0, 100));

    // Database check must be fully functional and connected
    assert_true($json['database']['connected'] === true, "Database must be reported as connected");
    assert_true($json['database']['ok'] === true, "Database schema check must be ok");
    assert_true($json['database']['schema_ready'] === true, "Database schema must be ready");
    assert_true(empty($json['database']['missing_tables']), "Database must not report missing tables");

    // Verify cache file was created
    assert_true(file_exists($schemaCacheFile), "Schema cache file must be written after healthy check");

    // Second execution should read from cache
    $secondOutput = shell_exec('php ' . escapeshellarg(__DIR__ . '/../public/api/health.php'));
    $secondJson = json_decode($secondOutput, true);
    assert_true($secondJson['database']['ok'] === true, "Cached database check must remain ok");
});

// -----------------------------------------------------------------------------
// Summary
// -----------------------------------------------------------------------------
echo "\n============================================\n";
echo "PERF, API & OPS TEST RESULTS:\n";
echo "Total Tests: $testCount\n";
echo "Passed: " . ($testCount - $failCount) . "\n";
echo "Failed: $failCount\n";
echo "============================================\n";

if ($failCount > 0) {
    exit(1);
}
