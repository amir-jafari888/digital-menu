<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Tenant;

/**
 * Shared plumbing for the server-rendered admin panel.
 *
 * Tenant scoping lives here: a normal user is always pinned to their own
 * tenant, and a super admin must explicitly "enter" a tenant. No controller
 * ever trusts a tenant id coming from the request.
 */
abstract class BaseAdminController
{
    protected const PER_PAGE = 20;

    protected static function render(string $view, array $data, string $title, string $active, string $actions = ''): void
    {
        View::render('admin/' . $view, array_merge($data, [
            'pageTitle'   => $title,
            'active'      => $active,
            'pageActions' => $actions,
        ]), 'app');
    }

    protected static function tenantId(bool $required = true): ?int
    {
        $id = Auth::tenantId();

        if ($id === null && Auth::isSuperAdmin()) {
            $acting = (int) Session::get('acting_tenant', 0);
            $id = $acting > 0 ? $acting : null;
        }

        if ($id === null && $required) {
            Session::flash('error', 'برای مدیریت منو، محصولات و سفارش‌ها اول یک کسب‌وکار را انتخاب کن.');
            Response::redirect(admin_url('tenants'));
        }

        return $id;
    }

    /** @return array<string, mixed> */
    protected static function tenant(): array
    {
        $tenant = (new Tenant())->find((int) self::tenantId());
        if ($tenant === null) {
            Session::flash('error', 'کسب‌وکار انتخابی پیدا نشد.');
            Response::redirect(admin_url('tenants'));
        }

        return $tenant;
    }

    protected static function flashOk(string $message): void
    {
        Session::flash('success', $message);
    }

    protected static function flashError(string $message): void
    {
        Session::flash('error', $message);
    }

    protected static function back(string $fallback): never
    {
        Response::redirect($fallback);
    }

    /** @return array{page:int, perPage:int, offset:int, pages:int} */
    protected static function pagination(int $total, int $perPage = self::PER_PAGE): array
    {
        $perPage = max(1, $perPage);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = max(1, min($pages, (int) Request::query('page', '1') ?: 1));

        return [
            'page'    => $page,
            'perPage' => $perPage,
            'offset'  => ($page - 1) * $perPage,
            'pages'   => $pages,
        ];
    }

    /** Log an action with the acting user and the resolved tenant. */
    protected static function audit(string $action, ?string $entity = null, ?int $entityId = null, array $meta = []): void
    {
        \App\Models\AuditLog::record(
            $action,
            Auth::id() ?: null,
            self::tenantId(false),
            $meta,
            $entity,
            $entityId
        );
    }
}
