<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Security;

final class AuditLog
{
    public static function record(
        string $action,
        ?int $userId = null,
        ?int $tenantId = null,
        array $meta = [],
        ?string $entity = null,
        ?int $entityId = null
    ): void {
        Database::run(
            'INSERT INTO audit_logs (user_id, tenant_id, action, entity, entity_id, ip_hash, meta)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $userId,
                $tenantId,
                mb_substr($action, 0, 80),
                $entity === null ? null : mb_substr($entity, 0, 40),
                $entityId,
                Security::ipHash(),
                $meta === [] ? null : json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]
        );
    }

    /** Record an action for the signed-in user, scoped to their tenant. */
    public static function forCurrentUser(string $action, ?string $entity = null, ?int $entityId = null, array $meta = []): void
    {
        self::record($action, Auth::id() ?: null, Auth::tenantId(), $meta, $entity, $entityId);
    }

    /** @return array<int, array<string, mixed>> */
    public static function recent(?int $tenantId, int $limit = 20, int $offset = 0): array
    {
        $limit  = max(1, min(200, $limit));
        $offset = max(0, $offset);

        if ($tenantId === null) {
            return Database::all(
                'SELECT a.*, u.name AS user_name FROM audit_logs a
                 LEFT JOIN users u ON u.id = a.user_id
                 ORDER BY a.id DESC LIMIT ' . $limit . ' OFFSET ' . $offset
            );
        }

        return Database::all(
            'SELECT a.*, u.name AS user_name FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.tenant_id = ?
             ORDER BY a.id DESC LIMIT ' . $limit . ' OFFSET ' . $offset,
            [$tenantId]
        );
    }
}
