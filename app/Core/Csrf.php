<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Synchroniser token CSRF protection.
 *
 * One unpredictable token per session, rotated after login/logout. The token
 * is accepted from the `_token` form field or the `X-CSRF-Token` header.
 * Same-site cookies are a second layer, never the only one.
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        Session::start();

        $token = Session::get(self::KEY);
        if (!is_string($token) || strlen($token) < 40) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::KEY, $token);
        }

        return $token;
    }

    public static function rotate(): void
    {
        Session::set(self::KEY, bin2hex(random_bytes(32)));
    }

    public static function check(?string $token = null): bool
    {
        $expected = Session::get(self::KEY);
        if (!is_string($expected) || $expected === '') {
            return false;
        }

        $token ??= $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }

    /**
     * Verify the token and, when the browser sends them, the Origin/Referer.
     * Rejects the request with 419 when anything looks wrong.
     */
    public static function assert(): void
    {
        if (!self::check()) {
            self::reject('توکن امنیتی فرم معتبر نیست. صفحه را دوباره بارگذاری کن.');
        }

        if (!self::sameOrigin()) {
            self::reject('درخواست از مبدأ نامعتبر ارسال شده است.');
        }
    }

    public static function sameOrigin(): bool
    {
        $source = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
        if ($source === '') {
            // Some privacy tools strip both headers; the token check already ran.
            return true;
        }

        $host = parse_url($source, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return false;
        }

        $allowed = [$_SERVER['HTTP_HOST'] ?? '', $_SERVER['SERVER_NAME'] ?? ''];
        $port    = parse_url($source, PHP_URL_PORT);
        $ownHost = (string) ($_SERVER['HTTP_HOST'] ?? '');

        foreach ($allowed as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                continue;
            }
            if (strcasecmp($host . ($port ? ':' . $port : ''), $candidate) === 0) {
                return true;
            }
            if (strcasecmp($host, $candidate) === 0) {
                return true;
            }
        }

        // Fallback for proxies that rewrite Host but keep a consistent SERVER_NAME.
        return strcasecmp($host, (string) parse_url(config('app.url') ?: '', PHP_URL_HOST)) === 0 && $ownHost !== '';
    }

    private static function reject(string $message): never
    {
        $isApi = str_contains(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/api/');

        if (!headers_sent()) {
            // 419 is not in Apache's built-in status table, and mod_php maps an
            // unknown numeric status to 500. Send the status line explicitly so
            // the client still sees 419.
            header('HTTP/1.1 419 Authentication Timeout', true, 419);
            header('Content-Type: ' . ($isApi ? 'application/json; charset=UTF-8' : 'text/html; charset=UTF-8'));
            header('Cache-Control: no-store');
        }

        if ($isApi) {
            echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
        } else {
            echo '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8">';
            echo '<title>درخواست منقضی</title>';
            echo '<body style="font-family:sans-serif;padding:40px;line-height:2">';
            echo '<h1>درخواست منقضی شده است</h1><p>' . e($message) . '</p>';
            echo '<p><a href="' . e(url('')) . '">بازگشت به خانه</a></p></body></html>';
        }

        exit;
    }
}
