<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/partials/icons.php';

$page = [
    'title'       => 'امکانات | ' . SITE_NAME,
    'description' => 'منوی دیجیتال، سفارش‌گیری، QR اختصاصی، باشگاه مشتریان، تبلیغات و گزارش؛ همه‌ی امکانات منوچی در یک پنل.',
    'active'      => 'features',
    'canonical'   => 'features.php',
];

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content">
    <?php
    $hero = [
        'kicker'  => 'امکانات منوچی',
        'title'   => 'همه‌ی چیزی که برای منوی دیجیتال لازم داری',
        'lead'    => 'از ساخت منو و QR تا سفارش، باشگاه مشتریان و تبلیغات؛ ابزارها کنار هم کار می‌کنند تا کار روزانه‌ات ساده‌تر شود.',
        'actions' => [
            ['label' => 'شروع رایگان', 'href' => url('register.php'), 'variant' => 'filled'],
            ['label' => 'دیدن تعرفه‌ها', 'href' => url('pricing.php'), 'variant' => 'outlined'],
        ],
    ];
    require __DIR__ . '/partials/page-hero.php';
    ?>

    <section class="section">
        <div class="wrap">
            <h2 class="visually-hidden">امکانات اصلی</h2>
            <div class="feature-grid">
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('refresh') ?></span>
                    <h3>منوی همیشه به‌روز</h3>
                    <p>قیمت، توضیح یا موجودی را تغییر بده؛ مشتری همان لحظه نسخه‌ی تازه را می‌بیند.</p>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('receipt') ?></span>
                    <h3>پنل سفارش</h3>
                    <p>سفارش‌های جدید را ببین، وضعیت را به‌روز کن و سفارش‌های میز را از دست نده.</p>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('qr') ?></span>
                    <h3>QR اختصاصی</h3>
                    <p>یک QR برای هر کسب‌وکار؛ مشتری بدون نصب اپلیکیشن منو را باز می‌کند.</p>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('gift') ?></span>
                    <h3>باشگاه مشتریان</h3>
                    <p>امتیاز، کش‌بک و پیشنهاد شخصی برای برگرداندن مشتری‌های قبلی.</p>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('megaphone') ?></span>
                    <h3>تبلیغات در اپ سراغ</h3>
                    <p>کسب‌وکارت را به مخاطب نزدیک و علاقه‌مند نشان بده و نتیجه را بسنج.</p>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('chart') ?></span>
                    <h3>گزارش و تحلیل</h3>
                    <p>ببین چه محصولی بیشتر دیده می‌شود و کدام مشتری ارزش بیشتری دارد.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section section--alt">
        <div class="wrap">
            <div class="section-head" data-reveal="up">
                <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>در عمل</span>
                <h2 class="section-head__title">از منو تا مشتری، یک مسیر</h2>
                <p class="section-head__lead">هر بخش روی بخش قبلی سوار می‌شود؛ لازم نیست ابزارهای جداگانه بخری.</p>
            </div>

            <div class="detail-row" data-reveal="up">
                <div class="detail-row__copy">
                    <h2>منو بساز، همین امروز</h2>
                    <p>اطلاعات کسب‌وکار و دسته‌ها را وارد کن. عکس، قیمت و توضیح هر محصول را اضافه کن و منو را منتشر کن.</p>
                    <ul class="check-list">
                        <li><?= icon('check-circle') ?><span>ویرایش نامحدود محصول و دسته</span></li>
                        <li><?= icon('check-circle') ?><span>پیش‌نمایش قبل از انتشار</span></li>
                        <li><?= icon('check-circle') ?><span>لینک و QR اختصاصی</span></li>
                    </ul>
                </div>
                <div class="detail-row__media">
                    <div class="paper" role="img" aria-label="نمونهٔ منوی منتشرشده">
                        <div class="paper__head"><span class="paper__logo">منوچی<span aria-hidden="true">.</span></span><span class="paper__progress">منوی منتشرشده</span></div>
                        <div class="category-option is-selected"><span class="category-option__emoji" aria-hidden="true">☕</span><span class="category-option__text"><strong>لاته کاراملی</strong><small>۱۲۵٬۰۰۰ تومان</small></span><span class="category-option__check" aria-hidden="true"></span></div>
                        <div class="category-option"><span class="category-option__emoji" aria-hidden="true">🍰</span><span class="category-option__text"><strong>چیزکیک روز</strong><small>۱۵۰٬۰۰۰ تومان</small></span><span class="category-option__check" aria-hidden="true"></span></div>
                        <div class="category-option"><span class="category-option__emoji" aria-hidden="true">🥤</span><span class="category-option__text"><strong>آیس لاته</strong><small>۹۵٬۰۰۰ تومان</small></span><span class="category-option__check" aria-hidden="true"></span></div>
                    </div>
                </div>
            </div>

            <div class="detail-row" data-reveal="up">
                <div class="detail-row__copy">
                    <h2>سفارش‌ها را منظم نگه دار</h2>
                    <p>هر سفارش با وضعیت مشخص وارد پنل می‌شود. تیمت می‌داند چه چیزی آماده است و چه چیزی در انتظار.</p>
                    <ul class="check-list">
                        <li><?= icon('check-circle') ?><span>وضعیت سفارش: جدید، در حال آماده‌سازی، تحویل‌شده</span></li>
                        <li><?= icon('check-circle') ?><span>یادداشت و سفارشی‌سازی هر آیتم</span></li>
                        <li><?= icon('check-circle') ?><span>تاریخچه‌ی سفارش هر مشتری</span></li>
                    </ul>
                </div>
                <div class="detail-row__media">
                    <div class="m3-card m3-card--elevated" style="width:min(100%,380px);display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;"><strong>سفارش #۱۰۲۴</strong><span class="m3-chip" style="background:var(--md-sys-color-primary-container);border-color:transparent;color:var(--md-sys-color-on-primary-container);">در حال آماده‌سازی</span></div>
                        <div class="m3-divider"></div>
                        <div style="display:flex;justify-content:space-between;font-size:0.84rem;"><span>۲ عدد لاته کاراملی</span><span>۲۵۰٬۰۰۰</span></div>
                        <div style="display:flex;justify-content:space-between;font-size:0.84rem;"><span>۱ عدد چیزکیک</span><span>۱۵۰٬۰۰۰</span></div>
                        <div class="m3-divider"></div>
                        <div style="display:flex;justify-content:space-between;font-weight:800;"><span>جمع</span><span style="color:var(--md-sys-color-primary);">۴۰۰٬۰۰۰ تومان</span></div>
                    </div>
                </div>
            </div>

            <div class="detail-row" data-reveal="up">
                <div class="detail-row__copy">
                    <h2>باشگاه مشتریان و تبلیغات</h2>
                    <p>بعد از ساخت منو، مشتری‌ها را نگه دار و مشتری تازه جذب کن. هر دو از همان پنل مدیریت می‌شوند.</p>
                    <ul class="check-list">
                        <li><?= icon('check-circle') ?><span>امتیاز و کش‌بک برای هر سفارش</span></li>
                        <li><?= icon('check-circle') ?><span>کمپین تبلیغاتی در اپ سراغ</span></li>
                        <li><?= icon('check-circle') ?><span>گزارش بازگشت مشتری و هزینه‌ی جذب</span></li>
                    </ul>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;">
                        <a class="m3-button m3-button--outlined" href="<?= url('loyalty.php') ?>">باشگاه مشتریان</a>
                        <a class="m3-button m3-button--outlined" href="<?= url('advertising.php') ?>">تبلیغات</a>
                    </div>
                </div>
                <div class="detail-row__media">
                    <div class="loyalty-card" role="img" aria-label="نمونهٔ کارت باشگاه مشتریان">
                        <div class="loyalty-card__head"><span>باشگاه مشتریان</span><span>عضو طلایی</span></div>
                        <div class="loyalty-card__points">۲٬۴۸۰ <small>امتیاز</small></div>
                        <div class="loyalty-card__bar"><span></span></div>
                        <div class="loyalty-card__foot"><span>۷۲۰ امتیاز تا سفارش رایگان</span><span>سطح ۳</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="wrap">
            <div class="final-cta__card" data-reveal="up">
                <div class="final-cta__copy">
                    <h2>آماده‌ای منوی بعدی‌ات را بسازی؟</h2>
                    <p>شروع رایگان است. در چند دقیقه اولین منوی دیجیتالت را منتشر کن.</p>
                </div>
                <div class="final-cta__actions">
                    <a class="m3-button m3-button--light m3-button--lg" href="<?= url('register.php') ?>">شروع ساخت منو <?= icon('arrow', 18, 'btn-arrow') ?></a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
