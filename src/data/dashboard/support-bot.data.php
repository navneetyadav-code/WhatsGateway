<?php
declare(strict_types=1);

require_once __DIR__ . '/chatbot.data.php';

$engineType = 'support';
$toolsConfig = json_decode((string) ($aiSettings['tools_config'] ?? '{}'), true) ?: [];
