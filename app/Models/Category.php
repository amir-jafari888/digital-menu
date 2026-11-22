<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Category extends Model
{
    protected string $table = 'categories';
    protected array $fillable = [
        'tenant_id', 'menu_id', 'name', 'description', 'sort_order', 'is_active',
    ];

    /** @return array<int, array<string, mixed>> */
    public function forTenant(int $tenantId, bool $onlyActive = false): array
    {
        $sql = 'SELECT c.*, m.name AS menu_name,
                       (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS products_count
                FROM categories c JOIN menus m ON m.id = c.menu_id
                WHERE c.tenant_id = ?';
        if ($onlyActive) {
            $sql .= ' AND c.is_active = 1';
        }
        $sql .= ' ORDER BY c.sort_order ASC, c.id ASC';

        return Database::all($sql, [$tenantId]);
    }

    /** @return array<int, array<string, mixed>> */
    public function forMenu(int $menuId, bool $onlyActive = true): array
    {
        $sql = 'SELECT * FROM categories WHERE menu_id = ?';
        if ($onlyActive) {
            $sql .= ' AND is_active = 1';
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';

        return Database::all($sql, [$menuId]);
    }

    public function belongsToTenant(int $categoryId, int $tenantId): bool
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM categories WHERE id = ? AND tenant_id = ?',
            [$categoryId, $tenantId]
        ) > 0;
    }

    /** @return array<string, mixed>|null */
    public function findForTenant(int $categoryId, int $tenantId): ?array
    {
        return Database::one(
            'SELECT * FROM categories WHERE id = ? AND tenant_id = ? LIMIT 1',
            [$categoryId, $tenantId]
        );
    }

    public function nextSortOrder(int $menuId): int
    {
        return (int) Database::scalar(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM categories WHERE menu_id = ?',
            [$menuId]
        );
    }
}
