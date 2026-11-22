<?php
/** Shown when a menu slug/QR is unknown or unpublished. */
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7faf3">
    <meta name="robots" content="noindex">
    <title><?= e($title ?? ('منو پیدا نشد | ' . SITE_NAME)) ?></title>
    <link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/system.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/menu.css')) ?>">
</head>

<body class="menu-page">
    <main class="menu-missing">
        <div class="menu-missing__card">
            <span class="menu-missing__mark" aria-hidden="true">☕</span>
            <h1>این منو در دسترس نیست</h1>
            <p>ممکن است لینک اشتباه باشد یا منوی کسب‌وکار هنوز منتشر نشده باشد.</p>
            <a class="m3-button m3-button--filled" href="<?= e(url('')) ?>">بازگشت به <?= e(SITE_NAME) ?></a>
        </div>
    </main>
</body>

</html>
