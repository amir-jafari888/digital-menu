<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;

final class PlatformController extends BaseAdminController
{
    public static function tenants(): void
    {
        Auth::requireRole(['super_admin']);

        self::render('platform/tenants', [
            'tenants'    => (new Tenant())->allWithPlan(),
            'plans'      => Plan::active(),
            'acting'     => (int) Session::get('acting_tenant', 0),
        ], 'کسب‌وکارها', 'tenants');
    }

    public static function tenantStore(): void
    {
        Auth::requireRole(['super_admin']);

        $validated = Validator::validate(Request::all(), [
            'name'        => 'required|string|max:120',
            'slug'        => 'nullable|string|max:60',
            'category'    => 'required|in:cafe,restaurant,bakery,dessert,other',
            'plan_id'     => 'required|int',
            'description' => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'owner_name'  => 'nullable|string|max:120',
            'owner_email' => 'nullable|email|max:190',
            'password'    => 'nullable|string|min:8|max:100',
        ], [
            'name'        => 'نام کسب‌وکار',
            'slug'        => 'نشانی',
            'category'    => 'دسته',
            'plan_id'     => 'پلن',
            'description' => 'توضیح',
            'phone'       => 'تلفن',
            'address'     => 'آدرس',
            'owner_name'  => 'نام مالک',
            'owner_email' => 'ایمیل مالک',
            'password'    => 'رمز مالک',
        ]);

        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('tenants'));
        }

        $data = $validated['data'];
        if (Plan::find((int) $data['plan_id']) === null) {
            self::flashError('پلن انتخابی معتبر نیست.');
            self::back(admin_url('tenants'));
        }

        $tenants = new Tenant();
        $slug = trim((string) ($data['slug'] ?? ''));
        $slug = $slug === '' ? Tenant::slugify((string) $data['name']) : Tenant::slugify($slug);
        if ($tenants->slugTaken($slug)) {
            $slug .= '-' . bin2hex(random_bytes(2));
        }

        $tenantId = $tenants->create([
            'name'        => (string) $data['name'],
            'slug'        => $slug,
            'category'    => (string) $data['category'],
            'description' => Validator::cleanText((string) ($data['description'] ?? '')),
            'phone'       => $data['phone'],
            'address'     => (string) ($data['address'] ?? ''),
            'status'      => 'pending',
            'plan_id'     => (int) $data['plan_id'],
            'trial_ends_at' => date('Y-m-d H:i:s', strtotime('+14 days')),
        ]);

        // Optional owner account, so the business can log in immediately.
        $ownerEmail = trim((string) ($data['owner_email'] ?? ''));
        if ($ownerEmail !== '') {
            $users = new User();
            if ($users->emailTaken($ownerEmail)) {
                self::flashError('کسب‌وکار ساخته شد، اما ایمیل مالک از قبل ثبت شده بود.');
                self::audit('tenant.created', 'tenant', $tenantId, ['slug' => $slug]);

                self::back(admin_url('tenants'));
            }

            $password = (string) ($data['password'] ?? '');
            if (mb_strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
                self::flashError('کسب‌وکار ساخته شد، اما رمز مالک معتبر نبود (حداقل ۸ کاراکتر، حرف و رقم).');
                self::audit('tenant.created', 'tenant', $tenantId, ['slug' => $slug]);

                self::back(admin_url('tenants'));
            }

            $users->create([
                'tenant_id'            => $tenantId,
                'name'                 => (string) ($data['owner_name'] ?: 'مالک کسب‌وکار'),
                'email'                => $ownerEmail,
                'phone'                => null,
                'password_hash'        => password_hash($password, PASSWORD_DEFAULT),
                'role'                 => 'owner',
                'status'               => 'active',
                'must_change_password' => 1,
                'email_verified_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        self::audit('tenant.created', 'tenant', $tenantId, ['slug' => $slug]);
        self::flashOk('کسب‌وکار ساخته شد.');
        self::back(admin_url('tenants'));
    }

    public static function tenantUpdate(array $params): void
    {
        Auth::requireRole(['super_admin']);

        $id = (int) $params['id'];
        $tenants = new Tenant();
        $tenant = $tenants->find($id);

        if ($tenant === null) {
            self::flashError('کسب‌وکار پیدا نشد.');
            self::back(admin_url('tenants'));
        }

        $validated = Validator::validate(Request::all(), [
            'name'     => 'required|string|max:120',
            'category' => 'required|in:cafe,restaurant,bakery,dessert,other',
            'plan_id'  => 'required|int',
            'status'   => 'required|in:pending,active,suspended',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string|max:255',
        ], [
            'name'     => 'نام',
            'category' => 'دسته',
            'plan_id'  => 'پلن',
            'status'   => 'وضعیت',
            'phone'    => 'تلفن',
            'address'  => 'آدرس',
        ]);

        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('tenants'));
        }

        $data = $validated['data'];
        if (Plan::find((int) $data['plan_id']) === null) {
            self::flashError('پلن انتخابی معتبر نیست.');
            self::back(admin_url('tenants'));
        }

        $tenants->update($id, [
            'name'     => (string) $data['name'],
            'category' => (string) $data['category'],
            'plan_id'  => (int) $data['plan_id'],
            'status'   => (string) $data['status'],
            'phone'    => $data['phone'],
            'address'  => (string) ($data['address'] ?? ''),
        ]);

        self::audit('tenant.updated', 'tenant', $id, ['status' => $data['status']]);
        self::flashOk('کسب‌وکار به‌روز شد.');
        self::back(admin_url('tenants'));
    }

    public static function tenantAct(array $params): void
    {
        Auth::requireRole(['super_admin']);

        $id = (int) $params['id'];
        if ((new Tenant())->find($id) === null) {
            self::flashError('کسب‌وکار پیدا نشد.');
            self::back(admin_url('tenants'));
        }

        Session::set('acting_tenant', $id);
        self::audit('tenant.act', 'tenant', $id);
        self::flashOk('وارد پنل کسب‌وکار شدی.');
        Response::redirect(admin_url());
    }

    public static function tenantExit(): void
    {
        Auth::requireRole(['super_admin']);

        Session::forget('acting_tenant');
        self::flashOk('از پنل کسب‌وکار خارج شدی.');
        Response::redirect(admin_url('tenants'));
    }

    public static function subscriptions(): void
    {
        Auth::requireRole(['super_admin']);

        self::render('platform/subscriptions', [
            'subscriptions' => Subscription::allWithTenant(100),
            'plans'         => Plan::all(),
        ], 'اشتراک‌ها و پرداخت‌ها', 'subscriptions');
    }

    public static function messages(): void
    {
        Auth::requireRole(['super_admin']);

        $status = Request::query('status');
        $messages = (new ContactMessage())->recent($status === '' ? null : $status, 100);

        self::render('platform/messages', [
            'messages' => $messages,
            'status'   => $status,
            'unread'   => (new ContactMessage())->unreadCount(),
        ], 'پیام‌های تماس', 'messages');
    }

    public static function messageStatus(array $params): void
    {
        Auth::requireRole(['super_admin']);

        $status = Request::string('status');
        if (!in_array($status, ['new', 'read', 'archived'], true)) {
            self::flashError('وضعیت نامعتبر است.');
            self::back(admin_url('messages'));
        }

        (new ContactMessage())->mark((int) $params['id'], $status);
        self::audit('message.status', 'message', (int) $params['id'], ['status' => $status]);
        self::flashOk('وضعیت پیام تغییر کرد.');
        self::back(admin_url('messages'));
    }

    public static function logs(): void
    {
        Auth::requireRole(['super_admin']);

        $page = max(1, (int) Request::query('page', '1'));
        $perPage = 40;
        $total = (int) Database::scalar('SELECT COUNT(*) FROM audit_logs');

        self::render('platform/logs', [
            'logs'    => AuditLog::recent(null, $perPage, ($page - 1) * $perPage),
            'page'    => $page,
            'pages'   => max(1, (int) ceil($total / $perPage)),
            'total'   => $total,
            'baseUrl' => admin_url('logs'),
        ], 'گزارش امنیتی', 'logs');
    }
}
