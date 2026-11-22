<?php
/**
 * @var array $subscriptions
 * @var array $plans
 */

$activeCount  = 0;
$trialCount   = 0;
$expiredCount = 0;
$mrr          = 0;

foreach ($subscriptions as $subscription) {
    $price = (int) $subscription['price'];

    if ((string) $subscription['status'] === 'active') {
        $activeCount++;
        $mrr += (string) $subscription['billing'] === 'yearly' ? (int) round($price / 12) : $price;
    } elseif ((string) $subscription['status'] === 'trial') {
        $trialCount++;
    } elseif ((string) $subscription['status'] === 'expired') {
        $expiredCount++;
    }
}

$billingLabels = ['monthly' => 'ماهانه', 'yearly' => 'سالانه'];
?>
<section class="stat-grid">
    <article class="stat stat--accent">
        <p class="stat__label">اشتراک فعال</p>
        <p class="stat__value"><?= fa_digits((string) $activeCount) ?></p>
        <p class="stat__hint">از <?= fa_digits((string) count($subscriptions)) ?> اشتراک</p>
    </article>
    <article class="stat">
        <p class="stat__label">در دورهٔ آزمایشی</p>
        <p class="stat__value"><?= fa_digits((string) $trialCount) ?></p>
        <p class="stat__hint">۱۴ روز رایگان</p>
    </article>
    <article class="stat">
        <p class="stat__label">درآمد ماهانهٔ تقریبی</p>
        <p class="stat__value"><?= money($mrr) ?></p>
        <p class="stat__hint">تومان، بر پایهٔ اشتراک‌های فعال</p>
    </article>
    <article class="stat">
        <p class="stat__label">منقضی‌شده</p>
        <p class="stat__value"><?= fa_digits((string) $expiredCount) ?></p>
        <p class="stat__hint">نیازمند تمدید</p>
    </article>
</section>

<section class="card" style="margin-top:16px">
    <div class="card__head">
        <div>
            <h2>پلن‌ها</h2>
            <p>مبالغ پایهٔ هر پلن؛ تغییر قیمت از طریق فایل داده انجام می‌شود.</p>
        </div>
    </div>
    <div class="card__body">
        <div class="grid-3">
            <?php foreach ($plans as $plan): ?>
                <article class="stat">
                    <p class="stat__label"><?= e((string) $plan['name']) ?></p>
                    <p class="stat__value"><?= money((int) $plan['monthly_price']) ?></p>
                    <p class="stat__hint">
                        ماهانه · سالانه <?= money((int) $plan['yearly_price']) ?> تومان
                    </p>
                    <?php if (($plan['features_list'] ?? []) !== []): ?>
                        <ul class="small muted" style="margin:10px 0 0;padding-inline-start:18px">
                            <?php foreach ($plan['features_list'] as $feature): ?>
                                <li><?= e($feature) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="card" style="margin-top:16px">
    <div class="card__head">
        <div>
            <h2>اشتراک‌ها و پرداخت‌ها</h2>
            <p>پرداخت‌ها به‌صورت دستی ثبت می‌شوند؛ درگاه پرداخت متصل نیست.</p>
        </div>
        <span class="small muted"><?= fa_digits((string) count($subscriptions)) ?> رکورد</span>
    </div>
    <div class="card__body card__body--flush">
        <?php if ($subscriptions === []): ?>
            <div class="empty">
                <span class="empty__mark" aria-hidden="true">💳</span>
                <h3>اشتراکی ثبت نشده</h3>
                <p>با ساخت کسب‌وکار، دورهٔ آزمایشی به‌صورت خودکار ایجاد می‌شود.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>کسب‌وکار</th>
                            <th>پلن</th>
                            <th>دوره</th>
                            <th>مبلغ</th>
                            <th>شروع</th>
                            <th>پایان</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptions as $subscription): ?>
                            <tr>
                                <td data-label="کسب‌وکار" class="cell-wrap">
                                    <a href="<?= e(admin_url('tenants')) ?>"><?= e((string) $subscription['tenant_name']) ?></a>
                                </td>
                                <td data-label="پلن"><?= e((string) $subscription['plan_name']) ?></td>
                                <td data-label="دوره"><?= e($billingLabels[(string) $subscription['billing']] ?? (string) $subscription['billing']) ?></td>
                                <td data-label="مبلغ">
                                    <?= (int) $subscription['price'] === 0 ? 'رایگان' : money((int) $subscription['price']) ?>
                                </td>
                                <td data-label="شروع" class="small"><?= e(jalali_date((string) $subscription['starts_at'])) ?></td>
                                <td data-label="پایان" class="small"><?= e(jalali_date((string) $subscription['ends_at'])) ?></td>
                                <td data-label="وضعیت"><?= status_badge((string) $subscription['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="card__foot">
        <span class="muted small">آخرین ۱۰۰ اشتراک نمایش داده می‌شود.</span>
    </div>
</section>
