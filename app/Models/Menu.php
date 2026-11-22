<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use App\Core\Security;

final class Menu extends Model
{
    protected string $table = 'menus';
    protected array $fillable = ['tenant_id', 'name', 'is_default', 'is_published'];

    /** @return array<int, array<string, mixed>> */
    public function forTenant(int $tenantId): array
    {
        return Database::all(
            'SELECT m.*,
                    (SELECT COUNT(*) FROM categories c WHERE c.menu_id = m.id) AS categories_count,
                    (SELECT COUNT(*) FROM products p WHERE p.tenant_id = m.tenant_id
                        AND p.category_id IN (SELECT id FROM categories WHERE menu_id = m.id)) AS products_count
             FROM menus m WHERE m.tenant_id = ? ORDER BY m.is_default DESC, m.id ASC',
            [$tenantId]
        );
    }

    /** @return array<string, mixed>|null */
    public function defaultFor(int $tenantId): ?array
    {
        return Database::one(
            'SELECT * FROM menus WHERE tenant_id = ? ORDER BY is_default DESC, id ASC LIMIT 1',
            [$tenantId]
        );
    }

    /** @return array<string, mixed>|null */
    public function publishedByQr(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/i', $token)) {
            return null;
        }

        return Database::one(
            'SELECT m.*, t.name AS tenant_name, t.slug AS tenant_slug, t.description AS tenant_description,
                    t.logo AS tenant_logo, t.phone AS tenant_phone, t.address AS tenant_address
             FROM menus m JOIN tenants t ON t.id = m.tenant_id
             WHERE m.qr_token = ? AND m.is_published = 1 AND t.status = \'active\'
             LIMIT 1',
            [$token]
        );
    }

    /** @return array<string, mixed>|null */
    public function publishedDefaultForTenant(int $tenantId): ?array
    {
        return Database::one(
            'SELECT m.*, t.name AS tenant_name, t.slug AS tenant_slug, t.description AS tenant_description,
                    t.logo AS tenant_logo, t.phone AS tenant_phone, t.address AS tenant_address
             FROM menus m JOIN tenants t ON t.id = m.tenant_id
             WHERE m.tenant_id = ? AND m.is_published = 1 AND t.status = \'active\'
             ORDER BY m.is_default DESC, m.id ASC LIMIT 1',
            [$tenantId]
        );
    }

    public function createForTenant(int $tenantId, string $name, bool $isDefault = false): int
    {
        return $this->create([
            'tenant_id'    => $tenantId,
            'name'         => $name,
            'is_default'   => $isDefault ? 1 : 0,
            'is_published' => 1,
            'qr_token'     => Security::randomToken(16),
        ]);
    }

    public function makeDefault(int $tenantId, int $menuId): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            Database::run('UPDATE menus SET is_default = 0 WHERE tenant_id = ?', [$tenantId]);
            Database::run('UPDATE menus SET is_default = 1 WHERE id = ? AND tenant_id = ?', [$menuId, $tenantId]);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function belongsToTenant(int $menuId, int $tenantId): bool
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM menus WHERE id = ? AND tenant_id = ?',
            [$menuId, $tenantId]
        ) > 0;
    }
}
