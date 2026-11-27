<?php
declare(strict_types=1);

require_once __DIR__ . '/chatbot.data.php';

$engineType = 'personal';
$toolsConfig = json_decode((string) ($aiSettings['tools_config'] ?? '{}'), true) ?: [];
$adminPhones = implode(', ', (array) ($toolsConfig['admin_phones'] ?? []));
$rawOwner = trim((string) ($aiSettings['owner_name'] ?? ''));
if ($rawOwner === '' || $rawOwner === 'Boss' || $rawOwner === 'the owner') {
    $userName = trim((string) ($user['name'] ?? ''));
    $ownerName = $userName !== '' ? $userName : (($rawOwner !== '' && $rawOwner !== 'the owner') ? $rawOwner : 'Boss');
} else {
    $ownerName = $rawOwner;
}

$defaultPersonalPersona = "You are the Executive Chief of Staff and Personal AI Assistant to " . $ownerName . ". Proactively manage daily schedules, triage priority communications, coordinate outbound correspondence, and execute connected tools with high precision. Keep briefings concise, structured, and action-oriented.";

$rawPersona = trim((string) ($toolsConfig['personal_system_prompt'] ?? ''));
// Guard against legacy support prompt contamination:
if ($rawPersona === '' || str_contains(strtolower($rawPersona), 'customer support assistant') || str_contains(strtolower($rawPersona), 'company knowledge base')) {
    $personalPersona = $defaultPersonalPersona;
} else {
    if (str_contains($rawPersona, 'to Boss.') && $ownerName !== 'Boss') {
        $rawPersona = str_replace('to Boss.', "to {$ownerName}.", $rawPersona);
    }
    $personalPersona = $rawPersona;
}

$defaultPersonalInternal = "1. Strict Grounding: State facts ONLY from verified executed tool results. Never invent records, prices, phone numbers, or dates.
2. Autonomous Tool Execution: Proactively execute your active connected tools whenever requested, strictly conforming to each tool's argument schema.
3. Multi-Turn Coreference & Parameter Resolution: When instructed to perform an action on previously mentioned content, autonomously extract all required arguments and execute immediately.
4. Formatting: Crisp, proactive, structured. Use *bold* highlights, clean bullet points, and appropriate emojis.
5. Silence Feedback: You are a personal assistant, NOT a customer service line. NEVER ask for customer reviews, 5-star ratings, or feedback surveys.";

$personalInternalPrompt = (string) ($toolsConfig['personal_internal_prompt'] ?? $defaultPersonalInternal);

if (isset($toolsConfig['screening_policy'])) {
    $screeningPolicy = (string) $toolsConfig['screening_policy'];
    if (str_contains($screeningPolicy, 'reaching out to Boss.') && $ownerName !== 'Boss') {
        $screeningPolicy = str_replace('reaching out to Boss.', "reaching out to {$ownerName}.", $screeningPolicy);
    }
} else {
    $screeningPolicy = "Thank you for reaching out to " . $ownerName . ". I am their personal assistant. Please leave your name and a brief message, and I will ensure they receive it.";
}

try {
    $activeToolsStmt = $pdo->prepare('SELECT tool_slug, display_name, description FROM ai_custom_tools WHERE user_id = ? AND is_active = 1 ORDER BY id ASC');
    $activeToolsStmt->execute([$userId]);
    $activeToolsList = $activeToolsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (\Throwable $e) {
    $activeToolsList = [];
}
