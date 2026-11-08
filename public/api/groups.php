<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if ($method !== 'GET') {
    json_response(['ok' => false, 'error' => 'Method not allowed. Use GET.'], 405);
}

// 1. Authenticate Request (API Key / Bearer Token or Authenticated Web Session)
$user = authenticate_api_request();
$isSessionUser = false;

if (!$user) {
    $sessionUser = current_user();
    if ($sessionUser) {
        $user = $sessionUser;
        $isSessionUser = true;
    } else {
        json_response([
            'ok' => false,
            'error' => 'Invalid API key, access token, or active session.',
            'meta_error' => [
                'message' => 'Invalid OAuth access token or API key.',
                'type' => 'OAuthException',
                'code' => 190,
            ],
        ], 401);
    }
}

$userId = (int) ($user['user_id'] ?? $user['id']);
$forceRefresh = !empty($_GET['refresh']) && ($_GET['refresh'] === '1' || strtolower((string) $_GET['refresh']) === 'true');
$search = trim((string) ($_GET['search'] ?? ''));

$pdo = Database::pdo();

// Check if WhatsApp session is connected
$sessionStmt = $pdo->prepare('SELECT status, sleeping, phone, push_name FROM whatsapp_sessions WHERE user_id = ? LIMIT 1');
$sessionStmt->execute([$userId]);
$waSession = $sessionStmt->fetch() ?: ['status' => 'idle'];
$deviceConnected = ($waSession['status'] ?? 'idle') === 'connected' || (!empty($waSession['sleeping']) && !empty($waSession['phone']));

// Check existing cached groups count in database
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM whatsapp_groups WHERE user_id = ?');
$countStmt->execute([$userId]);
$cachedCount = (int) $countStmt->fetchColumn();

// If force refresh requested OR no cached groups exist but device is connected, fetch live from worker
$liveGroups = null;
if ($forceRefresh || ($cachedCount === 0 && $deviceConnected)) {
    try {
        $workerRes = worker_request('GET', 'sessions/' . $userId . '/groups', [], 15);
        if (!empty($workerRes['ok']) && isset($workerRes['groups'])) {
            $liveGroups = $workerRes['groups'];
        }
    } catch (\Throwable $e) {
        // Fall back gracefully to database cache if worker probe fails
    }
}

// Query groups from database
$sql = 'SELECT group_jid AS id, subject, participants_count, is_admin, announce, description, created_time, last_synced_at FROM whatsapp_groups WHERE user_id = ?';
$params = [$userId];

if ($search !== '') {
    $sql .= ' AND (subject LIKE ? OR group_jid LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$sql .= ' ORDER BY subject ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$formatted = array_map(static function (array $r): array {
    return [
        'id' => (string) $r['id'],
        'subject' => (string) $r['subject'],
        'participants_count' => (int) ($r['participants_count'] ?? 0),
        'is_admin' => (bool) ($r['is_admin'] ?? false),
        'announce' => (bool) ($r['announce'] ?? false),
        'description' => (string) ($r['description'] ?? ''),
        'created_time' => $r['created_time'] ?? null,
        'last_synced_at' => $r['last_synced_at'] ?? null,
    ];
}, $rows);

json_response([
    'ok' => true,
    'count' => count($formatted),
    'device_connected' => $deviceConnected,
    'cached' => $liveGroups === null,
    'groups' => $formatted,
], 200);
