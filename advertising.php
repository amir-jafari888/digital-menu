<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/partials/icons.php';

$page = [
    'title'       => 'تبلیغات در اپ سراغ | ' . SITE_NAME,
    'description' => 'کسب‌وکارت را در اپ سراغ به مخاطب نزدیک و علاقه‌مند نشان بده و بازدید، کلیک و سفارش را اندازه‌گیری کن.',
    'active'      => 'advertising',
    'canonical'   => 'advertising.php',
];

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content">
    <?php
    $hero = [
        'kicker'  => 'تبلیغات در اپ سراغ',
        'title'   => 'کسب‌وکارت را به مخاطب درست نشان بده',
        'lead'    => 'اپ سراغ مخاطب‌های واقعی را کنار هم می‌آورد. منوچی تبلیغ کسب‌وکارت را در جای درست و به آدم درست نشان می‌دهد.',
        'actions' => [
            ['label' => 'شروع کمپین', 'href' => url('register.php'), 'variant' => 'filled'],
            ['label' => 'گفت‌وگو با ما', 'href' => url('contact.php'), 'variant' => 'outlined'],
        ],
    ];
    require __DIR__ . '/partials/page-hero.php';
    ?>

    <section class="section">
        <div class="wrap">
            <div class="spotlight__card" data-reveal="up">
                <div class="spotlight__grid">
                    <div class="spotlight__copy">
                        <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>تبلیغی که دیده می‌شود</span>
                        <h2 class="spotlight__title">تبلیغ، بخشی از تجربه‌ی کاربر است.</h2>
                        <p class="spotlight__lead">تبلیغ در اپ سراغ شبیه یک تبلیغ مزاحم نیست؛ در جریان طبیعی اپ و کنار محتوایی که کاربر دنبالش است نمایش داده می‌شود.</p>
                        <ul class="spotlight__list">
                            <li><?= icon('check-circle') ?><span>نمایش هدفمند بر اساس موقعیت و علاقه</span></li>
                            <li><?= icon('check-circle') ?><span>اندازه‌گیری بازدید، کلیک و سفارش</span></li>
                            <li><?= icon('check-circle') ?><span>بودجه‌ی قابل کنترل و گزارش شفاف</span></li>
                        </ul>
                    </div>
                    <div class="detail-row__media">
                        <div class="ad-card" role="img" aria-label="نمونهٔ تبلیغ کسب‌وکار در اپ سراغ">
                            <div class="ad-card__top"><span class="ad-card__avatar" aria-hidden="true"></span><div><strong>کافه رُز</strong><small>تبلیغ در اپ سراغ</small></div></div>
                            <div class="ad-card__media" aria-hidden="true"></div>
                            <div class="ad-card__body"><strong>قهوه‌ی تازه، هر روز صبح</strong><p>۲۰٪ تخفیف برای سفارش اول از طریق منوچی.</p></div>
                            <span class="ad-card__cta">دیدن منو و سفارش</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section--alt">
        <div class="wrap">
            <div class="section-head section-head--center" data-reveal="up">
                <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>قالب‌های تبلیغاتی</span>
                <h2 class="section-head__title">برای هر هدفی، یک قالب</h2>
                <p class="section-head__lead">از معرفی کسب‌وکار تا برگرداندن مشتری قبلی؛ قالب مناسب را انتخاب کن.</p>
            </div>
            <div class="feature-grid">
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('megaphone') ?></span>
                    <h3>معرفی کسب‌وکار</h3>
                    <p>کارت کسب‌وکارت را به کاربرهای نزدیک نشان بده تا برای اولین بار سراغت بیایند.</p>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('map-pin') ?></span>
                    <h3>پیشنهاد محلی</h3>
                    <p>بر اساس موقعیت، پیشنهادت را به کسانی نشان بده که همان حوالی هستند.</p>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('users') ?></span>
                    <h3>بازگرداندن مشتری</h3>
                    <p>به مشتری‌هایی که مدتی نیامده‌اند یک پیشنهاد اختصاصی نشان بده.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="wrap split">
            <div class="split__copy">
                <div class="section-head" data-reveal="up">
                    <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>اندازه‌گیری</span>
                    <h2 class="section-head__title">بدان چه چیزی جواب می‌دهد</h2>
                    <p class="section-head__lead">هر کمپین گزارش خودش را دارد؛ هزینه، بازدید، کلیک و سفارش را کنار هم ببین.</p>
                </div>
                <ul class="check-list" data-reveal="up">
                    <li><?= icon('check-circle') ?><span>هزینه‌ی هر بازدید و هر کلیک</span></li>
                    <li><?= icon('check-circle') ?><span>نرخ تبدیل از کلیک به سفارش</span></li>
                    <li><?= icon('check-circle') ?><span>مقایسه‌ی کمپین‌ها در یک بازه</span></li>
                    <li><?= icon('check-circle') ?><span>خروجی برای حساب‌داری و گزارش</span></li>
                </ul>
            </div>
            <div class="split__media" data-reveal="up">
                <div class="m3-card m3-card--elevated" style="width:min(100%,420px);display:flex;flex-direction:column;gap:14px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;"><strong>کمپین هفته‌ی گذشته</strong><span class="m3-chip" style="background:var(--md-sys-color-primary-container);border-color:transparent;color:var(--md-sys-color-on-primary-container);">فعال</span></div>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;text-align:center;">
                        <div><strong style="display:block;font-size:1.3rem;color:var(--md-sys-color-primary);">۱۲٬۴۰۰</strong><small class="muted">بازدید</small></div>
                        <div><strong style="display:block;font-size:1.3rem;color:var(--md-sys-color-primary);">۸۶۰</strong><small class="muted">کلیک</small></div>
                        <div><strong style="display:block;font-size:1.3rem;color:var(--md-sys-color-primary);">۱۴۹</strong><small class="muted">سفارش</small></div>
                    </div>
                    <div class="m3-divider"></div>
                    <div style="display:flex;justify-content:space-between;font-size:0.82rem;"><span class="muted">هزینه‌ی هر سفارش</span><strong>۴۸٬۰۰۰ تومان</strong></div>
                    <div style="display:flex;justify-content:space-between;font-size:0.82rem;"><span class="muted">نرخ تبدیل</span><strong>۱۷٪</strong></div>
                </div>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="wrap">
            <div class="final-cta__card" data-reveal="up">
                <div class="final-cta__copy">
                    <h2>اولین کمپینت را شروع کن</h2>
                    <p>بودجه را خودت تعیین می‌کنی و هر زمان خواستی متوقف می‌کنی.</p>
                </div>
                <div class="final-cta__actions">
                    <a class="m3-button m3-button--light m3-button--lg" href="<?= url('register.php') ?>">شروع کمپین <?= icon('arrow', 18, 'btn-arrow') ?></a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
