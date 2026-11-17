<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/partials/icons.php';

$page = [
    'title'       => 'چطور کار می‌کند | ' . SITE_NAME,
    'description' => 'در سه قدم منوی دیجیتال بساز: اطلاعات کسب‌وکار، دسته‌ها و محصولات، سپس لینک و QR اختصاصی.',
    'active'      => 'how-it-works',
    'canonical'   => 'how-it-works.php',
];

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main id="main-content">
    <?php
    $hero = [
        'kicker'  => 'چطور کار می‌کند',
        'title'   => 'از ایده تا منوی آنلاین، در سه قدم',
        'lead'    => 'برای شروع لازم نیست کارشناس طراحی یا فناوری باشی. اطلاعات منو را وارد کن و مرحله‌به‌مرحله پیش برو.',
        'actions' => [
            ['label' => 'شروع ساخت منو', 'href' => url('register.php'), 'variant' => 'filled'],
            ['label' => 'دیدن امکانات', 'href' => url('features.php'), 'variant' => 'outlined'],
        ],
    ];
    require __DIR__ . '/partials/page-hero.php';
    ?>

    <section class="section">
        <div class="wrap split">
            <div class="split__copy">
                <h2 class="visually-hidden">مراحل ساخت منو</h2>
                <div class="steps">
                    <div class="step" data-reveal="up">
                        <span class="step__num">۱</span>
                        <div><h3>اطلاعات کسب‌وکارت را وارد کن</h3><p>نام، مشخصات و ظاهر اولیه منویت را تنظیم کن. رنگ و لوگو را انتخاب کن تا منو شبیه برند خودت شود.</p></div>
                    </div>
                    <div class="step" data-reveal="up">
                        <span class="step__num">۲</span>
                        <div><h3>دسته‌ها و محصولات را اضافه کن</h3><p>عکس، توضیح و قیمت هر محصول را ثبت کن. می‌توانی محصول را پنهان کنی یا موجودی را به‌روز کنی.</p></div>
                    </div>
                    <div class="step" data-reveal="up">
                        <span class="step__num">۳</span>
                        <div><h3>لینک و QR منو را بگیر</h3><p>منو را منتشر کن، لینک و QR اختصاصی را بگیر و روی میز یا ویترین بگذار.</p></div>
                    </div>
                </div>
                <div style="margin-top:18px;" data-reveal="up">
                    <a class="m3-button m3-button--filled m3-button--lg" href="<?= url('register.php') ?>">شروع کن <?= icon('arrow', 18, 'btn-arrow') ?></a>
                </div>
            </div>
            <div class="split__media" data-reveal="up">
                <div class="paper" role="img" aria-label="نمونهٔ مرحلهٔ دوم ساخت منو">
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

    <section class="section section--alt">
        <div class="wrap">
            <div class="section-head section-head--center" data-reveal="up">
                <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>بعد از انتشار</span>
                <h2 class="section-head__title">منو که منتشر شد، تازه شروع کار است</h2>
                <p class="section-head__lead">سفارش بگیر، مشتری را نگه دار و از نتیجه یاد بگیر.</p>
            </div>
            <div class="feature-grid">
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('receipt') ?></span>
                    <h3>سفارش بگیر</h3>
                    <p>مشتری از منو انتخاب می‌کند و سفارش در پنل تو ظاهر می‌شود.</p>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('gift') ?></span>
                    <h3>مشتری را نگه دار</h3>
                    <p>با باشگاه مشتریان امتیاز بده و مشتری را دوباره برگردان.</p>
                </article>
                <article class="feature-card" data-reveal="up">
                    <span class="feature-card__icon"><?= icon('chart') ?></span>
                    <h3>نتیجه را ببین</h3>
                    <p>گزارش فروش و مشتری کمک می‌کند تصمیم بهتری بگیری.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="wrap">
            <div class="section-head" data-reveal="up">
                <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>سوال‌های رایج</span>
                <h2 class="section-head__title">قبل از شروع بدان</h2>
            </div>
            <div class="faq-list" style="max-width:820px;">
                <details class="faq-item" data-reveal="up" open>
                    <summary>چقدر طول می‌کشد تا منو آماده شود؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">اگر اطلاعات محصولات آماده باشد، اولین منو در چند دقیقه منتشر می‌شود.</div>
                </details>
                <details class="faq-item" data-reveal="up">
                    <summary>می‌توانم بعداً محصول اضافه کنم؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">بله. ویرایش محصول و دسته محدودیت ندارد و تغییرات همان لحظه در منو دیده می‌شود.</div>
                </details>
                <details class="faq-item" data-reveal="up">
                    <summary>QR را باید دوباره چاپ کنم؟<span class="faq-icon" aria-hidden="true">+</span></summary>
                    <div class="faq-item__body">خیر. QR به لینک ثابت منو وصل است؛ با تغییر محتوا، همان QR کار می‌کند.</div>
                </details>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="wrap">
            <div class="final-cta__card" data-reveal="up">
                <div class="final-cta__copy">
                    <h2>قدم اول را بردار</h2>
                    <p>ساخت منو رایگان است و به کارت بانکی نیاز ندارد.</p>
                </div>
                <div class="final-cta__actions">
                    <a class="m3-button m3-button--light m3-button--lg" href="<?= url('register.php') ?>">شروع رایگان <?= icon('arrow', 18, 'btn-arrow') ?></a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
