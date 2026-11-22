<?php
/**
 * @var array  $logs
 * @var int    $page
 * @var int    $pages
 * @var int    $total
 * @var string $baseUrl
 */

$labels = [
    'auth.login'             => 'ورود موفق',
    'auth.logout'            => 'خروج از حساب',
    'auth.login_failed'      => 'تلاش ورود ناموفق',
    'auth.register'          => 'ثبت‌نام کسب‌وکار',
    'auth.password_changed'  => 'تغییر رمز عبور',
    'profile.updated'        => 'ویرایش پروفایل',
    'settings.updated'       => 'تغییر تنظیمات',
    'tenant.created'         => 'ساخت کسب‌وکار',
    'tenant.updated'         => 'ویرایش کسب‌وکار',
    'tenant.act'             => 'ورود به پنل کسب‌وکار',
    'user.created'           => 'ساخت کاربر',
    'user.updated'           => 'ویرایش کاربر',
    'user.password_reset'    => 'بازنشانی رمز کاربر',
    'product.created'        => 'ساخت محصول',
    'product.updated'        => 'ویرایش محصول',
    'product.availability'   => 'تغییر موجودی محصول',
    'product.deleted'        => 'حذف محصول',
    'category.created'       => 'ساخت دسته‌بندی',
    'category.updated'       => 'ویرایش دسته‌بندی',
    'category.deleted'       => 'حذف دسته‌بندی',
    'menu.created'           => 'ساخت منو',
    'menu.default'           => 'انتخاب منوی پیش‌فرض',
    'menu.publish'           => 'انتشار منو',
    'table.created'          => 'ساخت میز',
    'table.deleted'          => 'حذف میز',
    'order.created'          => 'ثبت سفارش',
    'order.status'           => 'تغییر وضعیت سفارش',
    'reward.created'         => 'ساخت جایزه',
    'reward.updated'         => 'ویرایش جایزه',
    'reward.deleted'         => 'حذف جایزه',
    'campaign.created'       => 'ساخت کمپین',
    'campaign.updated'       => 'ویرایش کمپین',
    'campaign.status'        => 'تغییر وضعیت کمپین',
    'campaign.deleted'       => 'حذف کمپین',
    'message.status'         => 'تغییر وضعیت پیام',
];
?>
<section class="card">
    <div class="card__head">
        <div>
            <h2>گزارش امنیتی</h2>
            <p>ورودها، تغییرات داده‌ها و رویدادهای حساس. آدرس IP فقط به‌صورت هش ذخیره می‌شود.</p>
        </div>
        <span class="small muted"><?= fa_digits((string) $total) ?> رویداد</span>
    </div>
    <div class="card__body card__body--flush">
        <?php if ($logs === []): ?>
            <div class="empty">
                <span class="empty__mark" aria-hidden="true">🛡️</span>
                <h3>رویدادی ثبت نشده</h3>
                <p>با اولین ورود یا تغییر داده، رویدادها اینجا ثبت می‌شوند.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>زمان</th>
                            <th>رویداد</th>
                            <th>کاربر</th>
                            <th>موجودیت</th>
                            <th>جزئیات</th>
                            <th>IP (هش)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $meta = json_decode((string) ($log['meta'] ?? ''), true);
                            $metaText = is_array($meta) && $meta !== []
                                ? (string) json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                                : '';
                            $action = (string) $log['action'];
                            $entity = (string) ($log['entity'] ?? '');
                            $entityId = $log['entity_id'] ?? null;
                            ?>
                            <tr>
                                <td data-label="زمان" class="small muted"><?= e(jalali_date((string) $log['created_at'], true)) ?></td>
                                <td data-label="رویداد" class="cell-wrap">
                                    <strong class="small"><?= e($labels[$action] ?? $action) ?></strong>
                                    <br>
                                    <span class="mono small muted"><?= e($action) ?></span>
                                </td>
                                <td data-label="کاربر" class="cell-wrap">
                                    <?= e((string) ($log['user_name'] ?? 'سیستم')) ?>
                                    <?php if (!empty($log['tenant_id'])): ?>
                                        <br><span class="small muted">کسب‌وکار #<?= fa_digits((string) $log['tenant_id']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="موجودیت" class="mono small">
                                    <?= $entity === '' ? '—' : e($entity . ($entityId !== null ? '#' . $entityId : '')) ?>
                                </td>
                                <td data-label="جزئیات" class="small muted">
                                    <?= $metaText === '' ? '—' : e(mb_substr($metaText, 0, 140)) . (mb_strlen($metaText) > 140 ? '…' : '') ?>
                                </td>
                                <td data-label="IP (هش)" class="mono small muted">
                                    <?= e(mb_substr((string) ($log['ip_hash'] ?? ''), 0, 12)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="card__foot">
        <span class="small muted">صفحه <?= fa_digits((string) $page) ?> از <?= fa_digits((string) $pages) ?></span>
        <span class="spacer"></span>
        <?= paginate_links($page, $pages, $baseUrl) ?>
    </div>
</section>
