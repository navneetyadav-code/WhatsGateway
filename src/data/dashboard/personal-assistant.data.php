<?php
declare(strict_types=1);

require_once __DIR__ . '/chatbot.data.php';

$engineType = 'personal';
$toolsConfig = json_decode((string) ($aiSettings['tools_config'] ?? '{}'), true) ?: [];
$adminPhones = implode(', ', (array) ($toolsConfig['admin_phones'] ?? []));
$personalPrompt = (string) ($toolsConfig['personal_system_prompt'] ?? ($aiSettings['system_prompt'] ?? "You are the dedicated Personal AI Assistant to " . ($aiSettings['owner_name'] ?? 'Boss') . ". Manage schedules, assist with daily operations, and draft correspondence efficiently."));
$screeningPolicy = (string) ($toolsConfig['screening_policy'] ?? "Thank you for reaching out to " . ($aiSettings['owner_name'] ?? 'Boss') . ". I am their personal assistant. Please leave your name and a brief message, and I will ensure they receive it.");
