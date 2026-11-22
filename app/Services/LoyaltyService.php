<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Code;
use App\Core\Database;
use App\Core\Logger;
use App\Models\AuditLog;
use App\Models\Reward;
use App\Models\Setting;
use Throwable;

/**
 * Loyalty ledger.
 *
 * The customers.points_balance column is a cached total; loyalty_transactions
 * is the source of truth. Every balance change writes a ledger row inside the
 * same transaction and locks the customer row first, so two concurrent
 * redemptions cannot spend the same points twice.
 */
final class LoyaltyService
{
    /** Points earned when an order is completed (1 point per 10,000 Toman by default). */
    public static function awardForOrder(int $tenantId, int $customerId, int $orderId, int $total): int
    {
        $perUnit = max(0, Setting::int('loyalty_points_per_10000', 1));
        $points  = intdiv(max(0, $total), 10_000) * $perUnit;

        $pdo  = Database::pdo();
        $owns = !$pdo->inTransaction();

        try {
            if ($owns) {
                $pdo->beginTransaction();
            }

            $already = (int) Database::scalar(
                'SELECT COUNT(*) FROM loyalty_transactions
                 WHERE order_id = ? AND customer_id = ? AND reason = \'purchase\'',
                [$orderId, $customerId]
            );
            if ($already > 0) {
                if ($owns) {
                    $pdo->commit();
                }

                return 0;
            }

            $customer = Database::one(
                'SELECT id, points_balance FROM customers WHERE id = ? AND tenant_id = ? FOR UPDATE',
                [$customerId, $tenantId]
            );
            if ($customer === null) {
                if ($owns) {
                    $pdo->rollBack();
                }

                return 0;
            }

            $balance = (int) $customer['points_balance'] + $points;
            if ($points > 0) {
                Database::run(
                    'INSERT INTO loyalty_transactions
                        (tenant_id, customer_id, order_id, points, reason, balance_after, note)
                     VALUES (?, ?, ?, ?, \'purchase\', ?, ?)',
                    [$tenantId, $customerId, $orderId, $points, $balance, 'امتیاز سفارش #' . $orderId]
                );
            }

            Database::run(
                'UPDATE customers
                 SET points_balance = ?, total_spent = total_spent + ?, visits = visits + 1,
                     last_visit_at = NOW(), updated_at = NOW()
                 WHERE id = ? AND tenant_id = ?',
                [$balance, max(0, $total), $customerId, $tenantId]
            );

            if ($owns) {
                $pdo->commit();
            }

            return $points;
        } catch (Throwable $e) {
            if ($owns && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error('Loyalty award failed', ['message' => $e->getMessage()]);

            return 0;
        }
    }

    /**
     * Manual adjustment (positive or negative) by a staff member.
     * @return array{ok: bool, error?: string, balance?: int}
     */
    public static function adjust(int $tenantId, int $customerId, int $points, int $actorUserId, string $note = ''): array
    {
        if ($points === 0 || abs($points) > 100_000) {
            return ['ok' => false, 'error' => 'مقدار امتیاز معتبر نیست.'];
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $customer = Database::one(
                'SELECT id, points_balance FROM customers WHERE id = ? AND tenant_id = ? FOR UPDATE',
                [$customerId, $tenantId]
            );
            if ($customer === null) {
                $pdo->rollBack();

                return ['ok' => false, 'error' => 'مشتری پیدا نشد.'];
            }

            $balance = (int) $customer['points_balance'] + $points;
            if ($balance < 0) {
                $pdo->rollBack();

                return ['ok' => false, 'error' => 'امتیاز مشتری برای این کسر کافی نیست.'];
            }

            Database::run(
                'INSERT INTO loyalty_transactions
                    (tenant_id, customer_id, points, reason, balance_after, note, created_by)
                 VALUES (?, ?, ?, \'adjustment\', ?, ?, ?)',
                [$tenantId, $customerId, $points, $balance, mb_substr($note, 0, 255), $actorUserId]
            );
            Database::run(
                'UPDATE customers SET points_balance = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?',
                [$balance, $customerId, $tenantId]
            );

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            Logger::error('Loyalty adjustment failed', ['message' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'ثبت امتیاز انجام نشد.'];
        }

        AuditLog::record('loyalty.adjust', $actorUserId, $tenantId, ['points' => $points], 'customer', $customerId);

        return ['ok' => true, 'balance' => $balance];
    }

    /**
     * Spend points on a reward atomically.
     * @return array{ok: bool, error?: string, code?: string, balance?: int}
     */
    public static function redeem(int $tenantId, int $customerId, int $rewardId, ?int $actorUserId = null): array
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $customer = Database::one(
                'SELECT id, points_balance FROM customers WHERE id = ? AND tenant_id = ? FOR UPDATE',
                [$customerId, $tenantId]
            );
            if ($customer === null) {
                $pdo->rollBack();

                return ['ok' => false, 'error' => 'مشتری پیدا نشد.'];
            }

            $reward = Database::one(
                'SELECT * FROM rewards WHERE id = ? AND tenant_id = ? AND is_active = 1 FOR UPDATE',
                [$rewardId, $tenantId]
            );
            if ($reward === null) {
                $pdo->rollBack();

                return ['ok' => false, 'error' => 'این جایزه در دسترس نیست.'];
            }

            if ($reward['stock'] !== null && (int) $reward['stock'] <= 0) {
                $pdo->rollBack();

                return ['ok' => false, 'error' => 'موجودی این جایزه تمام شده است.'];
            }

            $cost    = (int) $reward['points_cost'];
            $balance = (int) $customer['points_balance'];

            if ($cost <= 0 || $balance < $cost) {
                $pdo->rollBack();

                return ['ok' => false, 'error' => 'امتیاز کافی برای دریافت این جایزه نداری.'];
            }

            $newBalance = $balance - $cost;

            if ($reward['stock'] !== null) {
                Database::run(
                    'UPDATE rewards SET stock = stock - 1 WHERE id = ? AND tenant_id = ? AND stock > 0',
                    [$rewardId, $tenantId]
                );
            }

            $code = self::uniqueRedemptionCode();
            Database::run(
                'INSERT INTO reward_redemptions (tenant_id, reward_id, customer_id, points_spent, code, status)
                 VALUES (?, ?, ?, ?, ?, \'pending\')',
                [$tenantId, $rewardId, $customerId, $cost, $code]
            );

            Database::run(
                'INSERT INTO loyalty_transactions
                    (tenant_id, customer_id, points, reason, balance_after, note, created_by)
                 VALUES (?, ?, ?, \'redemption\', ?, ?, ?)',
                [$tenantId, $customerId, -$cost, $newBalance, mb_substr((string) $reward['title'], 0, 255), $actorUserId]
            );

            Database::run(
                'UPDATE customers SET points_balance = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?',
                [$newBalance, $customerId, $tenantId]
            );

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            Logger::error('Reward redemption failed', ['message' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'ثبت جایزه انجام نشد.'];
        }

        AuditLog::record(
            'reward.redeemed',
            $actorUserId,
            $tenantId,
            ['reward_id' => $rewardId, 'code' => $code, 'points' => $cost],
            'customer',
            $customerId
        );

        return ['ok' => true, 'code' => $code, 'balance' => $newBalance];
    }

    /** @return array<int, array<string, mixed>> */
    public static function history(int $tenantId, int $customerId, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));

        return Database::all(
            'SELECT * FROM loyalty_transactions
             WHERE tenant_id = ? AND customer_id = ?
             ORDER BY id DESC LIMIT ' . $limit,
            [$tenantId, $customerId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function redemptions(int $tenantId, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));

        return Database::all(
            'SELECT r.*, c.name AS customer_name, c.phone AS customer_phone, rw.title AS reward_title
             FROM reward_redemptions r
             JOIN customers c ON c.id = r.customer_id
             JOIN rewards rw ON rw.id = r.reward_id
             WHERE r.tenant_id = ? ORDER BY r.id DESC LIMIT ' . $limit,
            [$tenantId]
        );
    }

    public static function markRedemptionUsed(int $redemptionId, int $tenantId, int $actorUserId): array
    {
        $affected = Database::run(
            'UPDATE reward_redemptions SET status = \'used\', used_at = NOW()
             WHERE id = ? AND tenant_id = ? AND status = \'pending\'',
            [$redemptionId, $tenantId]
        )->rowCount();

        if ($affected === 0) {
            return ['ok' => false, 'error' => 'این کد قابل استفاده نیست.'];
        }

        AuditLog::record('reward.used', $actorUserId, $tenantId, [], 'redemption', $redemptionId);

        return ['ok' => true];
    }

    private static function uniqueRedemptionCode(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $code = Code::reference('RW-', 6);
            if ((int) Database::scalar('SELECT COUNT(*) FROM reward_redemptions WHERE code = ?', [$code]) === 0) {
                return $code;
            }
        }

        return Code::reference('RW-', 10);
    }
}
