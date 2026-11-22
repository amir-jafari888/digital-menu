<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/partials/icons.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;

/**
 * Only local, single-slash paths are accepted as a post-login destination.
 * Anything else (absolute URLs, //host, protocol-relative, header injection)
 * falls back to the panel, so `next` can never become an open redirect.
 */
function login_next_path(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '' || !str_starts_with($raw, '/') || str_starts_with($raw, '//')) {
        return '';
    }
    if (preg_match('/[\x00-\x1F\x7F\\\\]/', $raw)) {
        return '';
    }

    return $raw;
}

Security::headers();
Session::start();
RateLimiter::prune();

if (Auth::check()) {
    Response::redirect(admin_url());
}

$errors     = [];
$identifier = '';
$next       = login_next_path(Request::query('next'));
$notice     = Session::getFlash('auth_notice');

if (Request::isPost()) {
    Csrf::assert();

    $identifier = Request::string('identifier');
    $password   = (string) Request::input('password', '');
    $next       = login_next_path(Request::string('next'));

    if ($identifier === '' || $password === '') {
        $errors[] = 'ایمیل/شماره موبایل و رمز عبور را وارد کن.';
    } elseif (mb_strlen($identifier) > 190 || mb_strlen($password) > 200) {
        $errors[] = 'اطلاعات ورود معتبر نیست.';
    } else {
        $result = Auth::attempt($identifier, $password);

        if ($result['ok']) {
            Response::redirect($next !== '' ? $next : admin_url());
        }

        $errors[] = (string) ($result['message'] ?? 'ورود انجام نشد.');
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

                <?php if (is_string($notice) && $notice !== ''): ?>
                    <div class="notice"><?= icon('clock', 20) ?><span><?= e($notice) ?></span></div>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <div class="notice" style="background:var(--md-sys-color-error-container);color:var(--md-sys-color-on-error-container);" role="alert">
                        <?= icon('bolt', 20) ?><span><?= e(implode(' ', $errors)) ?></span>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="<?= e(url('login.php')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="next" value="<?= e($next) ?>">

                    <div class="field">
                        <label for="identifier">ایمیل یا شماره موبایل</label>
                        <input class="field__control" id="identifier" name="identifier" type="text" inputmode="email" autocomplete="username" value="<?= e($identifier) ?>" maxlength="190" required>
                    </div>
                    <div class="field">
                        <label for="password">رمز عبور</label>
                        <input class="field__control" id="password" name="password" type="password" autocomplete="current-password" maxlength="200" required>
                    </div>
                    <label class="checkbox"><input type="checkbox" name="remember" value="1"><span>مرا به خاطر بسپار</span></label>
                    <button class="m3-button m3-button--filled m3-button--lg m3-button--block" type="submit">ورود <?= icon('arrow', 18, 'btn-arrow') ?></button>
                </form>

                <div class="auth-divider">یا</div>
                <p class="auth-switch">حساب نداری؟ <a href="<?= e(url('register.php')) ?>">ساخت حساب رایگان</a></p>
                <p class="auth-switch">رمزت را فراموش کردی؟ <a href="<?= e(url('contact.php')) ?>">با پشتیبانی تماس بگیر</a></p>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
