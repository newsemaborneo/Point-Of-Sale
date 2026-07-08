# Analisis Skalabilitas Perangkat Lunak (POS - Laravel)

Dokumen ini menyesuaikan kebutuhan dokumen proses pengujian/pengembangan yang mengarah ke capaian:
- J.620100.020.02 Menggunakan SQL
- J.620100.021.02 Menerapkan akses basis data
- J.620100.022.02 Mengimplementasikan algoritma pemrograman
- J.620100.023.02 Membuat dokumen kode program
- J.620100.025.02 Melakukan debugging
- J.620100.031.01 Melakukan profiling program
- J.620100.032.01 Menerapkan code review
- J.620100.033.02 Melaksanakan pengujian unit program
- J.620100.034.02 Melaksanakan pengujian integrasi program

Fokus pembahasan mengacu pada implementasi modul inti di repository: **TransactionController, StockController, ReportController,** dan route middleware berbasis role.

---

## 1) Tujuan dan Ruang Lingkup Skalabilitas

### Tujuan
Menilai kemampuan aplikasi POS untuk menangani:
- peningkatan jumlah transaksi harian (sales/purchases/returns),
- peningkatan jumlah data master (produk/pelanggan/supplier),
- peningkatan jumlah cabang/gudang (multi-warehouse),
- kebutuhan performa laporan & pencarian,
- kestabilan integritas data (khususnya stok).

### Ruang Lingkup yang dianalisis
1. **Akses basis data & SQL**
   - pola query Eloquent/Query Builder,
   - potensi N+1 query,
   - indeks dan strategi filter.
2. **Algoritma & pola pemrosesan**
   - loop transaksi per item,
   - perhitungan laporan (aggregate/join),
   - konsistensi stok via transaction.
3. **Profiling, debugging, code review, unit/integration tests**
   - apa yang perlu diuji untuk memastikan performa dan correctness.

---

## 2) Ringkasan Arsitektur Data yang Berpengaruh pada Skalabilitas

### Entitas inti (berdasarkan controller)
- **Sale, SaleItem, Payment**
- **Purchase, PurchaseItem, SupplierDebt / PurchaseOrder**
- **StockMovement, ProductStock, StockOpname**
- **CashRegister, CashMovement**
- **Branch, Warehouse**

### Pola konsistensi stok
- Perubahan stok dilakukan melalui:
  - update `ProductStock.quantity`,
  - pencatatan `StockMovement`.
- Transaksi penjualan menggunakan `DB::beginTransaction()` dan rollback pada exception.
- Manipulasi stok manual/opname menggunakan `DB::transaction()`.

Implikasi skalabilitas: operasi stok per item adalah hot path; efisiensi query dan jumlah round-trip DB akan menentukan throughput.

---

## 3) Analisis Skalabilitas per Modul

### 3.1 Transaksi POS (TransactionController)
**Hot path: method `store()`**
- Validasi request.
- Cek `CashRegister` status open.
- `Sale::create()`.
- Loop `request->items`:
  - `sale->items()->create(...)`
  - `ProductStock::firstOrCreate(...)`
  - `ProductStock->decrement(...)`
  - `StockMovement::create(...)`
- Simpan pembayaran bila relasi `payments()` tersedia.

**Risiko performa pada skala tinggi**
- Query per item: `firstOrCreate`, `decrement`, `StockMovement::create`, `SaleItem::create`.
- Potensi banyak round-trip DB ketika item per transaksi besar (mis. basket besar).

**Saran optimasi SQL/akses basis data**
1. **Batch operations** untuk `SaleItem` dan `StockMovement` (insert banyak baris) agar mengurangi round-trip.
2. **Mengunci baris stok** untuk mencegah race condition bila transaksi paralel memotong stok:
   - gunakan `SELECT ... FOR UPDATE` (atau Eloquent `lockForUpdate`) pada `ProductStock` row terkait.
3. **Kurangi `firstOrCreate` di hot path**:
   - preload `ProductStock` untuk semua `product_id` pada warehouse.
   - buat missing row sekali sebelum loop (bulk upsert).
4. **Indeks** minimal pada kolom filter/join:
   - `cash_registers(user_id,status)`,
   - `product_stocks(product_id,warehouse_id)`,
   - `stock_movements(product_id,warehouse_id,created_at)`,
   - `sale_items(sale_id,product_id)` dan `sales(invoice_number)`.

---

### 3.2 Manajemen Stok (StockController)
Method yang relevan:
- `adjustStock()` yang melakukan transaction per operasi.
- `transfer()` yang melakukan dua panggilan `adjustStock()` dalam `DB::transaction()`.
- `startOpname()` dan `completeOpname()`.

**Risiko performa**
- `startOpname()` membuat `StockOpnameItem` untuk setiap `ProductStock` pada warehouse:
  - O(n) baris; saat warehouse besar, bisa berat.
- `completeOpname()` loop setiap item yang difference != 0 lalu memanggil `adjustStock()`:
  - potensi transaction bersarang (karena `adjustStock` sudah transaction) → overhead.

**Saran optimasi algoritma/pemrograman**
1. Hindari nested transaction di hot path opname:
   - buat versi `adjustStockNoTransaction()` untuk dipanggil dari `completeOpname` agar satu transaction utama.
2. Batch insert `StockOpnameItem` saat start opname.
3. Batch update/insert untuk `StockMovement` bila banyak difference.

---

### 3.3 Laporan (ReportController)
Method yang berat:
- `sales()` (get semua lalu sum/count di PHP)
- `purchases()` (get semua lalu sum)
- `profitLoss()` (join sale_items dengan products untuk COGS)
- `stock()` (get semua movements lalu export)
- `bestSellingProducts()` (aggregate top 20)

**Risiko performa**
- Penggunaan `->get()` sebelum agregasi (`sum/count`) dapat menimbulkan load besar di memory dan query result.
- Ketika data meningkat, `->get()` tanpa pagination untuk laporan dapat menurunkan performa.

**Saran penerapan SQL (J.620100.020.02)**
1. Gunakan agregasi di SQL daripada di PHP:
   - contoh `sum`/`count` lewat query builder sebelum `get()`.
2. Untuk listing laporan, gunakan pagination atau limit sesuai kebutuhan UI.
3. Pastikan indeks untuk filter:
   - `sales(status,created_at,branch_id)`
   - `sale_items(sale_id,created_at?)` (atau lewat relasi)
   - `cash_movements(type,created_at)` dan `cash_registers(branch_id)`
   - `stock_movements(created_at,warehouse_id,type?)`

---

## 4) Kesiapan Pengujian (Unit dan Integrasi)

### 4.1 Unit testing yang disarankan
- Validasi perhitungan:
  - `change_amount = max(0, paid_amount - grand_total)`
  - perhitungan `profitLoss` (revenue, cogs, expenses) dengan dataset kecil.
- Unit untuk fungsi stok internal:
  - `adjustStock()` memastikan quantity_before/after benar dan `StockMovement` dibuat.

### 4.2 Integration testing yang disarankan
- Transaction POS:
  1. buka shift (create CashRegister open),
  2. jalankan `POST /transactions` dengan 2 item,
  3. assert:
     - `sales` bertambah,
     - `sale_items` sesuai,
     - `product_stocks.quantity` berkurang sesuai qty,
     - `stock_movements.type='sale'` dibuat.
- Retur:
  - assert stok naik dan movement type sesuai.
- Laporan:
  - seed data minimal lalu panggil endpoint laporan dan assert output aggregates.

---

## 5) Profiling & Debugging yang Perlu Dilakukan

### Profiling (J.620100.031.01)
- Profiling di hot path:
  - `TransactionController@store`
  - `StockController@startOpname/completeOpname`
  - `ReportController@stock/profitLoss`

Output yang dipantau:
- jumlah query per request,
- total waktu DB per endpoint,
- memory usage saat laporan menggunakan `get()`.

### Debugging (J.620100.025.02)
- Validasi edge case:
  - shift tidak ada → harus 403,
  - store hours enabled melewati batas jam,< TODO >
  - stok tidak ada row awal → harus `firstOrCreate` / upsert.
- Konsistensi transaksi: pastikan `DB::rollBack()` terjadi saat exception.

---

## 6) Code Review Checkpoints (J.620100.032.01)

Checklist yang relevan:
- Apakah query di loop item dapat di-optimize batch?
- Apakah ada nested transaction yang tidak perlu?
- Apakah indeks di DB mendukung filter dan join?
- Apakah `->get()` pada laporan menyebabkan load berlebih?
- Apakah `reference_type/ref_id` pada `StockMovement` konsisten?

---

## 7) Dokumentasi Kode Program (J.620100.023.02)

Template dokumentasi yang disarankan (dapat dipakai untuk menyusun dokumentasi modul ke depan):
1. Nama fungsi endpoint
2. Input request yang divalidasi
3. Perubahan data yang terjadi (tabel apa saja)
4. Proses stok/pembayaran
5. Error handling & response yang dihasilkan
6. Dampak performa (perkiraan query per item)

Dokumen ini sudah memetakan langkah-langkah di level controller.

---

## 8) SQL dan Akses Database: Rekomendasi Implementasi Praktis

1. Indexing (prioritas):
   - `product_stocks(product_id, warehouse_id)` unique/compound
   - `stock_movements(product_id, warehouse_id, created_at)`
   - `sales(status, created_at, branch_id)`
   - `sale_items(sale_id, product_id)`
   - `cash_registers(user_id, status)`
   - `cash_movements(cash_register_id, type, created_at)`
2. Gunakan `lockForUpdate` pada row stok untuk mencegah race condition.
3. Untuk laporan, lakukan agregasi dengan SQL (`sum`, `count`, `groupBy`) tanpa memuat seluruh dataset ke PHP.
4. Untuk update stok massal (opname/difference besar), pertimbangkan batch insert/update.

---

## 9) Penutup

Aplikasi POS ini sudah menerapkan prinsip konsistensi stok via `ProductStock` + `StockMovement` dan menggunakan transaksi database pada operasi penting. Skalabilitas ke depan terutama dipengaruhi oleh:
- jumlah query per item saat transaksi,
- cara laporan mengambil data (hindari `get()` besar tanpa agregasi/pagination),
- overhead nested transaction pada opname,
- dukungan indeks untuk filter & join.

Dokumen ini memberikan arah implementasi uji dan peningkatan sesuai capaian kode program dan pengujian yang Anda sebutkan.

