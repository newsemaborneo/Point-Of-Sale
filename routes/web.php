<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AiController;


// ===== AUTH (Modul 10) =====
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});
Route::get('/login', function () {
    return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
})->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me']);

    // ===== 1. DASHBOARD (semua role) =====
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===== AI SUPERADMIN =====
    Route::middleware('role:admin,supervisor')->group(function () {
        Route::get('/ai', [AiController::class, 'index'])->name('ai.index');
        Route::post('/ai/chat', [AiController::class, 'chat'])->name('ai.chat');
        Route::get('/ai/dashboard-data', [AiController::class, 'dashboardData'])->name('ai.dashboard-data');
        Route::post('/ai/conversations/new', [AiController::class, 'newConversation'])->name('ai.conversations.new');
        Route::delete('/ai/conversations/{conversation}', [AiController::class, 'destroyConversation'])->name('ai.conversations.destroy');
        Route::get('/ai/alerts', [AiController::class, 'getAlerts'])->name('ai.alerts');
    });

    // ===== 2. MANAJEMEN PRODUK =====
    // Lihat produk: semua role boleh (kasir butuh untuk cari produk saat POS)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/barcode/{barcode}', [ProductController::class, 'findByBarcode'])->name('products.barcode');

    // Kelola produk: admin & gudang saja
    // PENTING: route statis (create) HARUS didaftarkan sebelum route dinamis ({product})
    // agar Laravel tidak salah mencocokkan "create" sebagai nilai {product}.
    Route::middleware('role:admin,warehouse')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Route dinamis paling umum (single segment) diletakkan PALING AKHIR
    // agar tidak menangkap /products/create, /products/barcode/... dsb.
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // Kategori: hanya admin & gudang yang boleh kelola
    Route::resource('categories', CategoryController::class)
        ->except(['show'])
        ->middleware('role:admin,warehouse');

    // ===== 3. MANAJEMEN STOK (admin & gudang) =====
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/in/create', [StockController::class, 'createStockInForm'])->name('in.create')->middleware('role:admin,warehouse');
        Route::post('/in', [StockController::class, 'stockIn'])->name('in')->middleware('role:admin,warehouse');
        Route::get('/out/create', [StockController::class, 'createStockOutForm'])->name('out.create')->middleware('role:admin,warehouse');
        Route::post('/out', [StockController::class, 'stockOut'])->name('out')->middleware('role:admin,warehouse');
        Route::get('/transfer/create', [StockController::class, 'createTransferForm'])->name('transfer.create')->middleware('role:admin,warehouse');
        Route::post('/transfer', [StockController::class, 'transfer'])->name('transfer')->middleware('role:admin,warehouse');
        Route::get('/adjustment/create', [StockController::class, 'createAdjustmentForm'])->name('adjustment.create')->middleware('role:admin,warehouse');
        Route::post('/adjustment', [StockController::class, 'adjustment'])->name('adjustment')->middleware('role:admin,warehouse');
        Route::get('/history', [StockController::class, 'history'])->name('history')->middleware('role:admin,warehouse,supervisor');
        Route::get('/low-stock-alert', [StockController::class, 'lowStockAlert'])->name('low-alert')->middleware('role:admin,warehouse,supervisor');

        // Stock opname
        Route::get('/opname/create', [StockController::class, 'createOpnameForm'])->name('opname.create')->middleware('role:admin,warehouse');
        Route::post('/opname/start', [StockController::class, 'startOpname'])->name('opname.start')->middleware('role:admin,warehouse');
        Route::put('/opname/item/{item}', [StockController::class, 'updateOpnameItem'])->name('opname.item.update')->middleware('role:admin,warehouse');
        Route::post('/opname/{stockOpname}/complete', [StockController::class, 'completeOpname'])->name('opname.complete')->middleware('role:admin,warehouse');
    });

    // ===== 4 & 5. TRANSAKSI PENJUALAN + PEMBAYARAN (POS) =====
    Route::prefix('transactions')->name('transactions.')->group(function () {
        // Input transaksi: kasir saja
        Route::get('/pos', [TransactionController::class, 'create'])->name('pos')->middleware('role:cashier');
        Route::post('/', [TransactionController::class, 'store'])->name('store')->middleware('role:cashier');
        Route::get('/held', [TransactionController::class, 'held'])->name('held')->middleware('role:cashier');
        Route::get('/search-product', [TransactionController::class, 'searchProduct'])->name('search-product')->middleware('role:cashier');
        Route::post('/{sale}/resume', [TransactionController::class, 'resume'])->name('resume')->middleware('role:cashier');

        // Lihat riwayat & struk: kasir, admin, supervisor
        Route::get('/', [TransactionController::class, 'index'])->name('index')->middleware('role:cashier,admin,supervisor');
        Route::get('/{sale}', [TransactionController::class, 'show'])->name('show')->middleware('role:cashier,admin,supervisor');
        Route::get('/{sale}/receipt', [TransactionController::class, 'receipt'])->name('receipt')->middleware('role:cashier,admin,supervisor');
        Route::post('/{sale}/send-receipt', [TransactionController::class, 'sendReceipt'])->name('send-receipt')->middleware('role:cashier,admin,supervisor');
    });

    // ===== MANAJEMEN TIPE MEMBER =====
    Route::resource('member-types', \App\Http\Controllers\MemberTypeController::class)->except(['show'])->middleware('role:admin,supervisor');

    // ===== 6. MANAJEMEN PELANGGAN =====
    // Lihat pelanggan: semua role (kasir butuh untuk POS)
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    // Edit/hapus pelanggan & data piutang: admin & supervisor
    Route::middleware('role:admin,supervisor')->group(function () {
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::get('/customers/{customer}/purchase-history', [CustomerController::class, 'purchaseHistory'])->name('customers.purchaseHistory');
        Route::get('/customers/{customer}/debts', [CustomerController::class, 'debts'])->name('customers.debts');
        Route::post('/customer-debts/{debt}/pay', [CustomerController::class, 'payDebt'])->name('customers.debts.pay');
    });

    // ===== 7. SUPPLIER (admin & gudang) =====
    Route::resource('suppliers', SupplierController::class)->middleware('role:admin,warehouse');
    Route::get('/suppliers/{supplier}/purchase-history', [SupplierController::class, 'purchaseHistory'])->name('suppliers.purchaseHistory')->middleware('role:admin,warehouse');
    Route::get('/suppliers/{supplier}/debts', [SupplierController::class, 'debts'])->name('suppliers.debts')->middleware('role:admin,warehouse');

    // ===== 8. PEMBELIAN (admin & gudang) =====
    Route::prefix('purchases')->name('purchases.')->middleware('role:admin,warehouse')->group(function () {
        Route::post('/orders', [PurchaseController::class, 'storePO'])->name('orders.store');
        Route::get('/orders', [PurchaseController::class, 'indexPO'])->name('orders.index');
        Route::get('/orders/{purchaseOrder}/receive-form', [PurchaseController::class, 'createReceiveForm'])->name('orders.receive-form');
        Route::put('/orders/{purchaseOrder}/status', [PurchaseController::class, 'updatePOStatus'])->name('orders.status');
        Route::post('/orders/{purchaseOrder}/receive', [PurchaseController::class, 'receiveGoods'])->name('orders.receive');
        Route::get('/create', [PurchaseController::class, 'createPurchaseForm'])->name('create');
        Route::get('/', [PurchaseController::class, 'indexPurchases'])->name('index');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::get('/{purchase}/invoice', [PurchaseController::class, 'showInvoice'])->name('invoice');
    });

    // ===== 9. RETUR =====
    // Retur penjualan: kasir, admin, supervisor
    Route::middleware('role:cashier,admin,supervisor')->group(function () {
        Route::get('/api/sales/by-invoice/{invoice}', [TransactionController::class, 'findByInvoice']);
        Route::get('/sale-returns', [ReturnController::class, 'indexSaleReturns'])->name('sale-returns.index');
        Route::get('/sales/{sale}/return/create', [ReturnController::class, 'createSaleReturnForm'])->name('sales.return.create');
        Route::post('/sales/{sale}/return', [ReturnController::class, 'storeSaleReturn'])->name('sales.return');
        Route::get('/sale-returns/{saleReturn}', [ReturnController::class, 'showSaleReturn'])->name('sale-returns.show');
        Route::get('/sale-returns/{saleReturn}/edit', [ReturnController::class, 'editSaleReturn'])->name('sale-returns.edit');
        Route::put('/sale-returns/{saleReturn}', [ReturnController::class, 'updateSaleReturn'])->name('sale-returns.update');
        Route::delete('/sale-returns/{saleReturn}', [ReturnController::class, 'destroySaleReturn'])->name('sale-returns.destroy');
    });

    // Retur pembelian: admin & gudang
    Route::middleware('role:admin,warehouse')->group(function () {
        Route::get('/purchase-returns', [ReturnController::class, 'indexPurchaseReturns'])->name('purchase-returns.index');
        Route::get('/purchases/{purchase}/return/create', [ReturnController::class, 'createPurchaseReturnForm'])->name('purchases.return.create');
        Route::post('/purchases/{purchase}/return', [ReturnController::class, 'storePurchaseReturn'])->name('purchases.return');
        Route::get('/purchase-returns/{purchaseReturn}', [ReturnController::class, 'showPurchaseReturn'])->name('purchase-returns.show');
        Route::get('/purchase-returns/{purchaseReturn}/edit', [ReturnController::class, 'editPurchaseReturn'])->name('purchase-returns.edit');
        Route::put('/purchase-returns/{purchaseReturn}', [ReturnController::class, 'updatePurchaseReturn'])->name('purchase-returns.update');
        Route::delete('/purchase-returns/{purchaseReturn}', [ReturnController::class, 'destroyPurchaseReturn'])->name('purchase-returns.destroy');
    });

    // ===== 10. MANAJEMEN PENGGUNA (admin saja) =====
    Route::resource('users', UserController::class)->middleware('role:admin');
    Route::get('/users/{user}/activity-log', [UserController::class, 'activityLog'])->name('users.activityLog')->middleware('role:admin');

    // ===== 11. LAPORAN (admin & supervisor) =====
    Route::prefix('reports')->name('reports.')->group(function () {
        // Laporan Penjualan: bisa diakses oleh kasir, admin, dan supervisor
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales')->middleware('role:cashier,admin,supervisor');

        // Laporan lainnya: hanya admin & supervisor
        Route::middleware('role:admin,supervisor')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/cash', [ReportController::class, 'cash'])->name('cash');
        Route::get('/best-selling-products', [ReportController::class, 'bestSellingProducts'])->name('best-selling-products');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/suppliers', [ReportController::class, 'suppliers'])->name('suppliers');
        Route::get('/sales/export/pdf', [ReportController::class, 'exportSalesPdf'])->name('sales.export.pdf');
        Route::get('/sales/export/excel', [ReportController::class, 'exportSalesExcel'])->name('sales.export.excel');
        });
    });

    // ===== 12. MANAJEMEN KAS & SHIFT =====
    Route::prefix('cash')->name('cash.')->group(function () {
        // Fitur Buka/Tutup Shift Kasir untuk kasir, admin, dan supervisor
        Route::middleware('role:cashier,admin,supervisor')->group(function () {
            Route::get('/shift', [CashController::class, 'shiftIndex'])->name('shift');
            Route::post('/open', [CashController::class, 'openRegister'])->name('open');
            Route::post('/{cashRegister}/close', [CashController::class, 'closeRegister'])->name('close');
        });

        // Operasional transaksi kasir saja
        Route::middleware('role:cashier')->group(function () {
            Route::post('/in', [CashController::class, 'cashIn'])->name('in');
            Route::post('/out', [CashController::class, 'cashOut'])->name('out');
            Route::get('/current-shift', [CashController::class, 'currentShift'])->name('current-shift');
        });

        // Riwayat kas: admin & supervisor saja
        Route::get('/history', [CashController::class, 'history'])->name('history')->middleware('role:admin,supervisor');
    });

    // ===== 13. BARCODE (admin & gudang) =====
    Route::prefix('barcode')->name('barcode.')->middleware('role:admin,warehouse')->group(function () {
        Route::get('/', [BarcodeController::class, 'index'])->name('index');
        Route::get('/generate/{product}', [BarcodeController::class, 'generate'])->name('generate');
        Route::post('/generate-qrcode', [BarcodeController::class, 'generateQrCode'])->name('qrcode');
        Route::post('/print-labels', [BarcodeController::class, 'printLabels'])->name('print');
        Route::get('/scan/{code}', [BarcodeController::class, 'scan'])->name('scan');
    });

    // ===== 14. PROMO =====
    // Kelola promosi & voucher: admin & supervisor
    Route::prefix('promotions')->name('promotions.')->middleware('role:admin,supervisor')->group(function () {
        Route::get('/', [PromoController::class, 'index'])->name('index');
        Route::post('/', [PromoController::class, 'store'])->name('store');
        Route::put('/{promotion}', [PromoController::class, 'update'])->name('update');
        Route::delete('/{promotion}', [PromoController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('vouchers')->name('vouchers.')->group(function () {
        Route::get('/', [PromoController::class, 'indexVoucher'])->name('index')->middleware('role:admin,supervisor');
        Route::post('/', [PromoController::class, 'storeVoucher'])->name('store')->middleware('role:admin,supervisor');
        Route::put('/{voucher}', [PromoController::class, 'updateVoucher'])->name('update')->middleware('role:admin,supervisor');
        Route::delete('/{voucher}', [PromoController::class, 'destroyVoucher'])->name('destroy')->middleware('role:admin,supervisor');
        // Cek voucher: kasir butuh ini saat transaksi POS
        Route::post('/check', [PromoController::class, 'checkVoucher'])->name('check')->middleware('role:cashier,admin,supervisor');
    });

    // ===== 15. MULTI CABANG (admin saja) =====
    Route::resource('branches', BranchController::class)->except(['show'])->middleware('role:admin');
    Route::get('/branches/{branch}/report', [BranchController::class, 'report'])->name('branches.report')->middleware('role:admin,supervisor');
    Route::resource('warehouses', WarehouseController::class)->except(['show'])->middleware('role:admin');

    // ===== 17. AUDIT (admin saja) =====
    Route::middleware('role:admin')->group(function () {
        Route::get('/audit/logs', [AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/logs/{activityLog}', [AuditController::class, 'show'])->name('audit.show');
        Route::post('/audit/backup', [AuditController::class, 'backupDatabase'])->name('audit.backup');
    });

    // ===== 18. PENGATURAN (admin saja) =====
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');
        Route::put('/settings', [SettingController::class, 'update']);
        Route::post('/settings/logo', [SettingController::class, 'uploadLogo'])->name('settings.logo');
        Route::post('/settings/store-hours', [SettingController::class, 'updateStoreHours'])->name('settings.store-hours');
    });

    // ===== 19. NOTIFIKASI (semua role lihat, generate hanya admin) =====
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::post('/notifications/generate', [NotificationController::class, 'generateSystemNotifications'])->name('notifications.generate')->middleware('role:admin');
});

Route::get('/api/ai-data/context', [\App\Http\Controllers\AiController::class, 'getSystemContextApi']);
