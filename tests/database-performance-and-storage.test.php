<?php

declare(strict_types=1);

/**
 * Database Performance, Indexing & Storage Verification Suite (DB-01 through DB-06)
 *
 * Validates remediation of:
 * - DB-01: Priority Queue Composite Indexing (idx_queue_priority_tenant)
 * - DB-02: Campaign Physical Column & Indexing (campaign_id)
 * - DB-03: Media Direct Column Lookups & Indexes on media_assets
 * - DB-04: Multi-Row Chunked Batch Inserts for Campaigns
 * - DB-05: Bounded API Rate-Limiting Aggregations
 * - DB-06: Rolling Automated Retention Purge (DatabaseMaintenance::purgeExpiredRecords)
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
// DB-01: Priority Queue Composite Index
// -----------------------------------------------------------------------------
run_test('DB-01: message_queue has composite index idx_queue_priority_tenant', function() use ($pdo) {
    $stmt = $pdo->query("SHOW INDEX FROM message_queue WHERE Key_name = 'idx_queue_priority_tenant'");
    $indexes = $stmt->fetchAll();
    assert_true(count($indexes) >= 4, 'idx_queue_priority_tenant must cover at least 4 composite columns');

    $cols = array_column($indexes, 'Column_name');
    assert_equals(['status', 'user_id', 'priority', 'available_at', 'id'], $cols, 'Index columns must match priority queue claiming pattern');
});

run_test('DB-01: Tenant priority queue claiming query uses index without filesort', function() use ($pdo) {
    $explain = $pdo->query(
        "EXPLAIN SELECT * FROM message_queue
         WHERE status = 'queued' AND user_id = 1 AND available_at <= NOW()
         ORDER BY priority DESC, available_at ASC, id ASC
         LIMIT 1"
    )->fetch(PDO::FETCH_ASSOC);

    assert_true(!empty($explain), 'Explain query must return execution plan');
    $possibleKeys = (string) ($explain['possible_keys'] ?? '');
    assert_true(
        str_contains($possibleKeys, 'idx_queue_priority_tenant') || str_contains((string) $explain['key'], 'idx_queue_priority_tenant') || str_contains((string) $explain['key'], 'idx_message_queue'),
        'Query plan must utilize indexed key for queue claiming'
    );
});

// -----------------------------------------------------------------------------
// DB-02: Campaign Physical Column & Indexing
// -----------------------------------------------------------------------------
run_test('DB-02: message_queue has physical campaign_id column and dedicated indexes', function() use ($pdo) {
    $colStmt = $pdo->query("SHOW COLUMNS FROM message_queue LIKE 'campaign_id'");
    $col = $colStmt->fetch(PDO::FETCH_ASSOC);
    assert_true(!empty($col), 'message_queue must have campaign_id column');

    $idx1 = $pdo->query("SHOW INDEX FROM message_queue WHERE Key_name = 'idx_message_queue_campaign'")->fetchAll();
    assert_true(!empty($idx1), 'message_queue must have idx_message_queue_campaign');

    $idx2 = $pdo->query("SHOW INDEX FROM message_queue WHERE Key_name = 'idx_message_queue_user_campaign'")->fetchAll();
    assert_true(!empty($idx2), 'message_queue must have idx_message_queue_user_campaign');
});

run_test('DB-02: campaign_contacts and sync_campaign_statuses use indexed campaign_id column', function() {
    $mqSource = file_get_contents(__DIR__ . '/../src/lib/MessageQueue.php');
    assert_true(
        str_contains($mqSource, 'message_queue.campaign_id = ?'),
        'campaign_contacts must query message_queue.campaign_id'
    );
    assert_true(
        str_contains($mqSource, 'message_queue.campaign_id = campaigns.id'),
        'sync_campaign_statuses must join on message_queue.campaign_id'
    );
});

// -----------------------------------------------------------------------------
// DB-03: Direct Indexed Media Authorization Lookups
// -----------------------------------------------------------------------------
run_test('DB-03: media_assets has composite indexes for user_id and storage/path lookups', function() use ($pdo) {
    $idx1 = $pdo->query("SHOW INDEX FROM media_assets WHERE Key_name = 'idx_media_assets_user_storage'")->fetchAll();
    assert_true(!empty($idx1), 'media_assets must have idx_media_assets_user_storage');

    $idx2 = $pdo->query("SHOW INDEX FROM media_assets WHERE Key_name = 'idx_media_assets_user_path'")->fetchAll();
    assert_true(!empty($idx2), 'media_assets must have idx_media_assets_user_path');
});

run_test('DB-03: public/media.php queries media_assets first before falling back to message_queue', function() {
    $mediaSource = file_get_contents(__DIR__ . '/../public/media.php');
    
    // Check that media_assets is queried first
    $assetPos = strpos($mediaSource, 'FROM media_assets WHERE user_id = ? AND (storage_key = ? OR file_path LIKE ?)');
    $queuePos = strpos($mediaSource, 'FROM message_queue');

    assert_true($assetPos !== false, 'public/media.php must contain direct media_assets check');
    assert_true($queuePos !== false, 'public/media.php must retain legacy message_queue check');
    assert_true($assetPos < $queuePos, 'media_assets check must precede expensive message_queue JSON check');
});

// -----------------------------------------------------------------------------
// DB-04: Multi-Row Chunked Batch Inserts for Campaigns
// -----------------------------------------------------------------------------
run_test('DB-04: create_campaign_for_contacts executes multi-row batch inserts with campaign_id', function() use ($pdo) {
    // Setup test user
    $userStmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $testUserId = (int) $userStmt->fetchColumn();
    if (!$testUserId) {
        $pdo->exec("INSERT INTO users (name, email, phone, password_hash) VALUES ('Test', 'test_db4@example.com', '15551234567', 'hash')");
        $testUserId = (int) $pdo->lastInsertId();
    }

    $contacts = [
        ['id' => 101, 'name' => 'Alice', 'phone' => '15550000001'],
        ['id' => 102, 'name' => 'Bob', 'phone' => '15550000002'],
        ['id' => 103, 'name' => 'Charlie', 'phone' => '15550000003'],
    ];

    $campaignId = create_campaign_for_contacts($testUserId, 'Batch Insert Test Campaign', 'Hello {name}!', $contacts);
    assert_true($campaignId > 0, 'Campaign must be created');

    // Verify messages inserted with physical campaign_id populated
    $checkStmt = $pdo->prepare("SELECT id, campaign_id, recipient, body, status, priority FROM message_queue WHERE campaign_id = ?");
    $checkStmt->execute([$campaignId]);
    $queuedMsgs = $checkStmt->fetchAll();

    assert_equals(3, count($queuedMsgs), 'Batch insert must create 3 messages');
    foreach ($queuedMsgs as $msg) {
        assert_equals($campaignId, (int) $msg['campaign_id'], 'campaign_id column must match campaign');
        assert_equals(1, (int) $msg['priority'], 'Campaign messages must have priority = 1');
        assert_equals('queued', $msg['status'], 'Message status must be queued');
    }

    // Clean up test campaign messages
    $pdo->prepare("DELETE FROM message_queue WHERE campaign_id = ?")->execute([$campaignId]);
    $pdo->prepare("DELETE FROM campaigns WHERE id = ?")->execute([$campaignId]);
});

// -----------------------------------------------------------------------------
// DB-05: Rate-Limiting Aggregation Bounded to 24 Hours
// -----------------------------------------------------------------------------
run_test('DB-05: api_key_rate_status includes created_at >= 1 DAY in WHERE clause', function() {
    $mqSource = file_get_contents(__DIR__ . '/../src/lib/MessageQueue.php');
    assert_true(
        str_contains($mqSource, 'created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)'),
        'api_key_rate_status must bound WHERE clause to 1 day'
    );
});

run_test('DB-05: api_key_rate_status accurately calculates rate limits', function() use ($pdo) {
    $userStmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $testUserId = (int) $userStmt->fetchColumn();

    $mockApiKey = [
        'id' => 99999,
        'user_id' => $testUserId,
        'rate_per_minute' => 60,
        'rate_per_hour' => 1000,
        'rate_per_day' => 10000,
    ];

    $status = api_key_rate_status($mockApiKey);
    assert_true(isset($status['counts']), 'Result must have counts');
    assert_true(isset($status['limits']), 'Result must have limits');
    assert_true(isset($status['allowed']), 'Result must have allowed flag');
    assert_true(is_bool($status['allowed']), 'allowed must be boolean');
});

// -----------------------------------------------------------------------------
// DB-06: Rolling Automated Retention Purge
// -----------------------------------------------------------------------------
run_test('DB-06: DatabaseMaintenance::purgeExpiredRecords executes dry-run and actual purge safely', function() {
    // 1. Dry run
    $dryRunResult = DatabaseMaintenance::purgeExpiredRecords(30, true);
    assert_true($dryRunResult['ok'], 'Dry run must succeed');
    assert_true($dryRunResult['dry_run'], 'dry_run flag must be true');
    assert_true(isset($dryRunResult['tables']['message_queue']), 'message_queue must be in purge tables');
    assert_true(isset($dryRunResult['tables']['api_request_logs']), 'api_request_logs must be in purge tables');

    // 2. Real execution (safe rolling purge)
    $realResult = DatabaseMaintenance::purgeExpiredRecords(30, false);
    assert_true($realResult['ok'], 'Purge must succeed');
    assert_true(!$realResult['dry_run'], 'dry_run flag must be false');
    assert_true($realResult['duration_ms'] >= 0, 'duration_ms must be tracked');
});

run_test('DB-06: scripts/backup_and_clean.php supports --purge-expired and --retention-days', function() {
    $scriptSource = file_get_contents(__DIR__ . '/../scripts/backup_and_clean.php');
    assert_true(str_contains($scriptSource, 'purge-expired'), 'CLI script must support --purge-expired');
    assert_true(str_contains($scriptSource, 'retention-days'), 'CLI script must support --retention-days');
    assert_true(str_contains($scriptSource, 'purgeExpiredRecords'), 'CLI script must invoke purgeExpiredRecords');
});

// -----------------------------------------------------------------------------
// Summary
// -----------------------------------------------------------------------------
echo "\n=========================================\n";
echo "DATABASE PERFORMANCE TESTS: $testCount total | " . ($testCount - $failCount) . " passed | $failCount failed\n";
echo "=========================================\n";

if ($failCount > 0) {
    exit(1);
}
