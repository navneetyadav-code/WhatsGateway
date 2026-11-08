<?php

declare(strict_types=1);

function create_api_key(int $userId, string $name = 'API key'): array
{
    // SEC-06: Formalize Single Bearer Token Model.
    // The API token is a high-entropy 192-bit cryptographic secret token ('wa_live_...').
    // It is stored at rest as a SHA-256 hash (key_hash) and serves as the sole authentication secret.
    $apiKey = 'wa_live_' . bin2hex(random_bytes(24));
    $keyHash = hash('sha256', $apiKey);

    $stmt = Database::pdo()->prepare(
        'INSERT INTO api_keys (user_id, name, key_hash, key_prefix, secret_hash) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        trim($name) ?: 'API key',
        $keyHash,
        substr($apiKey, 0, 22),
        password_hash($apiKey, PASSWORD_DEFAULT),
    ]);

    return [
        'id' => (int) Database::pdo()->lastInsertId(),
        'api_key' => $apiKey,
        'api_secret' => $apiKey, // Backwards compatibility for callers inspecting credentials array
    ];
}

function regenerate_api_credentials(int $userId): array
{
    return create_api_key($userId, 'Generated key');
}

function list_api_keys(int $userId): array
{
    $stmt = Database::pdo()->prepare(
        'SELECT id, name, key_prefix, status, ip_allowlist, rate_per_minute, rate_per_hour, rate_per_day, last_used_at, created_at, allow_humanize
         FROM api_keys WHERE user_id = ? ORDER BY id DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function update_api_key_status(int $userId, int $keyId, string $status): void
{
    if (!in_array($status, ['active', 'disabled'], true)) {
        return;
    }

    $stmt = Database::pdo()->prepare('UPDATE api_keys SET status = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$status, $keyId, $userId]);
}

function delete_api_key(int $userId, int $keyId): void
{
    $stmt = Database::pdo()->prepare('DELETE FROM api_keys WHERE id = ? AND user_id = ?');
    $stmt->execute([$keyId, $userId]);
}

function update_api_key_limits(int $userId, int $keyId, array $data): void
{
    $minute = max(1, min(300, (int) ($data['rate_per_minute'] ?? 20)));
    $hour = max($minute, min(5000, (int) ($data['rate_per_hour'] ?? 300)));
    $day = max($hour, min(50000, (int) ($data['rate_per_day'] ?? 1000)));
    $allowlist = trim((string) ($data['ip_allowlist'] ?? ''));
    $allowHumanize = empty($data['allow_humanize']) ? 0 : 1;

    $stmt = Database::pdo()->prepare(
        'UPDATE api_keys SET rate_per_minute = ?, rate_per_hour = ?, rate_per_day = ?, ip_allowlist = ?, allow_humanize = ? WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([$minute, $hour, $day, $allowlist ?: null, $allowHumanize, $keyId, $userId]);
}

function authenticate_api_request(): ?array
{
    // SEC-06: Single Bearer Token Model.
    // The API token is a high-entropy 192-bit cryptographic secret token ('wa_live_...').
    $key = $_SERVER['HTTP_X_API_KEY'] ?? '';
    $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

    if (!$authorization && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (!$key) {
            $key = $headers['X-Api-Key'] ?? $headers['x-api-key'] ?? '';
        }
    }

    // Single Bearer Token format (Authorization: Bearer <token>)
    if (!$key && str_starts_with($authorization, 'Bearer ')) {
        $token = substr($authorization, 7);
        // Backwards compatibility: if legacy key:secret was passed, extract the key
        if (str_contains($token, ':')) {
            $token = explode(':', $token, 2)[0];
        }
        $key = trim($token);
    }

    // Fallback: check POST body or guarded query params for single-key authentication
    if (!$key) {
        // Priority 1: Request POST body payload (safe from access logs)
        $key = $_POST['api_key'] ?? $_POST['key'] ?? $_POST['access_token'] ?? '';

        // Priority 2: JSON payload if POST body was json
        if (!$key && !empty($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
            $jsonData = request_json();
            $key = $jsonData['api_key'] ?? $jsonData['key'] ?? $jsonData['access_token'] ?? '';
        }

        // Priority 3: GET query parameter (Guarded to prevent log leakage; requires ALLOW_QUERY_AUTH=true)
        if (!$key && (bool) app_config('security.allow_query_auth', false)) {
            $key = $_GET['api_key'] ?? $_GET['key'] ?? $_GET['access_token'] ?? '';
        }
    }

    if (!is_string($key) || $key === '') {
        return null;
    }

    $stmt = Database::pdo()->prepare(
        'SELECT api_keys.*, users.email, users.name AS user_name
         FROM api_keys
         JOIN users ON users.id = api_keys.user_id
         WHERE api_keys.key_hash = ? LIMIT 1'
    );
    $stmt->execute([hash('sha256', $key)]);
    $apiKey = $stmt->fetch();

    if (!$apiKey || $apiKey['status'] !== 'active') {
        return null;
    }

    if (!api_key_ip_allowed($apiKey, client_ip())) {
        return null;
    }

    Database::pdo()->prepare('UPDATE api_keys SET last_used_at = NOW() WHERE id = ?')->execute([$apiKey['id']]);

    return $apiKey;
}

function api_key_ip_allowed(array $apiKey, string $ip): bool
{
    $allowlist = trim((string) ($apiKey['ip_allowlist'] ?? ''));
    if ($allowlist === '') {
        return true;
    }

    $allowed = array_filter(array_map('trim', preg_split('/[\s,]+/', $allowlist) ?: []));
    return in_array($ip, $allowed, true);
}

function client_ip(): string
{
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

    // SEC-07: Configurable trusted proxies with safe default for container/loopback environments
    $customProxies = app_config('security.trusted_proxies');
    $isProxy = false;
    if ($customProxies !== null && $customProxies !== '') {
        $trustedList = array_filter(array_map('trim', explode(',', (string) $customProxies)));
        $isProxy = in_array($remote, $trustedList, true);
    } else {
        $isProxy = in_array($remote, ['127.0.0.1', '::1'], true)
            || str_starts_with($remote, '10.')
            || (filter_var($remote, FILTER_VALIDATE_IP) !== false && filter_var($remote, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false)
            || str_starts_with($remote, '192.168.');
    }

    if ($isProxy) {
        // 1. Cloudflare Connecting IP (authenticated by edge)
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $cfIp = trim((string) $_SERVER['HTTP_CF_CONNECTING_IP']);
            if (filter_var($cfIp, FILTER_VALIDATE_IP)) {
                return $cfIp;
            }
        }

        // 2. X-Real-IP (set by Nginx proxy_set_header X-Real-IP $remote_addr)
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $realIp = trim((string) $_SERVER['HTTP_X_REAL_IP']);
            if (filter_var($realIp, FILTER_VALIDATE_IP)) {
                return $realIp;
            }
        }

        // 3. X-Forwarded-For: Right-to-left evaluation to prevent client header injection
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $rawIps = array_filter(array_map('trim', explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])));
            if (!empty($rawIps)) {
                // Traverse right-to-left, skipping intermediate private/reserved proxy hops
                $selectedIp = null;
                for ($i = count($rawIps) - 1; $i >= 0; $i--) {
                    $candidate = $rawIps[$i];
                    if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                        $selectedIp = $candidate;
                        // Stop at the first non-private public IP
                        if (function_exists('is_private_ip') && !is_private_ip($candidate)) {
                            return $candidate;
                        }
                    }
                }
                if ($selectedIp !== null) {
                    return $selectedIp;
                }
            }
        }

        // 4. Client-IP fallback
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $clientIp = trim((string) $_SERVER['HTTP_CLIENT_IP']);
            if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
                return $clientIp;
            }
        }
    }

    return $remote;
}
