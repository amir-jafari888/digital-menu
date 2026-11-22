<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use App\Core\Security;

final class Product extends Model
{
    protected string $table = 'products';
    protected array $fillable = [
        'tenant_id', 'category_id', 'name', 'description', 'price',
        'compare_at_price', 'image', 'is_available', 'is_featured',
        'calories', 'allergens', 'sort_order',
    ];

    /**
     * @param array{search?:string, category_id?:int, available?:bool} $filters
     * @return array<int, array<string, mixed>>
     */
    public function forTenant(int $tenantId, array $filters = [], int $limit = 200, int $offset = 0): array
    {
        [$where, $params] = $this->filterSql($tenantId, $filters);
        $limit  = max(1, min(500, $limit));
        $offset = max(0, $offset);

        return Database::all(
            'SELECT p.*, c.name AS category_name FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE ' . $where . '
             ORDER BY c.sort_order ASC, p.sort_order ASC, p.id DESC
             LIMIT ' . $limit . ' OFFSET ' . $offset,
            $params
        );
    }

    /** @param array{search?:string, category_id?:int, available?:bool} $filters */
    public function countForTenant(int $tenantId, array $filters = []): int
    {
        [$where, $params] = $this->filterSql($tenantId, $filters);

        return (int) Database::scalar(
            'SELECT COUNT(*) FROM products p WHERE ' . $where,
            $params
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function forMenu(int $menuId): array
    {
        return Database::all(
            'SELECT p.*, c.name AS category_name, c.sort_order AS category_sort
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE c.menu_id = ? AND c.is_active = 1 AND p.is_available = 1
             ORDER BY c.sort_order ASC, p.sort_order ASC, p.id ASC',
            [$menuId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function featured(int $tenantId, int $limit = 4): array
    {
        $limit = max(1, min(12, $limit));

        return Database::all(
            'SELECT * FROM products
             WHERE tenant_id = ? AND is_featured = 1 AND is_available = 1
             ORDER BY sort_order ASC, id DESC LIMIT ' . $limit,
            [$tenantId]
        );
    }

    /** @return array<string, mixed>|null */
    public function findForTenant(int $productId, int $tenantId): ?array
    {
        return Database::one(
            'SELECT * FROM products WHERE id = ? AND tenant_id = ? LIMIT 1',
            [$productId, $tenantId]
        );
    }

    /** @param array<int, int> $ids */
    public function findManyForTenant(array $ids, int $tenantId): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $rows = Database::all(
            'SELECT * FROM products WHERE tenant_id = ? AND id IN (' . $placeholders . ')',
            [$tenantId, ...$ids]
        );

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id']] = $row;
        }

        return $map;
    }

    public function nextSortOrder(int $tenantId): int
    {
        return (int) Database::scalar(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM products WHERE tenant_id = ?',
            [$tenantId]
        );
    }

    /** Resolve the image column to a signed media URL (or null). */
    public static function imageUrl(?string $image): ?string
    {
        $image = trim((string) $image);
        if ($image === '') {
            return null;
        }

        if (preg_match('#^uploads/(\d+)\.[a-z0-9]+$#i', $image, $m)) {
            return Security::mediaUrl((int) $m[1]);
        }

        if (preg_match('#^assets/[a-zA-Z0-9_\-./]+\.[a-z0-9]+$#i', $image)) {
            return url($image);
        }

        return null;
    }

    /**
     * @param array{search?:string, category_id?:int, available?:bool} $filters
     * @return array{0:string, 1:array<int,mixed>}
     */
    private function filterSql(int $tenantId, array $filters): array
    {
        $where  = 'p.tenant_id = ?';
        $params = [$tenantId];

        if (!empty($filters['search'])) {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], (string) $filters['search']) . '%';
            $where .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($filters['category_id'])) {
            $where .= ' AND p.category_id = ?';
            $params[] = (int) $filters['category_id'];
        }

        if (array_key_exists('available', $filters) && $filters['available'] !== null) {
            $where .= ' AND p.is_available = ?';
            $params[] = $filters['available'] ? 1 : 0;
        }

        return [$where, $params];
    }
}
