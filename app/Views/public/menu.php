<?php
/**
 * Public digital menu (view).
 *
 * @var array  $menu
 * @var array  $sections
 * @var array|null $table
 * @var string $csrf
 * @var string $api
 * @var string $base
 * @var string|null $logo
 * @var int    $totalProducts
 * @var string $title
 */
$tenantName = (string) $menu['tenant_name'];
$menuName   = (string) $menu['name'];
$address    = (string) ($menu['tenant_address'] ?? '');
$phone      = (string) ($menu['tenant_phone'] ?? '');
$initial    = mb_substr($tenantName, 0, 1);
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f7faf3">
    <meta name="color-scheme" content="light">
    <meta name="robots" content="noindex">

    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e(mb_substr((string) ($menu['tenant_description'] ?? ''), 0, 155)) ?>">

    <link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/system.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/menu.css')) ?>">
</head>

<body class="menu-page">
    <a class="skip-link" href="#menu-main">رفتن به منو</a>

    <header class="menu-topbar">
        <a class="menu-brand" href="<?= e($base) ?>">
            <?php if ($logo !== null): ?>
                <img class="menu-brand__logo" src="<?= e($logo) ?>" alt="" width="44" height="44" loading="lazy">
            <?php else: ?>
                <span class="menu-brand__logo menu-brand__logo--initial" aria-hidden="true"><?= e($initial) ?></span>
            <?php endif; ?>
            <span class="menu-brand__text">
                <span class="menu-brand__name"><?= e($tenantName) ?></span>
                <?php if ($address !== ''): ?>
                    <span class="menu-brand__meta"><?= e($address) ?></span>
                <?php endif; ?>
            </span>
        </a>

        <?php if ($table !== null): ?>
            <span class="menu-table-chip">
                <span aria-hidden="true">◉</span>
                <?= e((string) $table['label']) ?>
            </span>
        <?php endif; ?>
    </header>

    <main id="menu-main" class="menu-shell"
          data-api="<?= e($api) ?>"
          data-csrf="<?= e($csrf) ?>"
          data-tenant="<?= e((string) $menu['tenant_slug']) ?>"
          data-menu-token="<?= e((string) $menu['qr_token']) ?>"
          data-table-token="<?= e($table === null ? '' : (string) $table['qr_token']) ?>"
          data-table-label="<?= e($table === null ? '' : (string) $table['label']) ?>">

        <section class="menu-hero">
            <p class="menu-hero__eyebrow"><?= e($menuName) ?></p>
            <h1 class="menu-hero__title"><?= e($tenantName) ?></h1>
            <?php if (($menu['tenant_description'] ?? '') !== ''): ?>
                <p class="menu-hero__desc"><?= e((string) $menu['tenant_description']) ?></p>
            <?php endif; ?>

            <div class="menu-hero__chips">
                <span class="chip chip--soft"><?= fa_digits((string) $totalProducts) ?> آیتم</span>
                <?php if ($phone !== ''): ?>
                    <a class="chip" href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone) ?? '') ?>"><?= fa_digits($phone) ?></a>
                <?php endif; ?>
                <span class="chip chip--soft">سفارش آنلاین</span>
            </div>
        </section>

        <?php if ($sections === []): ?>
            <p class="menu-empty">هنوز آیتمی برای این منو ثبت نشده است.</p>
        <?php else: ?>
            <nav class="menu-tabs" aria-label="دسته‌بندی منو">
                <?php foreach ($sections as $index => $section): ?>
                    <a class="menu-tab<?= $index === 0 ? ' is-active' : '' ?>"
                       href="#cat-<?= (int) $section['id'] ?>"
                       data-tab="<?= (int) $section['id'] ?>"><?= e((string) $section['name']) ?></a>
                <?php endforeach; ?>
            </nav>

            <?php foreach ($sections as $section): ?>
                <section class="menu-section" id="cat-<?= (int) $section['id'] ?>" data-section="<?= (int) $section['id'] ?>">
                    <header class="menu-section__head">
                        <h2><?= e((string) $section['name']) ?></h2>
                        <?php if (($section['description'] ?? '') !== ''): ?>
                            <p><?= e((string) $section['description']) ?></p>
                        <?php endif; ?>
                    </header>

                    <div class="menu-grid">
                        <?php foreach ($section['products'] as $product): ?>
                            <article class="product-card"
                                     data-id="<?= (int) $product['id'] ?>"
                                     data-name="<?= e($product['name']) ?>"
                                     data-price="<?= (int) $product['price'] ?>"
                                     data-image="<?= e((string) ($product['image'] ?? '')) ?>">
                                <div class="product-card__media">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?= e($product['image']) ?>" alt="" width="120" height="120" loading="lazy" decoding="async">
                                    <?php else: ?>
                                        <span aria-hidden="true"><?= e(mb_substr($product['name'], 0, 1)) ?></span>
                                    <?php endif; ?>
                                    <?php if ($product['featured']): ?>
                                        <span class="product-card__badge">پیشنهاد ما</span>
                                    <?php endif; ?>
                                </div>

                                <div class="product-card__body">
                                    <h3 class="product-card__name"><?= e($product['name']) ?></h3>
                                    <?php if ($product['description'] !== ''): ?>
                                        <p class="product-card__desc"><?= e($product['description']) ?></p>
                                    <?php endif; ?>

                                    <?php if ($product['calories'] !== null || $product['allergens'] !== ''): ?>
                                        <p class="product-card__meta">
                                            <?php if ($product['calories'] !== null): ?>
                                                <span><?= fa_digits((string) $product['calories']) ?> کالری</span>
                                            <?php endif; ?>
                                            <?php if ($product['allergens'] !== ''): ?>
                                                <span>حاوی <?= e($product['allergens']) ?></span>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="product-card__foot">
                                    <span class="product-card__price">
                                        <?php if ($product['old_price'] !== null && $product['old_price'] > $product['price']): ?>
                                            <s><?= money($product['old_price']) ?></s>
                                        <?php endif; ?>
                                        <strong><?= money($product['price']) ?> <small>تومان</small></strong>
                                    </span>
                                    <button class="m3-icon-button product-add" type="button"
                                            data-add="<?= (int) $product['id'] ?>"
                                            aria-label="افزودن <?= e($product['name']) ?> به سبد">
                                        <span aria-hidden="true">+</span>
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>

        <footer class="menu-footer">
            <p>ساخته‌شده با <strong><?= e(SITE_NAME) ?></strong> — منوی دیجیتال، باشگاه مشتریان و تبلیغات.</p>
            <p class="menu-footer__note">برای ثبت سفارش، آیتم‌ها را به سبد اضافه کن. پرداخت در محل انجام می‌شود.</p>
        </footer>
    </main>

    <div class="cart-bar" id="cart-bar" hidden>
        <button class="cart-bar__button" id="cart-open" type="button">
            <span class="cart-bar__count" id="cart-count">۰</span>
            <span class="cart-bar__label">مشاهدهٔ سبد</span>
            <span class="cart-bar__total" id="cart-total">۰ تومان</span>
        </button>
    </div>

    <dialog class="sheet" id="cart-dialog" aria-labelledby="cart-title">
        <form method="dialog" class="sheet__head">
            <h2 id="cart-title">سبد سفارش</h2>
            <button class="m3-icon-button" value="close" aria-label="بستن">×</button>
        </form>

        <div class="sheet__body">
            <ul class="cart-list" id="cart-list"></ul>
            <p class="cart-empty" id="cart-empty" hidden>سبد خالی است. از منو آیتم اضافه کن.</p>

            <div class="field">
                <label for="cart-note">توضیح سفارش (اختیاری)</label>
                <textarea class="field__control" id="cart-note" rows="2" maxlength="500" placeholder="مثلاً بدون شکر، شیر بادام…"></textarea>
            </div>

            <?php if ($table === null): ?>
                <fieldset class="field">
                    <legend>نوع سفارش</legend>
                    <div class="segmented" role="radiogroup" aria-label="نوع سفارش">
                        <label><input type="radio" name="order-type" value="takeaway" checked> بیرون‌بر</label>
                        <label><input type="radio" name="order-type" value="dine_in"> حضوری</label>
                        <label><input type="radio" name="order-type" value="delivery"> ارسال</label>
                    </div>
                </fieldset>
            <?php endif; ?>

            <div class="cart-contact">
                <div class="field">
                    <label for="cart-phone">شماره موبایل (برای امتیاز باشگاه مشتریان)</label>
                    <input class="field__control" id="cart-phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="۰۹۱۲۳۴۵۶۷۸۹" maxlength="20">
                </div>
                <div class="field">
                    <label for="cart-name">نام (اختیاری)</label>
                    <input class="field__control" id="cart-name" type="text" autocomplete="name" maxlength="120" placeholder="نام تو">
                </div>
            </div>

            <p class="form-error" id="cart-error" role="alert" hidden></p>
        </div>

        <form method="dialog" class="sheet__foot">
            <div class="cart-summary">
                <span>مبلغ کل</span>
                <strong id="cart-sum">۰ تومان</strong>
            </div>
            <button class="m3-button m3-button--filled m3-button--block" id="cart-submit" type="button">ثبت سفارش</button>
            <p class="cart-hint">
                <?php if ($table !== null): ?>
                    سفارش برای <?= e((string) $table['label']) ?> ثبت می‌شود.
                <?php else: ?>
                    پرداخت هنگام تحویل انجام می‌شود.
                <?php endif; ?>
            </p>
        </form>
    </dialog>

    <dialog class="sheet sheet--done" id="done-dialog" aria-labelledby="done-title">
        <div class="done">
            <span class="done__icon" aria-hidden="true">✓</span>
            <h2 id="done-title">سفارش ثبت شد</h2>
            <p class="done__text">کد سفارش تو:</p>
            <p class="done__code" id="done-code">—</p>
            <p class="done__hint">کد را برای پیگیری به همکاران ما نشان بده.</p>
            <form method="dialog">
                <button class="m3-button m3-button--filled m3-button--block">باشه، ممنون</button>
            </form>
        </div>
    </dialog>

    <template id="cart-row-template">
        <li class="cart-row">
            <div class="cart-row__main">
                <span class="cart-row__name"></span>
                <span class="cart-row__price"></span>
            </div>
            <div class="qty">
                <button type="button" data-step="-1" aria-label="کم کردن">−</button>
                <span class="qty__value" data-qty>۰</span>
                <button type="button" data-step="1" aria-label="زیاد کردن">+</button>
            </div>
            <button type="button" class="cart-row__remove" aria-label="حذف">حذف</button>
        </li>
    </template>

    <div class="toast" id="toast" role="status" aria-live="polite" hidden></div>

    <script src="<?= e(asset('js/menu.js')) ?>" defer></script>
</body>

</html>
