# Dokumentasi Kode Proyek POS (pos_app)

Dokumen ini menjelaskan struktur kode, alur request dari **routes → controller**, modul yang tersedia, serta hubungan antardata utama. Fokus pada bagian yang terlihat langsung di repository melalui file PHP (routes/controllers/models/migrations) dan kebijakan role.

> Catatan: dokumentasi ini bersifat ringkas-operasional untuk pengembang/QA. Perlu ditambah diagram/sequence jika diperlukan.

---

## 1. Ringkasan Arsitektur

- Framework: **Laravel (PHP)**.
- Pola umum:
  - **Routing** didefinisikan di `routes/web.php`.
  - **Business logic & transaksi** ada di controller:
    - `TransactionController` (POS/Sale)
    - `PurchaseController` (PO & Purchase)
    - `ReturnController` (Sale Return & Purchase Return)
    - `StockController` (stok manual & opname)
    - `CashController` (shift & cash in/out)
    - `ReportController` (laporan & export)
  - Perubahan stok selalu mempengaruhi:
    - `ProductStock` (saldo)
    - `StockMovement` (jejak pergerakan)
  - Validasi operasi penting banyak dilakukan melalui:
    - `DB::transaction()`
    - `request->validate()`
    - guard role via middleware `role:...`

---

## 2. Struktur Modul (berdasarkan Route)

Semua endpoint berada dalam `Route::middleware('auth')->group(...)` pada `routes/web.php`.

### 2.1 Dashboard
- `GET /dashboard`
- Controller: `DashboardController@index`
- Role: semua role (berdasarkan komentar: “semua role”).

### 2.2 Master Data Produk & Kategori
- Produk:
  - `GET /products` (lihat)
  - `GET /products/barcode/{barcode}` (cari via barcode)
  - Kelola produk (admin/warehouse):
    - `GET /products/create`
    - `POST /products`
    - `GET /products/{product}/edit`
    - `PUT /products/{product}`
    - `DELETE /products/{product}`
  - Tampilan produk individual: `GET /products/{product}`
- Kategori:
  - `Route::resource('categories', CategoryController::class)->except(['show'])`
  - Middleware: `role:admin,warehouse`

### 2.3 Stok & Stock Opname
Prefix: `/stock`
- Stok manual:
  - `POST /stock/in`
  - `POST /stock/out`
  - `POST /stock/transfer`
  - `POST /stock/adjustment`
- History & alert low stock:
  - `GET /stock/history`
  - `GET /stock/low-stock-alert`
- Stock opname:
  - `POST /stock/opname/start`
  - `PUT /stock/opname/item/{item}`
  - `POST /stock/opname/{stockOpname}/complete`

Middleware stok:
- Umumnya: `role:admin,warehouse`
- Untuk history/alert: `role:admin,warehouse,supervisor`

### 2.4 Transaksi Penjualan POS & Pembayaran
Prefix: `/transactions`
- POS input (kasir saja):
  - `GET /transactions/pos` → `TransactionController@create`
  - `POST /transactions` → `TransactionController@store`
  - `GET /transactions/held` → `TransactionController@held`
  - `GET /transactions/search-product` → `TransactionController@searchProduct` (dirujuk, file lengkap tidak dibaca di sesi ini)
  - `POST /transactions/{sale}/resume` → `TransactionController@resume` (dirujuk)
- Riwayat & struk:
  - `GET /transactions` → `TransactionController@index` (kasir/admin/supervisor)
  - `GET /transactions/{sale}` → `TransactionController@show` (kasir/admin/supervisor)
  - `GET /transactions/{sale}/receipt` → `TransactionController@receipt`
  - `POST /transactions/{sale}/send-receipt` → `TransactionController@sendReceipt`

Middleware transaksi POS: `role:cashier` untuk input; laporan/preview untuk role lebih luas.

### 2.5 Pelanggan
- `GET /customers`, `GET /customers/create`, `POST /customers`, `GET /customers/{customer}`
- Edit/hapus & piutang:
  - middleware `role:admin,supervisor`
  - endpoint debts & pay debt

### 2.6 Supplier
- `Route::resource('suppliers', SupplierController::class)` → middleware `role:admin,warehouse`
- Riwayat purchase & debts: route tambahan dengan middleware sama.

### 2.7 Pembelian (PO, Receive, Purchase)
Prefix: `/purchases`
- PO:
  - `POST /purchases/orders` → `PurchaseController@storePO`
  - `GET /purchases/orders` → `PurchaseController@indexPO`
  - `GET /purchases/orders/{purchaseOrder}/receive-form` → `PurchaseController@createReceiveForm`
  - `PUT /purchases/orders/{purchaseOrder}/status` → `PurchaseController@updatePOStatus`
  - `POST /purchases/orders/{purchaseOrder}/receive` → `PurchaseController@receiveGoods`
- Purchase langsung:
  - `GET /purchases/create` → `PurchaseController@createPurchaseForm`
  - `GET /purchases` → `PurchaseController@indexPurchases`
  - `POST /purchases` → `PurchaseController@store`
  - `GET /purchases/{purchase}/invoice` → `PurchaseController@showInvoice`

Middleware pembelian:
- `role:admin,warehouse`

### 2.8 Retur
- Retur penjualan (kasir/admin/supervisor):
  - `GET /sale-returns`
  - `GET /sales/{sale}/return/create`
  - `POST /sales/{sale}/return`
  - CRUD `sale-returns/{saleReturn}`
- Retur pembelian (admin/warehouse):
  - `GET /purchase-returns`
  - `GET /purchases/{purchase}/return/create`
  - `POST /purchases/{purchase}/return`
  - CRUD `purchase-returns/{purchaseReturn}`

### 2.9 Kas & Shift Kasir
Prefix: `/cash`
- Shift operasi:
  - `GET /cash/shift` → `CashController@shiftIndex`
  - `POST /cash/open` → `CashController@openRegister`
  - `POST /cash/{cashRegister}/close` → `CashController@closeRegister`
  - `POST /cash/in` → `CashController@cashIn`
  - `POST /cash/out` → `CashController@cashOut`
  - `GET /cash/current-shift` → `CashController@currentShift`
- History kas:
  - `GET /cash/history` → `CashController@history`

Middleware:
- Shift & cash: `role:cashier,admin,supervisor`
- History: `role:admin,supervisor`

### 2.10 Laporan
Prefix: `/reports`
- `GET /reports/sales` → `ReportController@sales` (cashier/admin/supervisor)
- `GET /reports/purchases` → `ReportController@purchases`
- `GET /reports/stock` → `ReportController@stock`
- `GET /reports/profit-loss` → `ReportController@profitLoss`
- `GET /reports/cash` → `ReportController@cash`
- `GET /reports/best-selling-products` → `ReportController@bestSellingProducts`
- `GET /reports/customers` → `ReportController@customers`
- `GET /reports/suppliers` → `ReportController@suppliers`
- Export:
  - `GET /reports/sales/export/pdf`
  - `GET /reports/sales/export/excel`

Middleware laporan:
- `admin,supervisor` (kecuali `/sales` yang lebih luas)

### 2.11 Promo & Voucher
- `GET/POST/PUT/DELETE /promotions` dan `vouchers`
- Voucher cek untuk POS:
  - `POST /vouchers/check` → middleware `role:cashier,admin,supervisor`

### 2.12 Barcode
Prefix: `/barcode`
- Admin/warehouse:
  - index, generate, generate QR, print labels, scan

### 2.13 Audit
- `GET /audit/logs`, `GET /audit/logs/{activityLog}`, `POST /audit/backup`
- Middleware: `role:admin`

### 2.14 Settings
- `GET/PUT /settings`, upload logo, update store hours
- Middleware: `role:admin`

---

## 3. Alur Request Utama (Request → Business Logic)

### 3.1 Autentikasi
- Route login: `POST /login` → `app/Http/Controllers/Auth/AuthController.php@login`
- Route logout: `POST /logout`
- Efek samping:
  - `ActivityLog::create(...)` untuk login/logout

### 3.2 POS Sale
File: `app/Http/Controllers/TransactionController.php`

**Endpoint utama:**
- `GET /transactions/pos` → `create()`
- `POST /transactions` → `store()`

**create(): data yang dibentuk untuk POS**
- Ambil `CashRegister` open untuk user
- Ambil master data `products`, `customers`, `categories`
- Tentukan `warehouseId`:
  - dari `cashRegister.warehouse_id` jika ada
  - atau dari `branch->warehouses` milik user
  - fallback `1`
- Ambil promo dan voucher aktif:
  - `Promotion::where('is_active', true)`
  - `Voucher::where('end_date' >= now OR null end_date)`
- Ambil setting jam operasional.
- Buat `productsJson` termasuk stok per warehouse via `stockInWarehouse($warehouseId)`.

**store(): eksekusi transaksi sale**
- Validate input (items, payment_method, paid_amount, subtotal, discount_total, grand_total, voucher_code optional).
- Cek shift kasir:
  - jika tidak ada → return JSON 403.
- Opsional jam operasional (Setting) menggunakan zona waktu `Asia/Jakarta`.
- `DB::beginTransaction()`
- Buat `Sale` dengan field:
  - invoice_number: `INV-Ymd-rand`
  - customer_id, user_id
  - cash_register_id
  - warehouse_id
  - branch_id
  - subtotal, discount_total, grand_total, paid_amount, change_amount
  - status: completed
- Loop items:
  - create `SaleItem`
  - update stok:
    - `ProductStock::firstOrCreate(...)`
    - `decrement(quantity, item.quantity)`
  - create `StockMovement` type `sale` dengan referensi ke `Sale`.
- Simpan payment:
  - `if method_exists($sale,'payments')` maka create relasi payments (payment_method, amount=grand_total, payment_date, status).
- Commit.

**Output:**
- JSON success + `sale_id` dan `invoice_number`.

### 3.3 Pembelian (PO & Receive)
File: `app/Http/Controllers/PurchaseController.php`

- `storePO()`:
  - membuat `PurchaseOrder` status `draft` + items.
- `receiveGoods()`:
  - membuat `Purchase`:
    - `invoice_number = PUR-...`
    - `payment_status` dihitung dari paid vs total
  - loop items:
    - buat `PurchaseItem`
    - tambah stok `ProductStock.increment`
    - buat `StockMovement` type `purchase` berelasi ke `Purchase`
    - update `PurchaseOrderItem.received_quantity`
  - jika belum paid penuh:
    - buat `SupplierDebt`.
  - update `PurchaseOrder.status='received'`.
- `store()` (purchase langsung): mirip receiveGoods tanpa PO.

### 3.4 Retur
File: `app/Http/Controllers/ReturnController.php`

- `storeSaleReturn()`:
  - membuat `SaleReturn` + `SaleReturnItem`
  - menambah stok (`ProductStock.increment`)
  - membuat `StockMovement` type `sale_return`.
- `storePurchaseReturn()`:
  - membuat `PurchaseReturn` + item
  - mengurangi stok (`ProductStock.decrement`)
  - membuat `StockMovement` type `purchase_return`.
- Selain create, controller juga menyediakan update/destroy untuk membatalkan retur:
  - destroySaleReturn(): stok dibalik (decrement lagi) dan membuat `sale_return_cancel`.
  - destroyPurchaseReturn(): stok dibalik (increment) dan membuat `purchase_return_cancel`.

### 3.5 Manajemen Stok (manual & opname)
File: `app/Http/Controllers/StockController.php`

- Metode umum: `adjustStock(productId, warehouseId, qtyChange, type, refId, refType, note, userId)`
  - firstOrCreate `ProductStock`
  - update quantity
  - buat `StockMovement`.
- Endpoints:
  - `stockIn` → type `in`
  - `stockOut` → type `out`
  - `transfer` → type `transfer` (dua kali: -qty dan +qty dalam 1 DB transaction)
  - `adjustment` → type `adjustment` (selisih actual_quantity - stock)
- Stock opname:
  - startOpname: buat header draft + item per product dari system stock
  - updateOpnameItem: simpan actual_quantity dan difference
  - completeOpname: setiap difference != 0 → `adjustStock(..., type='opname', ref...)` lalu status `completed`.

### 3.6 Kas & Shift
File: `app/Http/Controllers/CashController.php`

- `openRegister()`:
  - validasi opening_balance
  - cek shift open existing
  - buat `CashRegister`.
- `closeRegister()`:
  - hitung expectedBalance:
    - opening_balance + total sales cash (payments method cash) + cashIn - cashOut
  - simpan closing_balance, expected_balance, difference.
- `cashIn()` / `cashOut()`:
  - buat `CashMovement` type in/out dengan `cash_register_id` dan `user_id`.

### 3.7 Laporan
File: `app/Http/Controllers/ReportController.php`

- Helper `resolveBranchFilter()` menentukan cabang yang terlihat sesuai role.
- `handleReportOutput()` menangani:
  - export excel (`Excel::download`)
  - print (`view($printViewName)`)
  - default view.
- Implementasi laporan:
  - `sales()`: ambil Sale completed dengan filter tanggal dan cabang.
  - `purchases()`: ambil Purchase dan total.
  - `stock()`: ambil StockMovement berdasarkan filter warehouse/cabang/tanggal.
  - `profitLoss()`: revenue - cogs - expenses (expenses dari cash out, cogs dari sale_items*products.purchase_price).
  - `cash()`: agregasi cash movements.
  - `bestSellingProducts()`: agregasi `SaleItem` top 20.
  - `customers()` dan `suppliers()`.

---

## 4. Hubungan Data (High-level)

### 4.1 Stok
- Sumber kebenaran saldo: **ProductStock.quantity**.
- Jejak audit: **StockMovement**.
- Pola umum:
  - Operasi bisnis → ubah ProductStock → buat StockMovement.

### 4.2 Penjualan
- `Sale` menyimpan header.
- `SaleItem` menyimpan detail item.
- `Payment` menyimpan pembayaran (relasi dari Sale).

### 4.3 Pembelian
- `PurchaseOrder` (draft) → `Purchase` saat barang diterima.
- `PurchaseItem` detail faktur.
- `SupplierDebt` dicatat ketika paid < total.

### 4.4 Retur
- `SaleReturn` dan `PurchaseReturn` menyimpan header.
- Items retur menyimpan detail.
- Stok dibalik melalui `ProductStock` dan `StockMovement`.

---

## 5. Migrations (Apa yang Harus Dipahami)

Migrations penting yang terlihat dari file di repository:
- `create_sales_table` dan `create_sale_returns_table`
- `create_purchase_orders_table`, `create_purchases_table`, `create_purchase_returns_table`
- `create_stock_movements_table`, `create_stock_opnames_table`
- `create_cash_registers_table`, `create_cash_movements_table`
- `create_customer_debts_table`, `create_supplier_debts_table` (untuk piutang)
- `create_promotions_table`, `create_vouchers_table`
- `create_activity_logs_table`

> Untuk dokumentasi lengkap field per tabel, sebaiknya dibuat versi lanjut dengan membaca masing-masing migration.

---

## 6. Cara Membaca & Mengembangkan Kode

1. Mulai dari `routes/web.php` untuk memahami modul & role.
2. Pilih controller sesuai modul (Transaction/Purchase/Return/Stock/Cash/Report).
3. Telusuri titik transaksi database:
   - `DB::beginTransaction()` atau `DB::transaction()`.
4. Pastikan perubahan stok selalu menghasilkan `StockMovement` dan mengubah `ProductStock`.

---

## 7. Daftar File Utama (berdasarkan penelusuran saat ini)

- `routes/web.php`
- `app/Http/Controllers/TransactionController.php`
- `app/Http/Controllers/PurchaseController.php`
- `app/Http/Controllers/ReturnController.php`
- `app/Http/Controllers/StockController.php`
- `app/Http/Controllers/CashController.php`
- `app/Http/Controllers/ReportController.php`

---

## 8. Rekomendasi Pengembangan Dokumentasi Lanjutan (Opsional)

- Tambahkan dokumentasi:
  - struktur database per tabel (field, relasi, constraint)
  - diagram ERD sederhana
  - sequence diagram per proses (sale, purchase receive, retur, opname)
- Tambahkan bagian "API contracts" untuk endpoint JSON (mis. `TransactionController@store` mengembalikan JSON saat berhasil/gagal).

