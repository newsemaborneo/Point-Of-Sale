# Dokumentasi Proses Bisnis (POS, Pembelian, Stok, Retur, Kas, Laporan)

Dokumen ini merangkum alur proses bisnis utama pada aplikasi POS yang tersedia di repository `pos_app`. Fokus pada proses yang terdokumentasi/terimplementasi melalui controller, form, dan perubahan data (Sales/Purchase/StockMovement/CashRegister/CashMovement/Return).

---

## 0. Gambaran Umum Modul, Peran, & Prinsip Bisnis

### Role (berdasarkan route middleware)
- **admin**
  - Kelola master data (produk/kategori/pelanggan/supplier/user/role sesuai modul).
  - Kelola stok & pembelian.
  - Mengakses laporan lengkap.
  - Akses audit dan pengaturan.
- **supervisor**
  - Akses laporan (terutama untuk monitoring/pengawasan).
  - Akses beberapa aktivitas operasional seperti `stock/history`.
- **warehouse**
  - Operasional gudang: stok masuk/keluar, transfer, adjustment, opname.
  - Pembelian & retur pembelian.
- **cashier**
  - Operasional kasir: input penjualan POS, retur penjualan.
  - Transaksi penjualan otomatis memotong stok sesuai gudang shift kasir.

### Prinsip bisnis inti (berdasarkan implementasi)
1. **Konsistensi stok selalu lewat `ProductStock` + `StockMovement`**
   - Setiap perubahan stok dicatat sebagai pergerakan (audit trail operasional).
2. **Transaksi penjualan membutuhkan shift kasir `status=open`**
   - Jika shift belum dibuka, transaksi POS ditolak.
3. **Pembaruan stok terjadi saat transaksi disimpan**
   - Saat `Sale` tersimpan → stok berkurang.
   - Saat `Purchase` diterima → stok bertambah.
   - Saat retur → stok dibalikkan.
4. **Akuntabilitas pergerakan melalui reference**
   - `StockMovement` menyimpan hubungan ke dokumen sumber (contoh: `reference_type=Sale::class`).

### Entitas inti
- **Sale / SaleItem**: transaksi penjualan.
- **SaleReturn / SaleReturnItem**: retur penjualan.
- **PurchaseOrder / PurchaseOrderItem**: PO (draft/sent/partial/received).
- **Purchase / PurchaseItem**: faktur pembelian.
- **PurchaseReturn / PurchaseReturnItem**: retur pembelian.
- **ProductStock**: saldo stok per `product_id` dan `warehouse_id`.
- **StockMovement**: log pergerakan stok (type: sale, purchase, transfers, adjustment, opname, return).
- **CashRegister**: shift kasir.
- **CashMovement**: kas masuk/keluar di luar penjualan (operasional).
- **Payment**: pembayaran pada penjualan (pada controller, relasi `payments()` digunakan).

---

## 1. Proses Login & Audit Aktivitas

### Tujuan
Memastikan pengguna dapat mengakses sistem sesuai role dan aktivitasnya tercatat.

### Alur
1. User mengisi **email** dan **password**.
2. Sistem melakukan autentikasi (`Auth::attempt`).
3. Jika berhasil:
   - Session diregenerate (`regenerate`).
   - **ActivityLog** dibuat dengan modul `Auth`, action `login`, dan IP.
4. User dapat logout:
   - ActivityLog dibuat action `logout`.
   - Session invalidate & regenerate token.

### Output
- User masuk dan diarahkan ke `/dashboard`.
- Riwayat audit tercatat.

---

## 2. Dashboard

### Tujuan
Menampilkan ringkasan operasional.

### Alur
- Sistem menampilkan halaman `/dashboard`.
- Data disiapkan oleh `DashboardController@index` (dirujuk pada route, detail tidak dibaca pada sesi ini).

### Output
- Tampilan dashboard sesuai role.

---

## 3. Manajemen Stok (Manual & Operasional Gudang)

### 3.1. Stok Masuk (Stock In)

**Skenario:** gudang menerima stok secara manual (mis. penerimaan tanpa PO/faktur tertentu).

**Alur:**
1. Admin/warehouse membuka form stok masuk.
2. Input:
   - `product_id`, `warehouse_id`, `quantity`, `note`.
3. Sistem mengeksekusi `adjustStock(..., type='in')`:
   - Cari/buat `ProductStock` (default quantity 0 jika belum ada).
   - Hitung quantity sebelum.
   - Update `ProductStock.quantity += qtyChange`.
   - Buat `StockMovement` dengan:
     - `type=in`
     - `quantity=qtyChange`
     - `quantity_before/after`
     - `reference_type/ref_id` tidak diisi (manual)
     - `user_id` dari pembuat.

**Output:**
- Stok bertambah dan pergerakan tersimpan.

---

### 3.2. Stok Keluar (Stock Out)

**Skenario:** gudang mengeluarkan stok manual (mis. pemakaian internal, rusak, dll.).

**Alur:**
1. Input: `product_id`, `warehouse_id`, `quantity`.
2. Sistem memanggil `adjustStock(..., qtyChange=-quantity, type='out')`.
3. Membuat `StockMovement` dengan `type=out`.

**Output:**
- Stok berkurang dan log perubahan tersimpan.

---

### 3.3. Transfer Stok Antar Gudang

**Skenario:** memindahkan stok dari gudang asal ke gudang tujuan.

**Alur:**
1. Input: `product_id`, `from_warehouse_id`, `to_warehouse_id`, `quantity`, `note`.
2. Dalam transaksi database:
   - Adjust keluar dari gudang asal: `type='transfer'` dengan qty `-quantity`.
   - Adjust masuk ke gudang tujuan: `type='transfer'` dengan qty `+quantity`.
   - Masing-masing menghasilkan `StockMovement`.

**Output:**
- Kuantitas berpindah dan pergerakan dicatat dua kali (keluar & masuk).

---

### 3.4. Penyesuaian Stok (Adjustment)

**Skenario:** stok aktual berbeda dengan sistem (selisih timbang/koreksi stok).

**Alur:**
1. Input: `product_id`, `warehouse_id`, `actual_quantity`, `note`.
2. Sistem memuat `ProductStock` (default 0 jika belum ada).
3. Hitung selisih: `diff = actual_quantity - system_quantity`.
4. Panggil `adjustStock(..., qtyChange=diff, type='adjustment')`.
5. Sistem membuat `StockMovement` type `adjustment`.

**Output:**
- Stok disesuaikan ke jumlah aktual.

---

### 3.5. Stock Opname

**Skenario:** proses pencocokan fisik stok di gudang.

**Alur:**
1. Mulai opname (`startOpname`):
   - Input `warehouse_id`, `opname_date`, `note`.
   - Buat `StockOpname` status `draft`.
   - Inisialisasi `StockOpnameItem` untuk setiap `ProductStock` di warehouse tsb:
     - `system_quantity = stock.quantity`
     - `actual_quantity` awal sama dengan system
     - `difference=0`.
2. Update item (`updateOpnameItem`):
   - Input `actual_quantity` (dan note opsional).
   - Hitung `difference = actual_quantity - system_quantity`.
3. Selesaikan opname (`completeOpname`):
   - Untuk setiap item dengan `difference != 0`:
     - `adjustStock(product_id, warehouse_id, difference, type='opname', ref...)`
       - Membuat `StockMovement` type `opname` dengan referensi `StockOpname`.
   - Update `StockOpname.status='completed'`.

**Output:**
- Penyesuaian stok otomatis berdasarkan selisih fisik.

---

## 4. Proses Penjualan (POS: Sale)

### 4.1. Persiapan Kasir (Shift Kasir)

**Skenario:** transaksi POS hanya bisa diproses jika shift kasir dibuka.

**Alur (CashController):**
1. Kasir membuka shift (`openRegister`):
   - Validasi `opening_balance >= 0`.
   - Cek apakah ada `CashRegister` status `open` untuk user yang sama.
   - Buat `CashRegister`:
     - `user_id`
     - `branch_id` (dari request, fallback ke user)
     - `opened_at=now()`
     - `status='open'`.
2. Kasir menutup shift (`closeRegister`):
   - Validasi `closing_balance >= 0`.
   - Sistem hitung expected balance:
     - `opening_balance + salesCash + cashIn - cashOut`
   - Hitung selisih: `difference = closing_balance - expectedBalance`.
   - Update `CashRegister`:
     - `closing_balance`, `expected_balance`, `difference`, `closed_at`, `status='closed'`.

**Output:**
- Validasi shift memblokir transaksi bila shift belum dibuka.

---

### 4.2. Input Transaksi POS (create)

**Alur (TransactionController@create):**
1. Ambil `CashRegister` status `open` berdasarkan `user_id`.
2. Ambil daftar:
   - `products` aktif,
   - `customers`,
   - `categories`,
   - `activePromotions`.
   - `activeVouchers` (end_date masih berlaku atau null).
3. Tentukan `warehouseId`:
   - Prefer dari `cashRegister.warehouse_id`.
   - Jika tidak ada: ambil warehouse pertama milik branch user.
   - Jika tidak ada juga: fallback `warehouseId=1`.
4. Buat `productsJson` dengan stok per warehouse: `p->stockInWarehouse($warehouseId)`.
5. Set konfigurasi jam operasional dari `Setting`:
   - `store_hours_enabled`, `store_open_time`, `store_close_time`.

**Output:**
- Halaman POS `transactions/pos` berisi produk+stok real-time untuk gudang terkait.

---

### 4.3. Simpan Transaksi POS (store)

**Alur:**
1. Validasi request:
   - `items` minimal 1 baris.
   - `items.*` memuat `product_id`, `quantity`, `price`.
   - `payment_method`, `paid_amount`, `subtotal`, `discount_total`, `grand_total`.
   - `customer_id` nullable.
   - `voucher_code` nullable.
2. Ambil `CashRegister` status `open` untuk user.
3. Cek prasyarat:
   - Jika cash register tidak ada → respon 403.
   - Jika store hours enabled → sistem memeriksa jam buka/tutup.
4. Mulai transaksi database (`DB::beginTransaction`).
5. Buat `Sale`:
   - `invoice_number = INV-YYYYMMDD-rand`
   - `customer_id`, `user_id`, `cash_register_id`.
   - `warehouse_id` = cashRegister.warehouse_id (fallback 1).
   - `branch_id` = cashRegister.branch_id (fallback ke branch user jika cash register tidak punya branch_id).
   - `subtotal`, `discount_total`, `grand_total`.
   - `paid_amount`, `change_amount = max(0, paid_amount - grand_total)`.
   - `status='completed'`.
6. Untuk setiap item:
   - Hitung `subtotal = quantity * price`.
   - Buat `SaleItem`.
   - Kurangi stok di warehouse:
     - ambil/buat `ProductStock`.
     - decrement quantity.
   - Buat `StockMovement`:
     - `type='sale'`
     - `quantity = -quantity_item`
     - `quantity_before/after`
     - `reference_type=Sale::class`, `reference_id=sale->id`.
7. Buat pembayaran (`payments`) bila relasi tersedia:
   - `payment_method`, `amount=grand_total`, `payment_date=now()`, `status='completed'`.
8. Commit transaksi.

**Output:**
- Sale completed + stok terkurangi + log pergerakan stok.

---

## 5. Retur Penjualan (Sale Return)

### Tujuan
Mengembalikan barang dari penjualan ke stok gudang dan mencatat pengembalian dana (secara logis lewat `refund_method`).

### Alur (ReturnController@storeSaleReturn)
1. User membuka form retur untuk sebuah `Sale`.
2. Validasi input:
   - `items` minimal 1.
   - `refund_method` wajib.
   - `reason` opsional.
3. Jalankan transaksi database:
   - Buat `SaleReturn`:
     - `return_number = RTS-...`
     - `sale_id`, `user_id`, `return_date`, `reason`, `refund_method`, `branch_id=sale.branch_id`.
   - Untuk setiap item:
     - Cari `SaleItem` berdasarkan `product_id`.
     - Buat `SaleReturnItem`.
     - Tambahkan stok kembali:
       - `ProductStock` pada `sale->warehouse_id`.
       - increment `quantity`.
     - Buat `StockMovement`:
       - `type='sale_return'`
       - `quantity = +quantity_item`
       - `reference_type=SaleReturn::class`.
   - Update `SaleReturn.total`.

**Output:**
- Stok bertambah kembali dan retur tercatat.

---

## 6. Proses Pembelian (Purchase)

### 6.1. Purchase Order (PO)

**Skenario:** pembelian terencana sebelum barang diterima.

**Alur (PurchaseController@storePO):**
1. Validasi:
   - `supplier_id`, `warehouse_id`, `order_date`.
   - items: `product_id`, `quantity`, `price`.
2. Transaksi database:
   - Buat `PurchaseOrder`:
     - `po_number = PO-YYYYMMDD-...`
     - `supplier_id`, `warehouse_id`, `user_id`
     - `order_date`, `expected_date`, `status='draft'`
     - `total` = sum(quantity*price)
   - Untuk setiap item:
     - Buat `PurchaseOrderItem`.

**Output:**
- PO tersimpan sebagai draft.

---

### 6.2. Menerima Barang dari PO (receiveGoods)

**Skenario:** ketika barang benar-benar diterima, dibuat faktur `Purchase` dan stok bertambah.

**Alur (PurchaseController@receiveGoods):**
1. Validasi:
   - items: `product_id`, `quantity`, `price`.
   - `paid_amount` opsional.
   - `purchase_date` wajib.
2. Transaksi database:
   - Hitung `total` dan `paid`.
   - Buat `Purchase`:
     - `invoice_number = PUR-...`
     - `purchase_order_id`, `supplier_id`, `warehouse_id`, `user_id`
     - `purchase_date`, `total`, `paid_amount`
     - `payment_status`:
       - `paid` jika paid>=total
       - `partial` jika paid>0
       - `unpaid` jika paid==0.
   - Untuk tiap item:
     - Buat `PurchaseItem`.
     - Tambahkan stok:
       - `ProductStock` by `product_id` + `warehouse_id`.
       - increment quantity.
     - Buat `StockMovement`:
       - `type='purchase'`
       - `quantity=+qty`
       - referensi ke `Purchase`.
     - Update `PurchaseOrderItem.received_quantity` dengan `increment`.
   - Jika `payment_status != paid`:
     - Buat `SupplierDebt` dengan `status` partial/unpaid.
   - Update `PurchaseOrder.status='received'`.

**Output:**
- Faktur pembelian tercatat + stok bertambah + piutang pemasok (jika belum lunas).

---

### 6.3. Pembelian Langsung (tanpa PO)

**Alur (PurchaseController@store):**
1. Validasi `supplier_id`, `warehouse_id`, `purchase_date`, items, `paid_amount`.
2. Transaksi database:
   - Buat `Purchase` dengan `payment_status` sama seperti penerimaan PO.
   - Buat `PurchaseItem` per item.
   - Tambahkan stok & buat `StockMovement` type `purchase`.
   - Jika `paid < total` → buat `SupplierDebt`.

**Output:**
- Pembelian langsung tercatat dan stok bertambah.

---

## 7. Retur Pembelian (Purchase Return)

### Tujuan
Mengurangi stok yang sebelumnya ditambahkan dari pembelian dan mencatat retur terhadap purchase.

### Alur (ReturnController@storePurchaseReturn)
1. Validasi input items dan `reason`.
2. Transaksi database:
   - Buat `PurchaseReturn`:
     - `return_number = RTP-...`
     - `purchase_id`, `user_id`, `return_date`, `reason`, `total=0`.
   - Untuk setiap item:
     - Cari `PurchaseItem`.
     - Buat `PurchaseReturnItem`.
     - Kurangi stok:
       - `ProductStock` by `product_id` + `purchase->warehouse_id`.
       - decrement quantity.
     - Buat `StockMovement`:
       - `type='purchase_return'`
       - `quantity = -qty`.
   - Update `PurchaseReturn.total`.

**Output:**
- Stok berkurang kembali dan retur pembelian tercatat.

---

## 8. Kas & Shift Kasir (Cash Register)

### 8.1. Kas Masuk (Cash In)
- Untuk operasional di luar penjualan.
- Input: `cash_register_id`, `amount`, `category`, `description`.
- `CashMovement.type='in'` dan `user_id` disimpan.

### 8.2. Kas Keluar (Cash Out)
- Input: `cash_register_id`, `amount`, `category`, `description`.
- `CashMovement.type='out'` dan `user_id` disimpan.

### 8.3. Penutupan shift
- Sistem menghitung selisih berdasarkan:
  - cash register opening
  - penjualan cash (berdasarkan payments)
  - cash in/out

---

## 9. Pembayaran & Voucher/Promo (Konsep dalam POS)

### Promo & Voucher
- Data `activePromotions` dan `activeVouchers` diambil saat POS dibuka.
- Saat transaksi disimpan, `voucher_code` diterima (nullable).

Catatan: perhitungan diskon dari promo/voucher secara detail ada di kode lain (mis. `PromoController` dan/atau logika di frontend/JS di `pos.blade.php`), tetapi alur simpan transaksi tetap memastikan field `discount_total` dan `grand_total` sudah dikirim dari client.

---

## 10. Laporan

### 10.1. Laporan Penjualan
- Sumber: `Sale` status completed.
- Filter berdasarkan:
  - rentang tanggal `date_from` s/d `date_to`
  - cabang (untuk admin/supervisor)
- Dapat output view / print / export Excel.

### 10.2. Laporan Pembelian
- Sumber: `Purchase`.
- Filter tanggal & cabang.

### 10.3. Laporan Stok
- Sumber: `StockMovement`.
- Filter:
  - date range
  - warehouse (jika dipilih)
  - cabang (jika warehouse tidak dipilih)

### 10.4. Laporan Laba Rugi
- Hitung sederhana:
  - revenue = total grand_total dari sale completed
  - cogs = quantity sale_items * purchase_price (join products)
  - expenses = cash movement out

### 10.5. Laporan Kas
- Sumber: `CashMovement`.
- total in/out.

### 10.6. Produk Terlaris
- Sumber: `SaleItem`.
- agregasi quantity & subtotal dari sale completed.

---

## 11. Audit Log Sistem (Activity Logs)

### Tujuan
Melacak aktivitas pengguna pada modul-modul kritikal.

### Alur
- Login/logout dicatat melalui `AuthController`.
- Aktivitas lain (mis. aksi penting pada transaksi/master data) *umumnya* tercatat melalui mekanisme `ActivityLog` pada modul-modul lain.

---

## 11.1 Analisis Kebutuhan Perangkat Lunak (Software Requirements)

Bagian ini mengubah dokumentasi proses menjadi kebutuhan perangkat lunak yang dapat dipakai sebagai acuan pengembangan/QA.

### A. Kebutuhan Fungsional (Functional Requirements)

#### A1. Autentikasi & Akses Role
- Sistem harus menyediakan login dan logout.
- Login harus membuat entri **ActivityLog**.
- Sistem harus menerapkan otorisasi berbasis role menggunakan middleware `role:...`.

#### A2. Shift Kasir (CashRegister)
- Sistem harus mengizinkan kasir **membuka shift** hanya jika tidak ada shift `status=open` untuk user yang sama.
- Sistem harus mengizinkan kasir **menutup shift** dan menghitung selisih.
- Sistem harus mengaktifkan validasi transaksi penjualan berdasarkan shift terbuka.

#### A3. Penjualan POS (Sale)
- Sistem harus menyediakan UI/endpoint untuk pembuatan transaksi POS.
- Sistem harus memvalidasi field wajib: item minimal 1, qty minimal 1, price numeric, grand_total, payment_method, paid_amount.
- Sistem harus menghitung `change_amount`.
- Sistem harus memblokir penjualan jika shift kasir belum dibuka.
- Sistem harus melakukan stok decrement pada warehouse yang terkait dengan shift kasir.
- Sistem harus membuat `StockMovement` berelasi ke `Sale`.
- Sistem harus membuat record pembayaran (relasi `payments()`) untuk metode pembayaran.
- Sistem harus mendukung konfigurasi jam operasional via `Setting` (opsional/berdasarkan `store_hours_enabled`).

#### A4. Retur Penjualan (SaleReturn)
- Sistem harus menyediakan proses pembuatan retur untuk suatu Sale.
- Sistem harus memvalidasi items minimal 1 dan `refund_method` wajib.
- Sistem harus menambah stok kembali pada warehouse sale.
- Sistem harus membuat `StockMovement` type `sale_return`.

#### A5. Pembelian via PO
- Sistem harus menyediakan pembuatan Purchase Order dengan status awal `draft`.
- Sistem harus menyediakan proses menerima barang dari PO (receive goods).
- Sistem harus menghitung total purchase dan menentukan `payment_status` dari paid vs total.
- Sistem harus menambah stok pada receive.
- Sistem harus membuat `StockMovement` type `purchase` dan referensi ke `Purchase`.
- Sistem harus mengupdate `received_quantity` pada PO items.
- Sistem harus membuat `SupplierDebt` jika tidak lunas.

#### A6. Pembelian Langsung (tanpa PO)
- Sistem harus menyediakan proses input purchase langsung.
- Sistem harus menambah stok, membuat `StockMovement` type `purchase`, dan membuat `SupplierDebt` jika partial/unpaid.

#### A7. Retur Pembelian
- Sistem harus menyediakan proses retur untuk suatu Purchase.
- Sistem harus menambah data return header/detail.
- Sistem harus mengurangi stok pada warehouse purchase.
- Sistem harus membuat `StockMovement` type `purchase_return` berelasi ke `PurchaseReturn`.

#### A8. Manajemen Stok
- Sistem harus menyediakan operasi stok: in, out, transfer, adjustment.
- Setiap operasi harus membuat `StockMovement` sesuai type.
- Sistem harus mendukung stock opname (draft → complete) dan mengubah stok berdasarkan difference.

#### A9. Kas Masuk/Keluar (CashMovement)
- Sistem harus menyediakan input kas masuk/keluar yang terhubung ke `cash_register_id`.
- Sistem harus menandai `type=in|out`.

#### A10. Laporan
- Sistem harus menyediakan laporan penjualan, pembelian, stok, laba rugi, kas, produk terlaris, pelanggan, supplier.
- Laporan harus bisa difilter dengan tanggal dan (untuk admin/supervisor) filter cabang.
- Sistem harus mendukung output view/print dan export Excel (mengacu mekanisme `handleReportOutput`).

### B. Kebutuhan Non-Fungsional (Non-Functional Requirements)

#### B1. Konsistensi Data & Atomicity
- Operasi transaksi (sale, purchase receive, return, opname complete) harus berjalan dalam **database transaction** (`DB::transaction`) agar tidak terjadi stok/pembayaran terpisah.

#### B2. Audit Trail
- Setiap perubahan stok harus selalu menghasilkan `StockMovement`.
- Aktivitas login/logout harus menghasilkan `ActivityLog`.

#### B3. Keamanan & Compliance Akses
- Endpoint transaksi harus dilindungi middleware role.
- Penjualan POS harus tergantung shift kasir (security operasional).

### C. Kebutuhan Data (Data Requirements)
- Tersedia tabel:
  - `sales`, `sale_items`, `payments`
  - `sale_returns`, `sale_return_items`
  - `purchase_orders`, `purchase_order_items`
  - `purchases`, `purchase_items`
  - `purchase_returns`, `purchase_return_items`
  - `product_stocks`
  - `stock_movements`, `stock_opnames`, `stock_opname_items`
  - `cash_registers`, `cash_movements`
  - `supplier_debts` (dan customer debt jika digunakan di modul lain)

---

## 12. SOP Proses Bisnis (Lebih Detail)

Bagian ini menjelaskan SOP praktis langkah-per-langkah yang biasanya diperlukan untuk operasional harian.

### 12.1 SOP Penjualan POS (Sale)
**Aktor:** cashier

**Input yang digunakan:** produk + kuantitas/harga, opsional customer, payment_method/paid_amount, opsional voucher_code.

**Langkah SOP:**
1. Pastikan **shift kasir** sudah dibuka.
   - Jika belum, sistem menolak transaksi POS.
2. Pastikan produk yang dipilih adalah **produk aktif**.
3. Pastikan stok yang tampil adalah stok berdasarkan **warehouse shift**.
4. Jika store hours diaktifkan, pastikan transaksi dilakukan pada jam operasional.
5. Input item transaksi sampai minimal 1 baris.
6. Tentukan ringkasan transaksi:
   - subtotal, discount_total, grand_total.
7. Tentukan:
   - payment_method
   - opsional `voucher_code` (jika ada)
   - paid_amount (dan sistem otomatis menghitung change_amount).
8. Klik simpan:
   - Sistem membuat record `Sale` (status completed) + `SaleItem`.
   - Sistem mengurangi `ProductStock` untuk setiap item.
   - Sistem membuat `StockMovement` type `sale` per item.
   - Sistem membuat record pembayaran melalui relasi `payments()` (jika tersedia).
9. Verifikasi output:
   - invoice_number terbuat (format INV-...).
   - stok berkurang.

**Keluaran:** transaksi penjualan selesai, stok termutakhirkan.

---

### 12.2 SOP Retur Penjualan (Sale Return)
**Aktor:** cashier (dan admin/supervisor lewat route yang mengizinkan)

**Input:** daftar item yang diretur, reason opsional, refund_method wajib.

**Langkah SOP:**
1. Pilih transaksi penjualan (`Sale`) yang ingin diretur.
2. Masukkan item dan jumlah retur.
3. Isi `refund_method`.
4. Simpan retur:
   - Sistem membuat `SaleReturn` + `SaleReturnItem`.
   - Sistem **menambah** stok (`ProductStock`) pada `sale->warehouse_id`.
   - Sistem membuat `StockMovement` type `sale_return`.
   - Sistem menghitung total retur.
5. Cek hasil:
   - stok kembali bertambah.
   - retur tercatat (return_number format RTS-...).

**Keluaran:** stok kembali, dokumen retur tercatat.

---

### 12.3 SOP Pembelian via Purchase Order (PO) dan Receive Goods
**Aktor:** admin/warehouse

**Input PO:** supplier, warehouse, order_date, expected_date (opsional), item (qty & price), note (opsional).

**Langkah SOP PO:**
1. Buat Purchase Order:
   - Status awal `draft`.
   - Isi supplier & warehouse.
   - Isi item (quantity & price) → sistem menghitung total PO.
2. Setelah dikirim/direspon (jika ada proses status lain), status PO dapat diperbarui (draft/sent/partial/received).

**Input Receive Goods:** items diterima, paid_amount opsional, purchase_date wajib.

**Langkah SOP Receive Goods:**
1. Buka form receive untuk PO.
2. Masukkan item yang benar-benar diterima.
3. Tentukan paid_amount (bila lunas/ sebagian/ belum).
4. Sistem menyimpan `Purchase` (faktur):
   - invoice_number format PUR-...
   - `payment_status` ditentukan dari paid vs total.
5. Untuk setiap item:
   - Sistem membuat `PurchaseItem`.
   - Sistem menambah `ProductStock` di warehouse PO.
   - Sistem membuat `StockMovement` type `purchase`.
   - Sistem menambah `received_quantity` pada `PurchaseOrderItem`.
6. Jika belum lunas (partial/unpaid), sistem membuat `SupplierDebt`.
7. Sistem mengubah `PurchaseOrder.status='received'`.

**Keluaran:** faktur purchase tercatat, stok bertambah, piutang supplier (jika ada) tercatat.

---

### 12.4 SOP Retur Pembelian
**Aktor:** admin/warehouse

**Input:** purchase yang diretur, items, reason opsional.

**Langkah SOP:**
1. Buka form retur untuk `Purchase`.
2. Masukkan item & quantity retur.
3. Simpan retur:
   - Sistem membuat `PurchaseReturn` + `PurchaseReturnItem`.
   - Sistem mengurangi `ProductStock` pada `purchase->warehouse_id`.
   - Sistem membuat `StockMovement` type `purchase_return` (quantity negatif pada stok).
4. Verifikasi: total retur ter-update.

**Keluaran:** stok berkurang kembali sesuai retur.

---

### 12.5 SOP Manajemen Stok Operasional & Opname

#### (1) Stock In/Out
- **Stock In**: quantity ditambahkan, `StockMovement.type='in'`.
- **Stock Out**: quantity dikurangi, `StockMovement.type='out'`.

#### (2) Transfer
- Keluar dari gudang asal: type `transfer` qty `-quantity`.
- Masuk ke gudang tujuan: type `transfer` qty `+quantity`.

#### (3) Adjustment
- Sistem menghitung selisih antara actual_quantity dan system_quantity.
- Adjustment diterapkan dengan type `adjustment`.

#### (4) Stock Opname
- Mulai (`draft`) → inisialisasi item dari stock sistem.
- Update item actual quantity → hitung difference.
- Complete opname:
  - setiap difference ≠ 0 dipakai untuk `adjustStock` type `opname`.
  - StockOpname status menjadi `completed`.

---

### 12.6 SOP Shift Kasir & Kas Masuk/Keluar
**Aktor:** cashier/admin/supervisor (sesuai route)

#### Buka shift
1. Pastikan user belum punya shift `status=open`.
2. Input `opening_balance`.
3. Sistem membuat `CashRegister` dengan `branch_id`.

#### Tutup shift
1. Input `closing_balance`.
2. Sistem hitung expected balance dari:
   - opening_balance
   - sales cash (berdasarkan payments cash)
   - cashIn - cashOut
3. Hitung selisih & simpan status `closed`.

#### Kas masuk/keluar (di luar penjualan)
1. Input cash_register_id, amount, category, description.
2. Sistem membuat `CashMovement` type `in` atau `out`.

---

## 13. Lampiran: Sumber Kode Utama yang Ditelusuri
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Controllers/CashController.php`
- `app/Http/Controllers/TransactionController.php`
- `app/Http/Controllers/PurchaseController.php`
- `app/Http/Controllers/ReturnController.php`
- `app/Http/Controllers/StockController.php`
- `app/Http/Controllers/ReportController.php`
- `routes/web.php` (mapping modul & role)
