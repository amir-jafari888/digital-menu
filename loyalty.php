<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/partials/icons.php';

$page = [
    'title'       => 'باشگاه مشتریان | ' . SITE_NAME,
    'description' => 'با باشگاه مشتریان منوچی برای هر سفارش امتیاز و کش‌بک بساز و مشتری‌ها را دوباره به کسب‌وکارت برگردان.',
    'active'      => 'loyalty',
    'canonical'   => 'loyalty.php',
];

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content">
    <?php
    $hero = [
        'kicker'  => 'باشگاه مشتریان',
        'title'   => 'مشتری‌ها را با امتیاز نگه دار',
        'lead'    => 'هر سفارش یک قدم به وفاداری نزدیک‌تر است. امتیاز، کش‌بک و پیشنهاد شخصی بساز تا مشتری دوباره برگردد.',
        'actions' => [
            ['label' => 'ساخت باشگاه مشتریان', 'href' => url('register.php'), 'variant' => 'filled'],
            ['label' => 'دیدن تعرفه‌ها', 'href' => url('pricing.php'), 'variant' => 'outlined'],
        ],
    ];
    require __DIR__ . '/partials/page-hero.php';
    ?>

    <section class="section">
        <div class="wrap">
            <div class="spotlight__card spotlight__card--accent" data-reveal="up">
                <div class="spotlight__grid">
                    <div class="spotlight__copy">
                        <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>وفاداری، ساده و قابل اندازه‌گیری</span>
                        <h2 class="spotlight__title">هر سفارش، یک قدم به مشتری وفادار.</h2>
                        <p class="spotlight__lead">قانون امتیاز را خودت تعیین می‌کنی: چند تومان سفارش، چند امتیاز. مشتری امتیاز جمع می‌کند و آن را به تخفیف یا سفارش رایگان تبدیل می‌کند.</p>
                        <ul class="spotlight__list">
                            <li><?= icon('check-circle') ?><span>امتیاز برای هر سفارش و کش‌بک قابل تنظیم</span></li>
                            <li><?= icon('check-circle') ?><span>سطح‌بندی مشتری و پیشنهاد اختصاصی</span></li>
                            <li><?= icon('check-circle') ?><span>یادآوری بازگشت و ارزش هر مشتری</span></li>
                        </ul>
                    </div>
                    <div class="detail-row__media">
                        <div class="loyalty-card" role="img" aria-label="نمونهٔ کارت باشگاه مشتریان با ۲٬۴۸۰ امتیاز">
                            <div class="loyalty-card__head"><span>باشگاه مشتریان منوچی</span><span>عضو طلایی</span></div>
                            <div class="loyalty-card__points">۲٬۴۸۰ <small>امتیاز</small></div>
                            <div class="loyalty-card__bar"><span></span></div>
                            <div class="loyalty-card__foot"><span>۷۲۰ امتیاز تا سفارش رایگان</span><span>سطح ۳</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section--alt">
        <div class="wrap">
            <div class="section-head section-head--center" data-reveal="up">
                <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>چطور کار می‌کند</span>
                <h2 class="section-head__title">سه قدم تا یک باشگاه فعال</h2>
                <p class="section-head__lead">بدون پیچیدگی و بدون ابزار اضافه؛ از همان پنل منوچی.</p>
            </div>
            <div class="usecase-grid">
                <article class="usecase" data-reveal="up"><span class="usecase__icon"><?= icon('bolt') ?></span><h3>۱. قانون امتیاز را بساز</h3><p>مشخص کن هر هزار تومان چند امتیاز دارد و کش‌بک چطور محاسبه شود.</p></article>
                <article class="usecase" data-reveal="up"><span class="usecase__icon"><?= icon('users') ?></span><h3>۲. مشتری عضو می‌شود</h3><p>مشتری با شماره موبایل یا بعد از اولین سفارش عضو باشگاه می‌شود.</p></article>
                <article class="usecase" data-reveal="up"><span class="usecase__icon"><?= icon('gift') ?></span><h3>۳. امتیاز خرج می‌شود</h3><p>امتیاز به تخفیف، آیتم هدیه یا سفارش رایگان تبدیل می‌شود و مشتری برمی‌گردد.</p></article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="wrap">
            <div class="section-head" data-reveal="up">
                <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>سطح‌بندی</span>
                <h2 class="section-head__title">مشتری‌های بهتر، پاداش بهتر</h2>
                <p class="section-head__lead">سطح هر مشتری را بر اساس مجموع سفارش‌ها تعیین کن و برای هر سطح پاداش متفاوت بگذار.</p>
            </div>
            <div class="pricing-grid">
                <article class="price-card" data-reveal="up">
                    <h3 class="price-card__name">نقره‌ای</h3>
                    <p class="price-card__desc">شروع وفاداری؛ برای مشتری‌های تازه.</p>
                    <div class="price-card__price"><span class="price-card__amount">۱×</span><span class="price-card__unit">امتیاز</span></div>
                    <ul class="price-card__features">
                        <li><?= icon('check') ?><span>امتیاز پایه برای هر سفارش</span></li>
                        <li><?= icon('check') ?><span>دسترسی به پیشنهادهای عمومی</span></li>
                    </ul>
                </article>
                <article class="price-card price-card--featured" data-reveal="up">
                    <span class="price-card__badge">محبوب</span>
                    <h3 class="price-card__name">طلایی</h3>
                    <p class="price-card__desc">برای مشتری‌هایی که مرتب برمی‌گردند.</p>
                    <div class="price-card__price"><span class="price-card__amount">۱٫۵×</span><span class="price-card__unit">امتیاز</span></div>
                    <ul class="price-card__features">
                        <li><?= icon('check') ?><span>امتیاز بیشتر برای هر سفارش</span></li>
                        <li><?= icon('check') ?><span>پیشنهاد اختصاصی و یادآوری</span></li>
                        <li><?= icon('check') ?><span>کش‌بک بالاتر</span></li>
                    </ul>
                </article>
                <article class="price-card" data-reveal="up">
                    <h3 class="price-card__name">پلاتینیوم</h3>
                    <p class="price-card__desc">برای بهترین مشتری‌های کسب‌وکار.</p>
                    <div class="price-card__price"><span class="price-card__amount">۲×</span><span class="price-card__unit">امتیاز</span></div>
                    <ul class="price-card__features">
                        <li><?= icon('check') ?><span>حداکثر امتیاز و کش‌بک</span></li>
                        <li><?= icon('check') ?><span>دسترسی زودتر به پیشنهادها</span></li>
                        <li><?= icon('check') ?><span>پاداش ویژه‌ی تولد و مناسبت</span></li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="wrap">
            <div class="final-cta__card" data-reveal="up">
                <div class="final-cta__copy">
                    <h2>مشتری‌هایت را به باشگاه بیاور</h2>
                    <p>باشگاه مشتریان روی همان منوی دیجیتال سوار می‌شود؛ بدون ابزار جداگانه.</p>
                </div>
                <div class="final-cta__actions">
                    <a class="m3-button m3-button--light m3-button--lg" href="<?= url('register.php') ?>">شروع کن <?= icon('arrow', 18, 'btn-arrow') ?></a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
