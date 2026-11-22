<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Setting
{
    /** @var array<string, string>|null */
    private static ?array $cache = null;

    public static function get(string $key, string $default = ''): string
    {
        $all = self::all();

        return $all[$key] ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key, (string) $default);

        return (int) $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return self::get($key, $default ? '1' : '0') === '1';
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $rows = Database::all('SELECT `key`, `value` FROM settings');
        $out  = [];
        foreach ($rows as $row) {
            $out[(string) $row['key']] = (string) ($row['value'] ?? '');
        }

        return self::$cache = $out;
    }

    public static function set(string $key, string $value): void
    {
        Database::run(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [mb_substr($key, 0, 80), $value]
        );

        self::$cache = null;
    }

    /** @param array<string, string> $values */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            self::set((string) $key, (string) $value);
        }
    }
}
