<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Reward;
use App\Services\LoyaltyService;
use App\Services\OrderService;

final class SalesController extends BaseAdminController
{
    // ------------------------------------------------------------------ orders
    public static function orders(): void
    {
        $tenantId = (int) self::tenantId();

        $filters = [
            'status' => Request::query('status'),
            'search' => Request::query('q'),
            'from'   => Request::query('from'),
            'to'     => Request::query('to'),
        ];

        $orders = new Order();
        $total  = $orders->countForTenant($tenantId, $filters);
        $pager  = self::pagination($total);
        $rows   = $orders->forTenant($tenantId, $filters, $pager['perPage'], $pager['offset']);
        $counts = $orders->statusCounts($tenantId);

        $tabs = '<div class="seg-tabs">';
        $tabs .= '<a class="' . ($filters['status'] === '' ? 'is-active' : '') . '" href="' . e(admin_url('orders')) . '">همه</a>';
        foreach (Order::STATUS_LABELS as $status => $label) {
            $tabs .= '<a class="' . ($filters['status'] === $status ? 'is-active' : '') . '" href="' . e(admin_url('orders?status=' . $status)) . '">'
                . e($label) . ' (' . fa_digits((string) ($counts[$status] ?? 0)) . ')</a>';
        }
        $tabs .= '</div>';

        self::render('orders/index', [
            'orders'   => $rows,
            'filters'  => $filters,
            'pager'    => $pager,
            'total'    => $total,
            'tabs'     => $tabs,
            'baseUrl'  => admin_url('orders'),
        ], 'سفارش‌ها', 'orders');
    }

    public static function orderShow(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $orders   = new Order();
        $order    = $orders->findForTenant((int) $params['id'], $tenantId);

        if ($order === null) {
            self::flashError('سفارش پیدا نشد.');
            self::back(admin_url('orders'));
        }

        $items = $orders->items((int) $order['id']);

        self::render('orders/show', [
            'order' => $order,
            'items' => $items,
            'nextStatuses' => self::nextStatuses((string) $order['status']),
        ], 'سفارش ' . $order['code'], 'orders');
    }

    public static function orderStatus(array $params): void
    {
        Auth::requireAbility('orders.update');

        $tenantId = (int) self::tenantId();
        $status   = Request::string('status');

        $result = OrderService::updateStatus((int) $params['id'], $tenantId, $status, Auth::id());

        if (!$result['ok']) {
            self::flashError((string) ($result['error'] ?? 'تغییر وضعیت انجام نشد.'));
        } else {
            self::flashOk('وضعیت سفارش به «' . (Order::STATUS_LABELS[$status] ?? $status) . '» تغییر کرد.');
        }

        self::back(admin_url('orders/' . (int) $params['id']));
    }

    // --------------------------------------------------------------- customers
    public static function customers(): void
    {
        $tenantId = (int) self::tenantId();

        $filters = ['search' => Request::query('q')];
        $customers = new Customer();
        $total = $customers->countForTenant($tenantId, $filters);
        $pager = self::pagination($total);
        $rows  = $customers->forTenant($tenantId, $filters, $pager['perPage'], $pager['offset']);

        $summary = Database::one(
            'SELECT COUNT(*) AS customers,
                    COALESCE(SUM(points_balance), 0) AS points,
                    COALESCE(SUM(total_spent), 0) AS spent,
                    COALESCE(SUM(visits), 0) AS visits
             FROM customers WHERE tenant_id = ?',
            [$tenantId]
        ) ?? [];

        self::render('customers/index', [
            'customers' => $rows,
            'filters'   => $filters,
            'pager'     => $pager,
            'total'     => $total,
            'summary'   => $summary,
            'baseUrl'   => admin_url('customers'),
        ], 'مشتریان', 'customers');
    }

    public static function customerShow(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $customer = (new Customer())->findForTenant((int) $params['id'], $tenantId);

        if ($customer === null) {
            self::flashError('مشتری پیدا نشد.');
            self::back(admin_url('customers'));
        }

        $orders = Database::all(
            'SELECT id, code, status, total, placed_at FROM orders
             WHERE tenant_id = ? AND customer_id = ?
             ORDER BY id DESC LIMIT 20',
            [$tenantId, (int) $customer['id']]
        );

        $redemptions = Database::all(
            'SELECT r.*, rw.title AS reward_title FROM reward_redemptions r
             JOIN rewards rw ON rw.id = r.reward_id
             WHERE r.tenant_id = ? AND r.customer_id = ?
             ORDER BY r.id DESC LIMIT 20',
            [$tenantId, (int) $customer['id']]
        );

        self::render('customers/show', [
            'customer'    => $customer,
            'history'     => LoyaltyService::history($tenantId, (int) $customer['id'], 40),
            'orders'      => $orders,
            'redemptions' => $redemptions,
            'rewards'     => (new Reward())->forTenant($tenantId, true),
        ], 'مشتری: ' . ($customer['name'] !== '' ? $customer['name'] : $customer['phone']), 'customers');
    }

    public static function customerPoints(array $params): void
    {
        $tenantId = (int) self::tenantId();

        $validated = Validator::validate(Request::all(), [
            'points' => 'required|int|min:-100000|max:100000',
            'note'   => 'nullable|string|max:255',
        ], ['points' => 'امتیاز', 'note' => 'توضیح']);

        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('customers/' . (int) $params['id']));
        }

        $points = (int) $validated['data']['points'];
        if ($points === 0) {
            self::flashError('مقدار امتیاز نمی‌تواند صفر باشد.');
            self::back(admin_url('customers/' . (int) $params['id']));
        }

        $result = LoyaltyService::adjust(
            $tenantId,
            (int) $params['id'],
            $points,
            Auth::id(),
            Validator::cleanText((string) ($validated['data']['note'] ?? ''))
        );

        $result['ok']
            ? self::flashOk('امتیاز مشتری به‌روز شد. موجودی جدید: ' . fa_digits((string) $result['balance']))
            : self::flashError((string) ($result['error'] ?? 'ثبت امتیاز انجام نشد.'));

        self::back(admin_url('customers/' . (int) $params['id']));
    }

    public static function customerRedeem(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $rewardId = Request::int('reward_id');

        $result = LoyaltyService::redeem($tenantId, (int) $params['id'], $rewardId, Auth::id());

        if (!$result['ok']) {
            self::flashError((string) ($result['error'] ?? 'ثبت جایزه انجام نشد.'));
        } else {
            self::flashOk('جایزه ثبت شد. کد: ' . $result['code'] . ' — موجودی امتیاز: ' . fa_digits((string) $result['balance']));
        }

        self::back(admin_url('customers/' . (int) $params['id']));
    }

    public static function redemptionUsed(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $result = LoyaltyService::markRedemptionUsed((int) $params['id'], $tenantId, Auth::id());

        $result['ok']
            ? self::flashOk('کد جایزه استفاده‌شده ثبت شد.')
            : self::flashError((string) ($result['error'] ?? 'کد پیدا نشد.'));

        self::back(admin_url('customers'));
    }

    // ----------------------------------------------------------------- rewards
    public static function rewards(): void
    {
        $tenantId = (int) self::tenantId();

        $rewards = Database::all(
            'SELECT r.*,
                    (SELECT COUNT(*) FROM reward_redemptions x WHERE x.reward_id = r.id) AS redemptions
             FROM rewards r WHERE r.tenant_id = ? ORDER BY r.is_active DESC, r.points_cost ASC',
            [$tenantId]
        );

        self::render('rewards/index', [
            'rewards' => $rewards,
        ], 'جوایز باشگاه مشتریان', 'rewards');
    }

    public static function rewardStore(): void
    {
        $tenantId = (int) self::tenantId();
        $validated = self::rewardPayload();

        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('rewards'));
        }

        $data = $validated['data'];
        $data['tenant_id'] = $tenantId;
        $data['is_active'] = Request::bool('is_active') ? 1 : 0;

        $id = (new Reward())->create($data);
        self::audit('reward.created', 'reward', $id, ['title' => $data['title']]);
        self::flashOk('جایزه اضافه شد.');
        self::back(admin_url('rewards'));
    }

    public static function rewardUpdate(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $reward = (new Reward())->findForTenant((int) $params['id'], $tenantId);

        if ($reward === null) {
            self::flashError('جایزه پیدا نشد.');
            self::back(admin_url('rewards'));
        }

        $validated = self::rewardPayload();
        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('rewards'));
        }

        $data = $validated['data'];
        $data['is_active'] = Request::bool('is_active') ? 1 : 0;

        (new Reward())->update((int) $reward['id'], $data);
        self::audit('reward.updated', 'reward', (int) $reward['id']);
        self::flashOk('جایزه به‌روز شد.');
        self::back(admin_url('rewards'));
    }

    public static function rewardDelete(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $reward = (new Reward())->findForTenant((int) $params['id'], $tenantId);

        if ($reward === null) {
            self::flashError('جایزه پیدا نشد.');
            self::back(admin_url('rewards'));
        }

        $used = (int) Database::scalar('SELECT COUNT(*) FROM reward_redemptions WHERE reward_id = ?', [(int) $reward['id']]);
        if ($used > 0) {
            (new Reward())->update((int) $reward['id'], ['is_active' => 0]);
            self::flashOk('این جایزه سابقهٔ استفاده دارد؛ به‌جای حذف، غیرفعال شد.');
        } else {
            (new Reward())->delete((int) $reward['id']);
            self::flashOk('جایزه حذف شد.');
        }

        self::audit('reward.deleted', 'reward', (int) $reward['id']);
        self::back(admin_url('rewards'));
    }

    /** @return array{valid:bool, errors:array<string,string>, data:array<string,mixed>} */
    private static function rewardPayload(): array
    {
        $validated = Validator::validate(Request::all(), [
            'title'       => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
            'points_cost' => 'required|int|min:1|max:100000',
            'stock'       => 'nullable|int|min:0|max:100000',
        ], [
            'title'       => 'عنوان جایزه',
            'description' => 'توضیح',
            'points_cost' => 'امتیاز لازم',
            'stock'       => 'موجودی',
        ]);

        if ($validated['valid']) {
            $validated['data']['description'] = mb_substr(Validator::cleanText((string) ($validated['data']['description'] ?? '')), 0, 255);
        }

        return $validated;
    }

    /** @return array<int, string> */
    private static function nextStatuses(string $status): array
    {
        return match ($status) {
            'pending'   => ['confirmed', 'canceled'],
            'confirmed' => ['preparing', 'canceled'],
            'preparing' => ['ready', 'canceled'],
            'ready'     => ['completed', 'canceled'],
            default     => [],
        };
    }
}
