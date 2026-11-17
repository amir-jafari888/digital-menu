<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/partials/icons.php';

$sent    = false;
$errors  = [];
$name    = '';
$contact = '';
$subject = '';
$message = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $name    = trim((string) ($_POST['name'] ?? ''));
    $contact = trim((string) ($_POST['contact'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($name === '') {
        $errors[] = 'نام را وارد کن.';
    }
    if ($contact === '') {
        $errors[] = 'ایمیل یا شماره تماس را وارد کن.';
    }
    if ($message === '') {
        $errors[] = 'متن پیام را بنویس.';
    }

    if (!$errors) {
        // Front-end scaffold: no mail transport is configured yet.
        $sent = true;
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
                    <?= icon('bolt', 20) ?>
                    <span>این فرم نمایشی است و هنوز به سرویس ایمیل متصل نشده. برای شروع، ثبت‌نام رایگان را امتحان کن.</span>
                </div>
            </div>

            <form class="contact-form" method="post" action="<?= url('contact.php') ?>" data-reveal="up" novalidate>
                <?php if ($sent): ?>
                    <div class="notice"><?= icon('check-circle', 20) ?><span>پیامت ثبت شد. برای فعال‌سازی ارسال واقعی، سرویس ایمیل باید در بک‌اند تنظیم شود.</span></div>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <div class="notice" style="background:var(--md-sys-color-error-container);color:var(--md-sys-color-on-error-container);">
                        <?= icon('bolt', 20) ?><span><?= e(implode(' ', $errors)) ?></span>
                    </div>
                <?php endif; ?>

                <div class="field-row">
                    <div class="field">
                        <label for="name">نام</label>
                        <input class="field__control" id="name" name="name" type="text" autocomplete="name" value="<?= e($name) ?>" required>
                    </div>
                    <div class="field">
                        <label for="contact">ایمیل یا شماره تماس</label>
                        <input class="field__control" id="contact" name="contact" type="text" inputmode="email" autocomplete="email" value="<?= e($contact) ?>" required>
                    </div>
                </div>
                <div class="field">
                    <label for="subject">موضوع</label>
                    <select class="field__control" id="subject" name="subject">
                        <option value="demo"<?= $subject === 'demo' ? ' selected' : '' ?>>درخواست دمو</option>
                        <option value="support"<?= $subject === 'support' ? ' selected' : '' ?>>پشتیبانی</option>
                        <option value="sales"<?= $subject === 'sales' ? ' selected' : '' ?>>فروش و تعرفه</option>
                        <option value="other"<?= $subject === 'other' ? ' selected' : '' ?>>موضوع دیگر</option>
                    </select>
                </div>
                <div class="field">
                    <label for="message">پیام</label>
                    <textarea class="field__control" id="message" name="message" required><?= e($message) ?></textarea>
                </div>
                <button class="m3-button m3-button--filled m3-button--lg" type="submit">ارسال پیام <?= icon('arrow', 18, 'btn-arrow') ?></button>
            </form>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
