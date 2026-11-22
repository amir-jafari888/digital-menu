<?php
declare(strict_types=1);

/**
 * Public digital menu.
 *
 *   menu.php?slug=cafe-monochi          → default published menu
 *   menu.php?qr=<menu qr token>         → menu by QR
 *   menu.php?qr=<token>&t=<table token> → table-specific ordering
 */

require __DIR__ . '/app/bootstrap.php';

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Security;
use App\Core\Session;
use App\Core\View;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Tenant;

Security::headers();
Session::start();

$qr    = Request::query('qr');
$slug  = Request::query('slug');
$tableToken = Request::query('t');

$menu = null;
if ($qr !== '') {
    $menu = (new Menu())->publishedByQr($qr);
} elseif ($slug !== '') {
    $tenant = (new Tenant())->findBySlug($slug);
    if ($tenant !== null) {
        $menu = (new Menu())->publishedDefaultForTenant((int) $tenant['id']);
    }
}

if ($menu === null) {
    http_response_code(404);
    View::render('public/menu-missing', [
        'title' => 'منو پیدا نشد | ' . SITE_NAME,
    ]);
    exit;
}

$tenantId = (int) $menu['tenant_id'];

$table = null;
if ($tableToken !== '') {
    $table = (new DiningTable())->findByQr($tableToken, $tenantId);
}

$categories = (new Category())->forMenu((int) $menu['id'], true);
$products   = (new Product())->forMenu((int) $menu['id']);

$grouped = [];
foreach ($products as $product) {
    $grouped[(int) $product['category_id']][] = [
        'id'          => (int) $product['id'],
        'name'        => (string) $product['name'],
        'description' => (string) ($product['description'] ?? ''),
        'price'       => (int) $product['price'],
        'old_price'   => $product['compare_at_price'] === null ? null : (int) $product['compare_at_price'],
        'image'       => Product::imageUrl($product['image'] ?? null),
        'featured'    => (int) $product['is_featured'] === 1,
        'calories'    => $product['calories'] === null ? null : (int) $product['calories'],
        'allergens'   => (string) ($product['allergens'] ?? ''),
    ];
}

$sections = [];
foreach ($categories as $category) {
    $id = (int) $category['id'];
    if (empty($grouped[$id])) {
        continue;
    }
    $sections[] = [
        'id'          => $id,
        'name'        => (string) $category['name'],
        'description' => (string) ($category['description'] ?? ''),
        'products'    => $grouped[$id],
    ];
}

$totalProducts = array_sum(array_map(static fn (array $section): int => count($section['products']), $sections));

View::render('public/menu', [
    'title'       => $menu['tenant_name'] . ' | ' . $menu['name'] . ' — ' . SITE_NAME,
    'menu'        => $menu,
    'sections'    => $sections,
    'table'       => $table,
    'csrf'        => Csrf::token(),
    'api'         => api_url(),
    'base'        => url(''),
    'logo'        => Product::imageUrl($menu['tenant_logo'] ?? null),
    'totalProducts' => $totalProducts,
]);
