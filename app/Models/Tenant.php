<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Tenant extends Model
{
    protected string $table = 'tenants';
    protected array $fillable = [
        'name', 'slug', 'category', 'description', 'phone', 'address',
        'logo', 'cover', 'status', 'plan_id', 'trial_ends_at',
    ];

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        return Database::one(
            'SELECT t.*, p.name AS plan_name, p.slug AS plan_slug
             FROM tenants t LEFT JOIN plans p ON p.id = t.plan_id
             WHERE t.slug = ? LIMIT 1',
            [$slug]
        );
    }

    /** @return array<string, mixed>|null */
    public function withPlan(int $id): ?array
    {
        return Database::one(
            'SELECT t.*, p.name AS plan_name, p.slug AS plan_slug
             FROM tenants t LEFT JOIN plans p ON p.id = t.plan_id
             WHERE t.id = ? LIMIT 1',
            [$id]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function allWithPlan(): array
    {
        return Database::all(
            'SELECT t.*, p.name AS plan_name,
                    (SELECT COUNT(*) FROM users u WHERE u.tenant_id = t.id) AS users_count,
                    (SELECT COUNT(*) FROM orders o WHERE o.tenant_id = t.id) AS orders_count
             FROM tenants t LEFT JOIN plans p ON p.id = t.plan_id
             ORDER BY t.id DESC'
        );
    }

    public function slugTaken(string $slug, ?int $exceptId = null): bool
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM tenants WHERE slug = ? AND id <> ?',
            [$slug, $exceptId ?? 0]
        ) > 0;
    }

    public static function slugify(string $name): string
    {
        $slug = mb_strtolower(trim($name));
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        // Persian letters are valid in slugs, but keep it URL-friendly.
        if ($slug === '' || !preg_match('/^[a-z0-9\x{0600}-\x{06FF}\-]+$/u', $slug)) {
            $slug = 'shop-' . bin2hex(random_bytes(3));
        }

        return mb_substr($slug, 0, 60);
    }
}
