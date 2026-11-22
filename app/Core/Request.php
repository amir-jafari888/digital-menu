<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Read-only view of the current HTTP request.
 *
 * JSON bodies and form fields are exposed through one accessor, but the two
 * are never merged blindly: JSON is only parsed when the content type says so.
 */
final class Request
{
    private static ?array $payload = null;

    public static function method(): string
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }

        return $method;
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /** Path below the front controller, e.g. "/products/12". */
    public static function path(): string
    {
        $path = (string) ($_SERVER['PATH_INFO'] ?? '');

        if ($path === '') {
            $uri  = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
            $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
            if ($script !== '' && str_starts_with($uri, $script)) {
                $path = substr($uri, strlen($script));
            } else {
                $path = $uri;
            }
        }

        $path = rawurldecode($path);
        $path = str_replace('\\', '/', $path);

        // Collapse duplicate slashes and neutralise traversal attempts.
        $path = preg_replace('#/+#', '/', $path) ?? '/';
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            return '/';
        }

        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /** @return array<string, mixed> */
    public static function all(): array
    {
        if (self::$payload !== null) {
            return self::$payload;
        }

        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
        $payload     = $_POST;

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input', false, null, 0, 262_144);
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $payload = $decoded;
                }
            }
        }

        return self::$payload = $payload;
    }

    public static function input(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function string(string $key, string $default = ''): string
    {
        $value = self::all()[$key] ?? $default;

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::all()[$key] ?? null;
        if ($value === null || $value === '' || is_array($value)) {
            return $default;
        }

        return (int) Validator::normaliseDigits((string) $value);
    }

    public static function bool(string $key): bool
    {
        $value = self::all()[$key] ?? null;

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function query(string $key, string $default = ''): string
    {
        $value = $_GET[$key] ?? $default;

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public static function ip(): string
    {
        return Security::clientIp();
    }

    public static function userAgent(): string
    {
        return mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    public static function wantsJson(): bool
    {
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $xhr    = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

        return str_contains($accept, 'application/json') || $xhr === 'xmlhttprequest';
    }

    public static function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return isset($_SERVER[$key]) ? (string) $_SERVER[$key] : null;
    }
}
