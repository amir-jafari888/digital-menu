<?php
/**
 * @var array $menus
 * @var array $tables
 * @var array $tenant
 */
$menuUrl = static fn (array $menu): string => url('menu.php?qr=' . $menu['qr_token']);
?>
<section class="card">
    <div class="card__head">
        <div>
            <h2>منوهای دیجیتال</h2>
            <p>هر منو یک لینک QR اختصاصی دارد؛ همان لینک را روی میزها بگذار.</p>
        </div>
    </div>
    <div class="card__body card__body--flush">
        <?php if ($menus === []): ?>
            <div class="empty">
                <span class="empty__mark" aria-hidden="true">📱</span>
                <h3>منویی نداری</h3>
                <p>از فرم پایین اولین منو را بساز.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>دسته</th>
                            <th>محصول</th>
                            <th>وضعیت</th>
                            <th>لینک</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $menu): ?>
                            <tr>
                                <td data-label="نام" class="cell-wrap">
                                    <?= e((string) $menu['name']) ?>
                                    <?php if ((int) $menu['is_default'] === 1): ?>
                                        <span class="badge badge--ok">پیش‌فرض</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="دسته"><?= fa_digits((string) $menu['categories_count']) ?></td>
                                <td data-label="محصول"><?= fa_digits((string) $menu['products_count']) ?></td>
                                <td data-label="وضعیت">
                                    <?= (int) $menu['is_published'] === 1 ? '<span class="badge badge--ok">منتشرشده</span>' : '<span class="badge badge--muted">منتشرنشده</span>' ?>
                                </td>
                                <td data-label="لینک">
                                    <a class="mono small" href="<?= e($menuUrl($menu)) ?>" target="_blank" rel="noopener">باز کردن منو ↗</a>
                                </td>
                                <td data-label="">
                                    <div class="cell-actions">
                                        <form method="post" action="<?= e(admin_url('menus/' . (int) $menu['id'] . '/publish')) ?>">
                                            <?= csrf_field() ?>
                                            <button class="m3-button m3-button--text m3-button--sm" type="submit">
                                                <?= (int) $menu['is_published'] === 1 ? 'لغو انتشار' : 'انتشار' ?>
                                            </button>
                                        </form>
                                        <?php if ((int) $menu['is_default'] !== 1): ?>
                                            <form method="post" action="<?= e(admin_url('menus/' . (int) $menu['id'] . '/default')) ?>">
                                                <?= csrf_field() ?>
                                                <button class="m3-button m3-button--text m3-button--sm" type="submit">پیش‌فرض کن</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <form method="post" action="<?= e(admin_url('menus')) ?>">
        <?= csrf_field() ?>
        <div class="card__foot" style="justify-content:flex-start">
            <input class="field__control" name="name" placeholder="نام منوی جدید (مثلاً منوی صبحانه)" maxlength="100" required style="max-width:320px">
            <button class="m3-button m3-button--filled m3-button--sm" type="submit">ساخت منو</button>
        </div>
    </form>
</section>

<section class="card">
    <div class="card__head">
        <div>
            <h2>میزها و QR سفارش</h2>
            <p>برای هر میز یک لینک بساز و QR آن را چاپ کن؛ سفارش‌ها به همان میز وصل می‌شوند.</p>
        </div>
    </div>
    <div class="card__body card__body--flush">
        <?php if ($tables === []): ?>
            <div class="empty">
                <span class="empty__mark" aria-hidden="true">🪑</span>
                <h3>میزی ثبت نشده</h3>
                <p>میزها اختیاری‌اند؛ برای سفارش حضوری بهتر است اضافه شوند.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>برچسب</th>
                            <th>سفارش باز</th>
                            <th>وضعیت</th>
                            <th>لینک میز</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $table): ?>
                            <?php $defaultMenu = $menus[0] ?? null; ?>
                            <tr>
                                <td data-label="برچسب"><?= e((string) $table['label']) ?></td>
                                <td data-label="سفارش باز"><?= fa_digits((string) $table['open_orders']) ?></td>
                                <td data-label="وضعیت">
                                    <?= (int) $table['is_active'] === 1 ? '<span class="badge badge--ok">فعال</span>' : '<span class="badge badge--muted">غیرفعال</span>' ?>
                                </td>
                                <td data-label="لینک میز">
                                    <?php if ($defaultMenu !== null): ?>
                                        <a class="mono small" target="_blank" rel="noopener"
                                           href="<?= e(url('menu.php?qr=' . $defaultMenu['qr_token'] . '&t=' . $table['qr_token'])) ?>">باز کردن ↗</a>
                                    <?php else: ?>
                                        <span class="muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="">
                                    <div class="cell-actions">
                                        <form method="post" action="<?= e(admin_url('tables/' . (int) $table['id'] . '/toggle')) ?>">
                                            <?= csrf_field() ?>
                                            <button class="m3-button m3-button--text m3-button--sm" type="submit">
                                                <?= (int) $table['is_active'] === 1 ? 'غیرفعال' : 'فعال' ?>
                                            </button>
                                        </form>
                                        <form method="post" action="<?= e(admin_url('tables/' . (int) $table['id'] . '/delete')) ?>" data-confirm="این میز حذف شود؟">
                                            <?= csrf_field() ?>
                                            <button class="m3-button m3-button--text m3-button--sm" type="submit" style="color:var(--md-sys-color-error)">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <form method="post" action="<?= e(admin_url('tables')) ?>">
        <?= csrf_field() ?>
        <div class="card__foot" style="justify-content:flex-start">
            <input class="field__control" name="label" placeholder="برچسب میز (مثلاً میز ۵)" maxlength="40" required style="max-width:260px">
            <button class="m3-button m3-button--filled m3-button--sm" type="submit">افزودن میز</button>
        </div>
    </form>
</section>
