<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Subscription
{
    /** @return array<string, mixed>|null */
    public static function currentForTenant(int $tenantId): ?array
    {
        return Database::one(
            'SELECT s.*, p.name AS plan_name, p.slug AS plan_slug
             FROM subscriptions s JOIN plans p ON p.id = s.plan_id
             WHERE s.tenant_id = ?
             ORDER BY (s.status = \'active\') DESC, s.ends_at DESC, s.id DESC
             LIMIT 1',
            [$tenantId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function historyForTenant(int $tenantId, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));

        return Database::all(
            'SELECT s.*, p.name AS plan_name FROM subscriptions s
             JOIN plans p ON p.id = s.plan_id
             WHERE s.tenant_id = ? ORDER BY s.id DESC LIMIT ' . $limit,
            [$tenantId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function allWithTenant(int $limit = 100): array
    {
        $limit = max(1, min(200, $limit));

        return Database::all(
            'SELECT s.*, p.name AS plan_name, t.name AS tenant_name
             FROM subscriptions s
             JOIN plans p ON p.id = s.plan_id
             JOIN tenants t ON t.id = s.tenant_id
             ORDER BY s.id DESC LIMIT ' . $limit
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function paymentsForTenant(int $tenantId, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));

        return Database::all(
            'SELECT * FROM payments WHERE tenant_id = ? ORDER BY id DESC LIMIT ' . $limit,
            [$tenantId]
        );
    }

    /** Record a manual (offline) payment. Gateway integration is intentionally absent. */
    public static function recordManualPayment(int $tenantId, int $amount, ?int $subscriptionId, string $reference, int $userId): int
    {
        $id = Database::insert(
            'INSERT INTO payments (tenant_id, subscription_id, amount, gateway, reference, status, paid_at, meta)
             VALUES (?, ?, ?, \'manual\', ?, \'paid\', NOW(), ?)',
            [
                $tenantId,
                $subscriptionId,
                max(0, $amount),
                mb_substr($reference, 0, 100),
                json_encode(['recorded_by' => $userId], JSON_UNESCAPED_UNICODE),
            ]
        );

        return $id;
    }
}
