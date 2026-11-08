<?php

declare(strict_types=1);

/**
 * Industry-Standard SSRF Security Audit & Verification Suite (SEC-01)
 *
 * Validates remediation of SSRF vulnerabilities in:
 * - src/lib/Support.php (assert_safe_outbound_url & safe_curl_request)
 * - public/api/tools/test-tool.php (rest_api, webhook, shared_env_api categories)
 * - src/lib/AntiBotBypass.php (cookie challenge activation safe curl)
 * - worker/tools-engine.mjs (executeSharedEnvApi & resolveProxyCookie)
 */

function test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

require_once dirname(__DIR__) . '/src/bootstrap.php';

echo "Running SEC-01 SSRF Remediation Security Suite...\n";

// =========================================================================
// 1. UNIT TESTS: assert_safe_outbound_url Loopback & Local Domain Blocking
// =========================================================================
$loopbackTargets = [
    'http://127.0.0.1/api/test',
    'http://127.0.0.2:8080/metrics',
    'http://127.255.255.254/internal',
    'http://0.0.0.0:3000/status',
    'http://localhost/admin',
    'http://sub.localhost:8080/',
    'http://gateway.local/v1/api',
    'http://database.internal/query',
    'http://[::1]:8080/',
    'http://[::ffff:127.0.0.1]:3000/',
];

foreach ($loopbackTargets as $url) {
    $blocked = false;
    try {
        assert_safe_outbound_url($url);
    } catch (InvalidArgumentException $e) {
        $blocked = true;
    }
    test_assert($blocked, "Loopback URL {$url} must be rejected by assert_safe_outbound_url()");
}

// =========================================================================
// 2. UNIT TESTS: Cloud Instance Metadata Endpoints Blocking (IMDSv1/v2)
// =========================================================================
$metadataTargets = [
    'http://169.254.169.254/latest/meta-data/',
    'http://169.254.169.254/computeMetadata/v1/',
    'http://169.254.1.1/internal',
    'http://100.100.100.200/latest/meta-data/',
    'http://metadata.google.internal/computeMetadata/v1/',
];

foreach ($metadataTargets as $url) {
    $blocked = false;
    try {
        assert_safe_outbound_url($url);
    } catch (InvalidArgumentException $e) {
        $blocked = true;
    }
    test_assert($blocked, "Cloud metadata target {$url} must be blocked by assert_safe_outbound_url()");
}

// =========================================================================
// 3. UNIT TESTS: RFC 1918 Private VPC IP Address Blocking
// =========================================================================
$privateVpcTargets = [
    'http://10.0.0.1/health',
    'http://10.254.12.8:8000/admin',
    'http://172.16.0.1/',
    'http://172.20.10.5:9000/',
    'http://172.31.255.255/',
    'http://192.168.0.1/router',
    'http://192.168.1.100:8080/api',
];

foreach ($privateVpcTargets as $url) {
    $blocked = false;
    try {
        assert_safe_outbound_url($url);
    } catch (InvalidArgumentException $e) {
        $blocked = true;
    }
    test_assert($blocked, "Private RFC 1918 subnet URL {$url} must be blocked by assert_safe_outbound_url()");
}

// =========================================================================
// 4. UNIT TESTS: Non-HTTP Schemes & Credentials Injection
// =========================================================================
$invalidSchemes = [
    'file:///etc/passwd',
    'file:///C:/Windows/System32/drivers/etc/hosts',
    'gopher://127.0.0.1:6379/_FLUSHALL',
    'dict://127.0.0.1:11211/stat',
    'ftp://public.example.com/file.txt',
    'ldap://127.0.0.1/o=auth',
    'http://admin:secret@1.1.1.1/',
    'https://user:password@8.8.8.8/',
];

foreach ($invalidSchemes as $url) {
    $blocked = false;
    try {
        assert_safe_outbound_url($url);
    } catch (InvalidArgumentException $e) {
        $blocked = true;
    }
    test_assert($blocked, "Illegal scheme or credential URL {$url} must be rejected by assert_safe_outbound_url()");
}

// =========================================================================
// 5. UNIT TESTS: Scheme Enforcement (requireHttps)
// =========================================================================
$httpBlocked = false;
try {
    assert_safe_outbound_url('http://example.com/webhook', true);
} catch (InvalidArgumentException $e) {
    $httpBlocked = true;
}
test_assert($httpBlocked, 'Plain HTTP URL must be rejected when requireHttps is enabled');

$httpsAllowed = false;
try {
    $meta = assert_safe_outbound_url('https://example.com/webhook', true);
    if (!empty($meta['host'])) {
        $httpsAllowed = true;
    }
} catch (InvalidArgumentException $e) {
    $httpsAllowed = false;
}
test_assert($httpsAllowed, 'Valid HTTPS URL must pass validation when requireHttps is enabled');

// =========================================================================
// 6. UNIT TESTS: URL Resolution & safe_curl_request Defenses
// =========================================================================
test_assert(
    resolve_relative_url('https://example.com/api/v1', '/v2/users') === 'https://example.com/v2/users',
    'resolve_relative_url must resolve absolute paths correctly'
);
test_assert(
    resolve_relative_url('https://example.com/path/', 'next.php') === 'https://example.com/path/next.php',
    'resolve_relative_url must resolve relative file paths correctly'
);
test_assert(
    resolve_relative_url('https://example.com/path', 'https://other.com/target') === 'https://other.com/target',
    'resolve_relative_url must preserve absolute destination URLs'
);

// safe_curl_request must reject dangerous targets before touching network
$safeCurlBlocked = false;
try {
    safe_curl_request('http://127.0.0.1:8080/admin');
} catch (InvalidArgumentException $e) {
    $safeCurlBlocked = true;
}
test_assert($safeCurlBlocked, 'safe_curl_request must immediately reject restricted targets during pre-flight');

// =========================================================================
// 7. SOURCE INTEGRITY AUDIT: test-tool.php & AntiBotBypass.php
// =========================================================================
$testToolSource = (string) file_get_contents(dirname(__DIR__) . '/public/api/tools/test-tool.php');

test_assert(
    !str_contains($testToolSource, 'CURLOPT_FOLLOWLOCATION, true'),
    'public/api/tools/test-tool.php must NOT contain any blind CURLOPT_FOLLOWLOCATION => true directives'
);

test_assert(
    str_contains($testToolSource, 'assert_safe_outbound_url($finalUrl'),
    'test-tool.php must enforce assert_safe_outbound_url on rest_api category'
);

test_assert(
    str_contains($testToolSource, 'assert_safe_outbound_url($targetUrl, true)'),
    'test-tool.php must enforce assert_safe_outbound_url on webhook category with HTTPS required'
);

test_assert(
    str_contains($testToolSource, 'assert_safe_outbound_url($targetUrl)'),
    'test-tool.php must enforce assert_safe_outbound_url on shared_env_api category'
);

test_assert(
    str_contains($testToolSource, 'safe_curl_request($finalUrl') && str_contains($testToolSource, 'safe_curl_request($targetUrl'),
    'test-tool.php must route all outbound traffic through hardened safe_curl_request engine'
);

$antiBotSource = (string) file_get_contents(dirname(__DIR__) . '/src/lib/AntiBotBypass.php');
test_assert(
    str_contains($antiBotSource, 'safe_curl_request($activateUrl'),
    'AntiBotBypass::activateCookie must use safe_curl_request to prevent challenge activation SSRF'
);

// =========================================================================
// 8. SOURCE INTEGRITY AUDIT: worker/tools-engine.mjs
// =========================================================================
$workerSource = (string) file_get_contents(dirname(__DIR__) . '/worker/tools-engine.mjs');

test_assert(
    str_contains($workerSource, 'await assertSafeWebhookUrl(finalUrl'),
    'worker/tools-engine.mjs executeSharedEnvApi must validate finalUrl via assertSafeWebhookUrl'
);

test_assert(
    str_contains($workerSource, 'await assertSafeWebhookUrl(targetUrl,'),
    'worker/tools-engine.mjs resolveProxyCookie must validate targetUrl via assertSafeWebhookUrl'
);

// =========================================================================
// 9. SIMULATED API EXECUTION: Category SSRF Guardrail Responses (422)
// =========================================================================
// Test rest_api SSRF rejection logic
$restPayload = [
    'category' => 'rest_api',
    'execution_config' => [
        'url' => 'http://169.254.169.254/latest/meta-data/',
        'method' => 'GET'
    ],
    'args' => []
];

$rawUrl = $restPayload['execution_config']['url'];
$ssrfCaught = false;
try {
    assert_safe_outbound_url($rawUrl, false, false);
} catch (InvalidArgumentException $e) {
    $ssrfCaught = true;
    test_assert(
        str_contains($e->getMessage(), 'cloud metadata endpoints') || str_contains($e->getMessage(), 'private or reserved network IP'),
        'rest_api SSRF exception message must explicitly indicate security constraint violation'
    );
}
test_assert($ssrfCaught, 'rest_api payload targeting cloud metadata must trigger SSRF guardrail');

// Test webhook SSRF rejection logic (must enforce HTTPS)
$webhookPayload = [
    'category' => 'webhook',
    'execution_config' => [
        'target_url' => 'http://10.0.0.1:9090/webhook',
    ],
    'args' => []
];

$webhookCaught = false;
try {
    assert_safe_outbound_url($webhookPayload['execution_config']['target_url'], true);
} catch (InvalidArgumentException $e) {
    $webhookCaught = true;
}
test_assert($webhookCaught, 'webhook payload targeting private IP must trigger SSRF guardrail');

// Test shared_env_api SSRF rejection logic
$sharedPayload = [
    'category' => 'shared_env_api',
    'execution_config' => [
        'base_url' => 'http://127.0.0.1:3000',
    ],
    'args' => [
        'action' => 'health'
    ]
];

$sharedUrl = $sharedPayload['execution_config']['base_url'] . '/' . $sharedPayload['args']['action'];
$sharedCaught = false;
try {
    assert_safe_outbound_url($sharedUrl);
} catch (InvalidArgumentException $e) {
    $sharedCaught = true;
}
test_assert($sharedCaught, 'shared_env_api payload targeting loopback worker must trigger SSRF guardrail');

echo "All SEC-01 SSRF tests passed successfully!\n";
