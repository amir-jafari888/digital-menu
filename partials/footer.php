<?php
/** Shared footer + closing tags. */
$year = date('Y');
?>
<footer class="site-footer">
    <h2 class="visually-hidden">پیوندها و اطلاعات تماس</h2>
    <div class="wrap footer-grid">
        <div class="footer-brand">
            <a class="brand" href="<?= url('index.php') ?>" aria-label="<?= e(SITE_NAME) ?>، صفحه اصلی">
                <span class="brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 40 40" fill="none" focusable="false">
                        <rect width="40" height="40" rx="12" fill="currentColor" />
                        <path d="M11 11v18M11 12l9 13 9-13v17" stroke="#fff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span class="brand__name"><?= e(SITE_NAME) ?><span aria-hidden="true">.</span></span>
            </a>
            <p>منوی دیجیتال، باشگاه مشتریان و تبلیغات؛ در یک پلتفرم ساده برای کافه‌ها و رستوران‌ها.</p>
            <a class="app-badge" href="<?= url('advertising.php') ?>">
                <span class="app-badge__icon" aria-hidden="true">✦</span>
                <span><strong>اپ سراغ</strong><small>تبلیغات دیده‌شده</small></span>
            </a>
        </div>

        <nav class="footer-col" aria-label="محصول">
            <h3 class="footer-col__title">محصول</h3>
            <a href="<?= url('features.php') ?>">منوی دیجیتال</a>
            <a href="<?= url('loyalty.php') ?>">باشگاه مشتریان</a>
            <a href="<?= url('advertising.php') ?>">تبلیغات در اپ سراغ</a>
            <a href="<?= url('pricing.php') ?>">تعرفه‌ها</a>
        </nav>

        <nav class="footer-col" aria-label="راهنما">
            <h3 class="footer-col__title">راهنما</h3>
            <a href="<?= url('how-it-works.php') ?>">چطور کار می‌کند</a>
            <a href="<?= url('faq.php') ?>">سوالات متداول</a>
            <a href="<?= url('contact.php') ?>">تماس با ما</a>
        </nav>

        <nav class="footer-col" aria-label="حساب">
            <h3 class="footer-col__title">حساب</h3>
            <a href="<?= url('login.php') ?>">ورود</a>
            <a href="<?= url('register.php') ?>">ساخت حساب</a>
            <a href="<?= url('terms.php') ?>">قوانین استفاده</a>
            <a href="<?= url('privacy.php') ?>">حریم خصوصی</a>
        </nav>
    </div>

    <div class="wrap footer-bottom">
        <span>© <?= e((string) $year) ?> <?= e(SITE_NAME) ?>. تمامی حقوق محفوظ است.</span>
        <span class="footer-bottom__meta">ساخته‌شده برای تجربه‌ای ساده‌تر</span>
    </div>
</footer>

<button class="to-top m3-icon-button" type="button" aria-label="بازگشت به بالا" data-to-top hidden>
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" aria-hidden="true"><path d="M6 15l6-6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
</button>

<script src="<?= asset('js/main.js') ?>" defer></script>
</body>

</html>
