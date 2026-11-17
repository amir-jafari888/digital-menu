<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/partials/icons.php';

$page = [
    'title'       => 'سوالات متداول | ' . SITE_NAME,
    'description' => 'پاسخ سوال‌های رایج درباره‌ی منوی دیجیتال، QR، سفارش‌گیری، باشگاه مشتریان و تبلیغات در منوچی.',
    'active'      => 'faq',
    'canonical'   => 'faq.php',
];

$faqs = [
    ['مشتری برای دیدن منو باید برنامه‌ای نصب کند؟', 'خیر. مشتری با اسکن QR یا باز کردن لینک، منو را در مرورگر موبایل خود می‌بیند.'],
    ['بعد از تغییر قیمت باید QR جدید بسازم؟', 'خیر. QR به لینک منوی شما متصل است؛ با به‌روزرسانی اطلاعات، همان منو نمایش داده می‌شود.'],
    ['می‌توانم سفارش‌های مشتری را مدیریت کنم؟', 'بله. پنل سفارش‌ها وضعیت هر سفارش را نشان می‌دهد و می‌توانی آن را به‌روز کنی.'],
    ['باشگاه مشتریان چطور کار می‌کند؟', 'برای هر سفارش امتیاز تعریف می‌کنی؛ مشتری امتیاز جمع می‌کند و می‌تواند آن را به تخفیف یا سفارش رایگان تبدیل کند.'],
    ['تبلیغات در اپ سراغ چطور به من کمک می‌کند؟', 'کسب‌وکارت را به مخاطب‌های نزدیک و علاقه‌مند نشان می‌دهد و بازدید، کلیک و سفارش را قابل اندازه‌گیری می‌کند.'],
    ['برای شروع به دانش فنی نیاز دارم؟', 'خیر. مسیر ساخت منو مرحله‌ای طراحی شده تا بدون دانش طراحی سایت اطلاعات کسب‌وکارت را وارد کنی.'],
    ['می‌توانم چند منو برای چند شعبه داشته باشم؟', 'بله. در پلن کسب‌وکار می‌توانی چند شعبه و چند منو بسازی و همه را از یک پنل مدیریت کنی.'],
    ['پرداخت اشتراک چطور انجام می‌شود؟', 'درگاه پرداخت آنلاین و فاکتور رسمی در مرحله‌ی بعدی توسعه اضافه می‌شود. تا آن زمان می‌توانی از پلن رایگان شروع کنی.'],
    ['اطلاعات من و مشتری‌ها امن است؟', 'دسترسی به داده‌ها محدود به کسب‌وکار خودت است و داده‌ی هر کسب‌وکار جدا نگه داشته می‌شود.'],
    ['اگر به کمک نیاز داشته باشم چه کنم؟', 'از صفحه‌ی تماس با ما پیام بده؛ تیم پشتیبانی در روزهای کاری پاسخ می‌دهد.'],
];

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content">
    <?php
    $hero = [
        'kicker'  => 'سوالات متداول',
        'title'   => 'پاسخ سوال‌هایی که زیاد پرسیده می‌شود',
        'lead'    => 'اگر جواب سوالت را پیدا نکردی، از صفحه‌ی تماس با ما پیام بده.',
        'actions' => [
            ['label' => 'تماس با ما', 'href' => url('contact.php'), 'variant' => 'filled'],
            ['label' => 'شروع رایگان', 'href' => url('register.php'), 'variant' => 'outlined'],
        ],
    ];
    require __DIR__ . '/partials/page-hero.php';
    ?>

    <section class="section" style="padding-top:clamp(20px,3vw,32px);">
        <div class="wrap" style="max-width:880px;">
            <div class="faq-list">
                <?php foreach ($faqs as $index => $faq): ?>
                    <details class="faq-item" data-reveal="up"<?= $index === 0 ? ' open' : '' ?>>
                        <summary><?= e($faq[0]) ?><span class="faq-icon" aria-hidden="true">+</span></summary>
                        <div class="faq-item__body"><?= e($faq[1]) ?></div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="wrap">
            <div class="final-cta__card" data-reveal="up">
                <div class="final-cta__copy">
                    <h2>جوابت را پیدا نکردی؟</h2>
                    <p>سوالت را بنویس؛ در اولین فرصت پاسخ می‌دهیم.</p>
                </div>
                <div class="final-cta__actions">
                    <a class="m3-button m3-button--light m3-button--lg" href="<?= url('contact.php') ?>">تماس با ما <?= icon('arrow', 18, 'btn-arrow') ?></a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
