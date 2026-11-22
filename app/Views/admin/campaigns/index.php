<?php
/**
 * @var array $campaigns
 * @var int   $active
 * @var int   $impressions
 * @var int   $clicks
 */
use App\Models\Campaign;
use App\Models\Product;

$ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 1) : 0.0;
?>
<section class="stat-grid">
    <article class="stat stat--accent">
        <p class="stat__label">کمپین‌های فعال</p>
        <p class="stat__value"><?= fa_digits((string) $active) ?></p>
        <p class="stat__hint">از <?= fa_digits((string) count($campaigns)) ?> کمپین</p>
    </article>
    <article class="stat">
        <p class="stat__label">نمایش</p>
        <p class="stat__value"><?= fa_digits(number_format($impressions, 0, '.', '٬')) ?></p>
        <p class="stat__hint">مجموع نمایش‌ها</p>
    </article>
    <article class="stat">
        <p class="stat__label">کلیک</p>
        <p class="stat__value"><?= fa_digits(number_format($clicks, 0, '.', '٬')) ?></p>
        <p class="stat__hint">نرخ کلیک <?= fa_digits(number_format($ctr, 1)) ?>٪</p>
    </article>
</section>

<section class="card" style="margin-top:16px">
    <div class="card__head">
        <div>
            <h2>کمپین‌های تبلیغاتی</h2>
            <p>تبلیغ‌ها در اپ مشتری نمایش داده می‌شوند و فقط در بازهٔ زمانی و بودجهٔ تعیین‌شده سرو می‌شوند.</p>
        </div>
    </div>
    <div class="card__body card__body--flush">
        <?php if ($campaigns === []): ?>
            <div class="empty">
                <span class="empty__mark" aria-hidden="true">📣</span>
                <h3>کمپینی نداری</h3>
                <p>با «کمپین جدید» یک تبلیغ بساز و آن را فعال کن.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>تصویر</th>
                            <th>عنوان</th>
                            <th>جایگاه</th>
                            <th>بازه</th>
                            <th>بودجه</th>
                            <th>نمایش / کلیک</th>
                            <th>وضعیت</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campaigns as $campaign): ?>
                            <?php $image = Product::imageUrl($campaign['image'] ?? null); ?>
                            <tr>
                                <td data-label="تصویر">
                                    <?php if ($image !== null): ?>
                                        <img class="thumb" src="<?= e($image) ?>" alt="" width="44" height="44" loading="lazy">
                                    <?php else: ?>
                                        <span class="thumb thumb--empty" aria-hidden="true">📣</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="عنوان" class="cell-wrap">
                                    <a href="<?= e(admin_url('campaigns/' . (int) $campaign['id'] . '/edit')) ?>"><?= e((string) $campaign['title']) ?></a>
                                </td>
                                <td data-label="جایگاه"><?= e(Campaign::PLACEMENT_LABELS[(string) $campaign['placement']] ?? (string) $campaign['placement']) ?></td>
                                <td data-label="بازه" class="small">
                                    <?= e(jalali_date((string) $campaign['starts_at'])) ?>
                                    تا
                                    <?= e(jalali_date((string) $campaign['ends_at'])) ?>
                                </td>
                                <td data-label="بودجه">
                                    <?= (int) $campaign['budget'] === 0 ? 'نامحدود' : money((int) $campaign['budget']) ?>
                                </td>
                                <td data-label="نمایش / کلیک" class="small">
                                    <?= fa_digits(number_format((int) $campaign['impressions'], 0, '.', '٬')) ?>
                                    /
                                    <?= fa_digits(number_format((int) $campaign['clicks'], 0, '.', '٬')) ?>
                                </td>
                                <td data-label="وضعیت"><?= status_badge((string) $campaign['status']) ?></td>
                                <td data-label="">
                                    <div class="cell-actions">
                                        <?php if ($campaign['status'] !== 'active'): ?>
                                            <form method="post" action="<?= e(admin_url('campaigns/' . (int) $campaign['id'] . '/status')) ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="active">
                                                <button class="m3-button m3-button--text m3-button--sm" type="submit">فعال کن</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="<?= e(admin_url('campaigns/' . (int) $campaign['id'] . '/status')) ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="paused">
                                                <button class="m3-button m3-button--text m3-button--sm" type="submit">توقف</button>
                                            </form>
                                        <?php endif; ?>
                                        <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('campaigns/' . (int) $campaign['id'] . '/edit')) ?>">ویرایش</a>
                                        <form method="post" action="<?= e(admin_url('campaigns/' . (int) $campaign['id'] . '/delete')) ?>" data-confirm="این کمپین حذف شود؟">
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
</section>
