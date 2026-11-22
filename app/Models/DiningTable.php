<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use App\Core\Security;

final class DiningTable extends Model
{
    protected string $table = 'dining_tables';
    protected array $fillable = ['tenant_id', 'label', 'qr_token', 'is_active'];

    /** @return array<int, array<string, mixed>> */
    public function forTenant(int $tenantId): array
    {
        return Database::all(
            'SELECT t.*, (SELECT COUNT(*) FROM orders o WHERE o.table_id = t.id AND o.status IN (\'pending\',\'confirmed\',\'preparing\',\'ready\')) AS open_orders
             FROM dining_tables t WHERE t.tenant_id = ? ORDER BY t.id ASC',
            [$tenantId]
        );
    }

    /** @return array<string, mixed>|null */
    public function findForTenant(int $id, int $tenantId): ?array
    {
        return Database::one(
            'SELECT * FROM dining_tables WHERE id = ? AND tenant_id = ? LIMIT 1',
            [$id, $tenantId]
        );
    }

    /** @return array<string, mixed>|null */
    public function findByQr(string $token, int $tenantId): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/i', $token)) {
            return null;
        }

        return Database::one(
            'SELECT * FROM dining_tables WHERE qr_token = ? AND tenant_id = ? AND is_active = 1 LIMIT 1',
            [$token, $tenantId]
        );
    }

    public function createForTenant(int $tenantId, string $label): int
    {
        return $this->create([
            'tenant_id' => $tenantId,
            'label'     => $label,
            'qr_token'  => Security::randomToken(16),
            'is_active' => 1,
        ]);
    }

    public function labelTaken(int $tenantId, string $label, ?int $exceptId = null): bool
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM dining_tables WHERE tenant_id = ? AND label = ? AND id <> ?',
            [$tenantId, $label, $exceptId ?? 0]
        ) > 0;
    }
}
