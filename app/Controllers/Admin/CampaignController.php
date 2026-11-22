<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Campaign;
use App\Models\UploadedFile;
use App\Services\UploadService;

final class CampaignController extends BaseAdminController
{
    public static function index(): void
    {
        $tenantId = (int) self::tenantId();
        $campaigns = (new Campaign())->forTenant($tenantId);

        $active = count(array_filter($campaigns, static fn (array $c): bool => $c['status'] === 'active'));
        $impressions = array_sum(array_map(static fn (array $c): int => (int) $c['impressions'], $campaigns));
        $clicks = array_sum(array_map(static fn (array $c): int => (int) $c['clicks'], $campaigns));

        self::render('campaigns/index', [
            'campaigns'   => $campaigns,
            'active'      => $active,
            'impressions' => $impressions,
            'clicks'      => $clicks,
        ], 'کمپین‌های تبلیغاتی', 'campaigns', '<a class="m3-button m3-button--filled m3-button--sm" href="' . e(admin_url('campaigns/new')) . '">+ کمپین جدید</a>');
    }

    public static function create(): void
    {
        self::render('campaigns/form', [
            'campaign'   => null,
            'errors'     => [],
            'formAction' => admin_url('campaigns'),
        ], 'کمپین جدید', 'campaigns');
    }

    public static function store(): void
    {
        $tenantId = (int) self::tenantId();
        $payload = self::payload();

        if (!$payload['ok']) {
            self::render('campaigns/form', [
                'campaign'   => $payload['old'],
                'errors'     => $payload['errors'],
                'formAction' => admin_url('campaigns'),
            ], 'کمپین جدید', 'campaigns');

            return;
        }

        $data = $payload['data'];
        $data['tenant_id'] = $tenantId;
        $data['image'] = self::handleImage($tenantId, null);

        $id = (new Campaign())->create($data);
        self::audit('campaign.created', 'campaign', $id, ['title' => $data['title']]);
        self::flashOk('کمپین ساخته شد.');
        self::back(admin_url('campaigns'));
    }

    public static function edit(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $campaign = (new Campaign())->findForTenant((int) $params['id'], $tenantId);

        if ($campaign === null) {
            self::flashError('کمپین پیدا نشد.');
            self::back(admin_url('campaigns'));
        }

        self::render('campaigns/form', [
            'campaign'   => $campaign,
            'errors'     => [],
            'formAction' => admin_url('campaigns/' . (int) $campaign['id']),
        ], 'ویرایش کمپین', 'campaigns');
    }

    public static function update(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $campaign = (new Campaign())->findForTenant((int) $params['id'], $tenantId);

        if ($campaign === null) {
            self::flashError('کمپین پیدا نشد.');
            self::back(admin_url('campaigns'));
        }

        $payload = self::payload();
        if (!$payload['ok']) {
            self::render('campaigns/form', [
                'campaign'   => array_merge($campaign, $payload['old']),
                'errors'     => $payload['errors'],
                'formAction' => admin_url('campaigns/' . (int) $campaign['id']),
            ], 'ویرایش کمپین', 'campaigns');

            return;
        }

        $data = $payload['data'];
        $data['image'] = self::handleImage($tenantId, (string) ($campaign['image'] ?? ''));
        if (Request::bool('remove_image')) {
            $data['image'] = null;
        }

        (new Campaign())->update((int) $campaign['id'], $data);
        self::audit('campaign.updated', 'campaign', (int) $campaign['id']);
        self::flashOk('کمپین به‌روز شد.');
        self::back(admin_url('campaigns'));
    }

    public static function status(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $campaign = (new Campaign())->findForTenant((int) $params['id'], $tenantId);

        if ($campaign === null) {
            self::flashError('کمپین پیدا نشد.');
            self::back(admin_url('campaigns'));
        }

        $next = Request::string('status');
        if (!in_array($next, ['draft', 'active', 'paused', 'ended'], true)) {
            self::flashError('وضعیت نامعتبر است.');
            self::back(admin_url('campaigns'));
        }

        (new Campaign())->update((int) $campaign['id'], ['status' => $next]);
        self::audit('campaign.status', 'campaign', (int) $campaign['id'], ['status' => $next]);
        self::flashOk('وضعیت کمپین تغییر کرد.');
        self::back(admin_url('campaigns'));
    }

    public static function destroy(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $campaign = (new Campaign())->findForTenant((int) $params['id'], $tenantId);

        if ($campaign === null) {
            self::flashError('کمپین پیدا نشد.');
            self::back(admin_url('campaigns'));
        }

        (new Campaign())->delete((int) $campaign['id']);
        self::audit('campaign.deleted', 'campaign', (int) $campaign['id']);
        self::flashOk('کمپین حذف شد.');
        self::back(admin_url('campaigns'));
    }

    /** @return array{ok:bool, data?:array<string,mixed>, errors?:array<string,string>, old?:array<string,mixed>} */
    private static function payload(): array
    {
        $validated = Validator::validate(Request::all(), [
            'title'       => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'placement'   => 'required|in:app_home,app_search,app_nearby',
            'budget'      => 'nullable|int|min:0|max:100000000',
            'starts_at'   => 'required|date',
            'ends_at'     => 'required|date',
            'cta_url'     => 'nullable|url|max:500',
            'status'      => 'required|in:draft,active,paused,ended',
        ], [
            'title'       => 'عنوان',
            'description' => 'توضیح',
            'placement'   => 'جایگاه',
            'budget'      => 'بودجه',
            'starts_at'   => 'تاریخ شروع',
            'ends_at'     => 'تاریخ پایان',
            'cta_url'     => 'لینک مقصد',
            'status'      => 'وضعیت',
        ]);

        if (!$validated['valid']) {
            return ['ok' => false, 'errors' => $validated['errors'], 'old' => Request::all()];
        }

        $data = $validated['data'];
        if ($data['ends_at'] < $data['starts_at']) {
            return ['ok' => false, 'errors' => ['ends_at' => 'تاریخ پایان نمی‌تواند قبل از شروع باشد.'], 'old' => Request::all()];
        }

        $data['description'] = mb_substr(Validator::cleanText((string) ($data['description'] ?? '')), 0, 500);
        $data['budget']      = (int) ($data['budget'] ?? 0);
        $data['starts_at']   = $data['starts_at'] . ' 00:00:00';
        $data['ends_at']     = $data['ends_at'] . ' 23:59:59';

        return ['ok' => true, 'data' => $data];
    }

    private static function handleImage(int $tenantId, ?string $current): ?string
    {
        $file = $_FILES['image'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $current;
        }

        $result = UploadService::store($file, $tenantId, Auth::id());
        if (!$result['ok']) {
            self::flashError((string) $result['error']);

            return $current;
        }

        if ($current !== null && preg_match('#^uploads/(\d+)\.#', $current, $m)) {
            (new UploadedFile())->deleteWithFile((int) $m[1]);
        }

        return (string) $result['path'];
    }
}
