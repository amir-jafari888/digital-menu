<?php
/**
 * @var array $tenants
 * @var array $plans
 * @var int   $acting
 */

$categories = [
    'cafe'       => 'کافه',
    'restaurant' => 'رستوران',
    'bakery'     => 'نانوایی',
    'dessert'    => 'دسر و شیرینی',
    'other'      => 'سایر',
];
$statuses = [
    'pending'   => 'در انتظار تأیید',
    'active'    => 'فعال',
    'suspended' => 'معلق',
];

$activeCount = 0;
$usersTotal  = 0;
$ordersTotal = 0;
$actingTenant = null;

foreach ($tenants as $tenant) {
    if ((string) $tenant['status'] === 'active') {
        $activeCount++;
    }
    $usersTotal  += (int) ($tenant['users_count'] ?? 0);
    $ordersTotal += (int) ($tenant['orders_count'] ?? 0);

    if ($acting > 0 && (int) $tenant['id'] === $acting) {
        $actingTenant = $tenant;
    }
}
?>
<?php if ($actingTenant !== null): ?>
    <section class="card" style="border-color:var(--md-sys-color-primary)">
        <div class="card__body">
            <div class="row" style="justify-content:space-between;flex-wrap:wrap;gap:12px">
                <p style="margin:0">
                    اکنون به‌عنوان مدیر پلتفرم داخل پنل
                    <strong><?= e((string) $actingTenant['name']) ?></strong>
                    هستی. همهٔ عملیات به همین کسب‌وکار نسبت داده می‌شود.
                </p>
                <form method="post" action="<?= e(admin_url('tenants/exit')) ?>">
                    <?= csrf_field() ?>
                    <button class="m3-button m3-button--tonal m3-button--sm" type="submit">خروج از پنل کسب‌وکار</button>
                </form>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="stat-grid">
    <article class="stat stat--accent">
        <p class="stat__label">کسب‌وکارها</p>
        <p class="stat__value"><?= fa_digits((string) count($tenants)) ?></p>
        <p class="stat__hint"><?= fa_digits((string) $activeCount) ?> فعال</p>
    </article>
    <article class="stat">
        <p class="stat__label">کاربران</p>
        <p class="stat__value"><?= fa_digits((string) $usersTotal) ?></p>
        <p class="stat__hint">در همهٔ کسب‌وکارها</p>
    </article>
    <article class="stat">
        <p class="stat__label">سفارش‌ها</p>
        <p class="stat__value"><?= fa_digits((string) $ordersTotal) ?></p>
        <p class="stat__hint">مجموع ثبت‌شده</p>
    </article>
</section>

<section class="card" style="margin-top:16px">
    <div class="card__head">
        <div>
            <h2>کسب‌وکار جدید</h2>
            <p>پس از ساخت، می‌توانی با «ورود به پنل» منو و محصولات آن را مدیریت کنی.</p>
        </div>
    </div>
    <form method="post" action="<?= e(admin_url('tenants')) ?>">
        <?= csrf_field() ?>
        <div class="card__body">
            <div class="form-grid">
                <div class="field">
                    <label for="tenant-name">نام کسب‌وکار</label>
                    <input class="field__control" id="tenant-name" name="name" required maxlength="120">
                </div>
                <div class="field">
                    <label for="tenant-slug">نشانی منو (اختیاری)</label>
                    <input class="field__control" id="tenant-slug" name="slug" dir="ltr" maxlength="60" placeholder="my-cafe">
                    <span class="field__hint">خالی بگذاری، از نام ساخته می‌شود.</span>
                </div>
                <div class="field">
                    <label for="tenant-category">دسته</label>
                    <select class="field__control" id="tenant-category" name="category" required>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= e($key) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="tenant-plan">پلن</label>
                    <select class="field__control" id="tenant-plan" name="plan_id" required>
                        <?php foreach ($plans as $plan): ?>
                            <option value="<?= (int) $plan['id'] ?>">
                                <?= e((string) $plan['name']) ?> —
                                <?= money((int) $plan['monthly_price']) ?> تومان / ماه
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="tenant-phone">تلفن</label>
                    <input class="field__control" id="tenant-phone" name="phone" dir="ltr" maxlength="20">
                </div>
                <div class="field field--wide">
                    <label for="tenant-address">آدرس</label>
                    <input class="field__control" id="tenant-address" name="address" maxlength="255">
                </div>
                <div class="field field--wide">
                    <label for="tenant-description">توضیح</label>
                    <textarea class="field__control" id="tenant-description" name="description" rows="2" maxlength="500"></textarea>
                </div>
            </div>

            <h3 class="small" style="margin:18px 0 10px">حساب مالک (اختیاری)</h3>
            <div class="form-grid">
                <div class="field">
                    <label for="owner-name">نام مالک</label>
                    <input class="field__control" id="owner-name" name="owner_name" maxlength="120">
                </div>
                <div class="field">
                    <label for="owner-email">ایمیل مالک</label>
                    <input class="field__control" id="owner-email" name="owner_email" type="email" dir="ltr" maxlength="190">
                </div>
                <div class="field">
                    <label for="owner-password">رمز موقت مالک</label>
                    <input class="field__control" id="owner-password" name="password" type="text" autocomplete="off" minlength="8" maxlength="100">
                    <span class="field__hint">حداقل ۸ کاراکتر، شامل حرف و رقم. در اولین ورود باید تغییر کند.</span>
                </div>
            </div>
        </div>
        <div class="card__foot">
            <button class="m3-button m3-button--filled" type="submit">ساخت کسب‌وکار</button>
        </div>
    </form>
</section>

<section class="card" style="margin-top:16px">
    <div class="card__head">
        <div>
            <h2>همهٔ کسب‌وکارها</h2>
            <p>وضعیت، پلن و مالک هر کسب‌وکار را از همین‌جا مدیریت کن.</p>
        </div>
        <span class="small muted"><?= fa_digits((string) count($tenants)) ?> کسب‌وکار</span>
    </div>
    <div class="card__body card__body--flush">
        <?php if ($tenants === []): ?>
            <div class="empty">
                <span class="empty__mark" aria-hidden="true">🏪</span>
                <h3>کسب‌وکاری ثبت نشده</h3>
                <p>از فرم بالا اولین کسب‌وکار را بساز.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>کسب‌وکار</th>
                            <th>پلن</th>
                            <th>کاربران</th>
                            <th>سفارش‌ها</th>
                            <th>وضعیت</th>
                            <th>پایان دورهٔ آزمایشی</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tenants as $tenant): ?>
                            <?php $isActing = $acting > 0 && (int) $tenant['id'] === $acting; ?>
                            <tr>
                                <td data-label="کسب‌وکار" class="cell-wrap">
                                    <strong><?= e((string) $tenant['name']) ?></strong>
                                    <?php if ($isActing): ?>
                                        <span class="badge badge--info">در حال مدیریت</span>
                                    <?php endif; ?>
                                    <br>
                                    <span class="mono small"><?= e((string) $tenant['slug']) ?></span>
                                </td>
                                <td data-label="پلن"><?= e((string) ($tenant['plan_name'] ?? '—')) ?></td>
                                <td data-label="کاربران"><?= fa_digits((string) ($tenant['users_count'] ?? 0)) ?></td>
                                <td data-label="سفارش‌ها"><?= fa_digits((string) ($tenant['orders_count'] ?? 0)) ?></td>
                                <td data-label="وضعیت"><?= status_badge((string) $tenant['status']) ?></td>
                                <td data-label="پایان دورهٔ آزمایشی" class="small muted">
                                    <?= e(jalali_date($tenant['trial_ends_at'] ?? null)) ?>
                                </td>
                                <td data-label="">
                                    <div class="cell-actions">
                                        <a class="m3-button m3-button--text m3-button--sm"
                                           href="<?= e(url('menu.php?slug=' . rawurlencode((string) $tenant['slug']))) ?>"
                                           target="_blank" rel="noopener">منو</a>
                                        <form method="post" action="<?= e(admin_url('tenants/' . (int) $tenant['id'] . '/act')) ?>">
                                            <?= csrf_field() ?>
                                            <button class="m3-button m3-button--text m3-button--sm" type="submit">
                                                <?= $isActing ? 'بازگشت به پنل' : 'ورود به پنل' ?>
                                            </button>
                                        </form>
                                        <details>
                                            <summary class="small muted">مدیریت</summary>
                                            <form method="post" action="<?= e(admin_url('tenants/' . (int) $tenant['id'])) ?>" style="margin-top:10px">
                                                <?= csrf_field() ?>
                                                <div class="stack" style="gap:8px;min-width:240px">
                                                    <input class="field__control" name="name" value="<?= e((string) $tenant['name']) ?>" maxlength="120" required aria-label="نام">
                                                    <select class="field__control" name="category" aria-label="دسته">
                                                        <?php foreach ($categories as $key => $label): ?>
                                                            <option value="<?= e($key) ?>"<?= is_selected($key, (string) $tenant['category']) ?>><?= e($label) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <select class="field__control" name="plan_id" aria-label="پلن">
                                                        <?php foreach ($plans as $plan): ?>
                                                            <option value="<?= (int) $plan['id'] ?>"<?= is_selected((string) $plan['id'], (string) ($tenant['plan_id'] ?? '')) ?>><?= e((string) $plan['name']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <select class="field__control" name="status" aria-label="وضعیت">
                                                        <?php foreach ($statuses as $key => $label): ?>
                                                            <option value="<?= e($key) ?>"<?= is_selected($key, (string) $tenant['status']) ?>><?= e($label) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <input class="field__control" name="phone" value="<?= e((string) ($tenant['phone'] ?? '')) ?>" maxlength="20" dir="ltr" placeholder="تلفن" aria-label="تلفن">
                                                    <input class="field__control" name="address" value="<?= e((string) ($tenant['address'] ?? '')) ?>" maxlength="255" placeholder="آدرس" aria-label="آدرس">
                                                    <button class="m3-button m3-button--filled m3-button--sm" type="submit">ذخیره</button>
                                                </div>
                                            </form>
                                        </details>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
