<?php
declare(strict_types=1);

/**
 * Backend bootstrap.
 *
 * Loaded by entry points that need the database, sessions or authentication
 * (admin panel, JSON API, login/register/contact, public menu).
 * The static marketing pages keep requiring config/app.php only.
 */

use App\Core\Env;
use App\Core\Logger;

if (defined('MONOCHI_BOOTSTRAPPED')) {
    return;
}
define('MONOCHI_BOOTSTRAPPED', true);
define('ROOT_PATH', dirname(__DIR__));

// PSR-4 style autoloader for the App\ namespace.
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = ROOT_PATH . '/app/' . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require_once ROOT_PATH . '/config/app.php';   // constants + url()/asset()/e()
require_once ROOT_PATH . '/app/Helpers/functions.php';
require_once ROOT_PATH . '/app/Core/Logger.php';

Env::load(ROOT_PATH . '/.env');
date_default_timezone_set((string) Env::get('APP_TIMEZONE', 'Asia/Tehran'));

// Surface warnings as exceptions (they are bugs). Deprecations are logged and
// ignored so a newer PHP minor cannot take the whole site down.
error_reporting(E_ALL);
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    if (($severity & (E_DEPRECATED | E_USER_DEPRECATED)) !== 0) {
        Logger::warning('PHP deprecation', ['message' => $message, 'file' => $file . ':' . $line]);

        return true;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$isApi = str_contains(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/api/');

set_exception_handler(static function (Throwable $e) use ($isApi): void {
    Logger::error('Unhandled exception', [
        'type'    => $e::class,
        'message' => $e->getMessage(),
        'file'    => $e->getFile() . ':' . $e->getLine(),
    ]);

    $debug = (bool) config('app.debug');
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: ' . ($isApi ? 'application/json; charset=UTF-8' : 'text/html; charset=UTF-8'));
    }

    if ($isApi) {
        echo json_encode([
            'ok'    => false,
            'error' => $debug ? $e->getMessage() : 'خطای داخلی سرور.',
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    echo '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8">';
    echo '<title>خطای سرور</title><body style="font-family:sans-serif;padding:40px">';
    echo '<h1>خطای داخلی سرور</h1>';
    echo '<p>مشکلی پیش آمد. لطفاً کمی بعد دوباره تلاش کن.</p>';
    if ($debug) {
        echo '<pre dir="ltr" style="white-space:pre-wrap">' . e($e->getMessage()) . "\n" . e($e->getFile() . ':' . $e->getLine()) . '</pre>';
    }
    echo '</body></html>';
});
