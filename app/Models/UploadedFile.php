<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class UploadedFile extends Model
{
    protected string $table = 'uploaded_files';
    protected array $fillable = ['tenant_id', 'user_id', 'path', 'mime', 'size', 'original_name'];

    /** @return array<string, mixed>|null */
    public function findForTenant(int $id, ?int $tenantId): ?array
    {
        if ($tenantId === null) {
            return $this->find($id);
        }

        return Database::one(
            'SELECT * FROM uploaded_files WHERE id = ? AND (tenant_id = ? OR tenant_id IS NULL) LIMIT 1',
            [$id, $tenantId]
        );
    }

    public function deleteWithFile(int $id): bool
    {
        $row = $this->find($id);
        if ($row === null) {
            return false;
        }

        $path = (string) ($row['path'] ?? '');
        if (preg_match('#^uploads/[a-f0-9]+\.[a-z0-9]{2,5}$#i', $path)) {
            $absolute = storage_path($path);
            if (is_file($absolute)) {
                @unlink($absolute);
            }
        }

        return $this->delete($id);
    }
}
