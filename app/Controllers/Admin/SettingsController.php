<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\UploadedFile;
use App\Models\User;
use App\Services\UploadService;

final class SettingsController extends BaseAdminController
{
    public static function index(): void
    {
        // A super admin without an acting tenant still needs the profile and
        // password sections (e.g. while the forced password change is pending).
        $tenantId = self::tenantId(false);
        $tenant   = $tenantId === null ? null : (new Tenant())->find($tenantId);

        self::render('settings/index', [
            'tenant'        => $tenant ?? [],
            'settings'      => Setting::all(),
            'subscription'  => $tenantId === null ? null : Subscription::currentForTenant($tenantId),
            'payments'      => $tenantId === null ? [] : Subscription::paymentsForTenant($tenantId, 5),
            'user'          => Auth::user(),
            'plans'         => Plan::all(),
        ], 'تنظیمات', 'settings');
    }

    public static function saveSite(): void
    {
        $tenantId = (int) self::tenantId();

        $validated = Validator::validate(Request::all(), [
            'site_name'                 => 'required|string|max:80',
            'support_email'             => 'required|email|max:190',
            'support_phone'             => 'nullable|string|max:20',
            'loyalty_points_per_10000'  => 'required|int|min:0|max:100',
            'max_open_orders_per_table' => 'required|int|min:1|max:50',
        ], [
            'site_name'                 => 'نام سایت',
            'support_email'             => 'ایمیل پشتیبانی',
            'support_phone'             => 'تلفن پشتیبانی',
            'loyalty_points_per_10000'  => 'امتیاز به ازای هر ۱۰ هزار تومان',
            'max_open_orders_per_table' => 'حداکثر سفارش باز هر میز',
        ]);

        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('settings'));
        }

        $data = $validated['data'];
        Setting::setMany([
            'site_name'                 => (string) $data['site_name'],
            'support_email'             => (string) $data['support_email'],
            'support_phone'             => (string) ($data['support_phone'] ?? ''),
            'loyalty_points_per_10000'  => (string) (int) $data['loyalty_points_per_10000'],
            'max_open_orders_per_table' => (string) (int) $data['max_open_orders_per_table'],
            'ads_enabled'               => Request::bool('ads_enabled') ? '1' : '0',
        ]);

        self::audit('settings.updated', 'settings', null, ['scope' => 'site']);
        self::flashOk('تنظیمات ذخیره شد.');
        self::back(admin_url('settings'));
    }

    public static function saveBusiness(): void
    {
        $tenantId = (int) self::tenantId();

        $validated = Validator::validate(Request::all(), [
            'name'        => 'required|string|max:120',
            'category'    => 'required|in:cafe,restaurant,bakery,dessert,other',
            'description' => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
        ], [
            'name'        => 'نام کسب‌وکار',
            'category'    => 'دسته',
            'description' => 'توضیح',
            'phone'       => 'تلفن',
            'address'     => 'آدرس',
        ]);

        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('settings'));
        }

        $data = $validated['data'];
        $data['description'] = Validator::cleanText((string) ($data['description'] ?? ''));
        $data['logo'] = self::handleLogo($tenantId, (string) (self::tenant()['logo'] ?? ''));
        if (Request::bool('remove_logo')) {
            $data['logo'] = null;
        }

        (new \App\Models\Tenant())->update($tenantId, $data);
        self::audit('tenant.updated', 'tenant', $tenantId);
        self::flashOk('اطلاعات کسب‌وکار ذخیره شد.');
        self::back(admin_url('settings'));
    }

    public static function saveProfile(): void
    {
        $user = Auth::user();
        if ($user === null) {
            self::back(url('login.php'));
        }

        $validated = Validator::validate(Request::all(), [
            'name'  => 'required|string|max:120',
            'email' => 'nullable|email|max:190',
            'phone' => 'nullable|phone|max:20',
        ], ['name' => 'نام', 'email' => 'ایمیل', 'phone' => 'شماره موبایل']);

        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('settings#profile'));
        }

        $data  = $validated['data'];
        $users = new User();

        if ($data['email'] !== null && $users->emailTaken((string) $data['email'], (int) $user['id'])) {
            self::flashError('این ایمیل قبلاً ثبت شده است.');
            self::back(admin_url('settings#profile'));
        }
        if ($data['phone'] !== null && $users->phoneTaken((string) $data['phone'], (int) $user['id'])) {
            self::flashError('این شماره موبایل قبلاً ثبت شده است.');
            self::back(admin_url('settings#profile'));
        }
        if ($data['email'] === null && $data['phone'] === null) {
            self::flashError('حداقل ایمیل یا شماره موبایل را وارد کن.');
            self::back(admin_url('settings#profile'));
        }

        $users->update((int) $user['id'], [
            'name'  => (string) $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        self::audit('profile.updated', 'user', (int) $user['id']);
        self::flashOk('پروفایل ذخیره شد.');
        self::back(admin_url('settings#profile'));
    }

    public static function savePassword(): void
    {
        $user = Auth::user();
        if ($user === null) {
            self::back(url('login.php'));
        }

        $current = (string) Request::input('current_password', '');
        $new     = (string) Request::input('new_password', '');
        $confirm = (string) Request::input('confirm_password', '');

        if (!password_verify($current, (string) $user['password_hash'])) {
            self::flashError('رمز فعلی درست نیست.');
            self::back(admin_url('settings#password'));
        }
        if (mb_strlen($new) < 8) {
            self::flashError('رمز جدید باید حداقل ۸ کاراکتر باشد.');
            self::back(admin_url('settings#password'));
        }
        if (!preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) {
            self::flashError('رمز جدید باید هم حرف و هم رقم داشته باشد.');
            self::back(admin_url('settings#password'));
        }
        if (!hash_equals($new, $confirm)) {
            self::flashError('تکرار رمز جدید مطابقت ندارد.');
            self::back(admin_url('settings#password'));
        }

        (new User())->setPassword((int) $user['id'], $new, false);
        self::audit('auth.password_changed', 'user', (int) $user['id']);

        Session::regenerate();
        Csrf::rotate();

        self::flashOk('رمز عبور تغییر کرد.');
        self::back(admin_url('settings#password'));
    }

    // ------------------------------------------------------------------- users
    public static function users(): void
    {
        Auth::requireAbility('users.view');
        $tenantId = (int) self::tenantId();

        self::render('users/index', [
            'users' => (new User())->forTenant(Auth::isSuperAdmin() ? null : $tenantId),
        ], 'کاربران', 'users');
    }

    public static function userStore(): void
    {
        Auth::requireAbility('users.manage');

        $validated = Validator::validate(Request::all(), [
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|max:190',
            'phone'    => 'nullable|phone|max:20',
            'password' => 'required|string|min:8|max:100',
            'role'     => 'required|in:owner,staff',
        ], [
            'name'     => 'نام',
            'email'    => 'ایمیل',
            'phone'    => 'شماره موبایل',
            'password' => 'رمز عبور',
            'role'     => 'نقش',
        ]);

        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('users'));
        }

        $data  = $validated['data'];
        $users = new User();

        if ($users->emailTaken((string) $data['email'])) {
            self::flashError('این ایمیل قبلاً ثبت شده است.');
            self::back(admin_url('users'));
        }
        if ($data['phone'] !== null && $users->phoneTaken((string) $data['phone'])) {
            self::flashError('این شماره موبایل قبلاً ثبت شده است.');
            self::back(admin_url('users'));
        }
        if (!Auth::isSuperAdmin() && $data['role'] !== 'staff') {
            self::flashError('فقط مدیر پلتفرم می‌تواند کاربر مالک بسازد.');
            self::back(admin_url('users'));
        }
        if (!preg_match('/[A-Za-z]/', (string) $data['password']) || !preg_match('/\d/', (string) $data['password'])) {
            self::flashError('رمز عبور باید هم حرف و هم رقم داشته باشد.');
            self::back(admin_url('users'));
        }

        $id = $users->create([
            'tenant_id'            => Auth::isSuperAdmin() ? self::tenantId(false) : Auth::tenantId(),
            'name'                 => (string) $data['name'],
            'email'                => (string) $data['email'],
            'phone'                => $data['phone'],
            'password_hash'        => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'role'                 => (string) $data['role'],
            'status'               => 'active',
            'must_change_password' => 1,
            'email_verified_at'    => date('Y-m-d H:i:s'),
        ]);

        self::audit('user.created', 'user', $id, ['role' => $data['role']]);
        self::flashOk('کاربر اضافه شد. رمز موقت را به او بده تا تغییرش دهد.');
        self::back(admin_url('users'));
    }

    public static function userUpdate(array $params): void
    {
        Auth::requireAbility('users.manage');

        $id    = (int) $params['id'];
        $users = new User();
        $user  = $users->find($id);

        if ($user === null) {
            self::flashError('کاربر پیدا نشد.');
            self::back(admin_url('users'));
        }
        if (!Auth::isSuperAdmin() && (int) ($user['tenant_id'] ?? 0) !== (int) Auth::tenantId()) {
            self::flashError('این کاربر مال کسب‌وکار تو نیست.');
            self::back(admin_url('users'));
        }

        $validated = Validator::validate(Request::all(), [
            'name'   => 'required|string|max:120',
            'role'   => 'required|in:owner,staff',
            'status' => 'required|in:active,disabled',
        ], ['name' => 'نام', 'role' => 'نقش', 'status' => 'وضعیت']);

        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('users'));
        }

        if ($id === Auth::id() && $validated['data']['status'] !== 'active') {
            self::flashError('نمی‌توانی حساب خودت را غیرفعال کنی.');
            self::back(admin_url('users'));
        }
        if (!Auth::isSuperAdmin() && $validated['data']['role'] !== 'staff') {
            self::flashError('فقط مدیر پلتفرم می‌تواند نقش مالک تعیین کند.');
            self::back(admin_url('users'));
        }

        $users->update($id, [
            'name'   => (string) $validated['data']['name'],
            'role'   => (string) $validated['data']['role'],
            'status' => (string) $validated['data']['status'],
        ]);

        self::audit('user.updated', 'user', $id, ['role' => $validated['data']['role'], 'status' => $validated['data']['status']]);
        self::flashOk('کاربر به‌روز شد.');
        self::back(admin_url('users'));
    }

    public static function userPassword(array $params): void
    {
        Auth::requireAbility('users.manage');

        $id   = (int) $params['id'];
        $user = (new User())->find($id);

        if ($user === null) {
            self::flashError('کاربر پیدا نشد.');
            self::back(admin_url('users'));
        }
        if (!Auth::isSuperAdmin() && (int) ($user['tenant_id'] ?? 0) !== (int) Auth::tenantId()) {
            self::flashError('این کاربر مال کسب‌وکار تو نیست.');
            self::back(admin_url('users'));
        }

        $password = (string) Request::input('password', '');
        if (mb_strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            self::flashError('رمز جدید باید حداقل ۸ کاراکتر و شامل حرف و رقم باشد.');
            self::back(admin_url('users'));
        }

        (new User())->setPassword($id, $password, true);
        self::audit('user.password_reset', 'user', $id);
        self::flashOk('رمز موقت تنظیم شد. کاربر باید در ورود بعدی آن را تغییر دهد.');
        self::back(admin_url('users'));
    }

    private static function handleLogo(int $tenantId, string $current): ?string
    {
        $file = $_FILES['logo'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $current === '' ? null : $current;
        }

        $result = UploadService::store($file, $tenantId, Auth::id());
        if (!$result['ok']) {
            self::flashError((string) $result['error']);

            return $current === '' ? null : $current;
        }

        if ($current !== '' && preg_match('#^uploads/(\d+)\.#', $current, $m)) {
            (new UploadedFile())->deleteWithFile((int) $m[1]);
        }

        return (string) $result['path'];
    }
}
