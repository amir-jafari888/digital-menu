<?php
declare(strict_types=1);

/**
 * JSON API front controller.
 *
 *   /api/index.php/auth/login
 *   /api/index.php/menu/{slug}
 *   /api/index.php/orders
 *
 * PATH_INFO is used so the routes work on the PHP built-in server and on
 * Apache without any rewrite rules.
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Security;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\OrderService;

Security::headers(true);
Session::start();
RateLimiter::prune();

/**
 * CSRF is only meaningful for requests authenticated by a session cookie. A
 * client without a session (or with an anonymous one) has no ambient
 * credentials to abuse, so it may call the public endpoints directly — still
 * rate limited. Authenticated cookie requests always need a token.
 */
function require_csrf_when_cookie(): void
{
    if (isset($_COOKIE[session_name()]) && Auth::check()) {
        Csrf::assert();
    }
}

function client_rate_limit(string $bucket, int $max, int $window, int $block = 0): void
{
    $result = RateLimiter::attempt('api:' . $bucket . ':' . Security::ipHash(), $max, $window, $block);
    if (!$result['allowed']) {
        header('Retry-After: ' . $result['retry_after']);
        Response::error(
            'تعداد درخواست‌ها زیاد بود. تا ' . RateLimiter::humanRetry($result['retry_after']) . ' دیگر صبر کن.',
            429,
            ['retry_after' => $result['retry_after']]
        );
    }
}

/** @return array<string, mixed> */
function public_user(): array
{
    $user = Auth::user() ?? [];

    return [
        'id'                   => (int) ($user['id'] ?? 0),
        'name'                 => (string) ($user['name'] ?? ''),
        'role'                 => (string) ($user['role'] ?? ''),
        'tenant_id'            => isset($user['tenant_id']) ? (int) $user['tenant_id'] : null,
        'tenant_name'          => (string) ($user['tenant_name'] ?? ''),
        'must_change_password' => (int) ($user['must_change_password'] ?? 0) === 1,
    ];
}

$router = new Router();

// ---------------------------------------------------------------------------
// Health
// ---------------------------------------------------------------------------
$router->get('/', static function (): void {
    Response::ok(['service' => 'monochi-api', 'version' => 1]);
});

$router->get('/health', static function (): void {
    $db = false;
    try {
        $db = Database::scalar('SELECT 1') === 1 || Database::scalar('SELECT 1') === '1';
    } catch (Throwable) {
        $db = false;
    }

    Response::json(['ok' => $db, 'database' => $db ? 'up' : 'down']);
});

// ---------------------------------------------------------------------------
// Authentication
// ---------------------------------------------------------------------------
$router->post('/auth/login', static function (): void {
    require_csrf_when_cookie();
    client_rate_limit('login', 20, 600, 600);

    $identifier = Request::string('identifier');
    $password   = (string) Request::input('password', '');

    if ($identifier === '' || $password === '') {
        Response::error('ایمیل/موبایل و رمز عبور را وارد کن.', 422);
    }

    $result = Auth::attempt($identifier, $password);
    if (!$result['ok']) {
        $status = isset($result['retry_after']) ? 429 : 401;
        Response::error((string) $result['message'], $status, array_filter([
            'retry_after' => $result['retry_after'] ?? null,
        ]));
    }

    Response::ok([
        'user'       => public_user(),
        'csrf_token' => Csrf::token(),
    ]);
});

$router->post('/auth/logout', static function (): void {
    Csrf::assert();
    Auth::logout();
    Response::ok(['message' => 'خارج شدی.']);
});

$router->get('/auth/me', static function (): void {
    if (!Auth::check()) {
        Response::error('وارد نشده‌ای.', 401);
    }

    Response::ok([
        'user'       => public_user(),
        'csrf_token' => Csrf::token(),
    ]);
});

// ---------------------------------------------------------------------------
// Public menu
// ---------------------------------------------------------------------------
$renderMenu = static function (array $menu, ?int $tableId = null): void {
    $categories = (new Category())->forMenu((int) $menu['id'], true);
    $products   = (new Product())->forMenu((int) $menu['id']);

    $grouped = [];
    foreach ($products as $product) {
        $grouped[(int) $product['category_id']][] = [
            'id'               => (int) $product['id'],
            'name'             => (string) $product['name'],
            'description'      => (string) ($product['description'] ?? ''),
            'price'            => (int) $product['price'],
            'compare_at_price' => $product['compare_at_price'] === null ? null : (int) $product['compare_at_price'],
            'image'            => Product::imageUrl($product['image'] ?? null),
            'is_featured'      => (int) $product['is_featured'] === 1,
            'calories'         => $product['calories'] === null ? null : (int) $product['calories'],
            'allergens'        => (string) ($product['allergens'] ?? ''),
        ];
    }

    $categoryPayload = [];
    foreach ($categories as $category) {
        $id = (int) $category['id'];
        $categoryPayload[] = [
            'id'          => $id,
            'name'        => (string) $category['name'],
            'description' => (string) ($category['description'] ?? ''),
            'products'    => $grouped[$id] ?? [],
        ];
    }

    Response::ok([
        'tenant' => [
            'name'        => (string) $menu['tenant_name'],
            'slug'        => (string) $menu['tenant_slug'],
            'description' => (string) ($menu['tenant_description'] ?? ''),
            'phone'       => (string) ($menu['tenant_phone'] ?? ''),
            'address'     => (string) ($menu['tenant_address'] ?? ''),
            'logo'        => Product::imageUrl($menu['tenant_logo'] ?? null),
        ],
        'menu' => [
            'id'        => (int) $menu['id'],
            'name'      => (string) $menu['name'],
            'token'     => (string) $menu['qr_token'],
            'table_id'  => $tableId,
        ],
        'categories' => $categoryPayload,
    ]);
};

$router->get('/menu/{slug}', static function (array $params) use ($renderMenu): void {
    client_rate_limit('menu', 120, 60);

    $slug = (string) $params['slug'];
    $tenant = (new Tenant())->findBySlug($slug);
    if ($tenant === null || $tenant['status'] !== 'active') {
        Response::error('این منو در دسترس نیست.', 404);
    }

    $menu = (new Menu())->publishedDefaultForTenant((int) $tenant['id']);
    if ($menu === null) {
        Response::error('منوی منتشرشده‌ای برای این کسب‌وکار وجود ندارد.', 404);
    }

    $renderMenu($menu);
});

$router->get('/menu/qr/{token}', static function (array $params) use ($renderMenu): void {
    client_rate_limit('menu', 120, 60);

    $menu = (new Menu())->publishedByQr((string) $params['token']);
    if ($menu === null) {
        Response::error('این منو در دسترس نیست.', 404);
    }

    $renderMenu($menu);
});

// ---------------------------------------------------------------------------
// Orders (public)
// ---------------------------------------------------------------------------
$router->post('/orders', static function (): void {
    require_csrf_when_cookie();
    client_rate_limit('order', 8, 600, 1800);

    $slug = Request::string('tenant');
    $menuToken = Request::string('menu_token');
    $tableToken = Request::string('table_token');

    $menu = null;
    if ($menuToken !== '') {
        $menu = (new Menu())->publishedByQr($menuToken);
    } elseif ($slug !== '') {
        $tenant = (new Tenant())->findBySlug($slug);
        if ($tenant !== null && $tenant['status'] === 'active') {
            $menu = (new Menu())->publishedDefaultForTenant((int) $tenant['id']);
        }
    }

    if ($menu === null) {
        Response::error('منوی انتخابی معتبر نیست.', 404);
    }

    $tableId = null;
    if ($tableToken !== '') {
        $table = (new App\Models\DiningTable())->findByQr($tableToken, (int) $menu['tenant_id']);
        if ($table === null) {
            Response::error('میز انتخابی معتبر نیست.', 422);
        }
        $tableId = (int) $table['id'];
    }

    $result = OrderService::place(
        (int) $menu['tenant_id'],
        [
            'items'          => Request::input('items', []),
            'type'           => Request::string('type', 'dine_in'),
            'note'           => Request::string('note'),
            'customer_phone' => Request::string('customer_phone'),
            'customer_name'  => Request::string('customer_name'),
        ],
        (int) $menu['id'],
        $tableId
    );

    if (!$result['ok']) {
        Response::error((string) ($result['error'] ?? 'ثبت سفارش انجام نشد.'), 422);
    }

    $order = $result['order'];

    Response::ok([
        'order' => [
            'code'  => (string) $order['code'],
            'total' => (int) $order['total'],
            'type'  => (string) $order['type'],
        ],
    ], 201);
});

// ---------------------------------------------------------------------------
// Ads
// ---------------------------------------------------------------------------
$router->get('/ads', static function (): void {
    client_rate_limit('ads', 60, 60);

    $placement = Request::query('placement', 'app_home');
    if (!in_array($placement, Campaign::PLACEMENTS, true)) {
        Response::error('جایگاه تبلیغ نامعتبر است.', 422);
    }

    $ads = [];
    foreach ((new Campaign())->serving($placement, 5) as $campaign) {
        $ads[] = [
            'id'          => (int) $campaign['id'],
            'title'       => (string) $campaign['title'],
            'description' => (string) $campaign['description'],
            'image'       => Product::imageUrl($campaign['image'] ?? null),
            'cta_url'     => Security::safeUrl($campaign['cta_url'] ?? null),
            'tenant'      => (string) $campaign['tenant_name'],
            'tenant_slug' => (string) $campaign['tenant_slug'],
        ];
    }

    Response::ok(['ads' => $ads]);
});

$router->post('/ads/{id}/{event}', static function (array $params): void {
    require_csrf_when_cookie();
    client_rate_limit('ad-event', 60, 60);

    $id    = (int) $params['id'];
    $event = (string) $params['event'];
    if ($id <= 0 || !in_array($event, ['impression', 'click'], true)) {
        Response::error('درخواست نامعتبر است.', 422);
    }

    $ipHash = Security::ipHash();

    // Deduplicate: one event per IP per campaign per hour.
    $duplicate = (int) Database::scalar(
        'SELECT COUNT(*) FROM ad_events
         WHERE campaign_id = ? AND type = ? AND ip_hash = ? AND created_at > NOW() - INTERVAL 1 HOUR',
        [$id, $event, $ipHash]
    );
    if ($duplicate > 0) {
        Response::ok(['tracked' => false]);
    }

    $tracked = Campaign::track($id, $event, $ipHash);
    Response::ok(['tracked' => $tracked]);
});

// ---------------------------------------------------------------------------
// Contact
// ---------------------------------------------------------------------------
$router->post('/contact', static function (): void {
    require_csrf_when_cookie();
    client_rate_limit('contact', 3, 3600, 3600);

    // Honeypot: real users never fill this hidden field.
    if (Request::string('website') !== '') {
        Response::ok(['message' => 'پیام دریافت شد.']);
    }

    $validated = Validator::validate(Request::all(), [
        'name'    => 'required|string|max:120',
        'contact' => 'required|string|max:190',
        'subject' => 'nullable|string|max:120',
        'message' => 'required|string|min:10|max:4000',
    ], [
        'name'    => 'نام',
        'contact' => 'ایمیل یا شماره تماس',
        'subject' => 'موضوع',
        'message' => 'متن پیام',
    ]);

    if (!$validated['valid']) {
        Response::error((string) reset($validated['errors']), 422, ['errors' => $validated['errors']]);
    }

    $contact = (string) $validated['data']['contact'];
    if (!filter_var($contact, FILTER_VALIDATE_EMAIL) && !preg_match('/^(?:0|\+?98)?9\d{9}$/', preg_replace('/[\s\-()]/', '', Validator::normaliseDigits($contact)) ?? '')) {
        Response::error('ایمیل یا شماره تماس معتبر نیست.', 422);
    }

    (new ContactMessage())->store($validated['data'], Security::ipHash(), Request::userAgent());

    Response::ok(['message' => 'پیامت رسید. به‌زودی پاسخ می‌دهیم.'], 201);
});

$router->fallback(static function (): void {
    Response::error('مسیر API پیدا نشد.', 404);
});

$router->dispatch(Request::method(), Request::path());
