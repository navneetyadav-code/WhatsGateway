<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json');

$worker = worker_request('GET', 'health');
$workerOk = worker_health_ready($worker);
$requiredTables = [
    'users', 'api_keys', 'api_request_logs', 'campaigns', 'chatbot_rules', 'contacts',
    'login_attempts', 'message_logs', 'message_queue', 'password_resets',
    'webhooks', 'webhook_deliveries', 'whatsapp_sessions',
];
$databaseOk = false;
$databaseConnected = false;
$missingTables = $requiredTables;

// OPS-01: High-performance lightweight health monitoring.
// Uses fast SELECT 1 ping and caches schema validation for 60 seconds to prevent
// information_schema table locks and CPU exhaustion from frequent container liveness probes.
$isFullCheck = isset($_GET['full']);
$schemaCacheFile = sys_get_temp_dir() . '/whats_schema_health.json';
$schemaCacheTtl = 60;

try {
    $pdo = Database::pdo();
    $pdo->query('SELECT 1')->fetch();
    $databaseConnected = true;

    $cached = null;
    if (!$isFullCheck && file_exists($schemaCacheFile) && (time() - filemtime($schemaCacheFile) < $schemaCacheTtl)) {
        $cached = json_decode((string) file_get_contents($schemaCacheFile), true);
    }

    if (is_array($cached) && isset($cached['missing_tables'], $cached['database_ok'])) {
        $missingTables = $cached['missing_tables'];
        $databaseOk = (bool) $cached['database_ok'];
    } else {
        $databaseName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
        if ($databaseName !== '') {
            $statement = $pdo->query(
                'SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()'
            );
            $existingTables = $statement->fetchAll(PDO::FETCH_COLUMN);
            $missingTables = array_values(array_diff($requiredTables, $existingTables));
            $databaseOk = $missingTables === [];

            if ($databaseOk) {
                @file_put_contents($schemaCacheFile, json_encode([
                    'database_ok' => true,
                    'missing_tables' => [],
                    'checked_at' => time(),
                ]), LOCK_EX);
            }
        }
    }
} catch (Throwable $error) {
    error_log('[health] database probe failed: ' . get_class($error));
}

$ok = $workerOk && $databaseOk;
$status = $ok ? 200 : 503;

http_response_code($status);

echo json_encode([
    'ok' => $ok,
    'status' => $ok ? 'healthy' : 'degraded',
    'timestamp' => date('c'),
    'service' => 'whatsapp-api-hub',
    'database' => [
        'ok' => $databaseOk,
        'connected' => $databaseConnected,
        'schema_ready' => $databaseOk,
        'missing_tables' => $missingTables,
    ],
    'worker' => [
        'ok' => $workerOk,
        'status_code' => $worker['status_code'] ?? $worker['status'] ?? null,
        'service' => $worker['service'] ?? null,
        'error' => $workerOk ? null : 'Worker is not reachable or its database is not ready.',
    ],
]);
