<?php
declare(strict_types=1);

$sessionStmt = $pdo->prepare('SELECT * FROM whatsapp_sessions WHERE user_id = ? LIMIT 1');
$sessionStmt->execute([$user['id']]);
$session = $sessionStmt->fetch() ?: ['status' => 'idle'];

$deviceSleeping = !empty($session['sleeping']);
$deviceHasIdentity = !empty($session['phone']) || !empty($session['push_name']);
$deviceConnected = (($session['status'] ?? 'idle') === 'connected') || ($deviceSleeping && $deviceHasIdentity);

$contactOptionsStmt = $pdo->prepare('SELECT * FROM contacts WHERE user_id = ? ORDER BY name ASC, id DESC');
$contactOptionsStmt->execute([$user['id']]);
$contactOptions = $contactOptionsStmt->fetchAll();

$optedInCount = count(opted_in_contacts((int) $user['id']));
$campaigns = recent_campaigns((int) $user['id']);

$groupsStmt = $pdo->prepare('SELECT * FROM whatsapp_groups WHERE user_id = ? ORDER BY subject ASC');
$groupsStmt->execute([$user['id']]);
$groups = $groupsStmt->fetchAll();

$userApiKeys = list_api_keys((int) $user['id']);
$primaryApiKey = !empty($userApiKeys) ? ($userApiKeys[0]['key_prefix'] . '••••••••••••••••' ?? 'YOUR_API_KEY') : 'YOUR_API_KEY';
$hasApiKey = !empty($userApiKeys);
$apiBaseUrl = base_url('api/send.php');
