<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/partials/icons.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Logger;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\Menu;
use App\Models\Tenant;
use App\Models\User;

Security::headers();
Session::start();
RateLimiter::prune();

if (Auth::check()) {
    Response::redirect(admin_url());
}

$errors   = [];
$name     = '';
$business = '';
$contact  = '';
$notice   = Session::getFlash('auth_notice');

if (Request::isPost()) {
    Csrf::assert();

    $name     = Validator::cleanText(Request::string('name'));
    $business = Validator::cleanText(Request::string('business'));
    $contact  = trim(Request::string('contact'));
    $password = (string) Request::input('password', '');
    $terms    = Request::bool('terms');

    // Honeypot: a hidden field no human fills in.
    $honeypot = Request::string('website');

    $limit = RateLimiter::attempt('register:ip:' . Security::ipHash(), 5, 3600, 3600);
    if (!$limit['allowed']) {
        $errors[] = 'ثبت‌نام از این شبکه زیاد بود. تا ' . RateLimiter::humanRetry($limit['retry_after']) . ' دیگر صبر کن.';
    }

    if ($honeypot !== '') {
        // Pretend success to bots without writing anything.
        Response::redirect(url('login.php'));
    }

    if ($name === '') {
        $errors[] = 'نام و نام خانوادگی را وارد کن.';
    }
    if (mb_strlen($name) > 120) {
        $errors[] = 'نام بیش از حد طولانی است.';
    }
    if ($business === '') {
        $errors[] = 'نام کسب‌وکار را وارد کن.';
    }
    if (mb_strlen($business) > 120) {
        $errors[] = 'نام کسب‌وکار بیش از حد طولانی است.';
    }
    if ($contact === '') {
        $errors[] = 'ایمیل یا شماره موبایل را وارد کن.';
    }

    $email = null;
    $phone = null;
    if ($contact !== '') {
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $email = mb_strtolower($contact);
        } else {
            $normalised = preg_replace('/[\s\-()]/', '', Validator::normaliseDigits($contact)) ?? '';
            if (preg_match('/^(?:0|\+?98)?9\d{9}$/', $normalised)) {
                $phone = $normalised;
            } else {
                $errors[] = 'ایمیل یا شماره موبایل معتبر نیست.';
            }
        }
    }

    if (mb_strlen($password) < 8) {
        $errors[] = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors[] = 'رمز عبور باید هم حرف و هم رقم داشته باشد.';
    }
    if (!$terms) {
        $errors[] = 'پذیرش قوانین الزامی است.';
    }

    $users = new User();
    if ($email !== null && $users->emailTaken($email)) {
        $errors[] = 'این ایمیل قبلاً ثبت شده است.';
    }
    if ($phone !== null && $users->phoneTaken($phone)) {
        $errors[] = 'این شماره موبایل قبلاً ثبت شده است.';
    }

    if ($errors === []) {
        $pdo = Database::pdo();

        try {
            $planId = (int) Database::scalar(
                'SELECT id FROM plans WHERE slug = \'basic\' AND is_active = 1 LIMIT 1'
            );
            if ($planId === 0) {
                $planId = (int) Database::scalar('SELECT id FROM plans WHERE is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 1');
            }

            $pdo->beginTransaction();

            $tenants = new Tenant();
            $slug    = Tenant::slugify($business);
            if ($tenants->slugTaken($slug)) {
                $slug .= '-' . bin2hex(random_bytes(2));
            }

            $tenantId = $tenants->create([
                'name'          => $business,
                'slug'          => $slug,
                'category'      => 'cafe',
                'description'   => '',
                'address'       => '',
                'status'        => 'active',
                'plan_id'       => $planId > 0 ? $planId : null,
                'trial_ends_at' => date('Y-m-d H:i:s', strtotime('+14 days')),
            ]);

            $userId = $users->create([
                'tenant_id'            => $tenantId,
                'name'                 => $name,
                'email'                => $email,
                'phone'                => $phone,
                'password_hash'        => password_hash($password, PASSWORD_DEFAULT),
                'role'                 => 'owner',
                'status'               => 'active',
                'must_change_password' => 0,
                'email_verified_at'    => date('Y-m-d H:i:s'),
            ]);

            // Every business starts with one published menu so the QR link works.
            (new Menu())->createForTenant($tenantId, 'منوی اصلی', true);

            if ($planId > 0) {
                Database::run(
                    'INSERT INTO subscriptions (tenant_id, plan_id, billing, price, starts_at, ends_at, status)
                     VALUES (?, ?, \'monthly\', 0, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), \'trial\')',
                    [$tenantId, $planId]
                );
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error('Registration failed', ['message' => $e->getMessage()]);
            $errors[] = 'ساخت حساب انجام نشد. اگر تکرار شد با پشتیبانی تماس بگیر.';
        }

        if ($errors === [] && isset($userId, $tenantId)) {
            AuditLog::record('auth.register', (int) $userId, (int) $tenantId, ['slug' => $slug]);

            Auth::establish([
                'id'                   => (int) $userId,
                'tenant_id'            => (int) $tenantId,
                'name'                 => $name,
                'email'                => $email,
                'phone'                => $phone,
                'role'                 => 'owner',
                'status'               => 'active',
                'must_change_password' => 0,
                'tenant_name'          => $business,
                'tenant_slug'          => $slug,
                'tenant_status'        => 'active',
            ]);

            Session::flash('success', 'خوش آمدی! منوی تو ساخته شد. از بخش «محصولات» اولین آیتم را اضافه کن.');
            Response::redirect(admin_url());
        }
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

                <?php if (is_string($notice) && $notice !== ''): ?>
                    <div class="notice"><?= icon('clock', 20) ?><span><?= e($notice) ?></span></div>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <div class="notice" style="background:var(--md-sys-color-error-container);color:var(--md-sys-color-on-error-container);" role="alert">
                        <?= icon('bolt', 20) ?><span><?= e(implode(' ', $errors)) ?></span>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="<?= e(url('register.php')) ?>" novalidate>
                    <?= csrf_field() ?>
                    <div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
                        <label for="website">وب‌سایت</label>
                        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="name">نام و نام خانوادگی</label>
                            <input class="field__control" id="name" name="name" type="text" autocomplete="name" value="<?= e($name) ?>" maxlength="120" required>
                        </div>
                        <div class="field">
                            <label for="business">نام کسب‌وکار</label>
                            <input class="field__control" id="business" name="business" type="text" autocomplete="organization" value="<?= e($business) ?>" maxlength="120" required>
                        </div>
                    </div>
                    <div class="field">
                        <label for="contact">ایمیل یا شماره موبایل</label>
                        <input class="field__control" id="contact" name="contact" type="text" inputmode="email" autocomplete="email" value="<?= e($contact) ?>" maxlength="190" required>
                    </div>
                    <div class="field">
                        <label for="password">رمز عبور</label>
                        <input class="field__control" id="password" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="100" required>
                        <span class="field__hint">حداقل ۸ کاراکتر، شامل حرف و رقم.</span>
                    </div>
                    <label class="checkbox"><input type="checkbox" name="terms" value="1"><span>با <a href="<?= e(url('terms.php')) ?>" style="color:var(--md-sys-color-primary);font-weight:700;">قوانین استفاده</a> و <a href="<?= e(url('privacy.php')) ?>" style="color:var(--md-sys-color-primary);font-weight:700;">حریم خصوصی</a> موافقم.</span></label>
                    <button class="m3-button m3-button--filled m3-button--lg m3-button--block" type="submit">ساخت حساب رایگان <?= icon('arrow', 18, 'btn-arrow') ?></button>
                </form>

                <div class="auth-divider">یا</div>
                <p class="auth-switch">حساب داری؟ <a href="<?= e(url('login.php')) ?>">وارد شو</a></p>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
