<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/partials/icons.php';

$sent     = false;
$errors   = [];
$name     = '';
$business = '';
$contact  = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $name     = trim((string) ($_POST['name'] ?? ''));
    $business = trim((string) ($_POST['business'] ?? ''));
    $contact  = trim((string) ($_POST['contact'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $terms    = !empty($_POST['terms']);

    if ($name === '') {
        $errors[] = 'نام و نام خانوادگی را وارد کن.';
    }
    if ($business === '') {
        $errors[] = 'نام کسب‌وکار را وارد کن.';
    }
    if ($contact === '') {
        $errors[] = 'ایمیل یا شماره موبایل را وارد کن.';
    }
    if (mb_strlen($password) < 8) {
        $errors[] = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
    }
    if (!$terms) {
        $errors[] = 'پذیرش قوانین الزامی است.';
    }
    if (!$errors) {
        // Registration is not implemented yet; this is a UI scaffold.
        $sent = true;
    }
}

$page = [
    'title'       => 'ساخت حساب | ' . SITE_NAME,
    'description' => 'حساب رایگان منوچی بساز و اولین منوی دیجیتالت را در چند دقیقه منتشر کن.',
    'active'      => 'register',
    'canonical'   => 'register.php',
];

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content">
    <div class="auth-shell">
        <aside class="auth-aside" aria-label="مزایای شروع">
            <span class="kicker" style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.24);color:#eaffef;"><span class="kicker__dot" aria-hidden="true"></span>شروع رایگان</span>
            <h2>اولین منوی دیجیتالت را بساز</h2>
            <p>در چند دقیقه منو را منتشر کن، QR بگیر و سفارش بگیر. برای شروع، کارت بانکی لازم نیست.</p>
            <ul class="auth-aside__list">
                <li><?= icon('check-circle') ?><span>منوی دیجیتال و QR اختصاصی</span></li>
                <li><?= icon('check-circle') ?><span>سفارش‌گیری و پنل سفارش</span></li>
                <li><?= icon('check-circle') ?><span>باشگاه مشتریان در پلن حرفه‌ای</span></li>
            </ul>
        </aside>

        <div class="auth-main">
            <div class="auth-card" data-reveal="up">
                <div class="auth-card__head">
                    <h1>ساخت حساب</h1>
                    <p>اطلاعات کسب‌وکارت را وارد کن تا پنل آماده شود.</p>
                </div>

                <?php if ($sent): ?>
                    <div class="notice"><?= icon('check-circle', 20) ?><span>ثبت‌نام نمایشی ثبت شد. برای ساخت حساب واقعی، احراز هویت و پایگاه داده باید در بک‌اند فعال شود.</span></div>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <div class="notice" style="background:var(--md-sys-color-error-container);color:var(--md-sys-color-on-error-container);">
                        <?= icon('bolt', 20) ?><span><?= e(implode(' ', $errors)) ?></span>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="<?= url('register.php') ?>">
                    <div class="field-row">
                        <div class="field">
                            <label for="name">نام و نام خانوادگی</label>
                            <input class="field__control" id="name" name="name" type="text" autocomplete="name" value="<?= e($name) ?>" required>
                        </div>
                        <div class="field">
                            <label for="business">نام کسب‌وکار</label>
                            <input class="field__control" id="business" name="business" type="text" autocomplete="organization" value="<?= e($business) ?>" required>
                        </div>
                    </div>
                    <div class="field">
                        <label for="contact">ایمیل یا شماره موبایل</label>
                        <input class="field__control" id="contact" name="contact" type="text" inputmode="email" autocomplete="email" value="<?= e($contact) ?>" required>
                    </div>
                    <div class="field">
                        <label for="password">رمز عبور</label>
                        <input class="field__control" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                        <span class="field__hint">حداقل ۸ کاراکتر.</span>
                    </div>
                    <label class="checkbox"><input type="checkbox" name="terms" value="1"><span>با <a href="<?= url('terms.php') ?>" style="color:var(--md-sys-color-primary);font-weight:700;">قوانین استفاده</a> و <a href="<?= url('privacy.php') ?>" style="color:var(--md-sys-color-primary);font-weight:700;">حریم خصوصی</a> موافقم.</span></label>
                    <button class="m3-button m3-button--filled m3-button--lg m3-button--block" type="submit">ساخت حساب رایگان <?= icon('arrow', 18, 'btn-arrow') ?></button>
                </form>

                <div class="auth-divider">یا</div>
                <p class="auth-switch">حساب داری؟ <a href="<?= url('login.php') ?>">وارد شو</a></p>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
