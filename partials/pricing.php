<?php
/** Shared pricing block: heading, billing toggle, three tiers, note.
 *  Set $showPricingHead = false to reuse only the toggle + cards
 *  (used on pricing.php where the page hero already renders the heading).
 */
$showPricingHead = $showPricingHead ?? true;
?>
<div class="section-head section-head--center" data-reveal="up">
    <?php if ($showPricingHead): ?>
        <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span>تعرفه‌ی شفاف</span>
        <h2 class="section-head__title">پلنی که با کسب‌وکارت رشد می‌کند</h2>
        <p class="section-head__lead">از یک منوی ساده شروع کن و هر وقت آماده بودی، باشگاه مشتریان و تبلیغات را اضافه کن.</p>
    <?php else: ?>
        <h2 class="visually-hidden">پلن‌ها و تعرفه‌ها</h2>
    <?php endif; ?>
    <div class="pricing-toggle" role="group" aria-label="دوره‌ی پرداخت">
        <button type="button" data-billing="monthly" class="is-active" aria-pressed="true">ماهانه</button>
        <button type="button" data-billing="yearly" aria-pressed="false">سالانه <span class="pricing-toggle__save">۲ ماه هدیه</span></button>
    </div>
</div>

<div class="pricing-grid">
    <article class="price-card" data-reveal="up">
        <h3 class="price-card__name">پایه</h3>
        <p class="price-card__desc">برای شروع و ساخت منوی دیجیتال بدون هزینه.</p>
        <div class="price-card__price"><span class="price-card__amount" data-monthly="رایگان" data-yearly="رایگان">رایگان</span><span class="price-card__unit" data-monthly="برای همیشه" data-yearly="برای همیشه">برای همیشه</span></div>
        <ul class="price-card__features">
            <li><?= icon('check') ?><span>یک منوی دیجیتال</span></li>
            <li><?= icon('check') ?><span>QR اختصاصی</span></li>
            <li><?= icon('check') ?><span>ویرایش نامحدود محصول</span></li>
            <li class="is-muted"><?= icon('close') ?><span>باشگاه مشتریان</span></li>
            <li class="is-muted"><?= icon('close') ?><span>تبلیغات در اپ سراغ</span></li>
        </ul>
        <a class="m3-button m3-button--outlined m3-button--block" href="<?= url('register.php') ?>">شروع رایگان</a>
    </article>

    <article class="price-card price-card--featured" data-reveal="up">
        <span class="price-card__badge">پیشنهاد ما</span>
        <h3 class="price-card__name">حرفه‌ای</h3>
        <p class="price-card__desc">برای کافه و رستورانی که می‌خواهد مشتری را نگه دارد.</p>
        <div class="price-card__price"><span class="price-card__amount" data-monthly="۲۹۰٬۰۰۰" data-yearly="۲٬۹۰۰٬۰۰۰">۲۹۰٬۰۰۰</span><span class="price-card__unit" data-monthly="تومان / ماه" data-yearly="تومان / سال">تومان / ماه</span></div>
        <ul class="price-card__features">
            <li><?= icon('check') ?><span>همه‌ی امکانات پایه</span></li>
            <li><?= icon('check') ?><span>باشگاه مشتریان و امتیاز</span></li>
            <li><?= icon('check') ?><span>سفارش‌گیری و پنل سفارش</span></li>
            <li><?= icon('check') ?><span>گزارش فروش و مشتری</span></li>
            <li class="is-muted"><?= icon('close') ?><span>تبلیغات در اپ سراغ</span></li>
        </ul>
        <a class="m3-button m3-button--filled m3-button--block" href="<?= url('register.php') ?>">انتخاب حرفه‌ای <?= icon('arrow', 18, 'btn-arrow') ?></a>
    </article>

    <article class="price-card" data-reveal="up">
        <h3 class="price-card__name">کسب‌وکار</h3>
        <p class="price-card__desc">برای چند شعبه و رشد جدی از طریق تبلیغات.</p>
        <div class="price-card__price"><span class="price-card__amount" data-monthly="۵۹۰٬۰۰۰" data-yearly="۵٬۹۰۰٬۰۰۰">۵۹۰٬۰۰۰</span><span class="price-card__unit" data-monthly="تومان / ماه" data-yearly="تومان / سال">تومان / ماه</span></div>
        <ul class="price-card__features">
            <li><?= icon('check') ?><span>همه‌ی امکانات حرفه‌ای</span></li>
            <li><?= icon('check') ?><span>تبلیغات در اپ سراغ</span></li>
            <li><?= icon('check') ?><span>چند شعبه و چند منو</span></li>
            <li><?= icon('check') ?><span>گزارش پیشرفته و خروجی</span></li>
            <li><?= icon('check') ?><span>پشتیبانی اولویت‌دار</span></li>
        </ul>
        <a class="m3-button m3-button--outlined m3-button--block" href="<?= url('contact.php') ?>">گفت‌وگو با فروش</a>
    </article>
</div>
<p class="pricing-note">همه‌ی پلن‌ها بدون قرارداد بلندمدت هستند. پرداخت آنلاین و فاکتور رسمی به‌زودی اضافه می‌شود.</p>
