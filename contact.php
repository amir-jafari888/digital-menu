<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/partials/icons.php';

use App\Core\Csrf;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Security;
use App\Core\Session;
use App\Core\Validator;
use App\Models\ContactMessage;

Security::headers();
Session::start();
RateLimiter::prune();

$sent    = false;
$errors  = [];
$name    = '';
$contact = '';
$subject = '';
$message = '';

$subjects = [
    'demo'    => 'درخواست دمو',
    'support' => 'پشتیبانی',
    'sales'   => 'فروش و تعرفه',
    'other'   => 'موضوع دیگر',
];

if (Request::isPost()) {
    Csrf::assert();

    $name    = Validator::cleanText(Request::string('name'));
    $contact = trim(Request::string('contact'));
    $subject = Request::string('subject');
    $message = Validator::cleanText((string) Request::input('message', ''));

    if (Request::string('website') !== '') {
        // Honeypot hit: accept silently, store nothing.
        $sent = true;
    } else {
        $limit = RateLimiter::attempt('contact:ip:' . Security::ipHash(), 3, 3600, 3600);
        if (!$limit['allowed']) {
            $errors[] = 'تعداد پیام‌های ارسالی زیاد بود. تا ' . RateLimiter::humanRetry($limit['retry_after']) . ' دیگر صبر کن.';
        } elseif ($name === '') {
            $errors[] = 'نام را وارد کن.';
        } elseif (mb_strlen($name) > 120) {
            $errors[] = 'نام بیش از حد طولانی است.';
        } elseif ($contact === '') {
            $errors[] = 'ایمیل یا شماره تماس را وارد کن.';
        } elseif (mb_strlen($contact) > 190) {
            $errors[] = 'ایمیل یا شماره تماس بیش از حد طولانی است.';
        } elseif (!filter_var($contact, FILTER_VALIDATE_EMAIL)
            && !preg_match('/^(?:0|\+?98)?9\d{9}$/', preg_replace('/[\s\-()]/', '', Validator::normaliseDigits($contact)) ?? '')) {
            $errors[] = 'ایمیل یا شماره تماس معتبر نیست.';
        } elseif ($message === '' || mb_strlen($message) < 10) {
            $errors[] = 'متن پیام باید حداقل ۱۰ کاراکتر باشد.';
        } elseif (mb_strlen($message) > 4000) {
            $errors[] = 'متن پیام بیش از حد طولانی است.';
        } else {
            $subjectLabel = $subjects[$subject] ?? $subjects['other'];

            try {
                (new ContactMessage())->store([
                    'name'    => $name,
                    'contact' => $contact,
                    'subject' => $subjectLabel,
                    'message' => $message,
                ], Security::ipHash(), Request::userAgent());

                $sent    = true;
                $name    = '';
                $contact = '';
                $subject = '';
                $message = '';
            } catch (\Throwable $e) {
                \App\Core\Logger::error('Contact message failed', ['message' => $e->getMessage()]);
                $errors[] = 'ارسال پیام انجام نشد. کمی بعد دوباره تلاش کن.';
            }
        }
    }
}

$page = [
    'title'       => 'تماس با ما | ' . SITE_NAME,
    'description' => 'برای سوال، پیشنهاد یا درخواست دمو با تیم منوچی در تماس باش.',
    'active'      => 'contact',
    'canonical'   => 'contact.php',
];

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content">
    <?php
    $hero = [
        'kicker'  => 'تماس با ما',
        'title'   => 'حرف بزنیم',
        'lead'    => 'برای سوال، پیشنهاد یا درخواست دمو پیام بده. در روزهای کاری پاسخ می‌دهیم.',
        'actions' => [],
    ];
    require __DIR__ . '/partials/page-hero.php';
    ?>

    <section class="section" style="padding-top:clamp(20px,3vw,32px);">
        <div class="wrap contact-grid">
            <div class="contact-cards" data-reveal="up">
                <div class="contact-card">
                    <span class="contact-card__icon"><?= icon('mail') ?></span>
                    <div><strong>ایمیل</strong><a href="mailto:hello@monochi.ir">hello@monochi.ir</a></div>
                </div>
                <div class="contact-card">
                    <span class="contact-card__icon"><?= icon('clock') ?></span>
                    <div><strong>ساعت پاسخ‌گویی</strong><span>شنبه تا پنجشنبه، ۹ تا ۱۸</span></div>
                </div>
                <div class="contact-card">
                    <span class="contact-card__icon"><?= icon('instagram') ?></span>
                    <div><strong>اینستاگرام</strong><a href="https://instagram.com/monochi.app" rel="noopener" target="_blank">@monochi.app</a></div>
                </div>
                <div class="contact-card">
                    <span class="contact-card__icon"><?= icon('map-pin') ?></span>
                    <div><strong>آدرس</strong><span>تهران، ایران</span></div>
                </div>
                <div class="notice">
                    <?= icon('check-circle', 20) ?>
                    <span>پیام‌ها در پنل مدیریت ثبت می‌شوند و در روزهای کاری بررسی می‌شوند.</span>
                </div>
            </div>

            <form class="contact-form" method="post" action="<?= e(url('contact.php')) ?>" data-reveal="up" novalidate>
                <?= csrf_field() ?>
                <div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
                    <label for="website">وب‌سایت</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>

                <?php if ($sent): ?>
                    <div class="notice" role="status"><?= icon('check-circle', 20) ?><span>پیامت رسید. به‌زودی پاسخ می‌دهیم.</span></div>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <div class="notice" style="background:var(--md-sys-color-error-container);color:var(--md-sys-color-on-error-container);" role="alert">
                        <?= icon('bolt', 20) ?><span><?= e(implode(' ', $errors)) ?></span>
                    </div>
                <?php endif; ?>

                <div class="field-row">
                    <div class="field">
                        <label for="name">نام</label>
                        <input class="field__control" id="name" name="name" type="text" autocomplete="name" value="<?= e($name) ?>" maxlength="120" required>
                    </div>
                    <div class="field">
                        <label for="contact">ایمیل یا شماره تماس</label>
                        <input class="field__control" id="contact" name="contact" type="text" inputmode="email" autocomplete="email" value="<?= e($contact) ?>" maxlength="190" required>
                    </div>
                </div>
                <div class="field">
                    <label for="subject">موضوع</label>
                    <select class="field__control" id="subject" name="subject">
                        <?php foreach ($subjects as $value => $label): ?>
                            <option value="<?= e($value) ?>"<?= is_selected($value, $subject) ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="message">پیام</label>
                    <textarea class="field__control" id="message" name="message" rows="5" minlength="10" maxlength="4000" required><?= e($message) ?></textarea>
                </div>
                <button class="m3-button m3-button--filled m3-button--lg" type="submit">ارسال پیام <?= icon('arrow', 18, 'btn-arrow') ?></button>
            </form>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
