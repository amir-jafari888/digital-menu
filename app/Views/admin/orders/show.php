<?php
/**
 * @var array $order
 * @var array $items
 * @var array $nextStatuses
 */
use App\Core\Auth;

$canUpdate = Auth::can('orders.update');
?>
<div class="grid-2">
    <div class="stack">
        <section class="card">
            <div class="order-head">
                <div>
                    <p class="small muted" style="margin:0">کد سفارش</p>
                    <p class="order-code" style="margin:2px 0 0"><?= e((string) $order['code']) ?></p>
                </div>
                <div class="row">
                    <?= status_badge((string) $order['status']) ?>
                    <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('orders')) ?>">بازگشت</a>
                </div>
            </div>

            <div class="kv">
                <div>
                    <p class="kv__label">مشتری</p>
                    <p class="kv__value">
                        <?php if (!empty($order['customer_id'])): ?>
                            <a href="<?= e(admin_url('customers/' . (int) $order['customer_id'])) ?>">
                                <?= e((string) ($order['customer_name'] ?: $order['customer_phone'])) ?>
                            </a>
                        <?php else: ?>
                            مهمان
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <p class="kv__label">شماره تماس</p>
                    <p class="kv__value mono"><?= e((string) ($order['customer_phone'] ?? '—')) ?></p>
                </div>
                <div>
                    <p class="kv__label">نوع سفارش</p>
                    <p class="kv__value"><?= e(\App\Models\Order::TYPE_LABELS[(string) $order['type']] ?? (string) $order['type']) ?></p>
                </div>
                <div>
                    <p class="kv__label">میز</p>
                    <p class="kv__value"><?= e((string) ($order['table_label'] ?? '—')) ?></p>
                </div>
                <div>
                    <p class="kv__label">ثبت</p>
                    <p class="kv__value small"><?= e(jalali_date((string) $order['placed_at'], true)) ?></p>
                </div>
                <div>
                    <p class="kv__label">تکمیل</p>
                    <p class="kv__value small"><?= e(jalali_date($order['completed_at'] ?? null, true)) ?></p>
                </div>
            </div>

            <?php if ((string) $order['note'] !== ''): ?>
                <div class="card__body" style="border-top:1px solid var(--md-sys-color-outline-variant)">
                    <p class="kv__label">توضیح مشتری</p>
                    <p class="small" style="margin:4px 0 0;line-height:1.9"><?= nl2br(e((string) $order['note'])) ?></p>
                </div>
            <?php endif; ?>
        </section>

        <section class="card">
            <div class="card__head">
                <h2>اقلام سفارش</h2>
                <span class="small muted"><?= fa_digits((string) count($items)) ?> قلم</span>
            </div>
            <div class="card__body card__body--flush">
                <div class="table-wrap">
                    <table class="table table--stack">
                        <thead>
                            <tr>
                                <th>محصول</th>
                                <th>قیمت واحد</th>
                                <th>تعداد</th>
                                <th>جمع</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td data-label="محصول" class="cell-wrap"><?= e((string) $item['name']) ?></td>
                                    <td data-label="قیمت واحد"><?= money((int) $item['unit_price']) ?></td>
                                    <td data-label="تعداد"><?= fa_digits((string) $item['quantity']) ?></td>
                                    <td data-label="جمع"><?= money((int) $item['total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="totals">
                <div class="totals__row"><span>جمع اقلام</span><span><?= money((int) $order['subtotal']) ?> تومان</span></div>
                <?php if ((int) $order['discount'] > 0): ?>
                    <div class="totals__row"><span>تخفیف</span><span>−<?= money((int) $order['discount']) ?> تومان</span></div>
                <?php endif; ?>
                <div class="totals__row"><span>امتیاز کسب‌شده</span><span><?= fa_digits((string) $order['points_earned']) ?></span></div>
                <div class="totals__row totals__row--grand"><span>مبلغ نهایی</span><span><?= money((int) $order['total']) ?> تومان</span></div>
            </div>
        </section>
    </div>

    <section class="card">
        <div class="card__head">
            <h2>تغییر وضعیت</h2>
        </div>
        <div class="card__body">
            <?php if (!$canUpdate): ?>
                <p class="muted small" style="margin:0">نقش شما اجازهٔ تغییر وضعیت سفارش را ندارد.</p>
            <?php elseif ($nextStatuses === []): ?>
                <p class="muted small" style="margin:0">این سفارش در وضعیت نهایی است و تغییر نمی‌کند.</p>
            <?php else: ?>
                <div class="stack" style="gap:8px">
                    <?php foreach ($nextStatuses as $status): ?>
                        <form method="post" action="<?= e(admin_url('orders/' . (int) $order['id'] . '/status')) ?>"
                              <?= $status === 'canceled' ? 'data-confirm="این سفارش لغو شود؟"' : '' ?>>
                            <?= csrf_field() ?>
                            <input type="hidden" name="status" value="<?= e($status) ?>">
                            <button class="m3-button m3-button--<?= $status === 'canceled' ? 'outlined' : 'tonal' ?> m3-button--block" type="submit"
                                    <?= $status === 'canceled' ? 'style="color:var(--md-sys-color-error);border-color:var(--md-sys-color-error)"' : '' ?>>
                                <?= e(\App\Models\Order::STATUS_LABELS[$status] ?? $status) ?>
                            </button>
                        </form>
                    <?php endforeach; ?>
                </div>
                <p class="hint" style="margin-top:12px">با تکمیل سفارش، امتیاز باشگاه مشتریان به‌صورت خودکار محاسبه می‌شود.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
