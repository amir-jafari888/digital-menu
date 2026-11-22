<?php
/**
 * @var array  $messages
 * @var string $status
 * @var int    $unread
 */

$tabs = [
    ''         => 'همه',
    'new'      => 'جدید',
    'read'     => 'خوانده‌شده',
    'archived' => 'بایگانی',
];
$base = admin_url('messages');
?>
<div class="seg-tabs">
    <?php foreach ($tabs as $key => $label): ?>
        <a href="<?= e($base . ($key === '' ? '' : '?status=' . $key)) ?>"
           class="<?= (string) $status === (string) $key ? 'is-active' : '' ?>"
           <?= (string) $status === (string) $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<section class="card">
    <div class="card__head">
        <div>
            <h2>پیام‌های تماس</h2>
            <p>پیام‌های فرم «تماس با ما». پس از بررسی، وضعیت را به خوانده‌شده یا بایگانی تغییر بده.</p>
        </div>
        <span class="badge badge--info"><?= fa_digits((string) $unread) ?> پیام جدید</span>
    </div>
    <div class="card__body card__body--flush">
        <?php if ($messages === []): ?>
            <div class="empty">
                <span class="empty__mark" aria-hidden="true">✉️</span>
                <h3>پیامی نیست</h3>
                <p>پیام‌های فرم تماس با ما اینجا نمایش داده می‌شوند.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>فرستنده</th>
                            <th>موضوع</th>
                            <th>متن</th>
                            <th>تاریخ</th>
                            <th>وضعیت</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                            <?php $current = (string) $message['status']; ?>
                            <tr>
                                <td data-label="فرستنده" class="cell-wrap">
                                    <strong><?= e((string) $message['name']) ?></strong>
                                    <br>
                                    <span class="mono small" dir="ltr"><?= e((string) $message['contact']) ?></span>
                                </td>
                                <td data-label="موضوع"><?= e((string) ($message['subject'] ?: '—')) ?></td>
                                <td data-label="متن" class="cell-wrap" style="max-width:420px">
                                    <details>
                                        <summary class="small muted">
                                            <?= e(mb_substr((string) $message['message'], 0, 80)) ?><?= mb_strlen((string) $message['message']) > 80 ? '…' : '' ?>
                                        </summary>
                                        <p class="small" style="margin:8px 0 0;white-space:pre-wrap"><?= e((string) $message['message']) ?></p>
                                    </details>
                                </td>
                                <td data-label="تاریخ" class="small muted"><?= e(jalali_date((string) $message['created_at'], true)) ?></td>
                                <td data-label="وضعیت"><?= status_badge($current) ?></td>
                                <td data-label="">
                                    <div class="cell-actions">
                                        <?php if ($current !== 'read'): ?>
                                            <form method="post" action="<?= e(admin_url('messages/' . (int) $message['id'] . '/status')) ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="read">
                                                <button class="m3-button m3-button--text m3-button--sm" type="submit">خوانده شد</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($current !== 'archived'): ?>
                                            <form method="post" action="<?= e(admin_url('messages/' . (int) $message['id'] . '/status')) ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="archived">
                                                <button class="m3-button m3-button--text m3-button--sm" type="submit">بایگانی</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="<?= e(admin_url('messages/' . (int) $message['id'] . '/status')) ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="new">
                                                <button class="m3-button m3-button--text m3-button--sm" type="submit">بازگردانی</button>
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
    <div class="card__foot">
        <span class="small muted">حداکثر ۱۰۰ پیام اخیر نمایش داده می‌شود.</span>
    </div>
</section>
