<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Hardened native session wrapper.
 *
 * - Strict mode (client-supplied unknown IDs are rejected, blocking fixation)
 * - HttpOnly + SameSite=Lax cookies, Secure on HTTPS
 * - Idle timeout and absolute lifetime
 * - User-agent fingerprint binding (a stolen cookie is not enough)
 * - Flash messages
 */
final class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;

            return;
        }
        self::$started = true;

        $config = config('session');
        $idle   = max(300, (int) $config['idle']);
        $abs    = max($idle, (int) $config['absolute']);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', (string) $abs);
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '100');
        // session.sid_length / session.sid_bits_per_character are deprecated in
        // PHP 8.4+ (the defaults are already strong), so only set them on 8.3.
        if (PHP_VERSION_ID >= 80300 && PHP_VERSION_ID < 80400) {
            ini_set('session.sid_length', '48');
            ini_set('session.sid_bits_per_character', '6');
        }

        session_name((string) $config['name']);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => Security::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        if (!@session_start()) {
            throw new \RuntimeException('نشست کاربر آغاز نشد.', 500);
        }

        $now       = time();
        $created   = isset($_SESSION['_created']) ? (int) $_SESSION['_created'] : null;
        $seen      = isset($_SESSION['_seen']) ? (int) $_SESSION['_seen'] : null;
        $fingerprint = Security::hashIdentifier((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

        $expired = ($created !== null && $now - $created > $abs)
            || ($seen !== null && $now - $seen > $idle)
            || (isset($_SESSION['_fp']) && !hash_equals((string) $_SESSION['_fp'], $fingerprint));

        if ($expired) {
            $hadUser = isset($_SESSION['user_id']);
            self::destroy();
            self::begin();

            if ($hadUser) {
                self::flash('auth_notice', 'نشست شما به پایان رسید. لطفاً دوباره وارد شوید.');
            }

            return;
        }

        $_SESSION['_created'] ??= $now;
        $_SESSION['_seen']    = $now;
        $_SESSION['_fp']     ??= $fingerprint;

        self::rotateFlash();
    }

    /** Start a brand new session (after a timeout or privilege change). */
    public static function regenerate(): void
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['_created'] = time();
        $_SESSION['_seen']    = time();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);

        return $value;
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash_next'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        if (!isset($_SESSION['_flash_now'][$key])) {
            return $default;
        }
        $value = $_SESSION['_flash_now'][$key];
        unset($_SESSION['_flash_now'][$key]);

        return $value;
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            $params = session_get_cookie_params();
            if (!headers_sent()) {
                setcookie(session_name(), '', [
                    'expires'  => time() - 3600,
                    'path'     => $params['path'] ?: '/',
                    'domain'   => $params['domain'] ?: '',
                    'secure'   => (bool) $params['secure'],
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            session_destroy();
        }

        self::$started = false;
    }

    /** Create a fresh session id without losing the current process. */
    private static function begin(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_id('');
            @session_start();
        }
        $_SESSION['_created'] = time();
        $_SESSION['_seen']    = time();
        $_SESSION['_fp']      = Security::hashIdentifier((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $_SESSION['_flash_now']  = [];
        $_SESSION['_flash_next'] = [];
    }

    private static function rotateFlash(): void
    {
        $_SESSION['_flash_now']  = $_SESSION['_flash_next'] ?? [];
        $_SESSION['_flash_next'] = [];
    }
}
