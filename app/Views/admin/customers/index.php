<?php
/**
 * @var array $customers
 * @var array $filters
 * @var array $pager
 * @var int   $total
 * @var array $summary
 * @var string $baseUrl
 */
?>
<section class="stat-grid">
    <article class="stat">
        <p class="stat__label">مشتریان</p>
        <p class="stat__value"><?= fa_digits((string) ($summary['customers'] ?? 0)) ?></p>
        <p class="stat__hint"><?= fa_digits((string) ($summary['visits'] ?? 0)) ?> بازدید ثبت‌شده</p>
    </article>
    <article class="stat">
        <p class="stat__label">امتیاز در گردش</p>
        <p class="stat__value"><?= money((int) ($summary['points'] ?? 0)) ?></p>
        <p class="stat__hint">قابل خرج در جوایز</p>
    </article>
    <article class="stat stat--accent">
        <p class="stat__label">مجموع خرید</p>
        <p class="stat__value"><?= money((int) ($summary['spent'] ?? 0)) ?></p>
        <p class="stat__hint">تومان</p>
    </article>
</section>

<section class="card" style="margin-top:16px">
    <form class="filters" method="get" action="<?= e($baseUrl) ?>">
        <div class="field field--search">
            <label for="q">جست‌وجو</label>
            <input class="field__control" id="q" name="q" value="<?= e((string) $filters['search']) ?>" placeholder="نام یا شماره موبایل">
        </div>
        <button class="m3-button m3-button--tonal m3-button--sm" type="submit">جست‌وجو</button>
        <a class="m3-button m3-button--text m3-button--sm" href="<?= e($baseUrl) ?>">پاک‌کردن</a>
    </form>

    <div class="card__body card__body--flush">
        <?php if ($customers === []): ?>
            <div class="empty">
                <span class="empty__mark" aria-hidden="true">👥</span>
                <h3>مشتری‌ای پیدا نشد</h3>
                <p>مشتریان با ثبت شماره موبایل در سفارش‌ها ساخته می‌شوند.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>موبایل</th>
                            <th>امتیاز</th>
                            <th>مجموع خرید</th>
                            <th>بازدید</th>
                            <th>آخرین بازدید</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td data-label="نام" class="cell-wrap">
                                    <a href="<?= e(admin_url('customers/' . (int) $customer['id'])) ?>">
                                        <?= e($customer['name'] !== '' ? (string) $customer['name'] : 'بدون نام') ?>
                                    </a>
                                </td>
                                <td data-label="موبایل" class="mono"><?= e((string) $customer['phone']) ?></td>
                                <td data-label="امتیاز"><span class="badge badge--info"><?= fa_digits((string) $customer['points_balance']) ?></span></td>
                                <td data-label="مجموع خرید"><?= money((int) $customer['total_spent']) ?></td>
                                <td data-label="بازدید"><?= fa_digits((string) $customer['visits']) ?></td>
                                <td data-label="آخرین بازدید" class="small muted"><?= e(jalali_date($customer['last_visit_at'] ?? null, true)) ?></td>
                                <td data-label="">
                                    <div class="cell-actions">
                                        <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('customers/' . (int) $customer['id'])) ?>">پرونده</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="card__foot">
        <span class="small muted"><?= fa_digits((string) $total) ?> مشتری</span>
        <span class="spacer"></span>
        <?= paginate_links((int) $pager['page'], (int) $pager['pages'], $baseUrl) ?>
    </div>
</section>
