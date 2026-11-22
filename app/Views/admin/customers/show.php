<?php
/**
 * @var array $customer
 * @var array $history
 * @var array $orders
 * @var array $redemptions
 * @var array $rewards
 */
use App\Core\Auth;

$canManage = Auth::can('customers.manage');
?>
<div class="grid-2">
    <div class="stack">
        <section class="card">
            <div class="order-head">
                <div>
                    <p class="small muted" style="margin:0">پروندهٔ مشتری</p>
                    <h2 style="margin:2px 0 0;font-size:1.15rem">
                        <?= e($customer['name'] !== '' ? (string) $customer['name'] : 'بدون نام') ?>
                    </h2>
                    <p class="mono small muted" style="margin:4px 0 0"><?= e((string) $customer['phone']) ?></p>
                </div>
                <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('customers')) ?>">بازگشت</a>
            </div>

            <div class="kv">
                <div>
                    <p class="kv__label">امتیاز فعلی</p>
                    <p class="kv__value" style="color:var(--md-sys-color-primary);font-size:1.2rem"><?= fa_digits((string) $customer['points_balance']) ?></p>
                </div>
                <div>
                    <p class="kv__label">مجموع خرید</p>
                    <p class="kv__value"><?= money((int) $customer['total_spent']) ?></p>
                </div>
                <div>
                    <p class="kv__label">بازدید</p>
                    <p class="kv__value"><?= fa_digits((string) $customer['visits']) ?></p>
                </div>
                <div>
                    <p class="kv__label">عضویت از</p>
                    <p class="kv__value small"><?= e(jalali_date((string) $customer['created_at'])) ?></p>
                </div>
                <div>
                    <p class="kv__label">آخرین بازدید</p>
                    <p class="kv__value small"><?= e(jalali_date($customer['last_visit_at'] ?? null, true)) ?></p>
                </div>
                <div>
                    <p class="kv__label">ایمیل</p>
                    <p class="kv__value small mono"><?= e((string) ($customer['email'] ?? '—')) ?></p>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__head">
                <h2>تاریخچهٔ امتیاز</h2>
                <span class="small muted"><?= fa_digits((string) count($history)) ?> رکورد</span>
            </div>
            <div class="card__body card__body--flush">
                <?php if ($history === []): ?>
                    <div class="empty"><p>رکوردی ثبت نشده است.</p></div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table table--stack">
                            <thead>
                                <tr>
                                    <th>تاریخ</th>
                                    <th>تغییر</th>
                                    <th>موجودی</th>
                                    <th>دلیل</th>
                                    <th>توضیح</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $row): ?>
                                    <tr>
                                        <td data-label="تاریخ" class="small muted"><?= e(jalali_date((string) $row['created_at'], true)) ?></td>
                                        <td data-label="تغییر">
                                            <span class="badge badge--<?= (int) $row['points'] >= 0 ? 'ok' : 'danger' ?>">
                                                <?= (int) $row['points'] >= 0 ? '+' : '−' ?><?= fa_digits((string) abs((int) $row['points'])) ?>
                                            </span>
                                        </td>
                                        <td data-label="موجودی"><?= fa_digits((string) $row['balance_after']) ?></td>
                                        <td data-label="دلیل" class="small"><?= match ((string) $row['reason']) {
                                            'signup' => 'خوش‌آمدگویی',
                                            'purchase' => 'خرید',
                                            'redemption' => 'دریافت جایزه',
                                            default => 'اصلاح دستی',
                                        } ?></td>
                                        <td data-label="توضیح" class="small muted cell-wrap"><?= e((string) $row['note']) ?></td>
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
                <h2>سفارش‌های مشتری</h2>
            </div>
            <div class="card__body card__body--flush">
                <?php if ($orders === []): ?>
                    <div class="empty"><p>سفارشی ثبت نشده است.</p></div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table table--stack">
                            <thead>
                                <tr><th>کد</th><th>مبلغ</th><th>وضعیت</th><th>زمان</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td data-label="کد"><a class="mono" href="<?= e(admin_url('orders/' . (int) $order['id'])) ?>"><?= e((string) $order['code']) ?></a></td>
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
    </div>

    <div class="stack">
        <?php if ($canManage): ?>
            <section class="card">
                <div class="card__head"><h2>اصلاح امتیاز</h2></div>
                <form method="post" action="<?= e(admin_url('customers/' . (int) $customer['id'] . '/points')) ?>">
                    <?= csrf_field() ?>
                    <div class="card__body stack">
                        <div class="field">
                            <label for="points">مقدار امتیاز</label>
                            <input class="field__control" id="points" name="points" inputmode="numeric" required placeholder="مثلاً ۵۰ یا ۲۰-">
                            <span class="field__hint">برای کسر امتیاز، عدد منفی وارد کن.</span>
                        </div>
                        <div class="field">
                            <label for="note">توضیح</label>
                            <input class="field__control" id="note" name="note" maxlength="255" placeholder="مثلاً جبران سفارش ناقص">
                        </div>
                    </div>
                    <div class="card__foot">
                        <button class="m3-button m3-button--filled m3-button--sm" type="submit">ثبت تغییر</button>
                    </div>
                </form>
            </section>

            <section class="card">
                <div class="card__head"><h2>ثبت دریافت جایزه</h2></div>
                <?php if ($rewards === []): ?>
                    <div class="card__body">
                        <p class="muted small" style="margin:0">جایزهٔ فعالی تعریف نشده است.</p>
                        <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('rewards')) ?>">ساخت جایزه</a>
                    </div>
                <?php else: ?>
                    <form method="post" action="<?= e(admin_url('customers/' . (int) $customer['id'] . '/redeem')) ?>">
                        <?= csrf_field() ?>
                        <div class="card__body stack">
                            <div class="field">
                                <label for="reward_id">جایزه</label>
                                <select class="field__control" id="reward_id" name="reward_id" required>
                                    <?php foreach ($rewards as $reward): ?>
                                        <option value="<?= (int) $reward['id'] ?>">
                                            <?= e((string) $reward['title']) ?> — <?= fa_digits((string) $reward['points_cost']) ?> امتیاز
                                            <?= $reward['stock'] === null ? '' : '(' . fa_digits((string) $reward['stock']) . ' باقی‌مانده)' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <p class="hint">امتیاز به‌صورت اتمیک کسر می‌شود و یک کد یکتا برای تحویل ساخته می‌شود.</p>
                        </div>
                        <div class="card__foot">
                            <button class="m3-button m3-button--filled m3-button--sm" type="submit">ثبت جایزه</button>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <section class="card">
            <div class="card__head"><h2>جوایز دریافتی</h2></div>
            <div class="card__body card__body--flush">
                <?php if ($redemptions === []): ?>
                    <div class="empty"><p>جایزه‌ای دریافت نشده است.</p></div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table table--stack">
                            <thead><tr><th>جایزه</th><th>کد</th><th>وضعیت</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach ($redemptions as $redemption): ?>
                                    <tr>
                                        <td data-label="جایزه" class="cell-wrap"><?= e((string) $redemption['reward_title']) ?></td>
                                        <td data-label="کد" class="mono"><?= e((string) $redemption['code']) ?></td>
                                        <td data-label="وضعیت"><?= status_badge((string) $redemption['status']) ?></td>
                                        <td data-label="">
                                            <?php if ($redemption['status'] === 'pending' && $canManage): ?>
                                                <form method="post" action="<?= e(admin_url('redemptions/' . (int) $redemption['id'] . '/used')) ?>">
                                                    <?= csrf_field() ?>
                                                    <button class="m3-button m3-button--text m3-button--sm" type="submit">استفاده شد</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
