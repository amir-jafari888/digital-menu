<?php
/**
 * @var array $tenant
 * @var array $stats
 * @var array $counts
 * @var array $revenue
 * @var int   $maxRevenue
 * @var array $totals
 * @var array $recent
 * @var array $topCustomers
 * @var array|null $subscription
 * @var int   $activeRewards
 * @var int   $openOrders
 */
$chartMax = max(1, (int) $maxRevenue);
?>
<section class="stat-grid">
    <article class="stat stat--accent">
        <p class="stat__label">فروش امروز</p>
        <p class="stat__value"><?= money((int) $stats['revenue']) ?></p>
        <p class="stat__hint">تومان · <?= fa_digits((string) $stats['paid']) ?> سفارش مؤثر</p>
    </article>
    <article class="stat">
        <p class="stat__label">سفارش‌های امروز</p>
        <p class="stat__value"><?= fa_digits((string) $stats['orders']) ?></p>
        <p class="stat__hint"><?= fa_digits((string) $openOrders) ?> سفارش باز در جریان</p>
    </article>
    <article class="stat">
        <p class="stat__label">مشتریان باشگاه</p>
        <p class="stat__value"><?= fa_digits((string) ($totals['customers'] ?? 0)) ?></p>
        <p class="stat__hint"><?= money((int) ($totals['points'] ?? 0)) ?> امتیاز در گردش</p>
    </article>
    <article class="stat">
        <p class="stat__label">محصولات فعال</p>
        <p class="stat__value"><?= fa_digits((string) ($totals['products'] ?? 0)) ?></p>
        <p class="stat__hint"><?= fa_digits((string) $activeRewards) ?> جایزهٔ فعال</p>
    </article>
</section>

<div class="grid-2" style="margin-top:16px">
    <section class="card">
        <div class="card__head">
            <div>
                <h2>فروش هفت روز گذشته</h2>
                <p>مجموع سفارش‌های غیرلغوشده</p>
            </div>
            <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('orders')) ?>">همهٔ سفارش‌ها</a>
        </div>
        <div class="card__body">
            <div class="chart" role="img" aria-label="نمودار فروش هفت روز گذشته">
                <?php foreach ($revenue as $day): ?>
                    <?php $height = max(4, (int) round(((int) $day['revenue'] / $chartMax) * 100)); ?>
                    <div class="chart__col" title="<?= e(jalali_date($day['day'])) ?> — <?= money((int) $day['revenue']) ?>">
                        <div class="chart__bar" style="height: <?= $height ?>%"></div>
                        <span class="chart__label"><?= e(mb_substr(jalali_date($day['day']), 5)) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="stack">
        <section class="card">
            <div class="card__head">
                <h2>وضعیت اشتراک</h2>
                <?= $subscription !== null ? status_badge((string) $subscription['status']) : '' ?>
            </div>
            <div class="card__body">
                <?php if ($subscription !== null): ?>
                    <p class="muted small" style="margin:0 0 8px">پلن <strong><?= e((string) $subscription['plan_name']) ?></strong></p>
                    <div class="kv" style="padding:0">
                        <div>
                            <p class="kv__label">شروع</p>
                            <p class="kv__value"><?= e(jalali_date((string) $subscription['starts_at'])) ?></p>
                        </div>
                        <div>
                            <p class="kv__label">پایان</p>
                            <p class="kv__value"><?= e(jalali_date((string) $subscription['ends_at'])) ?></p>
                        </div>
                        <div>
                            <p class="kv__label">مبلغ</p>
                            <p class="kv__value"><?= money((int) $subscription['price']) ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="muted small" style="margin:0">اشتراک فعالی ثبت نشده است.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="card">
            <div class="card__head">
                <h2>دسترسی سریع</h2>
            </div>
            <div class="card__body row">
                <a class="m3-button m3-button--tonal m3-button--sm" href="<?= e(admin_url('products/new')) ?>">+ محصول</a>
                <a class="m3-button m3-button--tonal m3-button--sm" href="<?= e(admin_url('orders')) ?>">سفارش‌ها</a>
                <a class="m3-button m3-button--tonal m3-button--sm" href="<?= e(admin_url('customers')) ?>">مشتریان</a>
                <a class="m3-button m3-button--tonal m3-button--sm" href="<?= e(admin_url('campaigns/new')) ?>">کمپین</a>
            </div>
        </section>
    </div>
</div>

<div class="grid-2" style="margin-top:16px">
    <section class="card">
        <div class="card__head">
            <h2>آخرین سفارش‌ها</h2>
            <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('orders')) ?>">مشاهدهٔ همه</a>
        </div>
        <div class="card__body card__body--flush">
            <?php if ($recent === []): ?>
                <div class="empty">
                    <span class="empty__mark" aria-hidden="true">🧾</span>
                    <h3>هنوز سفارشی ثبت نشده</h3>
                    <p>منو را با مشتری‌ها به اشتراک بگذار تا اولین سفارش برسد.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table table--stack">
                        <thead>
                            <tr>
                                <th>کد</th>
                                <th>مشتری</th>
                                <th>مبلغ</th>
                                <th>وضعیت</th>
                                <th>زمان</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $order): ?>
                                <tr>
                                    <td data-label="کد"><a class="mono" href="<?= e(admin_url('orders/' . (int) $order['id'])) ?>"><?= e((string) $order['code']) ?></a></td>
                                    <td data-label="مشتری"><?= e((string) ($order['customer_name'] ?: $order['customer_phone'] ?: 'مهمان')) ?></td>
                                    <td data-label="مبلغ"><?= money((int) $order['total']) ?></td>
                                    <td data-label="وضعیت"><?= status_badge((string) $order['status']) ?></td>
                                    <td data-label="زمان" class="small muted"><?= e(jalali_date((string) $order['placed_at'], true)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card">
        <div class="card__head">
            <h2>مشتریان برتر</h2>
            <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('customers')) ?>">همه</a>
        </div>
        <div class="card__body card__body--flush">
            <?php if ($topCustomers === []): ?>
                <div class="empty">
                    <span class="empty__mark" aria-hidden="true">👥</span>
                    <h3>هنوز مشتری‌ای ثبت نشده</h3>
                    <p>با ثبت شماره موبایل در سفارش، مشتری به باشگاه اضافه می‌شود.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table table--stack">
                        <thead>
                            <tr>
                                <th>نام</th>
                                <th>امتیاز</th>
                                <th>خرید</th>
                                <th>بازدید</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topCustomers as $customer): ?>
                                <tr>
                                    <td data-label="نام">
                                        <a href="<?= e(admin_url('customers/' . (int) $customer['id'])) ?>">
                                            <?= e($customer['name'] !== '' ? (string) $customer['name'] : (string) $customer['phone']) ?>
                                        </a>
                                    </td>
                                    <td data-label="امتیاز"><?= fa_digits((string) $customer['points_balance']) ?></td>
                                    <td data-label="خرید"><?= money((int) $customer['total_spent']) ?></td>
                                    <td data-label="بازدید"><?= fa_digits((string) $customer['visits']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
