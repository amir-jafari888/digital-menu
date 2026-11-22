<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Fixed-window rate limiter backed by the `rate_limits` table.
 *
 * Every check runs in a transaction with SELECT ... FOR UPDATE so concurrent
 * requests cannot slip past the limit (no race condition). A bucket that
 * exceeds its allowance can be blocked for a longer cool-down, which is what
 * stops password spraying and order flooding.
 */
final class RateLimiter
{
    /**
     * @return array{allowed: bool, remaining: int, retry_after: int}
     */
    public static function attempt(string $bucket, int $max, int $window, int $blockFor = 0): array
    {
        $bucket = substr($bucket, 0, 190);
        $max    = max(1, $max);
        $window = max(1, $window);

        $pdo = Database::pdo();
        $now = time();

        try {
            $pdo->beginTransaction();

            $row = Database::one(
                'SELECT hits, window_start, blocked_until FROM rate_limits WHERE bucket = ? FOR UPDATE',
                [$bucket]
            );

            if ($row === null) {
                Database::run(
                    'INSERT INTO rate_limits (bucket, hits, window_start) VALUES (?, 1, ?)',
                    [$bucket, date('Y-m-d H:i:s', $now)]
                );
                $pdo->commit();

                return ['allowed' => true, 'remaining' => $max - 1, 'retry_after' => 0];
            }

            if (!empty($row['blocked_until'])) {
                $until = strtotime((string) $row['blocked_until']);
                if ($until !== false && $until > $now) {
                    $pdo->commit();

                    return ['allowed' => false, 'remaining' => 0, 'retry_after' => $until - $now];
                }
            }

            $started = strtotime((string) $row['window_start']);
            if ($started === false || $now - $started >= $window) {
                Database::run(
                    'UPDATE rate_limits SET hits = 1, window_start = ?, blocked_until = NULL WHERE bucket = ?',
                    [date('Y-m-d H:i:s', $now), $bucket]
                );
                $pdo->commit();

                return ['allowed' => true, 'remaining' => $max - 1, 'retry_after' => 0];
            }

            $hits = (int) $row['hits'] + 1;

            if ($hits > $max) {
                $retry = $blockFor > 0 ? $blockFor : max(1, $window - ($now - $started));
                Database::run(
                    'UPDATE rate_limits SET hits = ?, blocked_until = ? WHERE bucket = ?',
                    [$hits, date('Y-m-d H:i:s', $now + $retry), $bucket]
                );
                $pdo->commit();

                return ['allowed' => false, 'remaining' => 0, 'retry_after' => $retry];
            }

            Database::run('UPDATE rate_limits SET hits = ? WHERE bucket = ?', [$hits, $bucket]);
            $pdo->commit();

            return ['allowed' => true, 'remaining' => $max - $hits, 'retry_after' => 0];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error('Rate limiter failure', ['message' => $e->getMessage()]);

            // Fail closed on abuse-sensitive buckets: a broken limiter must not
            // silently turn into "no limiter" for authentication.
            return [
                'allowed'     => !str_starts_with($bucket, 'login') && !str_starts_with($bucket, 'register'),
                'remaining'   => 0,
                'retry_after' => 60,
            ];
        }
    }

    /** Convenience helper: "may this actor perform the action right now?" */
    public static function allow(string $bucket, int $max, int $window, int $blockFor = 0): bool
    {
        return self::attempt($bucket, $max, $window, $blockFor)['allowed'];
    }

    public static function clear(string $bucket): void
    {
        try {
            Database::run('DELETE FROM rate_limits WHERE bucket = ?', [substr($bucket, 0, 190)]);
        } catch (Throwable) {
            // Never let bookkeeping break a successful login.
        }
    }

    /** Housekeeping; called opportunistically (1 in 50 requests). */
    public static function prune(): void
    {
        if (random_int(1, 50) !== 1) {
            return;
        }

        try {
            Database::run(
                'DELETE FROM rate_limits WHERE window_start < NOW() - INTERVAL 2 DAY
                 AND (blocked_until IS NULL OR blocked_until < NOW()) LIMIT 500'
            );
            Database::run('DELETE FROM login_attempts WHERE created_at < NOW() - INTERVAL 30 DAY LIMIT 500');
        } catch (Throwable) {
            // Best effort only.
        }
    }

    /** Persian-friendly "try again in N" text. */
    public static function humanRetry(int $seconds): string
    {
        $seconds = max(1, $seconds);
        if ($seconds < 60) {
            return fa_digits((string) $seconds) . ' ثانیه';
        }
        $minutes = (int) ceil($seconds / 60);

        return fa_digits((string) $minutes) . ' دقیقه';
    }
}
