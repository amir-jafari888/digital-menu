<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/partials/icons.php';

$sent   = false;
$errors = [];
$identifier = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $identifier = trim((string) ($_POST['identifier'] ?? ''));
    $password   = (string) ($_POST['password'] ?? '');

    if ($identifier === '') {
        $errors[] = 'ایمیل یا شماره موبایل را وارد کن.';
    }
    if ($password === '') {
        $errors[] = 'رمز عبور را وارد کن.';
    }
    if (!$errors) {
        // Authentication is not implemented yet; this is a UI scaffold.
        $sent = true;
    }
}

$page = [
    'title'       => 'ورود | ' . SITE_NAME,
    'description' => 'به حساب منوچی وارد شو و منو، سفارش‌ها و باشگاه مشتریان را مدیریت کن.',
    'active'      => 'login',
    'canonical'   => 'login.php',
];

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content">
    <div class="auth-shell">
        <aside class="auth-aside" aria-label="مزایای منوچی">
            <span class="kicker" style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.24);color:#eaffef;"><span class="kicker__dot" aria-hidden="true"></span>خوش برگشتی</span>
            <h2>همه‌چیز از همان‌جایی که رها کردی</h2>
            <p>وارد شو و منو، سفارش‌ها و مشتری‌های باشگاه را در یک پنل ببین.</p>
            <ul class="auth-aside__list">
                <li><?= icon('check-circle') ?><span>منوی همیشه به‌روز</span></li>
                <li><?= icon('check-circle') ?><span>سفارش‌ها و وضعیت هرکدام</span></li>
                <li><?= icon('check-circle') ?><span>باشگاه مشتریان و امتیازها</span></li>
            </ul>
        </aside>

        <div class="auth-main">
            <div class="auth-card" data-reveal="up">
                <div class="auth-card__head">
                    <h1>ورود به حساب</h1>
                    <p>ایمیل یا شماره موبایل و رمز عبورت را وارد کن.</p>
                </div>

                <?php if ($sent): ?>
                    <div class="notice"><?= icon('check-circle', 20) ?><span>اطلاعات دریافت شد. ورود واقعی بعد از اتصال بک‌اند فعال می‌شود.</span></div>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <div class="notice" style="background:var(--md-sys-color-error-container);color:var(--md-sys-color-on-error-container);">
                        <?= icon('bolt', 20) ?><span><?= e(implode(' ', $errors)) ?></span>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="<?= url('login.php') ?>">
                    <div class="field">
                        <label for="identifier">ایمیل یا شماره موبایل</label>
                        <input class="field__control" id="identifier" name="identifier" type="text" inputmode="email" autocomplete="username" value="<?= e($identifier) ?>" required>
                    </div>
                    <div class="field">
                        <label for="password">رمز عبور</label>
                        <input class="field__control" id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>
                    <label class="checkbox"><input type="checkbox" name="remember" value="1"><span>مرا به خاطر بسپار</span></label>
                    <button class="m3-button m3-button--filled m3-button--lg m3-button--block" type="submit">ورود <?= icon('arrow', 18, 'btn-arrow') ?></button>
                </form>

                <div class="auth-divider">یا</div>
                <p class="auth-switch">حساب نداری؟ <a href="<?= url('register.php') ?>">ساخت حساب رایگان</a></p>
                <p class="auth-switch">رمزت را فراموش کردی؟ <a href="<?= url('contact.php') ?>">با پشتیبانی تماس بگیر</a></p>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
