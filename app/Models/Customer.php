<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Customer extends Model
{
    protected string $table = 'customers';
    protected array $fillable = [
        'tenant_id', 'phone', 'name', 'email', 'birthday',
        'points_balance', 'total_spent', 'visits', 'last_visit_at',
    ];

    /** @return array<string, mixed>|null */
    public function findByPhone(int $tenantId, string $phone): ?array
    {
        return Database::one(
            'SELECT * FROM customers WHERE tenant_id = ? AND phone = ? LIMIT 1',
            [$tenantId, $phone]
        );
    }

    /** @return array<string, mixed>|null */
    public function findForTenant(int $id, int $tenantId): ?array
    {
        return Database::one(
            'SELECT * FROM customers WHERE id = ? AND tenant_id = ? LIMIT 1',
            [$id, $tenantId]
        );
    }

    /**
     * @param array{search?:string} $filters
     * @return array<int, array<string, mixed>>
     */
    public function forTenant(int $tenantId, array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $where  = 'tenant_id = ?';
        $params = [$tenantId];

        if (!empty($filters['search'])) {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], (string) $filters['search']) . '%';
            $where .= ' AND (name LIKE ? OR phone LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }

        $limit  = max(1, min(300, $limit));
        $offset = max(0, $offset);

        return Database::all(
            'SELECT * FROM customers WHERE ' . $where
            . ' ORDER BY last_visit_at IS NULL, last_visit_at DESC, id DESC
               LIMIT ' . $limit . ' OFFSET ' . $offset,
            $params
        );
    }

    public function countForTenant(int $tenantId, array $filters = []): int
    {
        $where  = 'tenant_id = ?';
        $params = [$tenantId];

        if (!empty($filters['search'])) {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], (string) $filters['search']) . '%';
            $where .= ' AND (name LIKE ? OR phone LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }

        return (int) Database::scalar('SELECT COUNT(*) FROM customers WHERE ' . $where, $params);
    }

    public function findOrCreate(int $tenantId, string $phone, string $name = ''): array
    {
        $existing = $this->findByPhone($tenantId, $phone);
        if ($existing !== null) {
            if ($name !== '' && $existing['name'] === '') {
                Database::run('UPDATE customers SET name = ? WHERE id = ?', [$name, (int) $existing['id']]);
                $existing['name'] = $name;
            }

            return $existing;
        }

        $id = $this->create([
            'tenant_id' => $tenantId,
            'phone'     => $phone,
            'name'      => $name,
        ]);

        return (array) $this->find($id);
    }

    /** @return array<int, array<string, mixed>> */
    public function topByPoints(int $tenantId, int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));

        return Database::all(
            'SELECT id, name, phone, points_balance, total_spent, visits
             FROM customers WHERE tenant_id = ?
             ORDER BY points_balance DESC, total_spent DESC LIMIT ' . $limit,
            [$tenantId]
        );
    }
}
