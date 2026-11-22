<?php
/**
 * @var array $orders
 * @var array $filters
 * @var array $pager
 * @var int   $total
 * @var string $tabs
 * @var string $baseUrl
 */
?>
<?= $tabs ?>

<section class="card">
    <form class="filters" method="get" action="<?= e($baseUrl) ?>">
        <?php if ($filters['status'] !== ''): ?>
            <input type="hidden" name="status" value="<?= e((string) $filters['status']) ?>">
        <?php endif; ?>
        <div class="field field--search">
            <label for="q">جست‌وجو</label>
            <input class="field__control" id="q" name="q" value="<?= e((string) $filters['search']) ?>" placeholder="کد سفارش، نام یا موبایل مشتری">
        </div>
        <div class="field">
            <label for="from">از تاریخ</label>
            <input class="field__control" id="from" name="from" type="date" value="<?= e((string) $filters['from']) ?>">
        </div>
        <div class="field">
            <label for="to">تا تاریخ</label>
            <input class="field__control" id="to" name="to" type="date" value="<?= e((string) $filters['to']) ?>">
        </div>
        <button class="m3-button m3-button--tonal m3-button--sm" type="submit">اعمال</button>
        <a class="m3-button m3-button--text m3-button--sm" href="<?= e($baseUrl) ?>">پاک‌کردن</a>
    </form>

    <div class="card__body card__body--flush">
        <?php if ($orders === []): ?>
            <div class="empty">
                <span class="empty__mark" aria-hidden="true">🧾</span>
                <h3>سفارشی پیدا نشد</h3>
                <p>فیلترها را تغییر بده یا منتظر اولین سفارش بمان.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>کد</th>
                            <th>مشتری</th>
                            <th>نوع</th>
                            <th>میز</th>
                            <th>اقلام</th>
                            <th>مبلغ</th>
                            <th>وضعیت</th>
                            <th>زمان</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td data-label="کد">
                                    <a class="mono" href="<?= e(admin_url('orders/' . (int) $order['id'])) ?>"><?= e((string) $order['code']) ?></a>
                                </td>
                                <td data-label="مشتری" class="cell-wrap">
                                    <?php if (!empty($order['customer_name']) || !empty($order['customer_phone'])): ?>
                                        <?= e((string) ($order['customer_name'] ?: $order['customer_phone'])) ?>
                                    <?php else: ?>
                                        <span class="muted">مهمان</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="نوع"><?= e(\App\Models\Order::TYPE_LABELS[(string) $order['type']] ?? (string) $order['type']) ?></td>
                                <td data-label="میز"><?= e((string) ($order['table_label'] ?? '—')) ?></td>
                                <td data-label="اقلام"><?= fa_digits((string) $order['items_count']) ?></td>
                                <td data-label="مبلغ"><?= money((int) $order['total']) ?></td>
                                <td data-label="وضعیت"><?= status_badge((string) $order['status']) ?></td>
                                <td data-label="زمان" class="small muted"><?= e(jalali_date((string) $order['placed_at'], true)) ?></td>
                                <td data-label="">
                                    <div class="cell-actions">
                                        <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('orders/' . (int) $order['id'])) ?>">جزئیات</a>
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
        <span class="small muted"><?= fa_digits((string) $total) ?> سفارش</span>
        <span class="spacer"></span>
        <?= paginate_links((int) $pager['page'], (int) $pager['pages'], $baseUrl) ?>
    </div>
</section>
