<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Security helpers: headers, client address, opaque hashes, signed media URLs.
 */
final class Security
{
    /** Response headers applied to every dynamic response. */
    public static function headers(bool $isApi = false): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');

        $csp = "default-src 'self'; "
            . "script-src 'self'; "
            // Inline style *attributes* are used for tokens (e.g. --enter-delay) and M3 values.
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
            . "font-src 'self' https://fonts.gstatic.com; "
            . "img-src 'self' data:; "
            . "connect-src 'self'; "
            . "form-action 'self'; "
            . "frame-ancestors 'none'; "
            . "base-uri 'self'; "
            . "object-src 'none'";

        header('Content-Security-Policy: ' . $csp);

        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        if ($isApi) {
            header('Cache-Control: no-store');
        }
    }

    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        return (($_SERVER['SERVER_PORT'] ?? '') === '443');
    }

    /**
     * Client IP. X-Forwarded-For is honoured only when the direct peer is a
     * configured trusted proxy, otherwise clients could spoof their address.
     */
    public static function clientIp(): string
    {
        $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $proxies = (array) config('security.trusted_proxies', []);

        if ($proxies && in_array($remote, $proxies, true)) {
            $forwarded = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
            foreach (array_reverse(array_map('trim', explode(',', $forwarded))) as $candidate) {
                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return $candidate;
                }
            }
        }

        return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '0.0.0.0';
    }

    /** Non-reversible identifier for logs and rate limits (GDPR friendly). */
    public static function ipHash(?string $ip = null): string
    {
        $ip ??= self::clientIp();

        return hash_hmac('sha256', $ip, self::appKey());
    }

    public static function hashIdentifier(string $value): string
    {
        return hash_hmac('sha256', mb_strtolower(trim($value)), self::appKey());
    }

    public static function randomToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /** Signed URL for a private upload: media.php?f=ID&e=TS&s=SIG */
    public static function signMedia(int $fileId, int $expires): string
    {
        return hash_hmac('sha256', $fileId . '|' . $expires, self::appKey());
    }

    public static function verifyMedia(int $fileId, int $expires, string $signature): bool
    {
        if ($expires < time()) {
            return false;
        }

        return hash_equals(self::signMedia($fileId, $expires), $signature);
    }

    public static function mediaUrl(int $fileId, int $ttl = 86400 * 30): string
    {
        $expires = time() + $ttl;

        return url('media.php') . '?' . http_build_query([
            'f' => $fileId,
            'e' => $expires,
            's' => self::signMedia($fileId, $expires),
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /** Accept only http(s) links; neutralises javascript:/data: URLs. */
    public static function safeUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '' || strlen($url) > 500) {
            return null;
        }
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            return null;
        }
        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return null;
        }

        return $url;
    }

    private static function appKey(): string
    {
        $key = (string) config('app.key', '');
        if (strlen($key) < 32) {
            // Deterministic fallback keeps local installs working; .env.example
            // tells operators to set a real key in production.
            $key = hash('sha256', 'monochi-local-fallback|' . root_path());
        }

        return $key;
    }
}
