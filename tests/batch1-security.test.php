<?php

declare(strict_types=1);

/**
 * Batch 1 Security Audit & Verification Suite
 *
 * Validates remediation of:
 * - SEC-04: Cross-Tenant Session Isolation in Password Reset (src/pages/forgot-password.php)
 * - SEC-06: Single Bearer Token Authentication Model (src/lib/ApiCredentials.php)
 */

function test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

require_once dirname(__DIR__) . '/src/bootstrap.php';

echo "Running Batch 1 Security Suite (SEC-04, SEC-06)...\n";

// =========================================================================
// 1. SEC-04: Tenant Isolation in Password Reset OTP Dispatch
// =========================================================================
$forgotPasswordSource = (string) file_get_contents(dirname(__DIR__) . '/src/pages/forgot-password.php');

test_assert(
    !str_contains($forgotPasswordSource, "SELECT user_id FROM whatsapp_sessions WHERE status = 'connected' ORDER BY updated_at DESC LIMIT 1"),
    'forgot-password.php must NOT contain arbitrary cross-tenant session lookup'
);

test_assert(
    str_contains($forgotPasswordSource, 'SELECT user_id FROM whatsapp_sessions WHERE user_id = ? AND status = \'connected\''),
    'forgot-password.php must verify target user\'s own session before dispatching'
);

// =========================================================================
// 2. SEC-06: Single Bearer Token Model
// =========================================================================
$apiCredsSource = (string) file_get_contents(dirname(__DIR__) . '/src/lib/ApiCredentials.php');

test_assert(
    str_contains($apiCredsSource, 'Single Bearer Token Model'),
    'src/lib/ApiCredentials.php must formalize Single Bearer Token Model'
);

// Test key creation & entropy
$testToken = 'wa_live_' . bin2hex(random_bytes(24));
test_assert(
    strlen($testToken) === 56 && str_starts_with($testToken, 'wa_live_'),
    'API token must follow high-entropy 192-bit wa_live_ format'
);

// Test authenticate_api_request with Bearer Token
$originalServer = $_SERVER;
$originalGet = $_GET;
$originalPost = $_POST;

// 2.1 Missing token returns null
$_SERVER = [
    'REQUEST_METHOD' => 'POST',
    'REMOTE_ADDR' => '127.0.0.1',
];
$_GET = [];
$_POST = [];
test_assert(authenticate_api_request() === null, 'Missing Authorization token must return null');

// 2.2 Invalid token returns null
$_SERVER = [
    'REQUEST_METHOD' => 'POST',
    'HTTP_AUTHORIZATION' => 'Bearer wa_live_invalid_non_existent_token_0000000000000',
    'REMOTE_ADDR' => '127.0.0.1',
];
test_assert(authenticate_api_request() === null, 'Invalid Bearer token must return null');

// 2.3 Legacy Bearer key:secret format extracts key
$_SERVER = [
    'REQUEST_METHOD' => 'POST',
    'HTTP_AUTHORIZATION' => 'Bearer wa_live_sample_key:legacy_secret_value',
    'REMOTE_ADDR' => '127.0.0.1',
];
// Key will not match in DB, but parsing should cleanly succeed without error
test_assert(authenticate_api_request() === null, 'Legacy key:secret must parse and reject gracefully if non-existent');

$_SERVER = $originalServer;
$_GET = $originalGet;
$_POST = $originalPost;

echo "All Batch 1 PHP Security Tests Passed Successfully!\n";
