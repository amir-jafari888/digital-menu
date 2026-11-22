<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\AuditLog;

/**
 * Authentication + authorisation.
 *
 * Failed logins are limited on three axes: per IP, per identifier and a hard
 * identifier lockout read from `login_attempts`. Missing accounts still run a
 * dummy password_verify() so response time does not reveal whether the account
 * exists.
 */
final class Auth
{
    private const SESSION_KEY = 'user_id';
    private const MAX_IDENTIFIER_FAILURES = 5;
    private const LOCK_MINUTES = 15;

    /** bcrypt hash of a random throwaway string; only used for timing parity. */
    private const DUMMY_HASH = '$2y$12$ilTekJKY9l4JJ8SyBXUzXeMAZlMzxCjA.JUIukbodyR/IDYle.3u.';

    private static ?array $user = null;
    private static bool $resolved = false;

    /** role => abilities ('*' means everything, 'orders.*' is a prefix). */
    private const ABILITIES = [
        'super_admin' => ['*'],
        'owner'       => [
            'menu.*', 'categories.*', 'products.*', 'orders.*', 'customers.*',
            'rewards.*', 'campaigns.*', 'settings.*', 'uploads.*', 'reports.view',
            'tables.*', 'users.*', 'subscription.view',
        ],
        'staff'       => ['orders.view', 'orders.update', 'menu.view', 'reports.view'],
    ];

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        if (self::$resolved) {
            return self::$user;
        }
        self::$resolved = true;

        Session::start();
        $id = (int) Session::get(self::SESSION_KEY, 0);
        if ($id <= 0) {
            return null;
        }

        $row = Database::one(
            'SELECT u.*, t.name AS tenant_name, t.slug AS tenant_slug, t.status AS tenant_status
             FROM users u
             LEFT JOIN tenants t ON t.id = u.tenant_id
             WHERE u.id = ? AND u.status = \'active\'
             LIMIT 1',
            [$id]
        );

        if ($row === null) {
            self::logout(false);

            return null;
        }

        if (($row['tenant_status'] ?? null) === 'suspended') {
            self::logout(false);

            return null;
        }

        return self::$user = $row;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function id(): int
    {
        return (int) (self::user()['id'] ?? 0);
    }

    public static function tenantId(): ?int
    {
        $tenant = self::user()['tenant_id'] ?? null;

        return $tenant === null ? null : (int) $tenant;
    }

    public static function role(): string
    {
        return (string) (self::user()['role'] ?? '');
    }

    public static function isSuperAdmin(): bool
    {
        return self::role() === 'super_admin';
    }

    public static function is(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    public static function can(string $ability): bool
    {
        $role = self::role();
        if ($role === '') {
            return false;
        }

        foreach (self::ABILITIES[$role] ?? [] as $granted) {
            if ($granted === '*' || $granted === $ability) {
                return true;
            }
            if (str_ends_with($granted, '.*') && str_starts_with($ability, substr($granted, 0, -1))) {
                return true;
            }
        }

        return false;
    }

    public static function requireAbility(string $ability): void
    {
        if (!self::check()) {
            self::redirectToLogin();
        }
        if (!self::can($ability)) {
            self::forbidden();
        }
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            self::redirectToLogin();
        }
    }

    /**
     * @param array<int, string> $roles
     */
    public static function requireRole(array $roles): void
    {
        if (!self::check()) {
            self::redirectToLogin();
        }
        if (!in_array(self::role(), $roles, true)) {
            self::forbidden();
        }
    }

    /**
     * @return array{ok: bool, user?: array, message?: string, retry_after?: int}
     */
    public static function attempt(string $identifier, string $password, ?string $ip = null): array
    {
        $identifier = trim(Validator::normaliseDigits($identifier));
        $ip         = $ip ?? Security::clientIp();
        $ipHash     = Security::ipHash($ip);
        $idHash     = Security::hashIdentifier($identifier);

        $ipLimit = RateLimiter::attempt('login:ip:' . $ipHash, 12, 900, 900);
        if (!$ipLimit['allowed']) {
            return [
                'ok'          => false,
                'message'     => 'تلاش‌های ورود از این شبکه زیاد بود. تا ' . RateLimiter::humanRetry($ipLimit['retry_after']) . ' دیگر صبر کن.',
                'retry_after' => $ipLimit['retry_after'],
            ];
        }

        $idLimit = RateLimiter::attempt('login:id:' . $idHash, 6, 900, 900);
        if (!$idLimit['allowed']) {
            return [
                'ok'          => false,
                'message'     => 'برای این حساب تلاش‌های ناموفق زیادی ثبت شد. تا ' . RateLimiter::humanRetry($idLimit['retry_after']) . ' دیگر صبر کن.',
                'retry_after' => $idLimit['retry_after'],
            ];
        }

        $failures = (int) Database::scalar(
            'SELECT COUNT(*) FROM login_attempts
             WHERE identifier_hash = ? AND success = 0 AND created_at > NOW() - INTERVAL ' . self::LOCK_MINUTES . ' MINUTE',
            [$idHash]
        );

        if ($failures >= self::MAX_IDENTIFIER_FAILURES) {
            self::recordAttempt($idHash, $ipHash, false);

            return [
                'ok'          => false,
                'message'     => 'این حساب موقتاً قفل شده است. تا ' . fa_digits((string) self::LOCK_MINUTES) . ' دقیقهٔ دیگر دوباره تلاش کن.',
                'retry_after' => self::LOCK_MINUTES * 60,
            ];
        }

        if ($identifier === '' || $password === '') {
            return ['ok' => false, 'message' => 'ایمیل/موبایل و رمز عبور را وارد کن.'];
        }

        $user = Database::one(
            'SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1',
            [$identifier, $identifier]
        );

        if ($user === null) {
            // Equalise timing so account existence cannot be probed.
            password_verify($password, self::DUMMY_HASH);
            self::recordAttempt($idHash, $ipHash, false);
            self::audit('auth.login_failed', null, null, ['identifier' => $idHash]);

            return ['ok' => false, 'message' => 'ایمیل/موبایل یا رمز عبور درست نیست.'];
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            self::recordAttempt($idHash, $ipHash, false);
            self::audit('auth.login_failed', (int) $user['id'], $user['tenant_id'] ? (int) $user['tenant_id'] : null, []);

            return ['ok' => false, 'message' => 'ایمیل/موبایل یا رمز عبور درست نیست.'];
        }

        if (($user['status'] ?? '') !== 'active') {
            self::recordAttempt($idHash, $ipHash, false);

            return ['ok' => false, 'message' => 'این حساب غیرفعال شده است. با پشتیبانی تماس بگیر.'];
        }

        if ($user['tenant_id'] !== null) {
            $tenantStatus = Database::scalar('SELECT status FROM tenants WHERE id = ?', [(int) $user['tenant_id']]);
            if ($tenantStatus === 'suspended') {
                self::recordAttempt($idHash, $ipHash, false);

                return ['ok' => false, 'message' => 'دسترسی این کسب‌وکار موقتاً غیرفعال شده است.'];
            }
        }

        if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
            Database::run('UPDATE users SET password_hash = ? WHERE id = ?', [
                password_hash($password, PASSWORD_DEFAULT),
                (int) $user['id'],
            ]);
        }

        self::recordAttempt($idHash, $ipHash, true);
        Database::run(
            'UPDATE users SET last_login_at = NOW(), last_login_ip_hash = ? WHERE id = ?',
            [$ipHash, (int) $user['id']]
        );

        RateLimiter::clear('login:id:' . $idHash);

        self::establish($user);
        self::audit('auth.login', (int) $user['id'], $user['tenant_id'] ? (int) $user['tenant_id'] : null, []);

        return ['ok' => true, 'user' => $user];
    }

    /** Put a verified user into the session. */
    public static function establish(array $user): void
    {
        Session::start();
        Session::regenerate();
        Csrf::rotate();

        Session::set(self::SESSION_KEY, (int) $user['id']);
        self::$user     = $user;
        self::$resolved = true;
    }

    public static function logout(bool $audit = true): void
    {
        Session::start();

        if ($audit && self::$resolved && self::$user !== null) {
            self::audit('auth.logout', (int) self::$user['id'], self::$user['tenant_id'] ? (int) self::$user['tenant_id'] : null, []);
        }

        self::$user     = null;
        self::$resolved = true;

        Session::destroy();
    }

    public static function redirectToLogin(): never
    {
        $target = $_SERVER['REQUEST_URI'] ?? '';
        $next   = '/';
        if (is_string($target) && str_starts_with($target, '/') && !str_starts_with($target, '//')) {
            $next = $target;
        }

        Session::flash('intended', $next);

        Response::redirect(url('login.php') . '?next=' . rawurlencode($next));
    }

    private static function forbidden(): never
    {
        if (Request::wantsJson()) {
            Response::error('برای این کار دسترسی نداری.', 403);
        }

        http_response_code(403);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8">';
        echo '<title>دسترسی غیرمجاز</title><body style="font-family:sans-serif;padding:40px;line-height:2">';
        echo '<h1>دسترسی غیرمجاز</h1><p>این بخش برای نقش شما مجاز نیست.</p>';
        echo '<p><a href="' . e(admin_url()) . '">بازگشت به پنل</a></p></body></html>';
        exit;
    }

    private static function recordAttempt(string $idHash, string $ipHash, bool $success): void
    {
        try {
            Database::run(
                'INSERT INTO login_attempts (identifier_hash, ip_hash, success) VALUES (?, ?, ?)',
                [$idHash, $ipHash, $success ? 1 : 0]
            );
        } catch (\Throwable $e) {
            Logger::warning('Could not record login attempt', ['message' => $e->getMessage()]);
        }
    }

    private static function audit(string $action, ?int $userId, ?int $tenantId, array $meta): void
    {
        try {
            AuditLog::record($action, $userId, $tenantId, $meta);
        } catch (\Throwable $e) {
            Logger::warning('Could not write audit log', ['message' => $e->getMessage()]);
        }
    }
}
