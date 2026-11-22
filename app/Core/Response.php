<?php
declare(strict_types=1);

namespace App\Core;

/**
 * JSON / HTML / redirect helpers.
 */
final class Response
{
    /** @param array<string, mixed> $data */
    public static function json(array $data, int $status = 200): never
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=UTF-8');
            header('Cache-Control: no-store');
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function ok(array $data = [], int $status = 200): never
    {
        self::json(['ok' => true] + $data, $status);
    }

    public static function error(string $message, int $status = 400, array $extra = []): never
    {
        self::json(['ok' => false, 'error' => $message] + $extra, $status);
    }

    public static function notFound(string $message = 'مسیر مورد نظر پیدا نشد.'): never
    {
        if (Request::wantsJson()) {
            self::error($message, 404);
        }

        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8">';
        echo '<title>۴۰۴</title><body style="font-family:sans-serif;padding:40px;line-height:2">';
        echo '<h1>صفحه پیدا نشد</h1><p>' . e($message) . '</p>';
        echo '<p><a href="' . e(url('')) . '">بازگشت به خانه</a></p></body></html>';
        exit;
    }

    public static function redirect(string $path, int $status = 303): never
    {
        redirect_to($path, $status);
    }

    public static function back(string $fallback = '/'): never
    {
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $host    = (string) parse_url($referer, PHP_URL_HOST);
        $own     = (string) ($_SERVER['HTTP_HOST'] ?? '');

        if ($referer !== '' && $host !== '' && strcasecmp($host, $own) === 0) {
            $path = (string) parse_url($referer, PHP_URL_PATH);
            $path = $path === '' ? '/' : $path;
            $query = parse_url($referer, PHP_URL_QUERY);
            if (is_string($query) && $query !== '') {
                $path .= '?' . $query;
            }
            self::redirect($path);
        }

        self::redirect($fallback);
    }
}
