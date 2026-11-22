<?php
/**
 * Shared <head> + opening <body>.
 *
 * Expects $page:
 *   title, description, active, canonical (filename), og_image (optional)
 */
$page        = $page ?? [];
$active      = $page['active'] ?? '';
$title       = $page['title'] ?? (SITE_NAME . ' | ' . SITE_TAGLINE);
$description = $page['description'] ?? SITE_DESCRIPTION;
$canonical   = $page['canonical'] ?? basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$ogImage     = $page['og_image'] ?? asset('images/og-cover.png');
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f7faf3">
    <meta name="color-scheme" content="light">

    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <link rel="canonical" href="<?= e(canonical_url($canonical)) ?>">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:url" content="<?= e(canonical_url($canonical)) ?>">
    <meta property="og:image" content="<?= e(canonical_url('assets/images/og-cover.png')) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($title) ?>">
    <meta name="twitter:description" content="<?= e($description) ?>">
    <meta name="twitter:image" content="<?= e(canonical_url('assets/images/og-cover.png')) ?>">

    <link rel="icon" href="<?= asset('images/favicon.svg') ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= asset('images/favicon.svg') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/system.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">

    <!-- Entrance motion is opt-in: the page stands complete without this script. -->
    <script src="<?= asset('js/boot.js') ?>" defer></script>
</head>

<body class="page-<?= e($active ?: 'home') ?>">
    <a class="skip-link" href="#main-content">رفتن به محتوای اصلی</a>
