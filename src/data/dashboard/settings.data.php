<?php

declare(strict_types=1);

$user = $user ?? require_auth();
$userId = (int) $user['id'];
$pdo = Database::pdo();

// Determine active tab: 'session' (default) or 'security'
$activeTab = (string) ($_GET['tab'] ?? 'session');
if (!in_array($activeTab, ['session', 'security'], true)) {
    $activeTab = 'session';
}

// --------------------------------------------------------------------------
// 1. WhatsApp Session Memory Storage & Durability
// --------------------------------------------------------------------------
$sessionStmt = $pdo->prepare('SELECT * FROM whatsapp_sessions WHERE user_id = ? LIMIT 1');
$sessionStmt->execute([$userId]);
$session = $sessionStmt->fetch() ?: [
    'status' => 'idle',
    'storage_driver' => 'database_encrypted',
    'auto_sleep_enabled' => 1,
    'auto_sleep_timeout_seconds' => 60,
];

$storageDriver = (string) ($session['storage_driver'] ?? 'database_encrypted');

// Count stored encrypted keys in DB for this user
$keysStmt = $pdo->prepare('SELECT COUNT(*) FROM whatsapp_auth_keys WHERE user_id = ?');
$keysStmt->execute([$userId]);
$storedKeyCount = (int) $keysStmt->fetchColumn();

$deviceConnected = (($session['status'] ?? 'idle') === 'connected');
$devicePhone = $session['phone'] ?? null;
$devicePushName = $session['push_name'] ?? null;

// --------------------------------------------------------------------------
// 2. Account Security, Credentials & TOTP Authentication
// --------------------------------------------------------------------------
$sec = get_user_security($userId);

$userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$userStmt->execute([$userId]);
$userRecord = $userStmt->fetch() ?: $user;

$hasPassword = !empty($userRecord['password_hash']) && !empty($sec['password_set']);
$isGoogleLinked = !empty($sec['google_linked']);
$isTotpEnabled = !empty($sec['totp_enabled']);

$existingBackupCodesCount = 0;
if (!empty($sec['backup_codes'])) {
    $existingCodes = json_decode((string) $sec['backup_codes'], true) ?: [];
    $existingBackupCodesCount = count($existingCodes);
}

$showSetup2FA = isset($_GET['setup_2fa']) && !$isTotpEnabled;
$pendingSecret = '';
$totpUri = '';

if ($showSetup2FA) {
    $activeTab = 'security';
    if (empty($_SESSION['pending_totp_secret'])) {
        $_SESSION['pending_totp_secret'] = totp_generate_secret(20);
    }
    $pendingSecret = (string) $_SESSION['pending_totp_secret'];
    $totpUri = totp_uri((string) $userRecord['email'], $pendingSecret, 'WhatsGateway');
} else {
    unset($_SESSION['pending_totp_secret']);
}

$newlyGeneratedBackupCodes = $_SESSION['newly_generated_backup_codes'] ?? null;
unset($_SESSION['newly_generated_backup_codes']);
if (!empty($newlyGeneratedBackupCodes)) {
    $activeTab = 'security';
}
