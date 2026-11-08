<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/bootstrap.php';

// Authenticate caller (Dashboard Session or API Key)
$user = current_user();
if (!$user) {
    $apiUser = authenticate_api_request();
    if ($apiUser && !empty($apiUser['user_id'])) {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $apiUser['user_id']]);
        $user = $stmt->fetch();
    }
}

if (!$user) {
    json_response([
        'ok' => false,
        'error' => 'Unauthorized. Please log in to test tools.'
    ], 401);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$toolSlug = trim((string) ($input['tool_slug'] ?? ''));
$category = trim((string) ($input['category'] ?? 'rest_api'));
$executionConfig = is_array($input['execution_config'] ?? null) ? $input['execution_config'] : [];
$args = is_array($input['args'] ?? null) ? $input['args'] : [];

if (empty($executionConfig) && $toolSlug !== '') {
    $stmt = Database::pdo()->prepare('SELECT * FROM ai_custom_tools WHERE user_id = ? AND tool_slug = ? LIMIT 1');
    $stmt->execute([(int) $user['id'], $toolSlug]);
    $toolRow = $stmt->fetch();
    if ($toolRow) {
        $category = (string) $toolRow['category'];
        $executionConfig = json_decode((string) $toolRow['execution_config'], true) ?: [];
    }
}

$startTime = microtime(true);

if ($category === 'rest_api') {
    $rawUrl = trim((string) ($executionConfig['url'] ?? ''));
    if ($rawUrl === '') {
        json_response(['ok' => false, 'error' => 'Target URL is missing in tool execution configuration.'], 422);
    }

    // Interpolate {param} placeholders in URL
    $finalUrl = $rawUrl;
    foreach ($args as $k => $v) {
        $replacement = is_scalar($v) ? rawurlencode((string) $v) : '';
        $finalUrl = str_replace('{' . $k . '}', $replacement, $finalUrl);
    }

    // SEC-01: Multi-layer SSRF validation against private/loopback/cloud-metadata networks
    try {
        assert_safe_outbound_url($finalUrl, false, false);
    } catch (InvalidArgumentException $e) {
        json_response(['ok' => false, 'error' => 'SSRF validation failed: ' . $e->getMessage()], 422);
    }

    $method = strtoupper((string) ($executionConfig['method'] ?? 'GET'));
    $headers = is_array($executionConfig['headers'] ?? null) ? $executionConfig['headers'] : [];
    $formattedHeaders = [
        'User-Agent: ' . AntiBotBypass::BROWSER_USER_AGENT,
        'Accept: application/json'
    ];
    $hasExplicitCookie = false;
    foreach ($headers as $hk => $hv) {
        if (strcasecmp((string) $hk, 'Cookie') === 0) {
            $hasExplicitCookie = true;
        }
        $formattedHeaders[] = "{$hk}: {$hv}";
    }

    if (!$hasExplicitCookie) {
        $cachedCookie = AntiBotBypass::getCachedCookie($finalUrl);
        if ($cachedCookie) {
            $formattedHeaders[] = "Cookie: __test={$cachedCookie}";
        }
    }

    $postPayload = null;
    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        $bodyData = $args;
        $rawTemplate = $executionConfig['body_template'] ?? null;
        if (is_string($rawTemplate)) {
            $decodedTpl = json_decode($rawTemplate, true);
            $rawTemplate = (json_last_error() === JSON_ERROR_NONE) ? $decodedTpl : $rawTemplate;
        }

        if (!empty($rawTemplate)) {
            if (is_array($rawTemplate)) {
                $bodyData = $rawTemplate;
                array_walk_recursive($bodyData, function (&$val) use ($args) {
                    if (is_string($val)) {
                        foreach ($args as $ak => $av) {
                            if ($val === '{' . $ak . '}') {
                                $val = $av;
                                return;
                            }
                            if (str_contains($val, '{' . $ak . '}')) {
                                $val = str_replace('{' . $ak . '}', is_scalar($av) ? (string) $av : '', $val);
                            }
                        }
                    }
                });
            } else {
                $bodyData = (string) $rawTemplate;
                foreach ($args as $ak => $av) {
                    $bodyData = str_replace('{' . $ak . '}', is_scalar($av) ? (string) $av : '', (string) $bodyData);
                }
            }
        }

        $isForm = false;
        foreach ($headers as $hk => $hv) {
            if (strcasecmp((string) $hk, 'Content-Type') === 0 && str_contains(strtolower((string) $hv), 'application/x-www-form-urlencoded')) {
                $isForm = true;
                break;
            }
        }

        if ($isForm && is_array($bodyData)) {
            $formattedParams = [];
            foreach ($bodyData as $bk => $bv) {
                $formattedParams[$bk] = is_bool($bv) ? ($bv ? '1' : '0') : $bv;
            }
            $postPayload = http_build_query($formattedParams);
        } else {
            $hasCT = false;
            foreach ($formattedHeaders as $h) {
                if (stripos($h, 'Content-Type:') === 0) {
                    $hasCT = true;
                    break;
                }
            }
            if (!$hasCT) {
                $formattedHeaders[] = 'Content-Type: application/json';
            }
            $postPayload = is_array($bodyData) ? json_encode($bodyData) : (string) $bodyData;
        }
    }

    try {
        $curlResult = safe_curl_request($finalUrl, [
            'method' => $method,
            'headers' => $formattedHeaders,
            'body' => $postPayload,
            'timeout' => 12,
        ]);
    } catch (Throwable $e) {
        json_response([
            'ok' => false,
            'status' => 0,
            'latency_ms' => round((microtime(true) - $startTime) * 1000),
            'error' => 'SSRF validation failed: ' . $e->getMessage()
        ], 422);
    }

    $responseBody = $curlResult['body'];
    $httpCode = $curlResult['status'];
    $curlError = $curlResult['error'];
    $finalUrl = $curlResult['final_url'];

    // Auto-detect and bypass anti-bot / aes.js challenges (InfinityFree, ByetHost, etc.)
    if (AntiBotBypass::isChallenge((string) $responseBody)) {
        $solvedCookie = AntiBotBypass::solveChallenge((string) $responseBody);
        if ($solvedCookie) {
            AntiBotBypass::setCachedCookie($finalUrl, $solvedCookie);
            AntiBotBypass::activateCookie($finalUrl, $solvedCookie);

            $retryHeaders = [];
            $hasCookie = false;
            foreach ($formattedHeaders as $h) {
                if (stripos($h, 'Cookie:') === 0) {
                    $rawCookieVal = trim(substr($h, 7));
                    $retryHeaders[] = 'Cookie: ' . AntiBotBypass::injectOrReplaceCookie($rawCookieVal, $solvedCookie);
                    $hasCookie = true;
                } elseif (stripos($h, 'User-Agent:') === 0) {
                    $retryHeaders[] = 'User-Agent: ' . AntiBotBypass::BROWSER_USER_AGENT;
                } else {
                    $retryHeaders[] = $h;
                }
            }
            if (!$hasCookie) {
                $retryHeaders[] = "Cookie: __test={$solvedCookie}";
            }

            try {
                $retryResult = safe_curl_request($finalUrl, [
                    'method' => $method,
                    'headers' => $retryHeaders,
                    'body' => $postPayload,
                    'timeout' => 12,
                ]);

                if (!$retryResult['error'] && $retryResult['body']) {
                    $responseBody = $retryResult['body'];
                    $httpCode = $retryResult['status'];
                    $curlError = '';
                }
            } catch (Throwable $e) {
                // Terminate safely if challenge retry violates SSRF boundaries
            }
        }
    }

    $latencyMs = round((microtime(true) - $startTime) * 1000);

    if ($curlError) {
        json_response([
            'ok' => false,
            'status' => 0,
            'latency_ms' => $latencyMs,
            'error' => "cURL network error: {$curlError}"
        ]);
    }

    $decoded = json_decode($responseBody ?: '', true);
    $filteredData = $decoded ?? $responseBody;

    // Filter by response_path if configured
    $responsePath = trim((string) ($executionConfig['response_path'] ?? ''));
    if ($responsePath !== '' && is_array($decoded)) {
        $segments = explode('.', $responsePath);
        $curr = $decoded;
        foreach ($segments as $seg) {
            if (isset($curr[$seg])) {
                $curr = $curr[$seg];
            } else {
                break;
            }
        }
        $filteredData = $curr;
    }

    json_response([
        'ok' => ($httpCode >= 200 && $httpCode < 400),
        'status' => $httpCode,
        'latency_ms' => $latencyMs,
        'final_url' => $finalUrl,
        'data' => $filteredData
    ]);
} elseif ($category === 'webhook') {
    $targetUrl = trim((string) ($executionConfig['target_url'] ?? ''));
    if ($targetUrl === '') {
        json_response(['ok' => false, 'error' => 'Webhook target URL is missing.'], 422);
    }

    // SEC-01: Multi-layer SSRF validation against private/loopback/cloud-metadata networks
    try {
        assert_safe_outbound_url($targetUrl, true);
    } catch (InvalidArgumentException $e) {
        json_response(['ok' => false, 'error' => 'SSRF validation failed: ' . $e->getMessage()], 422);
    }

    $payload = [
        'event' => 'tool_test',
        'timestamp' => date('c'),
        'payload' => $args
    ];

    try {
        $curlResult = safe_curl_request($targetUrl, [
            'method' => 'POST',
            'headers' => [
                'Content-Type: application/json',
                'User-Agent: WhatsGateway-Webhook-Tester/3.0'
            ],
            'body' => $payload,
            'timeout' => 10,
            'require_https' => true,
        ]);
    } catch (Throwable $e) {
        json_response([
            'ok' => false,
            'status' => 0,
            'latency_ms' => round((microtime(true) - $startTime) * 1000),
            'error' => 'SSRF validation failed: ' . $e->getMessage()
        ], 422);
    }

    $responseBody = $curlResult['body'];
    $httpCode = $curlResult['status'];
    $latencyMs = round((microtime(true) - $startTime) * 1000);

    json_response([
        'ok' => ($httpCode >= 200 && $httpCode < 400),
        'status' => $httpCode,
        'latency_ms' => $latencyMs,
        'final_url' => $curlResult['final_url'],
        'data' => json_decode($responseBody ?: '', true) ?? $responseBody
    ]);
} elseif ($category === 'shared_env_api') {
    $baseUrl = rtrim((string) ($executionConfig['base_url'] ?? ''), '/');
    if ($baseUrl === '') {
        json_response(['ok' => false, 'error' => 'Production Base URL is missing.'], 422);
    }
    $token = trim((string) ($executionConfig['bearer_token'] ?? ''));
    $action = trim((string) ($args['action'] ?? 'health'));

    $targetUrl = $baseUrl . '/' . ltrim($action, '/');

    // SEC-01: Multi-layer SSRF validation against private/loopback/cloud-metadata networks
    try {
        assert_safe_outbound_url($targetUrl);
    } catch (InvalidArgumentException $e) {
        json_response(['ok' => false, 'error' => 'SSRF validation failed: ' . $e->getMessage()], 422);
    }

    $headers = [
        'Accept: application/json',
        'User-Agent: ' . AntiBotBypass::BROWSER_USER_AGENT
    ];
    if ($token !== '') {
        $headers[] = "Authorization: Bearer {$token}";
    }

    $cachedCookie = AntiBotBypass::getCachedCookie($targetUrl);
    if ($cachedCookie) {
        $headers[] = "Cookie: __test={$cachedCookie}";
    }

    try {
        $curlResult = safe_curl_request($targetUrl, [
            'method' => 'GET',
            'headers' => $headers,
            'timeout' => 12,
        ]);
    } catch (Throwable $e) {
        json_response([
            'ok' => false,
            'status' => 0,
            'latency_ms' => round((microtime(true) - $startTime) * 1000),
            'error' => 'SSRF validation failed: ' . $e->getMessage()
        ], 422);
    }

    $responseBody = $curlResult['body'];
    $httpCode = $curlResult['status'];
    $finalUrl = $curlResult['final_url'];

    if (AntiBotBypass::isChallenge((string) $responseBody)) {
        $solvedCookie = AntiBotBypass::solveChallenge((string) $responseBody);
        if ($solvedCookie) {
            AntiBotBypass::setCachedCookie($targetUrl, $solvedCookie);
            AntiBotBypass::activateCookie($targetUrl, $solvedCookie);

            $retryHeaders = [];
            $hasCookie = false;
            foreach ($headers as $h) {
                if (stripos($h, 'Cookie:') === 0) {
                    $rawCookieVal = trim(substr($h, 7));
                    $retryHeaders[] = 'Cookie: ' . AntiBotBypass::injectOrReplaceCookie($rawCookieVal, $solvedCookie);
                    $hasCookie = true;
                } else {
                    $retryHeaders[] = $h;
                }
            }
            if (!$hasCookie) {
                $retryHeaders[] = "Cookie: __test={$solvedCookie}";
            }

            try {
                $retryResult = safe_curl_request($targetUrl, [
                    'method' => 'GET',
                    'headers' => $retryHeaders,
                    'timeout' => 12,
                ]);
                if ($retryResult['body']) {
                    $responseBody = $retryResult['body'];
                    $httpCode = $retryResult['status'];
                }
            } catch (Throwable $e) {
                // Terminate safely if challenge retry violates SSRF boundaries
            }
        }
    }

    $latencyMs = round((microtime(true) - $startTime) * 1000);

    json_response([
        'ok' => ($httpCode >= 200 && $httpCode < 400),
        'status' => $httpCode,
        'latency_ms' => $latencyMs,
        'final_url' => $finalUrl,
        'data' => json_decode($responseBody ?: '', true) ?? $responseBody
    ]);
} else {
    json_response([
        'ok' => true,
        'status' => 200,
        'latency_ms' => 5,
        'message' => 'Built-in handler simulated successfully.',
        'data' => $args
    ]);
}
