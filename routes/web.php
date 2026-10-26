
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsignmentCustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\ExpeditionController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LabelPrintController;
use App\Http\Controllers\LabelSettingController;
use App\Http\Controllers\OperationalExpenseController;
use App\Http\Controllers\OperationalMasterController;
use App\Http\Controllers\OperationalPurchaseController;
use App\Http\Controllers\OutstandingDoController;
use App\Http\Controllers\OutstandingPartController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    fn () => auth()->check()
        ? redirect(auth()->user()->homePath())
        : redirect()->route('login')
);

/*
|--------------------------------------------------------------------------
| CATALOG PDF
|--------------------------------------------------------------------------
*/

Route::get(
    '/catalog/patria-air-brake-system-parts',
    fn () => response()->file(
        public_path('catalogs/patria-air-brake-system-parts.pdf'),
        ['Content-Type' => 'application/pdf']
    )
)->name('catalogs.patria');

Route::get(
    '/catalog/products/{filename}',
    [ProductCategoryController::class, 'catalog']
)
    ->where('filename', '[A-Za-z0-9]{40}\\.pdf')
    ->name('catalogs.products');

/*
|--------------------------------------------------------------------------
| GUEST
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get(
        '/login',
        [AuthController::class, 'create']
    )->name('login');

    Route::post(
        '/login',
        [AuthController::class, 'store']
    )->name('login.store');

    Route::get(
        '/pos/login',
        [AuthController::class, 'createPos']
    )->name('pos.login');

    Route::post(
        '/pos/login',
        [AuthController::class, 'storePos']
    )->name('pos.login.store');

    Route::get(
        '/forgot-password',
        [ForgotPasswordController::class, 'create']
    )->name('password.request');

    Route::post(
        '/forgot-password',
        [ForgotPasswordController::class, 'store']
    )->name('password.email');

    Route::get(
        '/reset-password/{token}',
        [ResetPasswordController::class, 'create']
    )->name('password.reset');

    Route::post(
        '/reset-password',
        [ResetPasswordController::class, 'store']
    )->name('password.store');
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::put(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::put(
        '/profile/password',
        [ProfileController::class, 'updatePassword']
    )->name('profile.password');

    Route::post(
        '/logout',
        [AuthController::class, 'destroy']
    )->name('logout');
});

/*
|--------------------------------------------------------------------------
| POS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'portal:pos',
    'permission:pos.access',
])->group(function () {

    Route::get(
        '/pos',
        [PosController::class, 'index']
    )->name('pos.index');
});

/*
|--------------------------------------------------------------------------
| BACKOFFICE
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'portal:backoffice',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        DashboardController::class
    )
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | LABEL
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/labels',
        [LabelPrintController::class, 'index']
    )
        ->middleware('permission:labels.view')
        ->name('labels.index');

    Route::get(
        '/labels/create',
        [LabelPrintController::class, 'create']
    )
        ->middleware('permission:labels.create')
        ->name('labels.create');

    Route::post(
        '/labels',
        [LabelPrintController::class, 'store']
    )
        ->middleware('permission:labels.create')
        ->name('labels.store');

    Route::post(
        '/labels/bulk-pdf',
        [LabelPrintController::class, 'bulkPdf']
    )
        ->middleware('permission:labels.print')
        ->name('labels.bulk-pdf');

    /*
    |--------------------------------------------------------------------------
    | HAPUS LABEL
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/labels/{labelPrint}',
        [LabelPrintController::class, 'destroy']
    )
        ->middleware('permission:labels.create')
        ->name('labels.destroy');

    Route::get(
        '/labels/{labelPrint}',
        [LabelPrintController::class, 'show']
    )
        ->middleware('permission:labels.view,labels.create')
        ->name('labels.show');

    Route::get(
        '/labels/{labelPrint}/pdf',
        [LabelPrintController::class, 'pdf']
    )
        ->middleware('permission:labels.print')
        ->name('labels.pdf');

    /*
    |--------------------------------------------------------------------------
    | DELIVERY ORDER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/delivery-orders',
        [DeliveryOrderController::class, 'index']
    )
        ->middleware('permission:delivery_orders.view')
        ->name('delivery-orders.index');

    Route::get(
        '/delivery-orders/create',
        [DeliveryOrderController::class, 'create']
    )
        ->middleware('permission:delivery_orders.create')
        ->name('delivery-orders.create');

    Route::post(
        '/delivery-orders',
        [DeliveryOrderController::class, 'store']
    )
        ->middleware('permission:delivery_orders.create')
        ->name('delivery-orders.store');

    Route::post(
        '/delivery-orders/customers',
        [DeliveryOrderController::class, 'storeCustomer']
    )
        ->middleware('permission:delivery_orders.create')
        ->name('delivery-orders.customers.store');

    Route::post(
        '/delivery-orders/batch',
        [DeliveryOrderController::class, 'batch']
    )
        ->middleware('permission:delivery_orders.print')
        ->name('delivery-orders.batch');

    Route::post(
        '/delivery-orders/batch/pdf',
        [DeliveryOrderController::class, 'batchPdf']
    )
        ->middleware('permission:delivery_orders.print')
        ->name('delivery-orders.batch.pdf');

    Route::post(
        '/delivery-orders/batch/shipping-stickers',
        [DeliveryOrderController::class, 'batchShippingStickerPdf']
    )
        ->middleware('permission:delivery_orders.print')
        ->name('delivery-orders.batch.shipping-stickers');

    Route::get(
        '/delivery-orders/{deliveryOrder}',
        [DeliveryOrderController::class, 'show']
    )
        ->middleware('permission:delivery_orders.view,delivery_orders.create')
        ->name('delivery-orders.show');

    Route::get(
        '/delivery-orders/{deliveryOrder}/edit',
        [DeliveryOrderController::class, 'edit']
    )
        ->middleware('permission:delivery_orders.create')
        ->name('delivery-orders.edit');

    Route::put(
        '/delivery-orders/{deliveryOrder}',
        [DeliveryOrderController::class, 'update']
    )
        ->middleware('permission:delivery_orders.create')
        ->name('delivery-orders.update');

    Route::get(
        '/delivery-orders/{deliveryOrder}/pdf',
        [DeliveryOrderController::class, 'pdf']
    )
        ->middleware('permission:delivery_orders.print')
        ->name('delivery-orders.pdf');

    Route::get(
        '/delivery-orders/{deliveryOrder}/shipping-sticker',
        [DeliveryOrderController::class, 'shippingStickerPdf']
    )
        ->middleware('permission:delivery_orders.print')
        ->name('delivery-orders.shipping-sticker');

    /*
    |--------------------------------------------------------------------------
    | STOCK MASUK
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/stock-masuk',
        [StockController::class, 'createIn']
    )
        ->middleware('permission:inventory.stock_in')
        ->name('stock.in.create');

    Route::post(
        '/stock-masuk',
        [StockController::class, 'storeIn']
    )
        ->middleware('permission:inventory.stock_in')
        ->name('stock.in.store');

    /*
    |--------------------------------------------------------------------------
    | STOCK KELUAR
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/stock-keluar',
        [StockController::class, 'createOut']
    )
        ->middleware('permission:inventory.stock_out')
        ->name('stock.out.create');

    Route::post(
        '/stock-keluar',
        [StockController::class, 'storeOut']
    )
        ->middleware('permission:inventory.stock_out')
        ->name('stock.out.store');

    /*
    |--------------------------------------------------------------------------
    | MASTER PART / PRODUCT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/products',
        [ProductController::class, 'index']
    )
        ->middleware('permission:products.view')
        ->name('products.index');

    Route::get(
        '/products/import-template',
        [ProductController::class, 'importTemplate']
    )
        ->middleware('permission:products.import')
        ->name('products.import-template');

    Route::post(
        '/products',
        [ProductController::class, 'store']
    )
        ->middleware('permission:products.manage')
        ->name('products.store');

    Route::post(
        '/products/import',
        [ProductController::class, 'import']
    )
        ->middleware('permission:products.import')
        ->name('products.import');

    Route::get(
        '/products/{product}/edit',
        [ProductController::class, 'edit']
    )
        ->middleware('permission:products.manage')
        ->name('products.edit');

    Route::put(
        '/products/{product}',
        [ProductController::class, 'update']
    )
        ->middleware('permission:products.manage')
        ->name('products.update');

    Route::delete(
        '/products/{product}',
        [ProductController::class, 'destroy']
    )
        ->middleware('permission:products.manage')
        ->name('products.destroy');

    /*
    |--------------------------------------------------------------------------
    | KATEGORI PRODUK
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/product-categories',
        [ProductCategoryController::class, 'index']
    )
        ->middleware('permission:products.view')
        ->name('product-categories.index');

    Route::resource(
        'product-categories',
        ProductCategoryController::class
    )
        ->only([
            'store',
            'edit',
            'update',
            'destroy',
        ])
        ->middleware('permission:products.manage');

    /*
    |--------------------------------------------------------------------------
    | WAREHOUSE
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'warehouses',
        WarehouseController::class
    )
        ->except([
            'create',
            'show',
        ])
        ->middleware('permission:inventory.manage');

    /*
    |--------------------------------------------------------------------------
    | MASTER EKSPEDISI
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'expeditions',
        ExpeditionController::class
    )
        ->except([
            'create',
            'show',
        ])
        ->middleware('permission:inventory.manage');

    /*
    |--------------------------------------------------------------------------
    | MASTER DATA OPERASIONAL
    |--------------------------------------------------------------------------
    */

    Route::prefix('operational-masters')
        ->name('operational-masters.')
        ->middleware('permission:inventory.manage')
        ->group(function () {

            Route::get(
                '/',
                [OperationalMasterController::class, 'index']
            )->name('index');

            /*
            | KEBUTUHAN OPERASIONAL
            */

            Route::post(
                '/needs',
                [OperationalMasterController::class, 'storeNeed']
            )->name('needs.store');

            Route::put(
                '/needs/{need}',
                [OperationalMasterController::class, 'updateNeed']
            )->name('needs.update');

            /*
            | KATEGORI OPERASIONAL
            */

            Route::post(
                '/categories',
                [OperationalMasterController::class, 'storeCategory']
            )->name('categories.store');

            Route::put(
                '/categories/{category}',
                [OperationalMasterController::class, 'updateCategory']
            )->name('categories.update');

            /*
            | LOKASI OPERASIONAL
            */

            Route::post(
                '/locations',
                [OperationalMasterController::class, 'storeLocation']
            )->name('locations.store');

            Route::put(
                '/locations/{location}',
                [OperationalMasterController::class, 'updateLocation']
            )->name('locations.update');

            /*
            | ITEM OPERASIONAL
            */

            Route::post(
                '/items',
                [OperationalMasterController::class, 'storeItem']
            )->name('items.store');

            Route::put(
                '/items/{item}',
                [OperationalMasterController::class, 'updateItem']
            )->name('items.update');
        });

    /*
    |--------------------------------------------------------------------------
    | PEMBELIAN KEBUTUHAN OPRASIONAL (PMB)
    |--------------------------------------------------------------------------
    */

    Route::prefix('operational-purchases')
        ->name('operational-purchases.')
        ->middleware('permission:inventory.manage')
        ->group(function () {

            Route::get(
                '/',
                [OperationalPurchaseController::class, 'index']
            )->name('index');

            Route::get(
                '/create',
                [OperationalPurchaseController::class, 'create']
            )->name('create');

            Route::post(
                '/',
                [OperationalPurchaseController::class, 'store']
            )->name('store');

            Route::get(
                '/{operationalPurchase}',
                [OperationalPurchaseController::class, 'show']
            )->name('show');
        });

    /*
    |--------------------------------------------------------------------------
    | BIAYA OPRSIONAL (BIO)
    |--------------------------------------------------------------------------
    */

    Route::prefix('operational-expenses')
        ->name('operational-expenses.')
        ->middleware('permission:inventory.manage')
        ->group(function () {

            /*
            | DAFTAR BIAYA OPERASIONAL
            */

            Route::get(
                '/',
                [OperationalExpenseController::class, 'index']
            )->name('index');

            /*
            | FORM TAMBAH BIAYA
            */

            Route::get(
                '/create',
                [OperationalExpenseController::class, 'create']
            )->name('create');

            /*
            | SIMPAN BIAYA OPERASIONAL
            */

            Route::post(
                '/',
                [OperationalExpenseController::class, 'store']
            )->name('store');

            /*
            | DETAIL DOKUMEN BIO
            */

            Route::get(
                '/{operationalExpense}',
                [OperationalExpenseController::class, 'show']
            )->name('show');
        });

    /*
    |--------------------------------------------------------------------------
    | STOCK CONSIGNMENT GUDANG CUSTOMER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/consignment-customer',
        [ConsignmentCustomerController::class, 'index']
    )
        ->middleware('permission:inventory.manage')
        ->name('consignment-customer.index');

    Route::post(
        '/consignment-customer',
        [ConsignmentCustomerController::class, 'store']
    )
        ->middleware('permission:inventory.manage')
        ->name('consignment-customer.store');

    /*
    |--------------------------------------------------------------------------
    | OUTSTANDING PARTS
    |--------------------------------------------------------------------------
    | Mencatat permintaan parts customer untuk:
    | - NORMAL
    | - CONSIGNMENT
    |
    | Mendukung:
    | - Daftar permintaan
    | - Tambah permintaan
    | - Detail permintaan
    | - Supply parsial dan histori pemenuhan
    |
    | Pencatatan tidak langsung mengubah stok.
    |--------------------------------------------------------------------------
    */

    Route::prefix('outstanding-parts')
        ->name('outstanding-parts.')
        ->middleware('permission:inventory.manage')
        ->group(function () {

            /*
            | DAFTAR OUTSTANDING PARTS
            */

            Route::get(
                '/',
                [OutstandingPartController::class, 'index']
            )->name('index');

            /*
            | FORM TAMBAH PERMINTAAN
            */

            Route::get(
                '/create',
                [OutstandingPartController::class, 'create']
            )->name('create');

            /*
            | SIMPAN PERMINTAAN
            */

            Route::post(
                '/',
                [OutstandingPartController::class, 'store']
            )->name('store');

            /*
            | SIMPAN SUPPLY PARSIAL DAN HISTORI
            | FITUR BARU
            */

            Route::post(
                '/{outstandingPart}/fulfillments',
                [
                    OutstandingPartController::class,
                    'storeFulfillment',
                ]
            )->name('fulfillments.store');

            /*
            | DETAIL PERMINTAAN DAN HISTORI
            */

            Route::get(
                '/{outstandingPart}',
                [OutstandingPartController::class, 'show']
            )->name('show');
        });

    /*
    |--------------------------------------------------------------------------
    | OUTSTANDING DELIVERY ORDER
    |--------------------------------------------------------------------------
    | Monitoring pengembalian dokumen Delivery Order asli.
    |
    | Mendukung:
    | - Daftar seluruh Delivery Order
    | - Filter status dokumen
    | - Detail tracking DO
    | - Update status dokumen asli
    | - Histori perubahan status
    |
    | Status dokumen tidak mengubah status pengiriman atau stok.
    |--------------------------------------------------------------------------
    */

    Route::prefix('outstanding-dos')
        ->name('outstanding-dos.')
        ->middleware('permission:inventory.manage')
        ->group(function () {

            /*
            | DAFTAR OUTSTANDING DO
            */

            Route::get(
                '/',
                [
                    OutstandingDoController::class,
                    'index',
                ]
            )->name('index');

            /*
            | DETAIL TRACKING DO ASLI
            */

            Route::get(
                '/{deliveryOrder}',
                [
                    OutstandingDoController::class,
                    'show',
                ]
            )->name('show');

            /*
            | UPDATE PENERIMAAN DO ASLI
            | BESERTA HISTORI PERUBAHAN
            */

            Route::post(
                '/{deliveryOrder}/tracking',
                [
                    OutstandingDoController::class,
                    'update',
                ]
            )->name('update');
        });

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/settings',
        [LabelSettingController::class, 'edit']
    )
        ->middleware('permission:branding.manage')
        ->name('settings.edit');

    Route::put(
        '/settings',
        [LabelSettingController::class, 'update']
    )
        ->middleware('permission:branding.manage')
        ->name('settings.update');

    Route::delete(
        '/settings/logo',
        [LabelSettingController::class, 'destroy']
    )
        ->middleware('permission:branding.manage')
        ->name('settings.logo.destroy');

    Route::delete(
        '/settings/favicon',
        [LabelSettingController::class, 'destroyFavicon']
    )
        ->middleware('permission:branding.manage')
        ->name('settings.favicon.destroy');

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware('super_admin')
        ->group(function () {

            Route::resource(
                'roles',
                RoleController::class
            )->except('show');

            Route::resource(
                'users',
                UserManagementController::class
            )->except('show');
        });
});
