<?php
declare(strict_types=1);

/**
 * Admin front controller.
 *
 *   /admin/index.php/products
 *   /admin/index.php/orders/12
 *
 * Every POST goes through CSRF verification, a per-user flood limit and the
 * role/ability check. Tenant scoping is enforced inside the controllers.
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\Admin\CampaignController;
use App\Controllers\Admin\CatalogController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\PlatformController;
use App\Controllers\Admin\SalesController;
use App\Controllers\Admin\SettingsController;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Security;
use App\Core\Session;

Security::headers();
Session::start();
RateLimiter::prune();

/** Force a password change before anything else can be used. */
function guard_password_state(): void
{
    $user = Auth::user();
    if ($user === null || (int) ($user['must_change_password'] ?? 0) !== 1) {
        return;
    }

    $path = Request::path();
    if ($path === '/settings' || $path === '/settings/password') {
        return;
    }

    Session::flash('error', 'برای ادامه، ابتدا رمز عبور موقت خود را تغییر بده.');
    Response::redirect(admin_url('settings#password'));
}

$router = new Router();

$get = static function (string $pattern, string $ability, callable $handler) use ($router): void {
    $router->get($pattern, static function (array $params = []) use ($ability, $handler): void {
        Auth::requireLogin();
        if ($ability !== '') {
            Auth::requireAbility($ability);
        }
        guard_password_state();
        $handler($params);
    });
};

$post = static function (string $pattern, string $ability, callable $handler) use ($router): void {
    $router->post($pattern, static function (array $params = []) use ($ability, $handler): void {
        Auth::requireLogin();
        if ($ability !== '') {
            Auth::requireAbility($ability);
        }
        guard_password_state();
        Csrf::assert();

        $limit = RateLimiter::attempt('admin:post:' . Auth::id(), 90, 60, 120);
        if (!$limit['allowed']) {
            Session::flash('error', 'تعداد درخواست‌ها زیاد بود. چند لحظه صبر کن.');
            Response::back(admin_url());
        }

        $handler($params);
    });
};

// ------------------------------------------------------------------ dashboard
$get('/', '', [DashboardController::class, 'index']);

// ------------------------------------------------------------------- products
$get('/products', 'products.view', [CatalogController::class, 'products']);
$get('/products/new', 'products.manage', [CatalogController::class, 'productCreate']);
$post('/products', 'products.manage', [CatalogController::class, 'productStore']);
$get('/products/{id}/edit', 'products.manage', [CatalogController::class, 'productEdit']);
$post('/products/{id}/toggle', 'products.manage', [CatalogController::class, 'productToggle']);
$post('/products/{id}/delete', 'products.manage', [CatalogController::class, 'productDelete']);
$post('/products/{id}', 'products.manage', [CatalogController::class, 'productUpdate']);

// ----------------------------------------------------------------- categories
$get('/categories', 'categories.view', [CatalogController::class, 'categories']);
$post('/categories', 'categories.manage', [CatalogController::class, 'categoryStore']);
$post('/categories/{id}', 'categories.manage', [CatalogController::class, 'categoryUpdate']);
$post('/categories/{id}/delete', 'categories.manage', [CatalogController::class, 'categoryDelete']);

// ------------------------------------------------------------------- menus/qr
$get('/menus', 'menu.view', [CatalogController::class, 'menus']);
$post('/menus', 'menu.manage', [CatalogController::class, 'menuStore']);
$post('/menus/{id}/default', 'menu.manage', [CatalogController::class, 'menuDefault']);
$post('/menus/{id}/publish', 'menu.manage', [CatalogController::class, 'menuPublish']);

$post('/tables', 'tables.manage', [CatalogController::class, 'tableStore']);
$post('/tables/{id}/toggle', 'tables.manage', [CatalogController::class, 'tableToggle']);
$post('/tables/{id}/delete', 'tables.manage', [CatalogController::class, 'tableDelete']);

// -------------------------------------------------------------------- orders
$get('/orders', 'orders.view', [SalesController::class, 'orders']);
$get('/orders/{id}', 'orders.view', [SalesController::class, 'orderShow']);
$post('/orders/{id}/status', 'orders.update', [SalesController::class, 'orderStatus']);

// ----------------------------------------------------------------- customers
$get('/customers', 'customers.view', [SalesController::class, 'customers']);
$get('/customers/{id}', 'customers.view', [SalesController::class, 'customerShow']);
$post('/customers/{id}/points', 'customers.manage', [SalesController::class, 'customerPoints']);
$post('/customers/{id}/redeem', 'customers.manage', [SalesController::class, 'customerRedeem']);
$post('/redemptions/{id}/used', 'customers.manage', [SalesController::class, 'redemptionUsed']);

// ------------------------------------------------------------------- rewards
$get('/rewards', 'rewards.view', [SalesController::class, 'rewards']);
$post('/rewards', 'rewards.manage', [SalesController::class, 'rewardStore']);
$post('/rewards/{id}', 'rewards.manage', [SalesController::class, 'rewardUpdate']);
$post('/rewards/{id}/delete', 'rewards.manage', [SalesController::class, 'rewardDelete']);

// ----------------------------------------------------------------- campaigns
$get('/campaigns', 'campaigns.view', [CampaignController::class, 'index']);
$get('/campaigns/new', 'campaigns.manage', [CampaignController::class, 'create']);
$post('/campaigns', 'campaigns.manage', [CampaignController::class, 'store']);
$get('/campaigns/{id}/edit', 'campaigns.manage', [CampaignController::class, 'edit']);
$post('/campaigns/{id}/status', 'campaigns.manage', [CampaignController::class, 'status']);
$post('/campaigns/{id}/delete', 'campaigns.manage', [CampaignController::class, 'destroy']);
$post('/campaigns/{id}', 'campaigns.manage', [CampaignController::class, 'update']);

// ------------------------------------------------------------------ settings
$get('/settings', '', [SettingsController::class, 'index']);
$post('/settings/site', 'settings.manage', [SettingsController::class, 'saveSite']);
$post('/settings/business', 'settings.manage', [SettingsController::class, 'saveBusiness']);
$post('/settings/profile', '', [SettingsController::class, 'saveProfile']);
$post('/settings/password', '', [SettingsController::class, 'savePassword']);

// --------------------------------------------------------------------- users
$get('/users', 'users.view', [SettingsController::class, 'users']);
$post('/users', 'users.manage', [SettingsController::class, 'userStore']);
$post('/users/{id}/password', 'users.manage', [SettingsController::class, 'userPassword']);
$post('/users/{id}', 'users.manage', [SettingsController::class, 'userUpdate']);

// ------------------------------------------------------------------ platform
$get('/tenants', 'tenants.view', [PlatformController::class, 'tenants']);
$post('/tenants', 'tenants.manage', [PlatformController::class, 'tenantStore']);
$post('/tenants/exit', 'tenants.manage', [PlatformController::class, 'tenantExit']);
$post('/tenants/{id}/act', 'tenants.manage', [PlatformController::class, 'tenantAct']);
$post('/tenants/{id}', 'tenants.manage', [PlatformController::class, 'tenantUpdate']);

$get('/subscriptions', 'platform.subscriptions', [PlatformController::class, 'subscriptions']);
$get('/messages', 'platform.messages', [PlatformController::class, 'messages']);
$post('/messages/{id}/status', 'platform.messages', [PlatformController::class, 'messageStatus']);
$get('/logs', 'platform.logs', [PlatformController::class, 'logs']);

$router->fallback(static function (): void {
    Response::notFound('این صفحهٔ پنل وجود ندارد.');
});

$router->dispatch(Request::method(), Request::path());
