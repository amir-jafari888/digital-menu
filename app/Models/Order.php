<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Order extends Model
{
    protected string $table = 'orders';
    protected array $fillable = [
        'tenant_id', 'menu_id', 'table_id', 'customer_id', 'code', 'type',
        'status', 'subtotal', 'discount', 'total', 'points_earned', 'note',
        'placed_at', 'completed_at',
    ];

    public const STATUSES = ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'canceled'];

    public const STATUS_LABELS = [
        'pending'   => 'در انتظار تأیید',
        'confirmed' => 'تأیید شده',
        'preparing' => 'در حال آماده‌سازی',
        'ready'     => 'آماده تحویل',
        'completed' => 'تکمیل شده',
        'canceled'  => 'لغو شده',
    ];

    public const TYPE_LABELS = [
        'dine_in'  => 'حضوری',
        'takeaway' => 'بیرون‌بر',
        'delivery' => 'ارسال',
    ];

    /** @return array<int, array<string, mixed>> */
    public function forTenant(int $tenantId, array $filters = [], int $limit = 50, int $offset = 0): array
    {
        [$where, $params] = $this->filterSql($tenantId, $filters);
        $limit  = max(1, min(200, $limit));
        $offset = max(0, $offset);

        return Database::all(
            'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone,
                    t.label AS table_label,
                    (SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id) AS items_count
             FROM orders o
             LEFT JOIN customers c ON c.id = o.customer_id
             LEFT JOIN dining_tables t ON t.id = o.table_id
             WHERE ' . $where . '
             ORDER BY o.placed_at DESC, o.id DESC
             LIMIT ' . $limit . ' OFFSET ' . $offset,
            $params
        );
    }

    public function countForTenant(int $tenantId, array $filters = []): int
    {
        [$where, $params] = $this->filterSql($tenantId, $filters);

        return (int) Database::scalar('SELECT COUNT(*) FROM orders o WHERE ' . $where, $params);
    }

    /** @return array<string, mixed>|null */
    public function findForTenant(int $orderId, int $tenantId): ?array
    {
        return Database::one(
            'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone,
                    t.label AS table_label, m.name AS menu_name
             FROM orders o
             LEFT JOIN customers c ON c.id = o.customer_id
             LEFT JOIN dining_tables t ON t.id = o.table_id
             LEFT JOIN menus m ON m.id = o.menu_id
             WHERE o.id = ? AND o.tenant_id = ? LIMIT 1',
            [$orderId, $tenantId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function items(int $orderId): array
    {
        return Database::all(
            'SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC',
            [$orderId]
        );
    }

    /** @return array<string, int> */
    public function statusCounts(int $tenantId): array
    {
        $rows = Database::all(
            'SELECT status, COUNT(*) AS total FROM orders WHERE tenant_id = ? GROUP BY status',
            [$tenantId]
        );

        $counts = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $row) {
            $counts[(string) $row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    /** @return array<string, mixed> */
    public function todayStats(int $tenantId): array
    {
        $row = Database::one(
            'SELECT COUNT(*) AS orders,
                    COALESCE(SUM(CASE WHEN status <> \'canceled\' THEN total ELSE 0 END), 0) AS revenue,
                    COALESCE(SUM(CASE WHEN status <> \'canceled\' THEN 1 ELSE 0 END), 0) AS paid_orders
             FROM orders WHERE tenant_id = ? AND DATE(placed_at) = CURDATE()',
            [$tenantId]
        );

        return [
            'orders'  => (int) ($row['orders'] ?? 0),
            'revenue' => (int) ($row['revenue'] ?? 0),
            'paid'    => (int) ($row['paid_orders'] ?? 0),
        ];
    }

    /** @return array<int, array{day:string, revenue:int, orders:int}> */
    public function revenueByDay(int $tenantId, int $days = 7): array
    {
        $days = max(1, min(30, $days));
        $rows = Database::all(
            'SELECT DATE(placed_at) AS day,
                    COALESCE(SUM(CASE WHEN status <> \'canceled\' THEN total ELSE 0 END), 0) AS revenue,
                    COUNT(*) AS orders
             FROM orders
             WHERE tenant_id = ? AND placed_at >= CURDATE() - INTERVAL ' . ($days - 1) . ' DAY
             GROUP BY DATE(placed_at) ORDER BY day ASC',
            [$tenantId]
        );

        $byDay = [];
        foreach ($rows as $row) {
            $byDay[(string) $row['day']] = [
                'day'     => (string) $row['day'],
                'revenue' => (int) $row['revenue'],
                'orders'  => (int) $row['orders'],
            ];
        }

        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime('-' . $i . ' days'));
            $out[] = $byDay[$day] ?? ['day' => $day, 'revenue' => 0, 'orders' => 0];
        }

        return $out;
    }

    public function openCountForTable(int $tableId): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM orders WHERE table_id = ?
             AND status IN (\'pending\',\'confirmed\',\'preparing\',\'ready\')',
            [$tableId]
        );
    }

    public function belongsToTenant(int $orderId, int $tenantId): bool
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM orders WHERE id = ? AND tenant_id = ?',
            [$orderId, $tenantId]
        ) > 0;
    }

    /**
     * @param array{status?:string, search?:string, from?:string, to?:string} $filters
     * @return array{0:string, 1:array<int,mixed>}
     */
    private function filterSql(int $tenantId, array $filters): array
    {
        $where  = 'o.tenant_id = ?';
        $params = [$tenantId];

        if (!empty($filters['status']) && in_array($filters['status'], self::STATUSES, true)) {
            $where .= ' AND o.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], (string) $filters['search']) . '%';
            $where .= ' AND (o.code LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($filters['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['from'])) {
            $where .= ' AND o.placed_at >= ?';
            $params[] = $filters['from'] . ' 00:00:00';
        }

        if (!empty($filters['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['to'])) {
            $where .= ' AND o.placed_at <= ?';
            $params[] = $filters['to'] . ' 23:59:59';
        }

        return [$where, $params];
    }
}
