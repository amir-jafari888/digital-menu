<?php
/**
 * @var array      $tenant
 * @var array      $settings
 * @var array|null $subscription
 * @var array      $payments
 * @var array      $user
 * @var array      $plans
 */
use App\Core\Auth;
use App\Models\Product;

$canManage = Auth::can('settings.manage') && !empty($tenant['id']);
$s = static fn (string $key, string $default = ''): string => (string) ($settings[$key] ?? $default);
$t = static fn (string $key, string $default = ''): string => (string) ($tenant[$key] ?? $default);
$logo = !empty($tenant['logo']) ? Product::imageUrl((string) $tenant['logo']) : null;
$categories = [
    'cafe'       => 'کافه',
    'restaurant' => 'رستوران',
    'bakery'     => 'نانوایی',
    'dessert'    => 'دسر و شیرینی',
    'other'      => 'سایر',
];
$statusClass = (int) ($user['must_change_password'] ?? 0) === 1 ? 'badge--warn' : 'badge--ok';
?>
<div class="seg-tabs">
    <a href="#business">کسب‌وکار</a>
    <a href="#site">تنظیمات عمومی</a>
    <a href="#subscription">اشتراک</a>
    <a href="#profile">پروفایل</a>
    <a href="#password">رمز عبور</a>
</div>

<div class="stack">
    <?php if ($canManage): ?>
        <section class="card" id="business">
            <div class="card__head">
                <div>
                    <h2>اطلاعات کسب‌وکار</h2>
                    <p>این اطلاعات در منوی عمومی و صفحهٔ QR نمایش داده می‌شود.</p>
                </div>
            </div>
            <form method="post" action="<?= e(admin_url('settings/business')) ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="card__body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="biz-name">نام کسب‌وکار</label>
                            <input class="field__control" id="biz-name" name="name" required maxlength="120" value="<?= e($t('name')) ?>">
                        </div>
                        <div class="field">
                            <label for="biz-category">دسته</label>
                            <select class="field__control" id="biz-category" name="category" required>
                                <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?= e($key) ?>"<?= is_selected($key, $t('category')) ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="biz-slug">نشانی منو</label>
                            <input class="field__control" id="biz-slug" dir="ltr" value="<?= e($t('slug')) ?>" readonly>
                            <span class="field__hint"><?= e(url('menu.php?slug=' . rawurlencode($t('slug')))) ?></span>
                        </div>
                        <div class="field">
                            <label for="biz-phone">تلفن</label>
                            <input class="field__control" id="biz-phone" name="phone" maxlength="20" dir="ltr" value="<?= e($t('phone')) ?>">
                        </div>
                        <div class="field field--wide">
                            <label for="biz-address">آدرس</label>
                            <input class="field__control" id="biz-address" name="address" maxlength="255" value="<?= e($t('address')) ?>">
                        </div>
                        <div class="field field--wide">
                            <label for="biz-description">دربارهٔ کسب‌وکار</label>
                            <textarea class="field__control" id="biz-description" name="description" rows="3" maxlength="500"><?= e($t('description')) ?></textarea>
                        </div>
                        <div class="field field--wide">
                            <label for="biz-logo">لوگو</label>
                            <input class="field__control" id="biz-logo" name="logo" type="file"
                                   accept="image/jpeg,image/png,image/webp" data-file-label="logo-name">
                            <p class="field__hint" id="logo-name">JPG، PNG یا WebP — حداکثر ۲ مگابایت.</p>
                            <?php if ($logo !== null): ?>
                                <div class="row" style="margin-top:8px">
                                    <img class="thumb" src="<?= e($logo) ?>" alt="لوگوی فعلی" width="56" height="56">
                                    <label class="switch-row">
                                        <input type="checkbox" name="remove_logo" value="1">
                                        حذف لوگوی فعلی
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card__foot">
                    <button class="m3-button m3-button--filled m3-button--sm" type="submit">ذخیرهٔ اطلاعات</button>
                </div>
            </form>
        </section>

        <section class="card" id="site">
            <div class="card__head">
                <div>
                    <h2>تنظیمات عمومی</h2>
                    <p>قواعد امتیازدهی و محدودیت سفارش برای همهٔ منوها.</p>
                </div>
            </div>
            <form method="post" action="<?= e(admin_url('settings/site')) ?>">
                <?= csrf_field() ?>
                <div class="card__body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="site_name">نام سایت</label>
                            <input class="field__control" id="site_name" name="site_name" required maxlength="80" value="<?= e($s('site_name', SITE_NAME)) ?>">
                        </div>
                        <div class="field">
                            <label for="support_email">ایمیل پشتیبانی</label>
                            <input class="field__control" id="support_email" name="support_email" type="email" dir="ltr" required maxlength="190" value="<?= e($s('support_email', 'hello@monochi.ir')) ?>">
                        </div>
                        <div class="field">
                            <label for="support_phone">تلفن پشتیبانی</label>
                            <input class="field__control" id="support_phone" name="support_phone" maxlength="20" dir="ltr" value="<?= e($s('support_phone')) ?>">
                        </div>
                        <div class="field">
                            <label for="loyalty_points_per_10000">امتیاز به ازای هر ۱۰٬۰۰۰ تومان</label>
                            <input class="field__control" id="loyalty_points_per_10000" name="loyalty_points_per_10000" inputmode="numeric" required value="<?= e($s('loyalty_points_per_10000', '1')) ?>">
                        </div>
                        <div class="field">
                            <label for="max_open_orders_per_table">حداکثر سفارش باز هر میز</label>
                            <input class="field__control" id="max_open_orders_per_table" name="max_open_orders_per_table" inputmode="numeric" required value="<?= e($s('max_open_orders_per_table', '5')) ?>">
                        </div>
                        <div class="field field--wide">
                            <label class="switch-row">
                                <input type="checkbox" name="ads_enabled" value="1"<?= is_checked($s('ads_enabled', '1') === '1') ?>>
                                نمایش تبلیغات در اپ
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card__foot">
                    <button class="m3-button m3-button--filled m3-button--sm" type="submit">ذخیرهٔ تنظیمات</button>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <section class="card" id="subscription">
        <div class="card__head">
            <h2>اشتراک و پرداخت‌ها</h2>
            <?= $subscription !== null ? status_badge((string) $subscription['status']) : '<span class="badge badge--muted">بدون اشتراک</span>' ?>
        </div>
        <div class="card__body">
            <?php if ($subscription !== null): ?>
                <div class="kv" style="padding:0">
                    <div>
                        <p class="kv__label">پلن</p>
                        <p class="kv__value"><?= e((string) $subscription['plan_name']) ?></p>
                    </div>
                    <div>
                        <p class="kv__label">شروع</p>
                        <p class="kv__value small"><?= e(jalali_date((string) $subscription['starts_at'])) ?></p>
                    </div>
                    <div>
                        <p class="kv__label">پایان</p>
                        <p class="kv__value small"><?= e(jalali_date((string) $subscription['ends_at'])) ?></p>
                    </div>
                    <div>
                        <p class="kv__label">مبلغ دوره</p>
                        <p class="kv__value"><?= money((int) $subscription['price']) ?> تومان</p>
                    </div>
                </div>
            <?php else: ?>
                <p class="muted small" style="margin:0">اشتراکی ثبت نشده است. برای فعال‌سازی با پشتیبانی تماس بگیر.</p>
            <?php endif; ?>
        </div>

        <?php if ($payments !== []): ?>
            <div class="card__body card__body--flush" style="border-top:1px solid var(--md-sys-color-outline-variant)">
                <div class="table-wrap">
                    <table class="table table--stack">
                        <thead>
                            <tr><th>تاریخ</th><th>مبلغ</th><th>روش</th><th>وضعیت</th><th>مرجع</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td data-label="تاریخ" class="small muted"><?= e(jalali_date((string) ($payment['paid_at'] ?? $payment['created_at']), true)) ?></td>
                                    <td data-label="مبلغ"><?= money((int) $payment['amount']) ?></td>
                                    <td data-label="روش"><?= $payment['gateway'] === 'manual' ? 'دستی' : e((string) $payment['gateway']) ?></td>
                                    <td data-label="وضعیت"><?= status_badge((string) $payment['status']) ?></td>
                                    <td data-label="مرجع" class="mono small"><?= e((string) ($payment['reference'] ?? '—')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section class="card" id="profile">
        <div class="card__head">
            <div>
                <h2>پروفایل من</h2>
                <p>ایمیل یا شماره موبایل برای ورود به پنل استفاده می‌شود.</p>
            </div>
            <span class="badge <?= e($statusClass) ?>"><?= (int) ($user['must_change_password'] ?? 0) === 1 ? 'تغییر رمز لازم است' : 'فعال' ?></span>
        </div>
        <form method="post" action="<?= e(admin_url('settings/profile')) ?>">
            <?= csrf_field() ?>
            <div class="card__body">
                <div class="form-grid">
                    <div class="field">
                        <label for="profile-name">نام</label>
                        <input class="field__control" id="profile-name" name="name" required maxlength="120" value="<?= e((string) $user['name']) ?>">
                    </div>
                    <div class="field">
                        <label for="profile-email">ایمیل</label>
                        <input class="field__control" id="profile-email" name="email" type="email" dir="ltr" maxlength="190" value="<?= e((string) ($user['email'] ?? '')) ?>">
                    </div>
                    <div class="field">
                        <label for="profile-phone">شماره موبایل</label>
                        <input class="field__control" id="profile-phone" name="phone" dir="ltr" maxlength="20" value="<?= e((string) ($user['phone'] ?? '')) ?>">
                    </div>
                </div>
            </div>
            <div class="card__foot">
                <button class="m3-button m3-button--filled m3-button--sm" type="submit">ذخیرهٔ پروفایل</button>
            </div>
        </form>
    </section>

    <section class="card" id="password">
        <div class="card__head">
            <div>
                <h2>تغییر رمز عبور</h2>
                <p>حداقل ۸ کاراکتر، شامل حرف و رقم.</p>
            </div>
        </div>
        <form method="post" action="<?= e(admin_url('settings/password')) ?>">
            <?= csrf_field() ?>
            <div class="card__body">
                <div class="form-grid">
                    <div class="field">
                        <label for="current_password">رمز فعلی</label>
                        <input class="field__control" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                    </div>
                    <div class="field">
                        <label for="new_password">رمز جدید</label>
                        <input class="field__control" id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="8" maxlength="100" required>
                    </div>
                    <div class="field">
                        <label for="confirm_password">تکرار رمز جدید</label>
                        <input class="field__control" id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" minlength="8" maxlength="100" required>
                    </div>
                </div>
            </div>
            <div class="card__foot">
                <button class="m3-button m3-button--filled m3-button--sm" type="submit">تغییر رمز</button>
            </div>
        </form>
    </section>
</div>
