<?php
declare(strict_types=1);

/**
 * Serves a private upload through an HMAC-signed, expiring URL.
 *
 * The file itself lives outside the document root (storage/uploads), so even
 * a guessed path cannot be fetched directly and no uploaded script can ever be
 * executed by PHP.
 */

require __DIR__ . '/app/bootstrap.php';

use App\Core\Security;
use App\Models\UploadedFile;

$id      = (int) ($_GET['f'] ?? 0);
$expires = (int) ($_GET['e'] ?? 0);
$signature = (string) ($_GET['s'] ?? '');

if ($id <= 0 || $expires <= 0 || $signature === '' || !Security::verifyMedia($id, $expires, $signature)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');
    echo 'Not found.';
    exit;
}

$file = (new UploadedFile())->find($id);
if ($file === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Not found.';
    exit;
}

$path = (string) ($file['path'] ?? '');
if (!preg_match('#^uploads/[a-f0-9]+\.[a-z0-9]{2,5}$#i', $path)) {
    http_response_code(404);
    echo 'Not found.';
    exit;
}

$base     = realpath(storage_path('uploads'));
$absolute = realpath(storage_path($path));

if ($base === false || $absolute === false || !str_starts_with($absolute, $base . DIRECTORY_SEPARATOR) || !is_file($absolute)) {
    http_response_code(404);
    echo 'Not found.';
    exit;
}

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

$mime = (string) ($file['mime'] ?? '');
if (!isset($allowed[$mime])) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = (string) $finfo->file($absolute);
    if (!isset($allowed[$mime])) {
        http_response_code(415);
        echo 'Unsupported media type.';
        exit;
    }
}

$size = (int) filesize($absolute);
$etag = '"' . substr(hash_file('sha256', $absolute), 0, 32) . '"';

header('Content-Type: ' . $mime);
header('Content-Length: ' . $size);
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="image.' . $allowed[$mime] . '"');
header('Cache-Control: private, max-age=86400, immutable');
header('ETag: ' . $etag);

if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
    http_response_code(304);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
    exit;
}

readfile($absolute);
