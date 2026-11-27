<?php
declare(strict_types=1);

require_once __DIR__ . '/chatbot.data.php';

$engineType = 'personal';
$toolsConfig = json_decode((string) ($aiSettings['tools_config'] ?? '{}'), true) ?: [];
$adminPhones = implode(', ', (array) ($toolsConfig['admin_phones'] ?? []));
$ownerName = trim((string) ($aiSettings['owner_name'] ?? '')) ?: 'the owner';

$personalPersona = (string) ($toolsConfig['personal_system_prompt'] ?? ($aiSettings['system_prompt'] ?? "You are the dedicated Personal AI Assistant to " . $ownerName . ". Manage schedules, assist with daily operations, and draft correspondence efficiently."));

$defaultPersonalInternal = "1. Strict Grounding: State facts ONLY from verified executed tool results. Never invent records, prices, phone numbers, or dates.
2. Autonomous Tool Execution: Proactively execute your active connected tools whenever requested, strictly conforming to each tool's argument schema.
3. Multi-Turn Coreference & Parameter Resolution: When instructed to perform an action on previously mentioned content, autonomously extract all required arguments and execute immediately.
4. Formatting: Crisp, proactive, structured. Use *bold* highlights, clean bullet points, and appropriate emojis.
5. Silence Feedback: You are a personal assistant, NOT a customer service line. NEVER ask for customer reviews, 5-star ratings, or feedback surveys.";

$personalInternalPrompt = (string) ($toolsConfig['personal_internal_prompt'] ?? $defaultPersonalInternal);
$screeningPolicy = (string) ($toolsConfig['screening_policy'] ?? "Thank you for reaching out to " . $ownerName . ". I am their personal assistant. Please leave your name and a brief message, and I will ensure they receive it.");
