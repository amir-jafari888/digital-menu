<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Code;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Validator;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Product;
use Throwable;

/**
 * Order placement and lifecycle.
 *
 * Prices, names and availability ALWAYS come from the database. The client only
 * chooses product ids and quantities, so a tampered request cannot change the
 * amount charged. Everything runs in one transaction.
 */
final class OrderService
{
    private const MAX_LINES = 40;
    private const MAX_QTY_PER_LINE = 20;
    private const MAX_TOTAL_QTY = 60;

    /**
     * @param array<string, mixed> $input  items[], type, note, customer_phone, customer_name
     * @return array{ok: bool, order?: array, error?: string}
     */
    public static function place(
        int $tenantId,
        array $input,
        ?int $menuId = null,
        ?int $tableId = null,
        ?int $actorUserId = null
    ): array {
        $rawItems = $input['items'] ?? [];
        if (!is_array($rawItems) || $rawItems === []) {
            return ['ok' => false, 'error' => 'سبد خرید خالی است.'];
        }
        if (count($rawItems) > self::MAX_LINES) {
            return ['ok' => false, 'error' => 'تعداد اقلام سفارش بیش از حد مجاز است.'];
        }

        $wanted = [];
        foreach ($rawItems as $line) {
            if (!is_array($line)) {
                continue;
            }
            $id  = (int) ($line['id'] ?? $line['product_id'] ?? 0);
            $qty = (int) ($line['qty'] ?? $line['quantity'] ?? 0);
            if ($id <= 0 || $qty <= 0) {
                continue;
            }
            $qty = min($qty, self::MAX_QTY_PER_LINE);
            $wanted[$id] = ($wanted[$id] ?? 0) + $qty;
        }

        if ($wanted === []) {
            return ['ok' => false, 'error' => 'هیچ قلم معتبری در سبد خرید نیست.'];
        }
        if (array_sum($wanted) > self::MAX_TOTAL_QTY) {
            return ['ok' => false, 'error' => 'تعداد کل اقلام سفارش بیش از حد مجاز است.'];
        }

        if ($menuId !== null && !(new Menu())->belongsToTenant($menuId, $tenantId)) {
            return ['ok' => false, 'error' => 'منوی انتخابی معتبر نیست.'];
        }

        if ($tableId !== null) {
            $table = Database::one(
                'SELECT id FROM dining_tables WHERE id = ? AND tenant_id = ? AND is_active = 1 LIMIT 1',
                [$tableId, $tenantId]
            );
            if ($table === null) {
                return ['ok' => false, 'error' => 'میز انتخابی معتبر نیست.'];
            }

            $maxOpen = max(1, (int) Database::scalar(
                'SELECT `value` FROM settings WHERE `key` = \'max_open_orders_per_table\' LIMIT 1'
            ) ?: 5);
            if ((new Order())->openCountForTable($tableId) >= $maxOpen) {
                return ['ok' => false, 'error' => 'برای این میز چند سفارش باز ثبت شده است. کمی بعد دوباره تلاش کن.'];
            }
        }

        $products = (new Product())->findManyForTenant(array_keys($wanted), $tenantId);
        if ($products === []) {
            return ['ok' => false, 'error' => 'محصولات سبد خرید در دسترس نیستند.'];
        }

        $type = (string) ($input['type'] ?? 'dine_in');
        if (!in_array($type, ['dine_in', 'takeaway', 'delivery'], true)) {
            $type = 'dine_in';
        }

        $note  = Validator::cleanText((string) ($input['note'] ?? ''));
        $note  = mb_substr($note, 0, 500);
        $phone = trim((string) ($input['customer_phone'] ?? ''));
        $name  = Validator::cleanText((string) ($input['customer_name'] ?? ''));

        $lines    = [];
        $subtotal = 0;
        foreach ($wanted as $productId => $qty) {
            $product = $products[$productId] ?? null;
            if ($product === null || (int) $product['is_available'] !== 1) {
                continue;
            }
            $unit = (int) $product['price'];
            $total = $unit * $qty;
            $subtotal += $total;
            $lines[] = [
                'product_id' => $productId,
                'name'       => (string) $product['name'],
                'unit_price' => $unit,
                'quantity'   => $qty,
                'total'      => $total,
            ];
        }

        if ($lines === []) {
            return ['ok' => false, 'error' => 'همهٔ اقلام سبد خرید ناموجود شده‌اند.'];
        }

        $customerId = null;
        if ($phone !== '') {
            $validated = Validator::validate(
                ['phone' => $phone, 'name' => $name],
                ['phone' => 'required|phone', 'name' => 'nullable|max:120'],
                ['phone' => 'شماره موبایل', 'name' => 'نام']
            );
            if (!$validated['valid']) {
                return ['ok' => false, 'error' => $validated['errors']['phone'] ?? 'شماره موبایل معتبر نیست.'];
            }
            $customer   = (new Customer())->findOrCreate($tenantId, (string) $validated['data']['phone'], $name);
            $customerId = (int) $customer['id'];
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $code = self::uniqueOrderCode();
            $orderId = Database::insert(
                'INSERT INTO orders
                    (tenant_id, menu_id, table_id, customer_id, code, type, status,
                     subtotal, discount, total, points_earned, note, placed_at)
                 VALUES (?, ?, ?, ?, ?, ?, \'pending\', ?, 0, ?, 0, ?, NOW())',
                [$tenantId, $menuId, $tableId, $customerId, $code, $type, $subtotal, $subtotal, $note]
            );

            foreach ($lines as $line) {
                Database::run(
                    'INSERT INTO order_items (order_id, product_id, name, unit_price, quantity, total)
                     VALUES (?, ?, ?, ?, ?, ?)',
                    [$orderId, $line['product_id'], $line['name'], $line['unit_price'], $line['quantity'], $line['total']]
                );
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            Logger::error('Order placement failed', ['message' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'ثبت سفارش انجام نشد. دوباره تلاش کن.'];
        }

        if ($actorUserId !== null) {
            \App\Models\AuditLog::record('order.created', $actorUserId, $tenantId, ['total' => $subtotal], 'order', $orderId);
        }

        $order = (new Order())->findForTenant($orderId, $tenantId);

        return ['ok' => true, 'order' => $order ?? ['id' => $orderId, 'code' => $code, 'total' => $subtotal]];
    }

    /**
     * Move an order through the allowed state machine.
     * @return array{ok: bool, error?: string}
     */
    public static function updateStatus(int $orderId, int $tenantId, string $status, int $actorUserId): array
    {
        $order = (new Order())->findForTenant($orderId, $tenantId);
        if ($order === null) {
            return ['ok' => false, 'error' => 'سفارش پیدا نشد.'];
        }
        if (!in_array($status, Order::STATUSES, true)) {
            return ['ok' => false, 'error' => 'وضعیت نامعتبر است.'];
        }

        $current = (string) $order['status'];
        $allowed = [
            'pending'   => ['confirmed', 'canceled'],
            'confirmed' => ['preparing', 'canceled'],
            'preparing' => ['ready', 'canceled'],
            'ready'     => ['completed', 'canceled'],
            'completed' => [],
            'canceled'  => [],
        ];

        if ($status === $current) {
            return ['ok' => true];
        }
        if (!in_array($status, $allowed[$current] ?? [], true)) {
            return ['ok' => false, 'error' => 'تغییر وضعیت از «' . (Order::STATUS_LABELS[$current] ?? $current) . '» به این وضعیت مجاز نیست.'];
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $affected = Database::run(
                'UPDATE orders SET status = ?, completed_at = ? WHERE id = ? AND tenant_id = ? AND status = ?',
                [$status, $status === 'completed' ? date('Y-m-d H:i:s') : null, $orderId, $tenantId, $current]
            )->rowCount();

            if ($affected === 0) {
                $pdo->rollBack();

                return ['ok' => false, 'error' => 'این سفارش هم‌زمان توسط کاربر دیگری تغییر کرد. صفحه را تازه کن.'];
            }

            $points = 0;
            $customerId = (int) ($order['customer_id'] ?? 0);
            if ($status === 'completed' && $customerId > 0 && (int) $order['points_earned'] === 0) {
                $points = LoyaltyService::awardForOrder(
                    $tenantId,
                    $customerId,
                    $orderId,
                    (int) $order['total']
                );
                if ($points > 0) {
                    Database::run('UPDATE orders SET points_earned = ? WHERE id = ?', [$points, $orderId]);
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            Logger::error('Order status update failed', ['message' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'تغییر وضعیت ثبت نشد.'];
        }

        \App\Models\AuditLog::record(
            'order.status',
            $actorUserId,
            $tenantId,
            ['from' => $current, 'to' => $status],
            'order',
            $orderId
        );

        return ['ok' => true];
    }

    /** Best-effort uniqueness; the column has a UNIQUE index as the real guard. */
    private static function uniqueOrderCode(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $code = Code::reference('MN-', 8);
            if ((int) Database::scalar('SELECT COUNT(*) FROM orders WHERE code = ?', [$code]) === 0) {
                return $code;
            }
        }

        return Code::reference('MN-', 12);
    }
}
