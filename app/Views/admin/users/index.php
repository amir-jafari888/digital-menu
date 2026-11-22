<?php
/**
 * @var array $users
 */
use App\Core\Auth;

$canManage = Auth::can('users.manage');
$isSuper   = Auth::isSuperAdmin();
?>
<section class="card">
    <div class="card__head">
        <div>
            <h2>کاربران پنل</h2>
            <p>هر کاربر با ایمیل یا شماره موبایل وارد می‌شود. رمز موقت بعد از اولین ورود باید تغییر کند.</p>
        </div>
        <span class="small muted"><?= fa_digits((string) count($users)) ?> کاربر</span>
    </div>
    <div class="card__body card__body--flush">
        <?php if ($users === []): ?>
            <div class="empty">
                <span class="empty__mark" aria-hidden="true">👤</span>
                <h3>کاربری ثبت نشده</h3>
                <p>اگر کارمند داری، از فرم پایین برایش حساب بساز.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>ایمیل / موبایل</th>
                            <th>نقش</th>
                            <th>کسب‌وکار</th>
                            <th>وضعیت</th>
                            <th>آخرین ورود</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $account): ?>
                            <tr>
                                <td data-label="نام" class="cell-wrap">
                                    <?= e((string) $account['name']) ?>
                                    <?php if ((int) $account['must_change_password'] === 1): ?>
                                        <span class="badge badge--warn">رمز موقت</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="ایمیل / موبایل" class="mono small">
                                    <?= e((string) ($account['email'] ?? '')) ?>
                                    <?php if (!empty($account['phone'])): ?>
                                        <br><?= e((string) $account['phone']) ?>
                                    <?php endif; ?>
                                </td>
                                <td data-label="نقش">
                                    <?= match ((string) $account['role']) {
                                        'super_admin' => 'مدیر پلتفرم',
                                        'owner'       => 'مالک',
                                        'staff'       => 'کارمند',
                                        default       => e((string) $account['role']),
                                    } ?>
                                </td>
                                <td data-label="کسب‌وکار"><?= e((string) ($account['tenant_name'] ?? '—')) ?></td>
                                <td data-label="وضعیت"><?= status_badge((string) $account['status']) ?></td>
                                <td data-label="آخرین ورود" class="small muted"><?= e(jalali_date($account['last_login_at'] ?? null, true)) ?></td>
                                <td data-label="">
                                    <?php if ($canManage && (string) $account['role'] !== 'super_admin'): ?>
                                        <details>
                                            <summary class="small muted">مدیریت</summary>
                                            <form method="post" action="<?= e(admin_url('users/' . (int) $account['id'])) ?>" style="margin-top:10px">
                                                <?= csrf_field() ?>
                                                <div class="stack" style="gap:8px;min-width:230px">
                                                    <input class="field__control" name="name" value="<?= e((string) $account['name']) ?>" maxlength="120" required aria-label="نام">
                                                    <select class="field__control" name="role" aria-label="نقش">
                                                        <option value="staff"<?= is_selected('staff', (string) $account['role']) ?>>کارمند</option>
                                                        <option value="owner"<?= is_selected('owner', (string) $account['role']) ?>>مالک</option>
                                                    </select>
                                                    <select class="field__control" name="status" aria-label="وضعیت">
                                                        <option value="active"<?= is_selected('active', (string) $account['status']) ?>>فعال</option>
                                                        <option value="disabled"<?= is_selected('disabled', (string) $account['status']) ?>>غیرفعال</option>
                                                    </select>
                                                    <button class="m3-button m3-button--filled m3-button--sm" type="submit">ذخیره</button>
                                                </div>
                                            </form>
                                            <form method="post" action="<?= e(admin_url('users/' . (int) $account['id'] . '/password')) ?>" style="margin-top:8px" data-confirm="رمز موقت جدید تنظیم شود؟">
                                                <?= csrf_field() ?>
                                                <div class="stack" style="gap:8px;min-width:230px">
                                                    <input class="field__control" name="password" type="text" autocomplete="off" minlength="8" maxlength="100" placeholder="رمز موقت جدید" required aria-label="رمز موقت جدید">
                                                    <button class="m3-button m3-button--tonal m3-button--sm" type="submit">تنظیم رمز موقت</button>
                                                </div>
                                            </form>
                                        </details>
                                    <?php else: ?>
                                        <span class="muted small">—</span>
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

<?php if ($canManage): ?>
    <section class="card">
        <div class="card__head">
            <div>
                <h2>کاربر جدید</h2>
                <p>کاربر جدید با رمز موقت ساخته می‌شود و در اولین ورود باید آن را تغییر دهد.</p>
            </div>
        </div>
        <form method="post" action="<?= e(admin_url('users')) ?>">
            <?= csrf_field() ?>
            <div class="card__body">
                <div class="form-grid">
                    <div class="field">
                        <label for="user-name">نام</label>
                        <input class="field__control" id="user-name" name="name" required maxlength="120">
                    </div>
                    <div class="field">
                        <label for="user-email">ایمیل</label>
                        <input class="field__control" id="user-email" name="email" type="email" dir="ltr" required maxlength="190">
                    </div>
                    <div class="field">
                        <label for="user-phone">شماره موبایل (اختیاری)</label>
                        <input class="field__control" id="user-phone" name="phone" dir="ltr" maxlength="20">
                    </div>
                    <div class="field">
                        <label for="user-role">نقش</label>
                        <select class="field__control" id="user-role" name="role">
                            <option value="staff">کارمند</option>
                            <?php if ($isSuper): ?>
                                <option value="owner">مالک</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="user-password">رمز موقت</label>
                        <input class="field__control" id="user-password" name="password" type="text" autocomplete="off" minlength="8" maxlength="100" required>
                        <span class="field__hint">حداقل ۸ کاراکتر، شامل حرف و رقم.</span>
                    </div>
                </div>
            </div>
            <div class="card__foot">
                <button class="m3-button m3-button--filled" type="submit">افزودن کاربر</button>
            </div>
        </form>
    </section>
<?php endif; ?>
