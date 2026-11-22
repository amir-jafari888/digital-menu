<?php
declare(strict_types=1);

use App\Core\Csrf;

/**
 * Shared helpers for the backend and the pages.
 * url(), asset() and e() live in config/app.php so the static pages keep working.
 */

function config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require ROOT_PATH . '/config/config.php';
    }
    if ($key === null) {
        return $config;
    }

    $value = $config;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }

    return $value;
}

function root_path(string $path = ''): string
{
    return ROOT_PATH . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

function storage_path(string $path = ''): string
{
    return root_path('storage' . ($path === '' ? '' : '/' . ltrim($path, '/')));
}

function admin_url(string $path = ''): string
{
    return url('admin/index.php') . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

function api_url(string $path = ''): string
{
    return url('api/index.php') . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

/** Format an integer amount of Toman with Persian digits. */
function money(int|float|string $amount): string
{
    $formatted = number_format((float) $amount, 0, '.', '٬');

    return fa_digits($formatted);
}

function fa_digits(string $value): string
{
    return strtr($value, [
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ]);
}

/** Convert Gregorian date to the Jalali calendar (no intl extension needed). */
function jalali_date(?string $datetime, bool $withTime = false): string
{
    if ($datetime === null || $datetime === '') {
        return '—';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '—';
    }

    [$gy, $gm, $gd] = array_map('intval', explode('-', date('Y-m-d', $ts)));
    $gDays = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gy2 = ($gm > 2) ? $gy + 1 : $gy;
    $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100)
        + intdiv($gy2 + 399, 400) + $gd + $gDays[$gm - 1];
    $jy = -1595 + (33 * intdiv($days, 12053));
    $days %= 12053;
    $jy += 4 * intdiv($days, 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += intdiv($days - 1, 365);
        $days = ($days - 1) % 365;
    }
    if ($days < 186) {
        $jm = 1 + intdiv($days, 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intdiv($days - 186, 30);
        $jd = 1 + (($days - 186) % 30);
    }

    $out = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    if ($withTime) {
        $out .= ' ' . date('H:i', $ts);
    }

    return fa_digits($out);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

/** Coloured status pill for orders, tenants, payments and messages. */
function status_badge(string $status): string
{
    $map = [
        'pending'   => ['در انتظار', 'warn'],
        'confirmed' => ['تأیید شده', 'info'],
        'preparing' => ['در حال آماده‌سازی', 'info'],
        'ready'     => ['آماده تحویل', 'ok'],
        'completed' => ['تکمیل شده', 'ok'],
        'canceled'  => ['لغو شده', 'danger'],
        'active'    => ['فعال', 'ok'],
        'inactive'  => ['غیرفعال', 'muted'],
        'disabled'  => ['غیرفعال', 'muted'],
        'suspended' => ['معلق', 'danger'],
        'draft'     => ['پیش‌نویس', 'muted'],
        'paused'    => ['متوقف', 'warn'],
        'ended'     => ['پایان‌یافته', 'muted'],
        'new'       => ['جدید', 'info'],
        'read'      => ['خوانده‌شده', 'muted'],
        'archived'  => ['بایگانی', 'muted'],
        'used'      => ['استفاده‌شده', 'ok'],
        'paid'      => ['پرداخت‌شده', 'ok'],
        'failed'    => ['ناموفق', 'danger'],
        'refunded'  => ['بازگشت‌داده‌شده', 'warn'],
        'trial'     => ['آزمایشی', 'info'],
        'expired'   => ['منقضی', 'danger'],
        'open'      => ['باز', 'warn'],
    ];

    [$label, $tone] = $map[$status] ?? [$status, 'muted'];

    return '<span class="badge badge--' . e($tone) . '">' . e($label) . '</span>';
}

/** Preserve the current query string while overriding some keys. */
function query_with(array $overrides): string
{
    $query = $_GET;
    foreach ($overrides as $key => $value) {
        if ($value === null) {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }

    return $query === [] ? '' : '?' . http_build_query($query);
}

/** Minimal pagination markup (returns '' when there is a single page). */
function paginate_links(int $page, int $pages, string $baseUrl): string
{
    if ($pages <= 1) {
        return '';
    }

    $page = max(1, min($pages, $page));
    $items = [];

    if ($page > 1) {
        $items[] = '<a class="page-link" href="' . e($baseUrl . query_with(['page' => $page - 1])) . '" rel="prev">قبلی</a>';
    }

    $items[] = '<span class="page-link is-current" aria-current="page">' . fa_digits((string) $page) . ' / ' . fa_digits((string) $pages) . '</span>';

    if ($page < $pages) {
        $items[] = '<a class="page-link" href="' . e($baseUrl . query_with(['page' => $page + 1])) . '" rel="next">بعدی</a>';
    }

    return '<nav class="pagination" aria-label="صفحه‌بندی">' . implode('', $items) . '</nav>';
}

/** @param array<string, string> $errors */
function field_error(array $errors, string $field): string
{
    return isset($errors[$field])
        ? '<p class="field__error" id="err-' . e($field) . '">' . e($errors[$field]) . '</p>'
        : '';
}

function is_selected(mixed $a, mixed $b): string
{
    return (string) $a === (string) $b ? ' selected' : '';
}

function is_checked(bool $condition): string
{
    return $condition ? ' checked' : '';
}

/** Inline 24px stroke icon for the admin sidebar (no external icon font). */
function admin_icon(string $name): string
{
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="11" width="7" height="10" rx="1.5"/><rect x="3" y="15" width="7" height="6" rx="1.5"/>',
        'orders'    => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z"/><path d="M9 8h6M9 12h6"/>',
        'products'  => '<path d="M4 8h12v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8z"/><path d="M16 10h2a2 2 0 0 1 0 4h-2"/><path d="M7 8V5a2 2 0 0 1 2-2h2"/>',
        'categories'=> '<path d="M4 6h16M4 12h16M4 18h10"/>',
        'menus'     => '<rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><path d="M14 14h3v3h-3zM17 17h3v3h-3"/>',
        'customers' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16 5.5a3 3 0 0 1 0 5.8M18 14.6c2 .7 3.4 2.4 3.4 5"/>',
        'rewards'   => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v8h14v-8"/><path d="M12 8v12"/><path d="M12 8C10 4.5 6.5 4 6.5 6.3 6.5 8 9 8 12 8zM12 8c2-3.5 5.5-4 5.5-1.7C17.5 8 15 8 12 8z"/>',
        'campaigns' => '<path d="M3 11v3l4 .8V10L3 11z"/><path d="M7 10l9-5v14l-9-5"/><path d="M18 9a4 4 0 0 1 0 6"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/>',
        'users'     => '<circle cx="12" cy="8" r="3.4"/><path d="M5 20c0-3.6 3-6 7-6s7 2.4 7 6"/>',
        'tenants'   => '<path d="M4 21V5l8-3v19"/><path d="M12 9h8v12h-8"/><path d="M7 7h1M7 11h1M7 15h1M15 13h2M15 17h2"/>',
        'messages'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'subs'      => '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19"/><path d="M6 15h4"/>',
        'logs'      => '<path d="M12 3l8 3v6c0 5-3.4 8-8 9-4.6-1-8-4-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/>',
        'logout'    => '<path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"/><path d="M10 8l-4 4 4 4"/><path d="M6 12h10"/>',
    ];

    $path = $paths[$name] ?? $paths['dashboard'];

    return '<svg class="nav-icon" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

/** Safe redirect: only local paths, never a full external URL. */
function redirect_to(string $path, int $status = 303): never
{
    if (preg_match('#^(https?:)?//#i', $path)) {
        $path = '/';
    }
    if (str_contains($path, "\r") || str_contains($path, "\n")) {
        $path = '/';
    }

    header('Location: ' . $path, true, $status);
    exit;
}
