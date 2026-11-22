<?php
/**
 * @var array $categories
 * @var array $menus
 */
?>
<div class="grid-2">
    <section class="card">
        <div class="card__head">
            <h2>دسته‌بندی‌ها</h2>
            <span class="small muted"><?= fa_digits((string) count($categories)) ?> دسته</span>
        </div>
        <div class="card__body card__body--flush">
            <?php if ($categories === []): ?>
                <div class="empty">
                    <span class="empty__mark" aria-hidden="true">🗂</span>
                    <h3>دسته‌ای نداری</h3>
                    <p>از فرم روبه‌رو اولین دسته را بساز (مثلاً «قهوه»).</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table table--stack">
                        <thead>
                            <tr>
                                <th>نام</th>
                                <th>منو</th>
                                <th>محصول</th>
                                <th>ترتیب</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td data-label="نام" class="cell-wrap">
                                        <?= e((string) $category['name']) ?>
                                        <?php if ((int) $category['is_active'] !== 1): ?>
                                            <span class="badge badge--muted">غیرفعال</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="منو"><?= e((string) $category['menu_name']) ?></td>
                                    <td data-label="محصول"><?= fa_digits((string) $category['products_count']) ?></td>
                                    <td data-label="ترتیب"><?= fa_digits((string) $category['sort_order']) ?></td>
                                    <td data-label="">
                                        <details>
                                            <summary class="small muted">ویرایش</summary>
                                            <form method="post" action="<?= e(admin_url('categories/' . (int) $category['id'])) ?>" style="margin-top:10px">
                                                <?= csrf_field() ?>
                                                <div class="stack" style="gap:8px;min-width:220px">
                                                    <input class="field__control" name="name" value="<?= e((string) $category['name']) ?>" maxlength="100" required aria-label="نام دسته">
                                                    <input class="field__control" name="description" value="<?= e((string) $category['description']) ?>" maxlength="255" placeholder="توضیح" aria-label="توضیح">
                                                    <input class="field__control" name="sort_order" value="<?= (int) $category['sort_order'] ?>" inputmode="numeric" aria-label="ترتیب">
                                                    <label class="switch-row">
                                                        <input type="checkbox" name="is_active" value="1"<?= is_checked((int) $category['is_active'] === 1) ?>>
                                                        فعال
                                                    </label>
                                                    <div class="row">
                                                        <button class="m3-button m3-button--filled m3-button--sm" type="submit">ذخیره</button>
                                                    </div>
                                                </div>
                                            </form>
                                            <form method="post" action="<?= e(admin_url('categories/' . (int) $category['id'] . '/delete')) ?>" data-confirm="این دسته حذف شود؟ محصولاتش بدون دسته می‌شوند." style="margin-top:6px">
                                                <?= csrf_field() ?>
                                                <button class="m3-button m3-button--text m3-button--sm" type="submit" style="color:var(--md-sys-color-error)">حذف دسته</button>
                                            </form>
                                        </details>
                                    </td>
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
            <h2>دستهٔ جدید</h2>
        </div>
        <?php if ($menus === []): ?>
            <div class="card__body">
                <p class="muted small">اول یک منو بساز؛ هر دسته داخل یک منو قرار می‌گیرد.</p>
                <a class="m3-button m3-button--tonal m3-button--sm" href="<?= e(admin_url('menus')) ?>">ساخت منو</a>
            </div>
        <?php else: ?>
            <form method="post" action="<?= e(admin_url('categories')) ?>">
                <?= csrf_field() ?>
                <div class="card__body stack">
                    <div class="field">
                        <label for="cat-name">نام دسته</label>
                        <input class="field__control" id="cat-name" name="name" required maxlength="100" placeholder="مثلاً نوشیدنی سرد">
                    </div>
                    <div class="field">
                        <label for="cat-menu">منو</label>
                        <select class="field__control" id="cat-menu" name="menu_id" required>
                            <?php foreach ($menus as $menu): ?>
                                <option value="<?= (int) $menu['id'] ?>"><?= e((string) $menu['name']) ?><?= (int) $menu['is_default'] === 1 ? ' (پیش‌فرض)' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="cat-desc">توضیح (اختیاری)</label>
                        <input class="field__control" id="cat-desc" name="description" maxlength="255">
                    </div>
                    <div class="field">
                        <label for="cat-sort">ترتیب</label>
                        <input class="field__control" id="cat-sort" name="sort_order" inputmode="numeric" placeholder="خودکار">
                    </div>
                </div>
                <div class="card__foot">
                    <button class="m3-button m3-button--filled" type="submit">افزودن دسته</button>
                </div>
            </form>
        <?php endif; ?>
    </section>
</div>
