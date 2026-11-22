<?php
/**
 * @var array $products
 * @var array $categories
 * @var array $filters
 * @var int   $total
 * @var string $baseUrl
 */
?>
<section class="card">
    <form class="filters" method="get" action="<?= e($baseUrl) ?>">
        <div class="field field--search">
            <label for="q">جست‌وجو</label>
            <input class="field__control" id="q" name="q" value="<?= e((string) $filters['search']) ?>" placeholder="نام یا توضیح محصول">
        </div>
        <div class="field">
            <label for="category">دسته</label>
            <select class="field__control" id="category" name="category">
                <option value="">همه</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>"<?= is_selected($category['id'], $filters['category_id'] ?? '') ?>><?= e((string) $category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="available">وضعیت</label>
            <select class="field__control" id="available" name="available">
                <option value="">همه</option>
                <option value="1"<?= is_selected('1', $filters['available'] === true ? '1' : '') ?>>موجود</option>
                <option value="0"<?= is_selected('0', $filters['available'] === false ? '0' : '') ?>>ناموجود</option>
            </select>
        </div>
        <button class="m3-button m3-button--tonal m3-button--sm" type="submit">اعمال</button>
        <a class="m3-button m3-button--text m3-button--sm" href="<?= e($baseUrl) ?>">پاک‌کردن</a>
    </form>

    <div class="card__body card__body--flush">
        <?php if ($products === []): ?>
            <div class="empty">
                <span class="empty__mark" aria-hidden="true">☕</span>
                <h3>محصولی پیدا نشد</h3>
                <p>با «محصول جدید» اولین آیتم منو را بساز.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>تصویر</th>
                            <th>نام</th>
                            <th>دسته</th>
                            <th>قیمت</th>
                            <th>وضعیت</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <?php $image = \App\Models\Product::imageUrl($product['image'] ?? null); ?>
                            <tr>
                                <td data-label="تصویر">
                                    <?php if ($image !== null): ?>
                                        <img class="thumb" src="<?= e($image) ?>" alt="" width="44" height="44" loading="lazy">
                                    <?php else: ?>
                                        <span class="thumb thumb--empty" aria-hidden="true"><?= e(mb_substr((string) $product['name'], 0, 1)) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="نام" class="cell-wrap">
                                    <a href="<?= e(admin_url('products/' . (int) $product['id'] . '/edit')) ?>"><?= e((string) $product['name']) ?></a>
                                    <?php if ((int) $product['is_featured'] === 1): ?>
                                        <span class="badge badge--warn">ویژه</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="دسته"><?= e((string) ($product['category_name'] ?? '—')) ?></td>
                                <td data-label="قیمت"><?= money((int) $product['price']) ?></td>
                                <td data-label="وضعیت">
                                    <?= (int) $product['is_available'] === 1 ? '<span class="badge badge--ok">موجود</span>' : '<span class="badge badge--muted">ناموجود</span>' ?>
                                </td>
                                <td data-label="">
                                    <div class="cell-actions">
                                        <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('products/' . (int) $product['id'] . '/edit')) ?>">ویرایش</a>
                                        <form method="post" action="<?= e(admin_url('products/' . (int) $product['id'] . '/toggle')) ?>">
                                            <?= csrf_field() ?>
                                            <button class="m3-button m3-button--text m3-button--sm" type="submit">
                                                <?= (int) $product['is_available'] === 1 ? 'ناموجود کن' : 'موجود کن' ?>
                                            </button>
                                        </form>
                                        <form method="post" action="<?= e(admin_url('products/' . (int) $product['id'] . '/delete')) ?>" data-confirm="این محصول حذف شود؟">
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
    <div class="card__foot">
        <span class="small muted"><?= fa_digits((string) $total) ?> محصول</span>
    </div>
</section>
