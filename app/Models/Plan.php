<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Plan
{
    /** @return array<int, array<string, mixed>> */
    public static function active(): array
    {
        $rows = Database::all('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
        foreach ($rows as &$row) {
            $row['features_list'] = self::features($row);
        }

        return $rows;
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        $rows = Database::all('SELECT * FROM plans ORDER BY sort_order ASC, id ASC');
        foreach ($rows as &$row) {
            $row['features_list'] = self::features($row);
        }

        return $rows;
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM plans WHERE id = ? LIMIT 1', [$id]);
    }

    /** @return array<int, string> */
    private static function features(array $row): array
    {
        $decoded = json_decode((string) ($row['features'] ?? '[]'), true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($item): string => is_string($item) ? $item : '',
            $decoded
        )));
    }
}
