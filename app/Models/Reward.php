<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Reward extends Model
{
    protected string $table = 'rewards';
    protected array $fillable = ['tenant_id', 'title', 'description', 'points_cost', 'stock', 'is_active'];

    /** @return array<int, array<string, mixed>> */
    public function forTenant(int $tenantId, bool $onlyActive = false): array
    {
        $sql = 'SELECT * FROM rewards WHERE tenant_id = ?';
        if ($onlyActive) {
            $sql .= ' AND is_active = 1 AND (stock IS NULL OR stock > 0)';
        }
        $sql .= ' ORDER BY points_cost ASC, id ASC';

        return Database::all($sql, [$tenantId]);
    }

    /** @return array<string, mixed>|null */
    public function findForTenant(int $id, int $tenantId): ?array
    {
        return Database::one('SELECT * FROM rewards WHERE id = ? AND tenant_id = ? LIMIT 1', [$id, $tenantId]);
    }

    /** Atomically reserve one unit of stock. Returns false when sold out. */
    public function decrementStock(int $id, int $tenantId): bool
    {
        $affected = Database::run(
            'UPDATE rewards SET stock = stock - 1
             WHERE id = ? AND tenant_id = ? AND stock IS NOT NULL AND stock > 0',
            [$id, $tenantId]
        )->rowCount();

        if ($affected > 0) {
            return true;
        }

        // Unlimited stock (NULL) is always available.
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM rewards WHERE id = ? AND tenant_id = ? AND stock IS NULL',
            [$id, $tenantId]
        ) > 0;
    }
}
