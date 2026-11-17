<?php
declare(strict_types=1);

/**
 * Monochi — application bootstrap.
 *
 * Kept deliberately tiny: the public site is a set of plain PHP pages that
 * share partials. The same helpers are ready to be reused by the MVC layer
 * once the backend is implemented.
 */

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

const SITE_NAME        = 'منوچی';
const SITE_NAME_LATIN  = 'Monochi';
const SITE_TAGLINE     = 'منوی دیجیتال، باشگاه مشتریان و تبلیغات';
const SITE_DESCRIPTION = 'منوچی منوی دیجیتال کافه و رستوران، باشگاه مشتریان و تبلیغات در اپ سراغ را در یک پلتفرم ساده جمع می‌کند.';

/**
 * URL path of the directory that holds the public pages.
 *
 * Works whether the project is served from the domain root (/) or from a
 * sub-folder (/digital-menu-latest/), which is the usual XAMPP setup.
 */
function app_base(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $dir    = str_replace('\\', '/', dirname($script));
    $dir    = rtrim($dir, '/');

    return $dir === '' ? '' : $dir;
}

/** Build an absolute-path URL that respects the install location. */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');

    return app_base() . ($path === '' ? '/' : '/' . $path);
}

/** URL for a file inside /assets. */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** Escape untrusted text for HTML output. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Absolute URL of the current request, used for canonical/OG tags. */
function canonical_url(string $path = ''): string
{
    $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // The Host header is client-controlled: keep canonical/OG URLs clean.
    if (!preg_match('/^[A-Za-z0-9.\-]+(:\d{1,5})?$/', $host)) {
        $host = 'localhost';
    }

    return $scheme . '://' . $host . url($path);
}

/** Primary header navigation. */
function nav_items(): array
{
    return [
        ['slug' => 'features',    'label' => 'امکانات',        'href' => 'features.php'],
        ['slug' => 'loyalty',     'label' => 'باشگاه مشتریان', 'href' => 'loyalty.php'],
        ['slug' => 'advertising', 'label' => 'تبلیغات',        'href' => 'advertising.php'],
        ['slug' => 'pricing',     'label' => 'تعرفه',          'href' => 'pricing.php'],
        ['slug' => 'faq',         'label' => 'سوالات متداول',   'href' => 'faq.php'],
    ];
}

/** Returns " is-active" when the given slug is the current page. */
function nav_state(string $slug, string $active): string
{
    return $slug === $active ? ' is-active' : '';
}
