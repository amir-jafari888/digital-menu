<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/partials/icons.php';

$page = [
    'title'       => 'تعرفه‌ها | ' . SITE_NAME,
    'description' => 'تعرفه‌های شفاف منوچی برای منوی دیجیتال، باشگاه مشتریان و تبلیغات؛ ماهانه یا سالانه، بدون قرارداد بلندمدت.',
    'active'      => 'pricing',
    'canonical'   => 'pricing.php',
];

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content">
    <?php
    $hero = [
        'kicker'  => 'تعرفه‌ها',
        'title'   => 'پلنی که با کسب‌وکارت رشد می‌کند',
        'lead'    => 'از یک منوی ساده شروع کن و هر وقت آماده بودی، باشگاه مشتریان و تبلیغات را اضافه کن. همه‌ی پلن‌ها بدون قرارداد بلندمدت هستند.',
        'actions' => [
            ['label' => 'شروع رایگان', 'href' => url('register.php'), 'variant' => 'filled'],
            ['label' => 'گفت‌وگو با فروش', 'href' => url('contact.php'), 'variant' => 'outlined'],
        ],
    ];
    require __DIR__ . '/partials/page-hero.php';
    ?>

    <section class="section" style="padding-top:clamp(20px,3vw,32px);">
        <div class="wrap">
            <?php
            $showPricingHead = false;
            require __DIR__ . '/partials/pricing.php';
            ?>
        </div>
    </section>

    <section class="section section--alt">
        <div class="wrap">
            <div class="section-head section-head--center" data-reveal="up">
                <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>مقایسه</span>
                <h2 class="section-head__title">کدام پلن مناسب توست؟</h2>
                <p class="section-head__lead">یک نگاه سریع به تفاوت امکانات.</p>
            </div>
            <div style="overflow-x:auto;" data-reveal="up">
                <table class="compare">
                    <thead>
                        <tr><th>امکان</th><th>پایه</th><th>حرفه‌ای</th><th>کسب‌وکار</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>منوی دیجیتال و QR</td><td data-label="پایه"><?= icon('check') ?></td><td data-label="حرفه‌ای"><?= icon('check') ?></td><td data-label="کسب‌وکار"><?= icon('check') ?></td></tr>
                        <tr><td>ویرایش نامحدود محصول</td><td data-label="پایه"><?= icon('check') ?></td><td data-label="حرفه‌ای"><?= icon('check') ?></td><td data-label="کسب‌وکار"><?= icon('check') ?></td></tr>
                        <tr><td>پنل سفارش</td><td data-label="پایه">—</td><td data-label="حرفه‌ای"><?= icon('check') ?></td><td data-label="کسب‌وکار"><?= icon('check') ?></td></tr>
                        <tr><td>باشگاه مشتریان و امتیاز</td><td data-label="پایه">—</td><td data-label="حرفه‌ای"><?= icon('check') ?></td><td data-label="کسب‌وکار"><?= icon('check') ?></td></tr>
                        <tr><td>گزارش فروش و مشتری</td><td data-label="پایه">—</td><td data-label="حرفه‌ای"><?= icon('check') ?></td><td data-label="کسب‌وکار"><?= icon('check') ?></td></tr>
                        <tr><td>تبلیغات در اپ سراغ</td><td data-label="پایه">—</td><td data-label="حرفه‌ای">—</td><td data-label="کسب‌وکار"><?= icon('check') ?></td></tr>
                        <tr><td>چند شعبه و چند منو</td><td data-label="پایه">—</td><td data-label="حرفه‌ای">—</td><td data-label="کسب‌وکار"><?= icon('check') ?></td></tr>
                        <tr><td>پشتیبانی اولویت‌دار</td><td data-label="پایه">—</td><td data-label="حرفه‌ای">—</td><td data-label="کسب‌وکار"><?= icon('check') ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="wrap">
            <div class="section-head" data-reveal="up">
                <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>سوال‌های پرداخت</span>
                <h2 class="section-head__title">چند سوال رایج</h2>
            </div>
            <div class="faq-list" style="max-width:820px;">
                <details class="faq-item" data-reveal="up" open>
                    <summary>می‌توانم هر زمان پلن را عوض کنم؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">بله. پلن را می‌توانی ارتقا یا تنزل بدهی؛ تفاوت هزینه به‌صورت نسبی محاسبه می‌شود.</div>
                </details>
                <details class="faq-item" data-reveal="up">
                    <summary>پرداخت آنلاین فعال است؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">درگاه پرداخت و فاکتور رسمی در مرحله‌ی بعدی توسعه اضافه می‌شود. برای شروع می‌توانی فرم ثبت‌نام را پر کنی.</div>
                </details>
                <details class="faq-item" data-reveal="up">
                    <summary>برای چند شعبه پلن جدا لازم است؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">پلن کسب‌وکار برای چند شعبه و چند منو طراحی شده است. برای تعداد بیشتر با فروش گفت‌وگو کن.</div>
                </details>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="wrap">
            <div class="final-cta__card" data-reveal="up">
                <div class="final-cta__copy">
                    <h2>از رایگان شروع کن</h2>
                    <p>بعد از دیدن نتیجه، پلن مناسب را انتخاب کن.</p>
                </div>
                <div class="final-cta__actions">
                    <a class="m3-button m3-button--light m3-button--lg" href="<?= url('register.php') ?>">شروع رایگان <?= icon('arrow', 18, 'btn-arrow') ?></a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
