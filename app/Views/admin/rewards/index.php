<?php
/**
 * @var array $rewards
 */
?>
<div class="grid-2">
    <section class="card">
        <div class="card__head">
            <h2>جوایز باشگاه</h2>
            <span class="small muted"><?= fa_digits((string) count($rewards)) ?> جایزه</span>
        </div>
        <div class="card__body card__body--flush">
            <?php if ($rewards === []): ?>
                <div class="empty">
                    <span class="empty__mark" aria-hidden="true">🎁</span>
                    <h3>جایزه‌ای نداری</h3>
                    <p>با یک قهوهٔ مهمان شروع کن؛ مشتری‌ها عاشقش می‌شوند.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table table--stack">
                        <thead>
                            <tr><th>عنوان</th><th>امتیاز</th><th>موجودی</th><th>دریافت</th><th>وضعیت</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rewards as $reward): ?>
                                <tr>
                                    <td data-label="عنوان" class="cell-wrap"><?= e((string) $reward['title']) ?></td>
                                    <td data-label="امتیاز"><?= fa_digits((string) $reward['points_cost']) ?></td>
                                    <td data-label="موجودی"><?= $reward['stock'] === null ? 'نامحدود' : fa_digits((string) $reward['stock']) ?></td>
                                    <td data-label="دریافت"><?= fa_digits((string) $reward['redemptions']) ?></td>
                                    <td data-label="وضعیت">
                                        <?= (int) $reward['is_active'] === 1 ? '<span class="badge badge--ok">فعال</span>' : '<span class="badge badge--muted">غیرفعال</span>' ?>
                                    </td>
                                    <td data-label="">
                                        <details>
                                            <summary class="small muted">ویرایش</summary>
                                            <form method="post" action="<?= e(admin_url('rewards/' . (int) $reward['id'])) ?>" style="margin-top:10px">
                                                <?= csrf_field() ?>
                                                <div class="stack" style="gap:8px;min-width:220px">
                                                    <input class="field__control" name="title" value="<?= e((string) $reward['title']) ?>" maxlength="120" required aria-label="عنوان">
                                                    <input class="field__control" name="description" value="<?= e((string) $reward['description']) ?>" maxlength="255" placeholder="توضیح" aria-label="توضیح">
                                                    <input class="field__control" name="points_cost" value="<?= (int) $reward['points_cost'] ?>" inputmode="numeric" required aria-label="امتیاز لازم">
                                                    <input class="field__control" name="stock" value="<?= $reward['stock'] === null ? '' : (int) $reward['stock'] ?>" inputmode="numeric" placeholder="موجودی (خالی = نامحدود)" aria-label="موجودی">
                                                    <label class="switch-row">
                                                        <input type="checkbox" name="is_active" value="1"<?= is_checked((int) $reward['is_active'] === 1) ?>>
                                                        فعال
                                                    </label>
                                                    <button class="m3-button m3-button--filled m3-button--sm" type="submit">ذخیره</button>
                                                </div>
                                            </form>
                                            <form method="post" action="<?= e(admin_url('rewards/' . (int) $reward['id'] . '/delete')) ?>" data-confirm="این جایزه حذف/غیرفعال شود؟" style="margin-top:6px">
                                                <?= csrf_field() ?>
                                                <button class="m3-button m3-button--text m3-button--sm" type="submit" style="color:var(--md-sys-color-error)">
                                                    <?= (int) $reward['redemptions'] > 0 ? 'غیرفعال کن' : 'حذف' ?>
                                                </button>
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
        <div class="card__head"><h2>جایزهٔ جدید</h2></div>
        <form method="post" action="<?= e(admin_url('rewards')) ?>">
            <?= csrf_field() ?>
            <div class="card__body stack">
                <div class="field">
                    <label for="reward-title">عنوان</label>
                    <input class="field__control" id="reward-title" name="title" required maxlength="120" placeholder="مثلاً یک قهوه مهمان ما">
                </div>
                <div class="field">
                    <label for="reward-desc">توضیح</label>
                    <input class="field__control" id="reward-desc" name="description" maxlength="255">
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label for="reward-points">امتیاز لازم</label>
                        <input class="field__control" id="reward-points" name="points_cost" inputmode="numeric" required value="100">
                    </div>
                    <div class="field">
                        <label for="reward-stock">موجودی</label>
                        <input class="field__control" id="reward-stock" name="stock" inputmode="numeric" placeholder="خالی = نامحدود">
                    </div>
                </div>
                <label class="switch-row">
                    <input type="checkbox" name="is_active" value="1" checked>
                    فعال باشد
                </label>
            </div>
            <div class="card__foot">
                <button class="m3-button m3-button--filled" type="submit">افزودن جایزه</button>
            </div>
        </form>
    </section>
</div>
