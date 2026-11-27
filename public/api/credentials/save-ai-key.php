<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/bootstrap.php';

$user = current_user();
if (!$user) {
    json_response(['ok' => false, 'error' => 'Unauthorized. Please log in.'], 401);
    exit;
}

verify_api_csrf();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id = !empty($input['id']) ? (int) $input['id'] : null;
$provider = trim((string) ($input['provider'] ?? ''));
if ($provider === 'openrouter') {
    $provider = 'custom';
}
$label = trim((string) ($input['label'] ?? ''));
$apiKey = trim((string) ($input['api_key'] ?? ''));
$model = trim((string) ($input['model'] ?? ''));
$endpoint = trim((string) ($input['endpoint'] ?? ''));
if ($provider === 'custom' && $endpoint === '') {
    $endpoint = 'https://api.openai.com/v1';
}
$priority = isset($input['priority']) ? max(1, (int) $input['priority']) : 1;

if (!in_array($provider, ['gemini', 'groq', 'custom'], true)) {
    json_response(['ok' => false, 'error' => 'Invalid AI provider. Supported: gemini, groq, custom.'], 422);
    exit;
}

if ($label === '') {
    $label = ($provider === 'custom') ? 'Custom API Key' : ucfirst($provider) . ' Key';
}

$pdo = Database::pdo();
$userId = (int) $user['id'];

// If model wasn't provided, pull default from backend ai_agent_settings
if ($model === '') {
    $settingsStmt = $pdo->prepare('SELECT gemini_model, groq_model, custom_model, openrouter_model FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
    $settingsStmt->execute([$userId]);
    $aiSettings = $settingsStmt->fetch(PDO::FETCH_ASSOC) ?: [];
    if ($provider === 'gemini') {
        $model = trim((string) ($aiSettings['gemini_model'] ?? ''));
    } elseif ($provider === 'groq') {
        $model = trim((string) ($aiSettings['groq_model'] ?? ''));
    } elseif ($provider === 'custom') {
        $model = trim((string) ($aiSettings['custom_model'] ?? ($aiSettings['openrouter_model'] ?? 'gpt-4o')));
    }
}

try {
    if ($id !== null) {
        // Update existing key
        $checkStmt = $pdo->prepare('SELECT id, api_key, model, endpoint FROM ai_provider_keys WHERE id = ? AND user_id = ? LIMIT 1');
        $checkStmt->execute([$id, $userId]);
        $existing = $checkStmt->fetch();
        if (!$existing) {
            json_response(['ok' => false, 'error' => 'API key record not found.'], 404);
            exit;
        }

        $finalKey = ($apiKey !== '') ? encrypt_credential($apiKey) : $existing['api_key'];
        $finalModel = ($model !== '') ? $model : ($existing['model'] ?? null);
        $finalEndpoint = ($endpoint !== '') ? $endpoint : ($existing['endpoint'] ?? null);

        $updateStmt = $pdo->prepare(
            'UPDATE ai_provider_keys 
             SET provider = ?, label = ?, api_key = ?, model = ?, endpoint = ?, priority = ?, status = "active", last_error = NULL
             WHERE id = ? AND user_id = ?'
        );
        $updateStmt->execute([$provider, $label, $finalKey, $finalModel, $finalEndpoint, $priority, $id, $userId]);
        $keyId = $id;
    } else {
        // Insert new key
        if ($apiKey === '') {
            json_response(['ok' => false, 'error' => 'API key string cannot be empty.'], 422);
            exit;
        }

        $insertStmt = $pdo->prepare(
            'INSERT INTO ai_provider_keys (user_id, provider, label, api_key, model, endpoint, priority, is_active, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, "active")'
        );
        $insertStmt->execute([$userId, $provider, $label, encrypt_credential($apiKey), $model ?: null, $endpoint ?: null, $priority]);
        $keyId = (int) $pdo->lastInsertId();
    }

    // Ensure ai_agent_settings exists and is enabled for this user
    $ownerName = trim((string) ($user['name'] ?? 'Boss')) ?: 'Boss';
    $pdo->prepare('INSERT INTO ai_agent_settings (user_id, enabled, auto_reply_enabled, provider_priority, agent_mode, owner_name) VALUES (?, 1, 1, "gemini", "support", ?) ON DUPLICATE KEY UPDATE enabled = 1, auto_reply_enabled = 1')->execute([$userId, $ownerName]);

    $activeKey = $finalKey ?? $apiKey;
    $activeModel = $finalModel ?? ($model ?: null);
    $activeEndpoint = $finalEndpoint ?? ($endpoint ?: 'https://api.openai.com/v1');
    if ($provider === 'gemini') {
        $pdo->prepare('UPDATE ai_agent_settings SET gemini_api_key = ?, gemini_model = COALESCE(?, gemini_model) WHERE user_id = ? AND (gemini_api_key IS NULL OR gemini_api_key = "")')->execute([$activeKey, $activeModel, $userId]);
    } elseif ($provider === 'groq') {
        $pdo->prepare('UPDATE ai_agent_settings SET groq_api_key = ?, groq_model = COALESCE(?, groq_model) WHERE user_id = ? AND (groq_api_key IS NULL OR groq_api_key = "")')->execute([$activeKey, $activeModel, $userId]);
    } elseif ($provider === 'custom') {
        $pdo->prepare('UPDATE ai_agent_settings SET custom_api_key = ?, custom_model = COALESCE(?, custom_model), custom_endpoint = COALESCE(?, custom_endpoint) WHERE user_id = ? AND (custom_api_key IS NULL OR custom_api_key = "")')->execute([$activeKey, $activeModel, $activeEndpoint, $userId]);
    }

    json_response([
        'ok' => true,
        'message' => 'AI API key saved successfully.',
        'key' => [
            'id' => $keyId,
            'provider' => $provider,
            'label' => $label,
            'model' => $model,
            'priority' => $priority,
            'status' => 'active'
        ]
    ]);
} catch (\Throwable $e) {
    json_response(['ok' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
}
