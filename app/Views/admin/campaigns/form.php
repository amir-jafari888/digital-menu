<?php
/**
 * @var array|null $campaign
 * @var array      $errors
 * @var string     $formAction
 */
use App\Models\Campaign;
use App\Models\Product;

$c     = $campaign ?? [];
$value = static fn (string $key, mixed $default = ''): string => (string) ($c[$key] ?? $default);
$date  = static fn (string $key): string => substr($value($key), 0, 10);
$image = !empty($c['image']) ? Product::imageUrl((string) $c['image']) : null;
$err   = static fn (string $field): string => isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="err-' . e($field) . '"' : '';
$title = $campaign === null ? 'کمپین جدید' : 'ویرایش ' . $value('title');
$starts = $date('starts_at') !== '' ? $date('starts_at') : date('Y-m-d');
$ends   = $date('ends_at') !== '' ? $date('ends_at') : date('Y-m-d', strtotime('+7 days'));
?>
<section class="card">
    <div class="card__head">
        <h2><?= e($title) ?></h2>
        <a class="m3-button m3-button--text m3-button--sm" href="<?= e(admin_url('campaigns')) ?>">بازگشت</a>
    </div>

    <form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <div class="card__body">
            <div class="form-grid">
                <div class="field field--wide">
                    <label for="title">عنوان تبلیغ</label>
                    <input class="field__control" id="title" name="title" required maxlength="120"
                           value="<?= e($value('title')) ?>"<?= $err('title') ?>>
                    <?= field_error($errors, 'title') ?>
                </div>

                <div class="field">
                    <label for="placement">جایگاه نمایش</label>
                    <select class="field__control" id="placement" name="placement" required<?= $err('placement') ?>>
                        <?php foreach (Campaign::PLACEMENT_LABELS as $key => $label): ?>
                            <option value="<?= e($key) ?>"<?= is_selected($key, $value('placement', 'app_home')) ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= field_error($errors, 'placement') ?>
                </div>

                <div class="field">
                    <label for="status">وضعیت</label>
                    <select class="field__control" id="status" name="status" required<?= $err('status') ?>>
                        <option value="draft"<?= is_selected('draft', $value('status', 'draft')) ?>>پیش‌نویس</option>
                        <option value="active"<?= is_selected('active', $value('status')) ?>>فعال</option>
                        <option value="paused"<?= is_selected('paused', $value('status')) ?>>متوقف</option>
                        <option value="ended"<?= is_selected('ended', $value('status')) ?>>پایان‌یافته</option>
                    </select>
                    <?= field_error($errors, 'status') ?>
                </div>

                <div class="field">
                    <label for="starts_at">تاریخ شروع</label>
                    <input class="field__control" id="starts_at" name="starts_at" type="date" required
                           value="<?= e($starts) ?>"<?= $err('starts_at') ?>>
                    <?= field_error($errors, 'starts_at') ?>
                </div>

                <div class="field">
                    <label for="ends_at">تاریخ پایان</label>
                    <input class="field__control" id="ends_at" name="ends_at" type="date" required
                           value="<?= e($ends) ?>"<?= $err('ends_at') ?>>
                    <?= field_error($errors, 'ends_at') ?>
                </div>

                <div class="field">
                    <label for="budget">بودجه (تومان)</label>
                    <input class="field__control" id="budget" name="budget" inputmode="numeric"
                           value="<?= e($value('budget', '0')) ?>"<?= $err('budget') ?>>
                    <span class="field__hint">۰ یعنی بدون سقف بودجه.</span>
                    <?= field_error($errors, 'budget') ?>
                </div>

                <div class="field">
                    <label for="cta_url">لینک مقصد (اختیاری)</label>
                    <input class="field__control" id="cta_url" name="cta_url" type="url" dir="ltr" maxlength="500"
                           placeholder="https://" value="<?= e($value('cta_url')) ?>"<?= $err('cta_url') ?>>
                    <?= field_error($errors, 'cta_url') ?>
                </div>

                <div class="field field--wide">
                    <label for="description">متن تبلیغ</label>
                    <textarea class="field__control" id="description" name="description" rows="3" maxlength="500"<?= $err('description') ?>><?= e($value('description')) ?></textarea>
                    <?= field_error($errors, 'description') ?>
                </div>

                <div class="field field--wide">
                    <label for="image">تصویر تبلیغ</label>
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
            </div>
        </div>

        <div class="card__foot">
            <a class="m3-button m3-button--text" href="<?= e(admin_url('campaigns')) ?>">انصراف</a>
            <button class="m3-button m3-button--filled" type="submit">ذخیرهٔ کمپین</button>
        </div>
    </form>
</section>
