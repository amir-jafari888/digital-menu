<?php
/**
 * @var array|null $product
 * @var array $categories
 * @var array $errors
 * @var string $formAction
 */
$p = $product ?? [];
$value = static fn (string $key, mixed $default = ''): string => (string) ($p[$key] ?? $default);
$image = !empty($p['image']) ? \App\Models\Product::imageUrl((string) $p['image']) : null;
$err = static fn (string $field): string => isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="err-' . e($field) . '"' : '';
$title = $product === null ? 'محصول جدید' : 'ویرایش ' . $value('name');
?>
<section class="card">
    <div class="card__head">
        <h2><?= e($title) ?></h2>
        <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('products')) ?>">بازگشت</a>
    </div>

    <form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <div class="card__body">
            <div class="form-grid">
                <div class="field field--wide">
                    <label for="name">نام محصول</label>
                    <input class="field__control" id="name" name="name" required maxlength="160"
                           value="<?= e($value('name')) ?>"<?= $err('name') ?>>
                    <?= field_error($errors, 'name') ?>
                </div>

                <div class="field">
                    <label for="category_id">دسته‌بندی</label>
                    <select class="field__control" id="category_id" name="category_id" required<?= $err('category_id') ?>>
                        <option value="">انتخاب کن</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"<?= is_selected($category['id'], $value('category_id')) ?>>
                                <?= e((string) $category['name']) ?> — <?= e((string) ($category['menu_name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= field_error($errors, 'category_id') ?>
                </div>

                <div class="field">
                    <label for="price">قیمت (تومان)</label>
                    <input class="field__control" id="price" name="price" inputmode="numeric" required
                           value="<?= e($value('price', '0')) ?>"<?= $err('price') ?>>
                    <?= field_error($errors, 'price') ?>
                </div>

                <div class="field">
                    <label for="compare_at_price">قیمت قبل از تخفیف (اختیاری)</label>
                    <input class="field__control" id="compare_at_price" name="compare_at_price" inputmode="numeric"
                           value="<?= e($value('compare_at_price')) ?>"<?= $err('compare_at_price') ?>>
                    <?= field_error($errors, 'compare_at_price') ?>
                </div>

                <div class="field">
                    <label for="calories">کالری (اختیاری)</label>
                    <input class="field__control" id="calories" name="calories" inputmode="numeric"
                           value="<?= e($value('calories')) ?>"<?= $err('calories') ?>>
                    <?= field_error($errors, 'calories') ?>
                </div>

                <div class="field field--wide">
                    <label for="description">توضیحات</label>
                    <textarea class="field__control" id="description" name="description" rows="3" maxlength="4000"<?= $err('description') ?>><?= e($value('description')) ?></textarea>
                    <?= field_error($errors, 'description') ?>
                </div>

                <div class="field">
                    <label for="allergens">حساسیت‌زاها</label>
                    <input class="field__control" id="allergens" name="allergens" maxlength="255"
                           placeholder="شیر، گلوتن…" value="<?= e($value('allergens')) ?>"<?= $err('allergens') ?>>
                    <?= field_error($errors, 'allergens') ?>
                </div>

                <div class="field">
                    <label for="sort_order">ترتیب نمایش</label>
                    <input class="field__control" id="sort_order" name="sort_order" inputmode="numeric"
                           value="<?= e($value('sort_order', '0')) ?>">
                </div>

                <div class="field field--wide">
                    <label for="image">تصویر محصول</label>
                    <input class="field__control" id="image" name="image" type="file"
                           accept="image/jpeg,image/png,image/webp" data-file-label="image-name">
                    <p class="field__hint" id="image-name">JPG، PNG یا WebP — حداکثر ۲ مگابایت.</p>
                    <?php if ($image !== null): ?>
                        <div class="row" style="margin-top:8px">
                            <img class="thumb" src="<?= e($image) ?>" alt="تصویر فعلی" width="56" height="56">
                            <label class="switch-row">
                                <input type="checkbox" name="remove_image" value="1">
                                حذف تصویر فعلی
                            </label>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="field field--wide">
                    <label class="switch-row">
                        <input type="checkbox" name="is_available" value="1"<?= is_checked($product === null || (int) ($p['is_available'] ?? 1) === 1) ?>>
                        موجود در منو
                    </label>
                    <label class="switch-row">
                        <input type="checkbox" name="is_featured" value="1"<?= is_checked((int) ($p['is_featured'] ?? 0) === 1) ?>>
                        نمایش در بخش «پیشنهاد ما»
                    </label>
                </div>
            </div>
        </div>

        <div class="card__foot">
            <a class="m3-button m3-button--text" href="<?= e(admin_url('products')) ?>">انصراف</a>
            <button class="m3-button m3-button--filled" type="submit">ذخیرهٔ محصول</button>
        </div>
    </form>
</section>
