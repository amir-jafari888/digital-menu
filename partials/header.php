<?php
/** Shared top app bar + mobile navigation sheet. */
$active = $page['active'] ?? '';
$items  = nav_items();
?>
<header class="app-bar" id="site-header" data-header>
    <div class="wrap app-bar__inner">
        <a class="brand" href="<?= url('index.php') ?>" aria-label="<?= e(SITE_NAME) ?>، صفحه اصلی">
            <span class="brand__mark" aria-hidden="true">
                <svg viewBox="0 0 40 40" fill="none" focusable="false">
                    <rect width="40" height="40" rx="12" fill="currentColor" />
                    <path d="M11 11v18M11 12l9 13 9-13v17" stroke="#fff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
            <span class="brand__name"><?= e(SITE_NAME) ?><span aria-hidden="true">.</span></span>
        </a>

        <nav class="primary-nav" aria-label="منوی اصلی">
            <?php foreach ($items as $item): ?>
                <a class="nav-link<?= nav_state($item['slug'], $active) ?>" href="<?= url($item['href']) ?>"<?= $active === $item['slug'] ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="app-bar__actions">
            <a class="m3-button m3-button--text m3-button--sm" href="<?= url('login.php') ?>">ورود</a>
            <a class="m3-button m3-button--filled m3-button--sm" href="<?= url('register.php') ?>">شروع رایگان</a>
        </div>

        <button class="nav-toggle m3-icon-button" type="button" aria-label="باز کردن منو" aria-expanded="false" aria-controls="mobile-nav" data-nav-toggle>
            <span class="nav-toggle__box" aria-hidden="true"><span></span><span></span><span></span></span>
        </button>
    </div>
</header>

<!-- Rendered outside <header>: backdrop-filter creates a containing block
     that would otherwise pin these fixed-position elements to the app bar. -->
<div class="sheet-scrim" data-nav-close hidden></div>

<div class="nav-sheet" id="mobile-nav" role="dialog" aria-modal="true" aria-label="منوی موبایل" aria-hidden="true" data-nav-sheet>
        <div class="nav-sheet__handle" aria-hidden="true"></div>
        <div class="nav-sheet__head">
            <span class="nav-sheet__title">منو</span>
            <button class="m3-icon-button" type="button" aria-label="بستن منو" data-nav-close>
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
            </button>
        </div>
        <nav class="nav-sheet__nav" aria-label="منوی موبایل">
            <?php foreach ($items as $item): ?>
                <a class="nav-sheet__link<?= nav_state($item['slug'], $active) ?>" href="<?= url($item['href']) ?>"<?= $active === $item['slug'] ? ' aria-current="page"' : '' ?>>
                    <span><?= e($item['label']) ?></span>
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </a>
            <?php endforeach; ?>
            <a class="nav-sheet__link" href="<?= url('how-it-works.php') ?>">
                <span>چطور کار می‌کند</span>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </a>
            <a class="nav-sheet__link" href="<?= url('contact.php') ?>">
                <span>تماس با ما</span>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </a>
        </nav>
        <div class="nav-sheet__actions">
            <a class="m3-button m3-button--outlined m3-button--block" href="<?= url('login.php') ?>">ورود</a>
            <a class="m3-button m3-button--filled m3-button--block" href="<?= url('register.php') ?>">شروع رایگان</a>
        </div>
    </div>
