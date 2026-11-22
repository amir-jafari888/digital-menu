<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Campaign extends Model
{
    protected string $table = 'ad_campaigns';
    protected array $fillable = [
        'tenant_id', 'title', 'description', 'image', 'cta_url', 'placement',
        'budget', 'spent', 'starts_at', 'ends_at', 'status',
    ];

    public const PLACEMENTS = ['app_home', 'app_search', 'app_nearby'];

    public const PLACEMENT_LABELS = [
        'app_home'   => 'صفحهٔ اصلی اپ',
        'app_search' => 'نتایج جست‌وجو',
        'app_nearby' => 'کسب‌وکارهای نزدیک',
    ];

    /** @return array<int, array<string, mixed>> */
    public function forTenant(int $tenantId): array
    {
        return Database::all(
            'SELECT * FROM ad_campaigns WHERE tenant_id = ? ORDER BY id DESC',
            [$tenantId]
        );
    }

    /** @return array<string, mixed>|null */
    public function findForTenant(int $id, int $tenantId): ?array
    {
        return Database::one(
            'SELECT * FROM ad_campaigns WHERE id = ? AND tenant_id = ? LIMIT 1',
            [$id, $tenantId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function serving(string $placement, int $limit = 5): array
    {
        if (!in_array($placement, self::PLACEMENTS, true)) {
            return [];
        }
        $limit = max(1, min(20, $limit));

        return Database::all(
            'SELECT a.*, t.name AS tenant_name, t.slug AS tenant_slug
             FROM ad_campaigns a JOIN tenants t ON t.id = a.tenant_id
             WHERE a.status = \'active\' AND a.placement = ?
               AND a.starts_at <= NOW() AND a.ends_at >= NOW()
               AND (a.budget = 0 OR a.spent < a.budget)
               AND t.status = \'active\'
             ORDER BY a.id DESC LIMIT ' . $limit,
            [$placement]
        );
    }

    /** Register an impression/click and keep the counter columns in sync. */
    public static function track(int $campaignId, string $type, string $ipHash): bool
    {
        if (!in_array($type, ['impression', 'click'], true)) {
            return false;
        }

        $exists = (int) Database::scalar(
            'SELECT COUNT(*) FROM ad_campaigns WHERE id = ? AND status = \'active\'',
            [$campaignId]
        );
        if ($exists === 0) {
            return false;
        }

        $column = $type === 'click' ? 'clicks' : 'impressions';

        Database::run(
            'INSERT INTO ad_events (campaign_id, type, ip_hash) VALUES (?, ?, ?)',
            [$campaignId, $type, $ipHash]
        );
        Database::run('UPDATE ad_campaigns SET `' . $column . '` = `' . $column . '` + 1 WHERE id = ?', [$campaignId]);

        return true;
    }
}
