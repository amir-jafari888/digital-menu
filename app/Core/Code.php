<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Human-friendly reference codes (order numbers, redemption codes).
 * Ambiguous characters (0/O, 1/I) are intentionally excluded.
 */
final class Code
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function reference(string $prefix, int $length = 8): string
    {
        $length = max(4, min(20, $length));
        $max    = strlen(self::ALPHABET) - 1;
        $out    = '';

        for ($i = 0; $i < $length; $i++) {
            $out .= self::ALPHABET[random_int(0, $max)];
        }

        return $prefix . $out;
    }
}
