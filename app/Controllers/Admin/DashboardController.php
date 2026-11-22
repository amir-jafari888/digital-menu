<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Reward;
use App\Models\Subscription;

final class DashboardController extends BaseAdminController
{
    public static function index(): void
    {
        $tenantId = self::tenantId(false);

        if ($tenantId === null) {
            if (!Auth::isSuperAdmin()) {
                self::flashError('حساب شما به کسب‌وکاری متصل نیست.');
                \App\Core\Response::redirect(url(''));
            }

            self::platform();

            return;
        }

        $tenant  = self::tenant();
        $orders  = new Order();
        $stats   = $orders->todayStats($tenantId);
        $counts  = $orders->statusCounts($tenantId);
        $revenue = $orders->revenueByDay($tenantId, 7);

        $totals = Database::one(
            'SELECT
                (SELECT COUNT(*) FROM customers WHERE tenant_id = ?) AS customers,
                (SELECT COALESCE(SUM(points_balance),0) FROM customers WHERE tenant_id = ?) AS points,
                (SELECT COUNT(*) FROM products WHERE tenant_id = ? AND is_available = 1) AS products,
                (SELECT COUNT(*) FROM ad_campaigns WHERE tenant_id = ? AND status = \'active\') AS campaigns
             ',
            [$tenantId, $tenantId, $tenantId, $tenantId]
        ) ?? [];

        $recent = $orders->forTenant($tenantId, [], 8);
        $topCustomers = (new Customer())->topByPoints($tenantId, 5);
        $subscription = Subscription::currentForTenant($tenantId);
        $activeRewards = count((new Reward())->forTenant($tenantId, true));
        $maxRevenue = max(1, ...array_map(static fn (array $day): int => $day['revenue'], $revenue));

        self::render('dashboard', [
            'tenant'        => $tenant,
            'stats'         => $stats,
            'counts'        => $counts,
            'revenue'       => $revenue,
            'maxRevenue'    => $maxRevenue,
            'totals'        => $totals,
            'recent'        => $recent,
            'topCustomers'  => $topCustomers,
            'subscription'  => $subscription,
            'activeRewards' => $activeRewards,
            'openOrders'    => $counts['pending'] + $counts['confirmed'] + $counts['preparing'] + $counts['ready'],
        ], 'داشبورد', 'dashboard');
    }

    private static function platform(): void
    {
        $stats = Database::one(
            'SELECT
                (SELECT COUNT(*) FROM tenants) AS tenants,
                (SELECT COUNT(*) FROM tenants WHERE status = \'active\') AS active_tenants,
                (SELECT COUNT(*) FROM users) AS users,
                (SELECT COUNT(*) FROM orders) AS orders,
                (SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> \'canceled\') AS revenue,
                (SELECT COUNT(*) FROM contact_messages WHERE status = \'new\') AS new_messages
             '
        ) ?? [];

        $tenants = (new \App\Models\Tenant())->allWithPlan();
        $subscriptions = Subscription::allWithTenant(8);
        $logs = AuditLog::recent(null, 10);

        self::render('platform-dashboard', [
            'stats'         => $stats,
            'tenants'       => array_slice($tenants, 0, 6),
            'subscriptions' => $subscriptions,
            'logs'          => $logs,
            'newMessages'   => (new ContactMessage())->unreadCount(),
        ], 'داشبورد پلتفرم', 'dashboard', '<a class="m3-button m3-button--filled m3-button--sm" href="' . e(admin_url('tenants')) . '">مدیریت کسب‌وکارها</a>');
    }
}
