<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Core\Security;
use App\Models\AuditLog;
use App\Models\UploadedFile;

/**
 * Image uploads.
 *
 * Files never land in the web root: they are stored in storage/uploads and
 * served through media.php with an HMAC-signed URL. Validation uses the real
 * file content (finfo + getimagesize), never the client-supplied name or MIME.
 */
final class UploadService
{
    /**
     * @param array{name?:string, type?:string, tmp_name?:string, error?:int, size?:int} $file
     * @return array{ok: bool, id?: int, url?: string, error?: string}
     */
    public static function store(array $file, ?int $tenantId, ?int $userId): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => self::uploadError($error)];
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['ok' => false, 'error' => 'فایل ارسالی معتبر نیست.'];
        }

        $size    = (int) ($file['size'] ?? 0);
        $maxSize = (int) config('uploads.max_bytes', 2 * 1024 * 1024);
        if ($size <= 0 || $size > $maxSize) {
            return ['ok' => false, 'error' => 'حجم تصویر باید کمتر از ' . fa_digits((string) (int) ($maxSize / 1024 / 1024)) . ' مگابایت باشد.'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = (string) $finfo->file($tmp);
        $allowed = (array) config('uploads.mimes', []);
        if (!in_array($mime, $allowed, true)) {
            return ['ok' => false, 'error' => 'فقط تصویر JPG، PNG یا WebP مجاز است.'];
        }

        $info = @getimagesize($tmp);
        if ($info === false || empty($info[0]) || empty($info[1])) {
            return ['ok' => false, 'error' => 'فایل ارسالی یک تصویر سالم نیست.'];
        }

        [$width, $height] = [(int) $info[0], (int) $info[1]];
        $maxPixels = (int) config('uploads.max_pixels', 12_000_000);
        if ($width < 16 || $height < 16 || ($width * $height) > $maxPixels) {
            return ['ok' => false, 'error' => 'ابعاد تصویر مجاز نیست.'];
        }

        $extMap = (array) config('uploads.ext', []);
        $ext    = $extMap[$mime] ?? null;
        if (!is_string($ext)) {
            return ['ok' => false, 'error' => 'نوع تصویر پشتیبانی نمی‌شود.'];
        }

        $dir = storage_path('uploads');
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            Logger::error('Upload directory is not writable');

            return ['ok' => false, 'error' => 'ذخیرهٔ فایل ممکن نشد.'];
        }

        $temporary = $dir . '/' . bin2hex(random_bytes(16)) . '.tmp';
        if (!@move_uploaded_file($tmp, $temporary)) {
            Logger::error('move_uploaded_file failed');

            return ['ok' => false, 'error' => 'ذخیرهٔ فایل ممکن نشد.'];
        }

        @chmod($temporary, 0644);

        try {
            $id = (new UploadedFile())->create([
                'tenant_id'     => $tenantId,
                'user_id'       => $userId,
                'path'          => 'uploads/pending',
                'mime'          => $mime,
                'size'          => $size,
                'original_name' => mb_substr(basename((string) ($file['name'] ?? 'image')), 0, 255),
            ]);

            $finalName = $id . '.' . $ext;
            $finalPath = $dir . '/' . $finalName;

            if (!@rename($temporary, $finalPath)) {
                @unlink($temporary);
                (new UploadedFile())->delete($id);

                return ['ok' => false, 'error' => 'ذخیرهٔ فایل ممکن نشد.'];
            }

            Database::run(
                'UPDATE uploaded_files SET path = ? WHERE id = ?',
                ['uploads/' . $finalName, $id]
            );
        } catch (\Throwable $e) {
            @unlink($temporary);
            Logger::error('Upload bookkeeping failed', ['message' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'ثبت فایل انجام نشد.'];
        }

        if ($userId !== null) {
            AuditLog::record('upload.created', $userId, $tenantId, ['mime' => $mime, 'size' => $size], 'file', $id);
        }

        return ['ok' => true, 'id' => $id, 'path' => 'uploads/' . $finalName, 'url' => Security::mediaUrl($id)];
    }

    /** @return array{ok: bool, error?: string} */
    public static function remove(int $fileId, ?int $tenantId, ?int $userId = null): array
    {
        $row = (new UploadedFile())->findForTenant($fileId, $tenantId);
        if ($row === null) {
            return ['ok' => false, 'error' => 'فایل پیدا نشد.'];
        }

        (new UploadedFile())->deleteWithFile($fileId);

        if ($userId !== null) {
            AuditLog::record('upload.deleted', $userId, $tenantId, [], 'file', $fileId);
        }

        return ['ok' => true];
    }

    private static function uploadError(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'حجم فایل بیش از حد مجاز است.',
            UPLOAD_ERR_PARTIAL                        => 'فایل به‌طور کامل ارسال نشد.',
            UPLOAD_ERR_NO_FILE                        => 'فایلی انتخاب نشده است.',
            UPLOAD_ERR_NO_TMP_DIR                     => 'پوشهٔ موقت روی سرور موجود نیست.',
            UPLOAD_ERR_CANT_WRITE                     => 'نوشتن فایل روی سرور ممکن نشد.',
            default                                   => 'ارسال فایل انجام نشد.',
        };
    }
}
