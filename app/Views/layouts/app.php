<?php
/**
 * Admin shell.
 *
 * @var string $content
 * @var string $pageTitle
 * @var string $active
 * @var string|null $pageActions
 */

use App\Core\Auth;
use App\Core\Session;

$user       = Auth::user() ?? [];
$pageTitle  = $pageTitle ?? 'پنل مدیریت';
$active     = $active ?? '';
$pageActions = $pageActions ?? '';
$tenantName = (string) ($user['tenant_name'] ?? 'پلتفرم منوچی');
$roleLabel  = match ((string) ($user['role'] ?? '')) {
    'super_admin' => 'مدیر پلتفرم',
    'owner'       => 'مالک کسب‌وکار',
    'staff'       => 'کارمند',
    default       => 'کاربر',
};
$mustChange = (int) ($user['must_change_password'] ?? 0) === 1;

$success = Session::getFlash('success');
$error   = Session::getFlash('error');
$notice  = Session::getFlash('auth_notice');

$groups = [
    [
        'label' => 'فروش',
        'items' => [
            ['key' => 'dashboard', 'label' => 'داشبورد', 'href' => admin_url(), 'icon' => 'dashboard', 'ability' => 'orders.view'],
            ['key' => 'orders', 'label' => 'سفارش‌ها', 'href' => admin_url('orders'), 'icon' => 'orders', 'ability' => 'orders.view'],
        ],
    ],
    [
        'label' => 'منو',
        'items' => [
            ['key' => 'products', 'label' => 'محصولات', 'href' => admin_url('products'), 'icon' => 'products', 'ability' => 'products.view'],
            ['key' => 'categories', 'label' => 'دسته‌بندی‌ها', 'href' => admin_url('categories'), 'icon' => 'categories', 'ability' => 'categories.view'],
            ['key' => 'menus', 'label' => 'منوها و میزها', 'href' => admin_url('menus'), 'icon' => 'menus', 'ability' => 'menu.view'],
        ],
    ],
    [
        'label' => 'مشتریان',
        'items' => [
            ['key' => 'customers', 'label' => 'مشتریان', 'href' => admin_url('customers'), 'icon' => 'customers', 'ability' => 'customers.view'],
            ['key' => 'rewards', 'label' => 'جوایز', 'href' => admin_url('rewards'), 'icon' => 'rewards', 'ability' => 'rewards.view'],
        ],
    ],
    [
        'label' => 'رشد',
        'items' => [
            ['key' => 'campaigns', 'label' => 'کمپین‌های تبلیغاتی', 'href' => admin_url('campaigns'), 'icon' => 'campaigns', 'ability' => 'campaigns.view'],
        ],
    ],
    [
        'label' => 'مدیریت',
        'items' => [
            ['key' => 'settings', 'label' => 'تنظیمات', 'href' => admin_url('settings'), 'icon' => 'settings', 'ability' => null],
            ['key' => 'users', 'label' => 'کاربران', 'href' => admin_url('users'), 'icon' => 'users', 'ability' => 'users.view'],
        ],
    ],
];

if (Auth::isSuperAdmin()) {
    $groups[] = [
        'label' => 'پلتفرم',
        'items' => [
            ['key' => 'tenants', 'label' => 'کسب‌وکارها', 'href' => admin_url('tenants'), 'icon' => 'tenants', 'ability' => 'tenants.view'],
            ['key' => 'subscriptions', 'label' => 'اشتراک‌ها', 'href' => admin_url('subscriptions'), 'icon' => 'subs', 'ability' => 'platform.subscriptions'],
            ['key' => 'messages', 'label' => 'پیام‌های تماس', 'href' => admin_url('messages'), 'icon' => 'messages', 'ability' => 'platform.messages'],
            ['key' => 'logs', 'label' => 'گزارش امنیتی', 'href' => admin_url('logs'), 'icon' => 'logs', 'ability' => 'platform.logs'],
        ],
    ];
}
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f7faf3">
    <meta name="color-scheme" content="light">
    <meta name="robots" content="noindex, nofollow">

    <title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>

    <link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/system.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>

<body class="admin-body">
    <a class="skip-link" href="#main-content">رفتن به محتوای اصلی</a>

    <div class="admin-shell">
        <aside class="admin-sidebar" id="admin-sidebar" aria-label="منوی پنل">
            <div class="admin-brand">
                <span class="admin-brand__mark" aria-hidden="true">م</span>
                <span class="admin-brand__text">
                    <strong><?= e(SITE_NAME) ?></strong>
                    <small>پنل مدیریت</small>
                </span>
            </div>

            <div class="admin-tenant">
                <span class="admin-tenant__label">کسب‌وکار</span>
                <span class="admin-tenant__name"><?= e($tenantName) ?></span>
            </div>

            <nav class="admin-nav">
                <?php foreach ($groups as $group): ?>
                    <?php
                    $items = array_filter($group['items'], static fn (array $item): bool => $item['ability'] === null || Auth::can($item['ability']));
                    if ($items === []) {
                        continue;
                    }
                    ?>
                    <div class="admin-nav__group">
                        <p class="admin-nav__label"><?= e($group['label']) ?></p>
                        <?php foreach ($items as $item): ?>
                            <a class="admin-nav__link<?= $active === $item['key'] ? ' is-active' : '' ?>"
                               href="<?= e($item['href']) ?>"
                               <?= $active === $item['key'] ? 'aria-current="page"' : '' ?>>
                                <?= admin_icon($item['icon']) ?>
                                <span><?= e($item['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </nav>

            <div class="admin-user">
                <div class="admin-user__avatar" aria-hidden="true"><?= e(mb_substr((string) ($user['name'] ?? '؟'), 0, 1)) ?></div>
                <div class="admin-user__text">
                    <strong><?= e((string) ($user['name'] ?? '')) ?></strong>
                    <small><?= e($roleLabel) ?></small>
                </div>
            </div>
        </aside>

        <div class="admin-scrim" id="admin-scrim" hidden></div>

        <div class="admin-main">
            <header class="admin-topbar">
                <button class="m3-icon-button admin-menu-btn" id="admin-menu-toggle"
                        aria-controls="admin-sidebar" aria-expanded="false" aria-label="باز کردن منو">
                    <span aria-hidden="true">☰</span>
                </button>

                <div class="admin-topbar__heading">
                    <p class="admin-topbar__eyebrow"><?= e($tenantName) ?></p>
                    <h1 class="admin-topbar__title"><?= e($pageTitle) ?></h1>
                </div>

                <div class="admin-topbar__actions">
                    <?php if ($mustChange): ?>
                        <a class="admin-alert-chip" href="<?= e(admin_url('settings#password')) ?>">تغییر رمز لازم است</a>
                    <?php endif; ?>
                    <form action="<?= e(url('logout.php')) ?>" method="post" class="admin-logout">
                        <?= csrf_field() ?>
                        <button class="m3-button m3-button--text m3-button--sm" type="submit">
                            <?= admin_icon('logout') ?>
                            خروج
                        </button>
                    </form>
                </div>
            </header>

            <?php if ($notice !== null): ?>
                <div class="admin-flash admin-flash--info" role="status"><?= e((string) $notice) ?></div>
            <?php endif; ?>
            <?php if (is_string($success) && $success !== ''): ?>
                <div class="admin-flash admin-flash--ok" role="status"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if (is_string($error) && $error !== ''): ?>
                <div class="admin-flash admin-flash--error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <?php if ($pageActions !== ''): ?>
                <div class="admin-page-actions"><?= $pageActions ?></div>
            <?php endif; ?>

            <main id="main-content" class="admin-content">
                <?= $content ?>
            </main>

            <footer class="admin-footer">
                <p><?= e(SITE_NAME) ?> — نسخهٔ ۱.۰ · <a href="<?= e(url('')) ?>">بازگشت به سایت</a></p>
            </footer>
        </div>
    </div>

    <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>

</html>
