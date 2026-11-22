<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = [
        'tenant_id', 'name', 'email', 'phone', 'password_hash', 'role',
        'status', 'must_change_password', 'email_verified_at',
    ];

    /** @return array<string, mixed>|null */
    public function findByIdentifier(string $identifier): ?array
    {
        return Database::one(
            'SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1',
            [$identifier, $identifier]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function forTenant(?int $tenantId): array
    {
        if ($tenantId === null) {
            return Database::all(
                'SELECT u.*, t.name AS tenant_name FROM users u
                 LEFT JOIN tenants t ON t.id = u.tenant_id
                 ORDER BY u.id DESC'
            );
        }

        return Database::all(
            'SELECT u.*, t.name AS tenant_name FROM users u
             LEFT JOIN tenants t ON t.id = u.tenant_id
             WHERE u.tenant_id = ? ORDER BY u.id DESC',
            [$tenantId]
        );
    }

    public function emailTaken(string $email, ?int $exceptId = null): bool
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?',
            [$email, $exceptId ?? 0]
        ) > 0;
    }

    public function phoneTaken(string $phone, ?int $exceptId = null): bool
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM users WHERE phone = ? AND id <> ?',
            [$phone, $exceptId ?? 0]
        ) > 0;
    }

    public function setPassword(int $id, string $plain, bool $mustChange = false): void
    {
        Database::run(
            'UPDATE users SET password_hash = ?, must_change_password = ?, updated_at = NOW() WHERE id = ?',
            [password_hash($plain, PASSWORD_DEFAULT), $mustChange ? 1 : 0, $id]
        );
    }
}
