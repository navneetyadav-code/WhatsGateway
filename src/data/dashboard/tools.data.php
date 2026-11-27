<?php
declare(strict_types=1);

$userId = (int) ($user['id'] ?? ($_SESSION['user_id'] ?? 1));

// 1. Universal Custom Tools Catalog for this User
$toolsStmt = $pdo->prepare('SELECT * FROM ai_custom_tools WHERE user_id = ? ORDER BY id ASC');
$toolsStmt->execute([$userId]);
$userTools = $toolsStmt->fetchAll();

foreach ($userTools as &$ut) {
    $ut['parameters_schema'] = !empty($ut['parameters_schema']) ? json_decode((string) $ut['parameters_schema'], true) : [];
    $ut['execution_config'] = !empty($ut['execution_config']) ? json_decode((string) $ut['execution_config'], true) : [];
    $ut['rbac_roles'] = !empty($ut['rbac_roles']) ? json_decode((string) $ut['rbac_roles'], true) : ['guest', 'customer', 'admin'];
}
unset($ut);

// 2. AI Settings (for Whitelisted Admin Phones & Providers)
$aiStmt = $pdo->prepare('SELECT * FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$aiStmt->execute([$userId]);
$aiRow = $aiStmt->fetch() ?: [];

$toolsConfigRaw = $aiRow['tools_config'] ?? null;
$toolsConfig = [];
if (!empty($toolsConfigRaw)) {
    $toolsConfig = json_decode((string) $toolsConfigRaw, true) ?: [];
}
if (!isset($toolsConfig['admin_phones']) || !is_array($toolsConfig['admin_phones'])) {
    $toolsConfig['admin_phones'] = [];
}
$agentMode = $aiRow['agent_mode'] ?? 'support';

// 3. Recent Tool Invocations
$invStmt = $pdo->prepare('SELECT * FROM ai_tool_invocations WHERE user_id = ? ORDER BY id DESC LIMIT 20');
$invStmt->execute([$userId]);
$recentInvocations = $invStmt->fetchAll();

// 4. App Store Presets & Installation Map (for Quick Add Tools)
require_once __DIR__ . '/app-store.data.php';

// 5. Active AI Provider Keys & Models (from /ai-credentials) for Tool Synthesis
$aiKeysStmt = $pdo->prepare(
    'SELECT id, provider, label, model, priority, is_active, status, fail_count, last_error
     FROM ai_provider_keys
     WHERE user_id = ?
     ORDER BY provider ASC, priority ASC, id ASC'
);
$aiKeysStmt->execute([$userId]);
$userAiKeys = $aiKeysStmt->fetchAll(PDO::FETCH_ASSOC);

// Also include legacy keys from ai_agent_settings if not in ai_provider_keys
$legacyAiKeys = [];
if (!empty($aiRow['gemini_api_key'])) {
    $hasDbGemini = false;
    foreach ($userAiKeys as $uk) {
        if ($uk['provider'] === 'gemini') { $hasDbGemini = true; break; }
    }
    if (!$hasDbGemini) {
        $legacyAiKeys[] = [
            'id' => 'legacy_gemini',
            'provider' => 'gemini',
            'label' => 'Legacy Gemini Key',
            'model' => $aiRow['gemini_model'] ?: (config_env('DEFAULT_GEMINI_MODEL') ?: 'gemini-2.5-flash'),
            'priority' => 99,
            'is_active' => 1,
            'status' => 'active'
        ];
    }
}
if (!empty($aiRow['groq_api_key'])) {
    $hasDbGroq = false;
    foreach ($userAiKeys as $uk) {
        if ($uk['provider'] === 'groq') { $hasDbGroq = true; break; }
    }
    if (!$hasDbGroq) {
        $legacyAiKeys[] = [
            'id' => 'legacy_groq',
            'provider' => 'groq',
            'label' => 'Legacy Groq Key',
            'model' => $aiRow['groq_model'] ?: (config_env('DEFAULT_GROQ_MODEL') ?: 'llama-3.3-70b-versatile'),
            'priority' => 99,
            'is_active' => 1,
            'status' => 'active'
        ];
    }
}
$customLegacyKey = !empty($aiRow['custom_api_key']) ? $aiRow['custom_api_key'] : (!empty($aiRow['openrouter_api_key']) ? $aiRow['openrouter_api_key'] : null);
if (!empty($customLegacyKey)) {
    $hasDbCustom = false;
    foreach ($userAiKeys as $uk) {
        if ($uk['provider'] === 'custom' || $uk['provider'] === 'openrouter') { $hasDbCustom = true; break; }
    }
    if (!$hasDbCustom) {
        $legacyAiKeys[] = [
            'id' => 'legacy_custom',
            'provider' => 'custom',
            'label' => 'Legacy Custom API Key',
            'model' => $aiRow['custom_model'] ?: ($aiRow['openrouter_model'] ?: 'gpt-4o'),
            'priority' => 99,
            'is_active' => 1,
            'status' => 'active'
        ];
    }
}

$allAiKeysForModal = array_merge($userAiKeys, $legacyAiKeys);
$defaultProviderPriority = $aiRow['provider_priority'] ?? 'gemini';
