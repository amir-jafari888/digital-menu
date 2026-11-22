<?php
/**
 * @var array $stats
 * @var array $tenants
 * @var array $subscriptions
 * @var array $logs
 * @var int   $newMessages
 */
?>
<section class="stat-grid">
    <article class="stat stat--accent">
        <p class="stat__label">کسب‌وکارها</p>
        <p class="stat__value"><?= fa_digits((string) ($stats['tenants'] ?? 0)) ?></p>
        <p class="stat__hint"><?= fa_digits((string) ($stats['active_tenants'] ?? 0)) ?> فعال</p>
    </article>
    <article class="stat">
        <p class="stat__label">کاربران پلتفرم</p>
        <p class="stat__value"><?= fa_digits((string) ($stats['users'] ?? 0)) ?></p>
        <p class="stat__hint">مدیر، مالک و کارمند</p>
    </article>
    <article class="stat">
        <p class="stat__label">کل سفارش‌ها</p>
        <p class="stat__value"><?= fa_digits((string) ($stats['orders'] ?? 0)) ?></p>
        <p class="stat__hint"><?= money((int) ($stats['revenue'] ?? 0)) ?> تومان گردش</p>
    </article>
    <article class="stat">
        <p class="stat__label">پیام‌های جدید</p>
        <p class="stat__value"><?= fa_digits((string) $newMessages) ?></p>
        <p class="stat__hint"><a href="<?= e(admin_url('messages')) ?>">مشاهدهٔ پیام‌ها</a></p>
    </article>
</section>

<div class="grid-2" style="margin-top:16px">
    <section class="card">
        <div class="card__head">
            <h2>کسب‌وکارهای تازه</h2>
            <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('tenants')) ?>">مدیریت همه</a>
        </div>
        <div class="card__body card__body--flush">
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>نشانی</th>
                            <th>پلن</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tenants as $tenant): ?>
                            <tr>
                                <td data-label="نام"><?= e((string) $tenant['name']) ?></td>
                                <td data-label="نشانی" class="mono small"><?= e((string) $tenant['slug']) ?></td>
                                <td data-label="پلن"><?= e((string) ($tenant['plan_name'] ?? '—')) ?></td>
                                <td data-label="وضعیت"><?= status_badge((string) $tenant['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card__head">
            <h2>آخرین رویدادهای امنیتی</h2>
            <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('logs')) ?>">گزارش کامل</a>
        </div>
        <div class="card__body">
            <?php if ($logs === []): ?>
                <p class="muted small" style="margin:0">رویدادی ثبت نشده است.</p>
            <?php else: ?>
                <ul class="timeline">
                    <?php foreach ($logs as $log): ?>
                        <li>
                            <span class="dot" aria-hidden="true"></span>
                            <span>
                                <strong class="mono small"><?= e((string) $log['action']) ?></strong>
                                — <?= e((string) ($log['user_name'] ?? 'سیستم')) ?>
                                <span class="muted small"><?= e(jalali_date((string) $log['created_at'], true)) ?></span>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>
</div>

<section class="card" style="margin-top:16px">
    <div class="card__head">
        <h2>اشتراک‌های اخیر</h2>
        <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('subscriptions')) ?>">همهٔ اشتراک‌ها</a>
    </div>
    <div class="card__body card__body--flush">
        <div class="table-wrap">
            <table class="table table--stack">
                <thead>
                    <tr>
                        <th>کسب‌وکار</th>
                        <th>پلن</th>
                        <th>مبلغ</th>
                        <th>پایان</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscriptions as $subscription): ?>
                        <tr>
                            <td data-label="کسب‌وکار"><?= e((string) $subscription['tenant_name']) ?></td>
                            <td data-label="پلن"><?= e((string) $subscription['plan_name']) ?></td>
                            <td data-label="مبلغ"><?= money((int) $subscription['price']) ?></td>
                            <td data-label="پایان" class="small"><?= e(jalali_date((string) $subscription['ends_at'])) ?></td>
                            <td data-label="وضعیت"><?= status_badge((string) $subscription['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
