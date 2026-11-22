<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Validator;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\Product;
use App\Models\UploadedFile;
use App\Services\UploadService;

final class CatalogController extends BaseAdminController
{
    // ---------------------------------------------------------------- products
    public static function products(): void
    {
        $tenantId = (int) self::tenantId();

        $filters = [
            'search'      => Request::query('q'),
            'category_id' => (int) Request::query('category', '0') ?: null,
            'available'   => Request::query('available', '') === '' ? null : Request::query('available') === '1',
        ];

        $products = (new Product())->forTenant($tenantId, $filters, 200);
        $total    = (new Product())->countForTenant($tenantId, $filters);

        self::render('products/index', [
            'products'   => $products,
            'categories' => (new Category())->forTenant($tenantId),
            'filters'    => $filters,
            'total'      => $total,
            'baseUrl'    => admin_url('products'),
        ], 'محصولات', 'products', '<a class="m3-button m3-button--filled m3-button--sm" href="' . e(admin_url('products/new')) . '">+ محصول جدید</a>');
    }

    public static function productCreate(): void
    {
        $tenantId = (int) self::tenantId();
        $categories = (new Category())->forTenant($tenantId);

        if ($categories === []) {
            self::flashError('اول یک دسته‌بندی بساز، بعد محصول اضافه کن.');
            self::back(admin_url('categories'));
        }

        self::render('products/form', [
            'product'    => null,
            'categories' => $categories,
            'errors'     => [],
            'formAction' => admin_url('products'),
        ], 'محصول جدید', 'products');
    }

    public static function productStore(): void
    {
        $tenantId = (int) self::tenantId();
        $payload  = self::productPayload($tenantId);

        if (!$payload['ok']) {
            self::render('products/form', [
                'product'    => $payload['old'],
                'categories' => (new Category())->forTenant($tenantId),
                'errors'     => $payload['errors'],
                'formAction' => admin_url('products'),
            ], 'محصول جدید', 'products');

            return;
        }

        $data = $payload['data'];
        $data['tenant_id']   = $tenantId;
        $data['sort_order']  = (int) ($data['sort_order'] ?? 0) ?: (new Product())->nextSortOrder($tenantId);
        $data['image']       = self::handleImage($tenantId, null);

        $id = (new Product())->create($data);
        self::audit('product.created', 'product', $id, ['name' => $data['name']]);

        self::flashOk('محصول «' . $data['name'] . '» اضافه شد.');
        self::back(admin_url('products'));
    }

    public static function productEdit(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $product  = (new Product())->findForTenant((int) $params['id'], $tenantId);

        if ($product === null) {
            self::flashError('محصول پیدا نشد.');
            self::back(admin_url('products'));
        }

        self::render('products/form', [
            'product'    => $product,
            'categories' => (new Category())->forTenant($tenantId),
            'errors'     => [],
            'formAction' => admin_url('products/' . (int) $product['id']),
        ], 'ویرایش محصول', 'products');
    }

    public static function productUpdate(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $product  = (new Product())->findForTenant((int) $params['id'], $tenantId);

        if ($product === null) {
            self::flashError('محصول پیدا نشد.');
            self::back(admin_url('products'));
        }

        $payload = self::productPayload($tenantId);

        if (!$payload['ok']) {
            self::render('products/form', [
                'product'    => array_merge($product, $payload['old']),
                'categories' => (new Category())->forTenant($tenantId),
                'errors'     => $payload['errors'],
                'formAction' => admin_url('products/' . (int) $product['id']),
            ], 'ویرایش محصول', 'products');

            return;
        }

        $data = $payload['data'];
        $image = self::handleImage($tenantId, (string) ($product['image'] ?? ''));

        if (Request::bool('remove_image')) {
            $image = null;
        }
        $data['image'] = $image;

        (new Product())->update((int) $product['id'], $data);
        self::audit('product.updated', 'product', (int) $product['id'], ['name' => $data['name']]);

        self::flashOk('تغییرات محصول ذخیره شد.');
        self::back(admin_url('products'));
    }

    public static function productToggle(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $product  = (new Product())->findForTenant((int) $params['id'], $tenantId);

        if ($product === null) {
            self::flashError('محصول پیدا نشد.');
            self::back(admin_url('products'));
        }

        $next = (int) $product['is_available'] === 1 ? 0 : 1;
        (new Product())->update((int) $product['id'], ['is_available' => $next]);
        self::audit('product.availability', 'product', (int) $product['id'], ['available' => $next]);

        self::flashOk($next === 1 ? 'محصول موجود شد.' : 'محصول ناموجود شد.');
        self::back(admin_url('products'));
    }

    public static function productDelete(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $product  = (new Product())->findForTenant((int) $params['id'], $tenantId);

        if ($product === null) {
            self::flashError('محصول پیدا نشد.');
            self::back(admin_url('products'));
        }

        if (!self::isOwner()) {
            self::flashError('فقط مالک کسب‌وکار می‌تواند محصول حذف کند.');
            self::back(admin_url('products'));
        }

        (new Product())->delete((int) $product['id']);
        self::audit('product.deleted', 'product', (int) $product['id'], ['name' => $product['name']]);

        self::flashOk('محصول حذف شد.');
        self::back(admin_url('products'));
    }

    // -------------------------------------------------------------- categories
    public static function categories(): void
    {
        $tenantId = (int) self::tenantId();

        self::render('categories/index', [
            'categories' => (new Category())->forTenant($tenantId),
            'menus'      => (new Menu())->forTenant($tenantId),
        ], 'دسته‌بندی‌ها', 'categories');
    }

    public static function categoryStore(): void
    {
        $tenantId = (int) self::tenantId();
        $validated = Validator::validate(Request::all(), [
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'menu_id'     => 'required|int',
            'sort_order'  => 'nullable|int|min:0|max:9999',
        ], [
            'name'        => 'نام دسته',
            'description' => 'توضیح',
            'menu_id'     => 'منو',
            'sort_order'  => 'ترتیب',
        ]);

        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('categories'));
        }

        $menuId = (int) $validated['data']['menu_id'];
        if (!(new Menu())->belongsToTenant($menuId, $tenantId)) {
            self::flashError('منوی انتخابی معتبر نیست.');
            self::back(admin_url('categories'));
        }

        $id = (new Category())->create([
            'tenant_id'   => $tenantId,
            'menu_id'     => $menuId,
            'name'        => $validated['data']['name'],
            'description' => (string) ($validated['data']['description'] ?? ''),
            'sort_order'  => (int) ($validated['data']['sort_order'] ?? 0) ?: (new Category())->nextSortOrder($menuId),
            'is_active'   => 1,
        ]);

        self::audit('category.created', 'category', $id, ['name' => $validated['data']['name']]);
        self::flashOk('دسته‌بندی اضافه شد.');
        self::back(admin_url('categories'));
    }

    public static function categoryUpdate(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $category = (new Category())->findForTenant((int) $params['id'], $tenantId);

        if ($category === null) {
            self::flashError('دسته‌بندی پیدا نشد.');
            self::back(admin_url('categories'));
        }

        $validated = Validator::validate(Request::all(), [
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'sort_order'  => 'nullable|int|min:0|max:9999',
        ], [
            'name'        => 'نام دسته',
            'description' => 'توضیح',
            'sort_order'  => 'ترتیب',
        ]);

        if (!$validated['valid']) {
            self::flashError((string) reset($validated['errors']));
            self::back(admin_url('categories'));
        }

        (new Category())->update((int) $category['id'], [
            'name'        => $validated['data']['name'],
            'description' => (string) ($validated['data']['description'] ?? ''),
            'sort_order'  => (int) ($validated['data']['sort_order'] ?? $category['sort_order']),
            'is_active'   => Request::bool('is_active') ? 1 : 0,
        ]);

        self::audit('category.updated', 'category', (int) $category['id']);
        self::flashOk('دسته‌بندی به‌روز شد.');
        self::back(admin_url('categories'));
    }

    public static function categoryDelete(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $category = (new Category())->findForTenant((int) $params['id'], $tenantId);

        if ($category === null) {
            self::flashError('دسته‌بندی پیدا نشد.');
            self::back(admin_url('categories'));
        }

        (new Category())->delete((int) $category['id']);
        self::audit('category.deleted', 'category', (int) $category['id'], ['name' => $category['name']]);
        self::flashOk('دسته‌بندی حذف شد. محصولات آن بدون دسته شدند.');
        self::back(admin_url('categories'));
    }

    // ------------------------------------------------------------- menus/tables
    public static function menus(): void
    {
        $tenantId = (int) self::tenantId();

        self::render('menus/index', [
            'menus'  => (new Menu())->forTenant($tenantId),
            'tables' => (new DiningTable())->forTenant($tenantId),
            'tenant' => self::tenant(),
        ], 'منوها و میزها', 'menus');
    }

    public static function menuStore(): void
    {
        $tenantId = (int) self::tenantId();
        $name = mb_substr(trim(Request::string('name')), 0, 100);

        if ($name === '') {
            self::flashError('نام منو را وارد کن.');
            self::back(admin_url('menus'));
        }

        $menus = new Menu();
        $isDefault = $menus->forTenant($tenantId) === [];
        $id = $menus->createForTenant($tenantId, $name, $isDefault);

        self::audit('menu.created', 'menu', $id, ['name' => $name]);
        self::flashOk('منو ساخته شد. آدرس QR آن در جدول منوها آمده است.');
        self::back(admin_url('menus'));
    }

    public static function menuDefault(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $menus = new Menu();

        if (!$menus->belongsToTenant((int) $params['id'], $tenantId)) {
            self::flashError('منو پیدا نشد.');
            self::back(admin_url('menus'));
        }

        $menus->makeDefault($tenantId, (int) $params['id']);
        self::audit('menu.default', 'menu', (int) $params['id']);
        self::flashOk('منوی پیش‌فرض تغییر کرد.');
        self::back(admin_url('menus'));
    }

    public static function menuPublish(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $menu = (new Menu())->find((int) $params['id']);

        if ($menu === null || (int) $menu['tenant_id'] !== $tenantId) {
            self::flashError('منو پیدا نشد.');
            self::back(admin_url('menus'));
        }

        $next = (int) $menu['is_published'] === 1 ? 0 : 1;
        (new Menu())->update((int) $menu['id'], ['is_published' => $next]);
        self::audit('menu.publish', 'menu', (int) $menu['id'], ['published' => $next]);
        self::flashOk($next === 1 ? 'منو منتشر شد.' : 'منو از دسترس خارج شد.');
        self::back(admin_url('menus'));
    }

    public static function tableStore(): void
    {
        $tenantId = (int) self::tenantId();
        $label = mb_substr(trim(Request::string('label')), 0, 40);

        if ($label === '') {
            self::flashError('برچسب میز را وارد کن.');
            self::back(admin_url('menus'));
        }

        $tables = new DiningTable();
        if ($tables->labelTaken($tenantId, $label)) {
            self::flashError('میز دیگری با این برچسب وجود دارد.');
            self::back(admin_url('menus'));
        }

        $id = $tables->createForTenant($tenantId, $label);
        self::audit('table.created', 'table', $id, ['label' => $label]);
        self::flashOk('میز اضافه شد.');
        self::back(admin_url('menus'));
    }

    public static function tableToggle(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $table = (new DiningTable())->findForTenant((int) $params['id'], $tenantId);

        if ($table === null) {
            self::flashError('میز پیدا نشد.');
            self::back(admin_url('menus'));
        }

        $next = (int) $table['is_active'] === 1 ? 0 : 1;
        (new DiningTable())->update((int) $table['id'], ['is_active' => $next]);
        self::flashOk($next === 1 ? 'میز فعال شد.' : 'میز غیرفعال شد.');
        self::back(admin_url('menus'));
    }

    public static function tableDelete(array $params): void
    {
        $tenantId = (int) self::tenantId();
        $table = (new DiningTable())->findForTenant((int) $params['id'], $tenantId);

        if ($table === null) {
            self::flashError('میز پیدا نشد.');
            self::back(admin_url('menus'));
        }

        (new DiningTable())->delete((int) $table['id']);
        self::audit('table.deleted', 'table', (int) $table['id'], ['label' => $table['label']]);
        self::flashOk('میز حذف شد.');
        self::back(admin_url('menus'));
    }

    // ----------------------------------------------------------------- helpers
    /**
     * @return array{ok:bool, data?:array<string,mixed>, errors?:array<string,string>, old?:array<string,mixed>}
     */
    private static function productPayload(int $tenantId): array
    {
        $validated = Validator::validate(Request::all(), [
            'name'             => 'required|string|max:160',
            'description'      => 'nullable|string|max:4000',
            'price'            => 'required|int|min:0|max:100000000',
            'compare_at_price' => 'nullable|int|min:0|max:100000000',
            'category_id'      => 'required|int',
            'calories'         => 'nullable|int|min:0|max:5000',
            'allergens'        => 'nullable|string|max:255',
            'sort_order'       => 'nullable|int|min:0|max:9999',
        ], [
            'name'             => 'نام محصول',
            'description'      => 'توضیحات',
            'price'            => 'قیمت',
            'compare_at_price' => 'قیمت قبل از تخفیف',
            'category_id'      => 'دسته‌بندی',
            'calories'         => 'کالری',
            'allergens'        => 'حساسیت‌زاها',
            'sort_order'       => 'ترتیب',
        ]);

        if (!$validated['valid']) {
            return ['ok' => false, 'errors' => $validated['errors'], 'old' => Request::all()];
        }

        $data = $validated['data'];
        $categoryId = (int) $data['category_id'];

        if (!(new Category())->belongsToTenant($categoryId, $tenantId)) {
            return ['ok' => false, 'errors' => ['category_id' => 'دسته‌بندی انتخابی مال این کسب‌وکار نیست.'], 'old' => Request::all()];
        }

        if ($data['compare_at_price'] !== null && (int) $data['compare_at_price'] > 0 && (int) $data['compare_at_price'] <= (int) $data['price']) {
            $data['compare_at_price'] = null;
        }

        $data['description'] = Validator::cleanText((string) ($data['description'] ?? ''));
        $data['allergens']   = mb_substr(Validator::cleanText((string) ($data['allergens'] ?? '')), 0, 255);
        $data['is_available'] = Request::bool('is_available') ? 1 : 0;
        $data['is_featured']  = Request::bool('is_featured') ? 1 : 0;

        return ['ok' => true, 'data' => $data];
    }

    /** Upload a new image when one was sent, otherwise keep $current. */
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

        // Remove the previous private upload now that it has been replaced.
        if ($current !== null && preg_match('#^uploads/(\d+)\.#', $current, $m)) {
            (new UploadedFile())->deleteWithFile((int) $m[1]);
        }

        return (string) $result['path'];
    }

    private static function isOwner(): bool
    {
        return Auth::isSuperAdmin() || Auth::role() === 'owner';
    }

    /** Unused placeholder kept explicit so static analysis notices future use. */
    private static function menuLinks(array $menus): array
    {
        return array_map(static fn (array $menu): string => url('menu.php?qr=' . $menu['qr_token']), $menus);
    }
}
