<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/partials/icons.php';

$page = [
    'title'       => SITE_NAME . ' | منوی دیجیتال، باشگاه مشتریان و تبلیغات',
    'description' => 'منوچی منوی دیجیتال کافه و رستوران، باشگاه مشتریان و تبلیغات در اپ سراغ را در یک پلتفرم ساده جمع می‌کند.',
    'active'      => 'home',
    'canonical'   => 'index.php',
];

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content">

    <!-- Hero -->
    <section class="hero" id="top" data-enter>
        <div class="wrap hero__grid">
            <div class="hero__copy">
                <span class="kicker" data-enter style="--enter-delay: 40ms"><span class="kicker__dot" aria-hidden="true"></span>منوی دیجیتال، باشگاه مشتریان و تبلیغات</span>
                <h1 class="hero__title" data-enter style="--enter-delay: 110ms">منوی دیجیتال، مشتری وفادار،<br><span class="accent underline">رشد بی‌وقفه.</span></h1>
                <p class="hero__lead" data-enter style="--enter-delay: 180ms">منوچی منوی کافه و رستورانت را به یک تجربه‌ی سریع تبدیل می‌کند، باشگاه مشتریان می‌سازد و کسب‌وکارت را در اپ سراغ به مشتری‌های تازه معرفی می‌کند.</p>
                <div class="hero__actions" data-enter style="--enter-delay: 250ms">
                    <a class="m3-button m3-button--filled m3-button--lg" href="<?= url('register.php') ?>">شروع رایگان <?= icon('arrow', 18, 'btn-arrow') ?></a>
                    <a class="m3-button m3-button--outlined m3-button--lg" href="#demo"><?= icon('play', 18) ?> دیدن پیش‌نمایش</a>
                </div>
                <div class="hero__proof" data-enter style="--enter-delay: 320ms">
                    <div class="hero__avatars" aria-hidden="true"><span>ک</span><span>م</span><span>ر</span><span>+</span></div>
                    <div class="hero__proof-text">
                        <strong>ساده برای شما، راحت برای مشتری</strong>
                        <small>از ساخت منو تا باشگاه مشتریان</small>
                    </div>
                </div>
            </div>

            <div class="hero__visual" id="demo" aria-label="پیش‌نمایش منوی دیجیتال منوچی">
                <div class="phone" data-enter style="--enter-delay: 300ms">
                    <div class="phone__status"><span class="dot" aria-hidden="true"></span><span>منوی کافه</span><span aria-hidden="true">•••</span></div>
                    <div class="phone__cover">
                        <span class="phone__sun" aria-hidden="true"></span>
                        <span class="phone__cup" aria-hidden="true"></span>
                        <span class="phone__cover-label"><small>CAFE NO. 08</small><strong>یک مکث<br>برای خودت.</strong></span>
                        <div class="qr-card" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="currentColor"><path d="M4 4h20v20H4zM10 10v8h8v-8zM40 4h20v20H40zM46 10v8h8v-8zM4 40h20v20H4zM10 46v8h8v-8zM32 4h4v8h-4zM28 16h8v4h-8zM32 24h8v4h-8zM28 32h8v8h-8zM40 32h4v8h-4zM48 28h12v4H48zM48 40h4v8h-4zM32 48h8v4h-8zM40 56h8v4h-8zM56 48h4v12h-4z" /></svg>
                        </div>
                    </div>
                    <div class="phone__body">
                        <div class="phone__title-row">
                            <div><small>پیشنهاد امروز</small><strong>محبوب‌های کافه</strong></div>
                            <span class="phone__filter" aria-hidden="true">همه</span>
                        </div>
                        <div class="menu-row">
                            <span class="menu-row__art menu-row__art--coffee" aria-hidden="true"></span>
                            <div class="menu-row__info"><strong>لاته کاراملی</strong><small>اسپرسو، شیر، کارامل</small><span class="menu-row__price">۱۲۵٬۰۰۰ <small>تومان</small></span></div>
                            <button type="button" class="add-item" aria-label="افزودن لاته به سبد" data-price="125000">+</button>
                        </div>
                        <div class="menu-row">
                            <span class="menu-row__art menu-row__art--cake" aria-hidden="true"></span>
                            <div class="menu-row__info"><strong>چیزکیک روز</strong><small>تازه و دست‌ساز</small><span class="menu-row__price">۱۵۰٬۰۰۰ <small>تومان</small></span></div>
                            <button type="button" class="add-item" aria-label="افزودن چیزکیک به سبد" data-price="150000">+</button>
                        </div>
                        <div class="phone__cart"><span>سبد سفارش <b class="cart-count">۰</b></span><strong class="cart-total">۰ تومان</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="stats" aria-label="منوچی در یک نگاه">
        <div class="stats__item"><strong>۳ سرویس</strong><span>منو، باشگاه مشتریان، تبلیغات</span></div>
        <div class="stats__item"><strong>بدون نصب</strong><span>مشتری فقط QR را اسکن می‌کند</span></div>
        <div class="stats__item"><strong>QR اختصاصی</strong><span>برای هر کسب‌وکار</span></div>
        <div class="stats__item"><strong>پنل واحد</strong><span>مدیریت همه‌چیز در یک جا</span></div>
    </section>

    <!-- Features -->
    <section class="section" id="features">
        <div class="wrap">
            <div class="section-head section-head--center" data-reveal="up">
                <h2 class="section-head__title">کمتر درگیر کارهای تکراری شو</h2>
                <p class="section-head__lead">روی چیزی تمرکز کن که واقعاً مهم است: مشتری و کیفیت تجربه‌اش.</p>
            </div>

            <div class="feature-grid">
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('refresh') ?></span>
                    <h3>منوی همیشه به‌روز</h3>
                    <p>قیمت، توضیح یا موجودی را تغییر بده؛ لازم نیست منو را دوباره چاپ کنی.</p>
                    <a class="feature-card__link" href="<?= url('features.php') ?>">بیشتر بدانید <?= icon('arrow', 16) ?></a>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('receipt') ?></span>
                    <h3>سفارش‌گیری منظم</h3>
                    <p>سفارش‌ها را در یک پنل ببین و وضعیت هرکدام را به‌سادگی به‌روز کن.</p>
                    <a class="feature-card__link" href="<?= url('features.php') ?>">بیشتر بدانید <?= icon('arrow', 16) ?></a>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('qr') ?></span>
                    <h3>QR اختصاصی</h3>
                    <p>برای هر کسب‌وکار یک لینک و QR جدا؛ مشتری بدون نصب باز می‌کند.</p>
                    <a class="feature-card__link" href="<?= url('how-it-works.php') ?>">بیشتر بدانید <?= icon('arrow', 16) ?></a>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('gift') ?></span>
                    <h3>باشگاه مشتریان</h3>
                    <p>امتیاز و کش‌بک بساز و مشتری‌ها را دوباره به کسب‌وکارت برگردان.</p>
                    <a class="feature-card__link" href="<?= url('loyalty.php') ?>">بیشتر بدانید <?= icon('arrow', 16) ?></a>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('megaphone') ?></span>
                    <h3>تبلیغات در اپ سراغ</h3>
                    <p>کسب‌وکارت را در اپ سراغ به مخاطب‌های نزدیک و درست نشان بده.</p>
                    <a class="feature-card__link" href="<?= url('advertising.php') ?>">بیشتر بدانید <?= icon('arrow', 16) ?></a>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('chart') ?></span>
                    <h3>گزارش و تحلیل</h3>
                    <p>ببین چه چیزی فروش می‌آورد و کدام محصول بیشتر دیده می‌شود.</p>
                    <a class="feature-card__link" href="<?= url('features.php') ?>">بیشتر بدانید <?= icon('arrow', 16) ?></a>
                </article>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="section section--alt" id="how-it-works">
        <div class="wrap split">
            <div class="split__copy">
                <div class="section-head" data-reveal="up">
                    <h2 class="section-head__title">از ایده تا منوی آنلاین، در سه قدم</h2>
                    <p class="section-head__lead">برای شروع لازم نیست کارشناس طراحی یا فناوری باشی. اطلاعات منو را وارد کن و مرحله‌به‌مرحله پیش برو.</p>
                </div>
                <div class="steps">
                    <div class="step" data-reveal="up">
                        <span class="step__num">۱</span>
                        <div><h3>اطلاعات کسب‌وکارت را وارد کن</h3><p>نام، مشخصات و ظاهر اولیه منویت را تنظیم کن.</p></div>
                    </div>
                    <div class="step" data-reveal="up">
                        <span class="step__num">۲</span>
                        <div><h3>دسته‌ها و محصولات را اضافه کن</h3><p>عکس، توضیح و قیمت هر محصول را ثبت کن.</p></div>
                    </div>
                    <div class="step" data-reveal="up">
                        <span class="step__num">۳</span>
                        <div><h3>لینک و QR منو را بگیر</h3><p>منویت را با مشتری‌ها به اشتراک بگذار و سفارش بگیر.</p></div>
                    </div>
                </div>
                <div style="margin-top: 18px;" data-reveal="up">
                    <a class="m3-button m3-button--filled m3-button--lg" href="<?= url('register.php') ?>">شروع ساخت منو <?= icon('arrow', 18, 'btn-arrow') ?></a>
                </div>
            </div>

            <div class="split__media" data-reveal="up">
                <div class="paper" role="img" aria-label="نمونهٔ مرحله‌بندی ساخت منو">
                    <div class="paper__head"><span class="paper__logo">منوچی<span aria-hidden="true">.</span></span><span class="paper__progress">مرحله ۲ از ۳</span></div>
                    <div class="progress-track"><span></span></div>
                    <span class="paper__eyebrow">منوی شما</span>
                    <h3>چی سرو می‌کنی؟</h3>
                    <p>اولین دسته‌بندی‌ات را انتخاب کن یا بساز.</p>
                    <div class="category-option is-selected"><span class="category-option__emoji" aria-hidden="true">☕</span><span class="category-option__text"><strong>نوشیدنی گرم</strong><small>۸ محصول</small></span><span class="category-option__check" aria-hidden="true"></span></div>
                    <div class="category-option"><span class="category-option__emoji" aria-hidden="true">🥤</span><span class="category-option__text"><strong>نوشیدنی سرد</strong><small>۶ محصول</small></span><span class="category-option__check" aria-hidden="true"></span></div>
                    <div class="category-option"><span class="category-option__emoji" aria-hidden="true">🍰</span><span class="category-option__text"><strong>کیک و دسر</strong><small>۴ محصول</small></span><span class="category-option__check" aria-hidden="true"></span></div>
                    <div class="paper__footer"><span>مرحله بعدی: محصولات</span><span class="paper__next">ادامه ←</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Loyalty spotlight -->
    <section class="section" id="loyalty">
        <div class="wrap">
            <div class="spotlight__card spotlight__card--accent" data-reveal="up">
                <div class="spotlight__grid">
                    <div class="spotlight__copy">
                        <h2 class="spotlight__title">مشتری‌ها را با امتیاز نگه دار.</h2>
                        <p class="spotlight__lead">هر سفارش یک قدم به وفاداری نزدیک‌تر است. امتیاز، کش‌بک و پیشنهاد شخصی بساز تا مشتری دوباره برگردد.</p>
                        <ul class="spotlight__list">
                            <li><?= icon('check-circle') ?><span>امتیاز برای هر سفارش و کش‌بک قابل تنظیم</span></li>
                            <li><?= icon('check-circle') ?><span>سطح‌بندی مشتری‌ها و پیشنهاد اختصاصی</span></li>
                            <li><?= icon('check-circle') ?><span>گزارش بازگشت مشتری و ارزش هر سفارش</span></li>
                        </ul>
                        <div class="spotlight__actions">
                            <a class="m3-button m3-button--light m3-button--lg" href="<?= url('loyalty.php') ?>">ساخت باشگاه مشتریان <?= icon('arrow', 18, 'btn-arrow') ?></a>
                        </div>
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

    <!-- Advertising spotlight -->
    <section class="section section--alt" id="advertising">
        <div class="wrap">
            <div class="spotlight__card" data-reveal="up">
                <div class="spotlight__grid">
                    <div class="spotlight__copy">
                        <h2 class="spotlight__title">کسب‌وکارت را به مخاطب درست نشان بده.</h2>
                        <p class="spotlight__lead">اپ سراغ مخاطب‌های واقعی را کنار هم می‌آورد. منوچی تبلیغ کسب‌وکارت را در جای درست و به آدم درست نشان می‌دهد.</p>
                        <ul class="spotlight__list">
                            <li><?= icon('check-circle') ?><span>نمایش هدفمند بر اساس موقعیت و علاقه</span></li>
                            <li><?= icon('check-circle') ?><span>اندازه‌گیری بازدید، کلیک و سفارش</span></li>
                            <li><?= icon('check-circle') ?><span>بودجه‌ی قابل کنترل و گزارش شفاف</span></li>
                        </ul>
                        <div class="spotlight__actions">
                            <a class="m3-button m3-button--light m3-button--lg" href="<?= url('advertising.php') ?>">درباره‌ی تبلیغات <?= icon('arrow', 18, 'btn-arrow') ?></a>
                        </div>
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

    <!-- Pricing -->
    <section class="section" id="pricing">
        <div class="wrap">
            <?php require __DIR__ . '/partials/pricing.php'; ?>
        </div>
    </section>

    <!-- Use cases -->
    <section class="section section--alt">
        <div class="wrap">
            <div class="section-head section-head--center" data-reveal="up">
                <h2 class="section-head__title">هر جایی که منو هست، منوچی جا دارد</h2>
                <p class="section-head__lead">از کافه‌ی کوچک تا فودکورت شلوغ؛ منو و مشتری را یک‌جا مدیریت کن.</p>
            </div>
            <div class="usecase-grid">
                <article class="usecase" data-reveal="up"><span class="usecase__icon"><?= icon('coffee') ?></span><h3>کافه</h3><p>منوی سریع، سفارش راحت و باشگاه مشتریان برای مشتری‌های همیشگی.</p></article>
                <article class="usecase" data-reveal="up"><span class="usecase__icon"><?= icon('restaurant') ?></span><h3>رستوران</h3><p>دسته‌بندی غذا، پیشنهاد روز و مدیریت سفارش‌های میز.</p></article>
                <article class="usecase" data-reveal="up"><span class="usecase__icon"><?= icon('store') ?></span><h3>فودکورت</h3><p>چند فروشنده، یک منو و تجربه‌ی یکپارچه برای مشتری.</p></article>
                <article class="usecase" data-reveal="up"><span class="usecase__icon"><?= icon('cake') ?></span><h3>بیکری و دسر</h3><p>محصولات تازه، موجودی روز و سفارش پیش از رسیدن.</p></article>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="section" id="faq">
        <div class="wrap faq-grid">
            <div class="section-head" data-reveal="up">
                <h2 class="section-head__title">قبل از شروع، این‌ها را بدان</h2>
                <p class="section-head__lead">جواب چند سوال رایج درباره‌ی منوی دیجیتال، باشگاه مشتریان و تبلیغات.</p>
                <a class="m3-button m3-button--text" href="<?= url('contact.php') ?>" style="align-self:flex-start;">سوال دیگری داری؟ بنویس <?= icon('arrow', 16) ?></a>
            </div>
            <div class="faq-list">
                <details class="faq-item" data-reveal="up" open>
                    <summary>مشتری برای دیدن منو باید برنامه‌ای نصب کند؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">خیر. مشتری با اسکن QR یا باز کردن لینک، منو را در مرورگر موبایل خود می‌بیند.</div>
                </details>
                <details class="faq-item" data-reveal="up">
                    <summary>بعد از تغییر قیمت باید QR جدید بسازم؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">خیر. QR به لینک منوی شما متصل است؛ با به‌روزرسانی اطلاعات، همان منو نمایش داده می‌شود.</div>
                </details>
                <details class="faq-item" data-reveal="up">
                    <summary>می‌توانم سفارش‌های مشتری را مدیریت کنم؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">بله. پنل سفارش‌ها وضعیت هر سفارش را نشان می‌دهد و می‌توانی آن را به‌روز کنی.</div>
                </details>
                <details class="faq-item" data-reveal="up">
                    <summary>باشگاه مشتریان چطور کار می‌کند؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">برای هر سفارش امتیاز تعریف می‌کنی؛ مشتری امتیاز جمع می‌کند و می‌تواند آن را به تخفیف یا سفارش رایگان تبدیل کند.</div>
                </details>
                <details class="faq-item" data-reveal="up">
                    <summary>تبلیغات در اپ سراغ چطور به من کمک می‌کند؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">کسب‌وکارت را به مخاطب‌های نزدیک و علاقه‌مند نشان می‌دهد و بازدید، کلیک و سفارش را قابل اندازه‌گیری می‌کند.</div>
                </details>
                <details class="faq-item" data-reveal="up">
                    <summary>برای شروع به دانش فنی نیاز دارم؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">خیر. مسیر ساخت منو مرحله‌ای طراحی شده تا بدون دانش طراحی سایت اطلاعات کسب‌وکارت را وارد کنی.</div>
                </details>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="final-cta">
        <div class="wrap">
            <div class="final-cta__card" data-reveal="up">
                <div class="final-cta__copy">
                    <h2>منوی بعدی‌ات، دیجیتالیه.</h2>
                    <p>یک تجربه‌ی ساده‌تر برای تو و مشتری‌هایت بساز. شروع رایگان است و در چند دقیقه آماده می‌شوی.</p>
                </div>
                <div class="final-cta__actions">
                    <a class="m3-button m3-button--light m3-button--lg" href="<?= url('register.php') ?>">شروع ساخت منو <?= icon('arrow', 18, 'btn-arrow') ?></a>
                    <a class="m3-button m3-button--outlined m3-button--lg" href="<?= url('pricing.php') ?>" style="color:#f4fff8;border-color:rgba(244,255,248,.6);">دیدن تعرفه‌ها</a>
                </div>
            </div>
        </div>
    </section>

</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
