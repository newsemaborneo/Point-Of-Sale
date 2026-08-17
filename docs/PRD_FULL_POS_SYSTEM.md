# Product Requirements Document (PRD) - Full Sistem Point of Sales (POS) Terintegrasi AI

## 1. Informasi Dokumen
- **Nama Proyek:** Sistem POS Terintegrasi & Manajemen Cabang Berbasis AI
- **Versi:** 1.0
- **Status:** Perencanaan & Implementasi Berjalan
- **Tujuan Dokumen:** Menjabarkan spesifikasi lengkap dari keseluruhan sistem POS, fitur, arsitektur, dan integrasi modul yang ada di dalam aplikasi.

## 2. Ringkasan Eksekutif
Proyek ini adalah sistem *Point of Sales* (POS) komprehensif yang dirancang untuk mengelola operasional ritel multi-cabang. Sistem ini tidak hanya menangani transaksi kasir dasar, tetapi juga manajemen inventaris, pembelian (purchasing), utang piutang, perpindahan stok antar gudang, manajemen pelanggan (loyalty), hingga shift kasir dan pergerakan uang tunai (cash management).

Keunggulan utama sistem ini adalah adopsi teknologi *Artificial Intelligence* (AI) pada dashboard manajemen (SuperAdmin) yang mampu melakukan analisis data otomatis, memberikan rekomendasi keputusan prioritas, dan memiliki fitur Chat Assistant yang mampu menjawab pertanyaan analitis berdasarkan data riil dari sistem.

## 3. Sasaran Pengguna & Peran (Roles)
Sistem ini dirancang multi-role (RBAC - Role Based Access Control) dengan tingkat otorisasi yang berbeda:
1. **SuperAdmin / Pemilik Bisnis:** Memiliki akses penuh ke seluruh cabang, pengaturan sistem, fitur AI Analysis, dan laporan keuangan tingkat atas.
2. **Admin:** Mengelola master data (produk, supplier, pelanggan), stok opname, dan laporan manajerial.
3. **Supervisor:** Mengawasi operasional harian cabang tertentu, menyetujui pembatalan transaksi, dan mengawasi pergerakan kas.
4. **Cashier (Kasir):** Bertanggung jawab atas modul POS (transaksi penjualan), manajemen shift kasir (buka/tutup shift), dan penerimaan pembayaran.

## 4. Ruang Lingkup Sistem (Modul Utama)

### 4.1. Modul Pengguna & Master Data
- **Manajemen Pengguna & Peran:** Pengelolaan User, otorisasi Role (SuperAdmin, Admin, Supervisor, Cashier).
- **Manajemen Hierarki:** Pengelolaan Cabang (Branch) dan Gudang (Warehouse).
- **Katalog Produk:** Pengelolaan Produk, Kategori, Satuan (Unit), dan Paket Produk (Product Bundle).
- **Barcode System:** Generate dan cetak barcode untuk produk fisik.

### 4.2. Modul Penjualan & POS (Point of Sales)
- **Transaksi Kasir (POS):** Pemrosesan Sale dan SaleItem dengan dukungan barcode scanner.
- **Manajemen Shift Kasir (Cash Register):** Pencatatan buka/tutup laci uang, saldo awal, dan saldo akhir.
- **Promosi & Diskon:** Penerapan Promotion dan Voucher otomatis pada transaksi.
- **Retur Penjualan:** Pemrosesan SaleReturn dan pengembalian dana/stok.

### 4.3. Modul Pelanggan & Piutang
- **Database Pelanggan:** Profil Customer dan histori transaksi.
- **Loyalty Program:** Penghitungan dan penukaran Customer Point.
- **Manajemen Piutang:** Pencatatan utang pelanggan (CustomerDebt) dan pelunasannya (CustomerDebtPayment).

### 4.4. Modul Pembelian & Utang (Purchasing)
- **Manajemen Supplier:** Database Supplier.
- **Purchase Order (PO):** Pembuatan pesanan pembelian ke supplier.
- **Pembelian Langsung:** Pencatatan Purchase dan penerimaan barang.
- **Utang Supplier:** Pencatatan utang ke supplier (SupplierDebt) dan pelunasannya.
- **Retur Pembelian:** Pemrosesan PurchaseReturn ke supplier.

### 4.5. Modul Inventaris & Stok
- **Pemantauan Stok:** Real-time ProductStock di berbagai cabang/gudang.
- **Pergerakan Stok (Stock Movement):** Transfer antar cabang/gudang, penyesuaian stok masuk/keluar.
- **Stock Opname:** Proses audit stok fisik (StockOpname & StockOpnameItem).

### 4.6. Modul Keuangan & Kas (Cash Management)
- **Arus Kas:** Pencatatan pergerakan kas masuk/keluar (CashMovement) di luar transaksi penjualan.
- **Manajemen Pembayaran:** Rekonsiliasi Payment dari berbagai metode pembayaran (Tunai, Transfer, E-Wallet).

### 4.7. Modul Kecerdasan Buatan (AI Intelligence)
- **AI Analysis Dashboard:** Menganalisis kondisi bisnis secara komprehensif dan real-time.
- **AI Recommendation:** Memberikan saran proaktif (contoh: restock produk yang hampir habis, promosi untuk produk lambat terjual).
- **AI Chat Assistant (Data-driven):** Chatbot interaktif untuk SuperAdmin yang dapat merespons pertanyaan natural language seputar operasional (misal: "Apa produk terlaris di cabang A minggu ini?") berdasarkan data riil dari sistem POS.

### 4.8. Modul Laporan & Sistem
- **Reporting:** Laporan penjualan, laporan stok, laba rugi kotor, laporan pajak, dan performa kasir.
- **Audit & Log:** Pencatatan aktivitas pengguna (ActivityLog) untuk keamanan dan compliance.
- **Notifikasi:** Peringatan stok menipis, peringatan utang jatuh tempo (AppNotification).
- **Pengaturan Sistem:** Konfigurasi pajak, struk, dan pengaturan global (Setting).

## 5. Alur Kerja (Workflows) Penting

### 5.1 Alur Transaksi Kasir (POS Workflow)
1. Kasir login dan melakukan **Buka Shift** (Open Register) dengan memasukkan saldo awal kas.
2. Kasir memproses transaksi (scan barcode produk, pilih pelanggan, terapkan promo/voucher).
3. Kasir menerima pembayaran (Payment) dan menyelesaikan pesanan (Sale).
4. Sistem secara otomatis memotong Stok (ProductStock) dan mencatat pergerakan kas/piutang.
5. Di akhir jam kerja, kasir melakukan **Tutup Shift** (Close Register) untuk mencocokkan fisik uang dengan catatan sistem.

### 5.2 Alur Restock & Pembelian
1. AI Assistant atau laporan menunjukkan stok produk di bawah batas aman.
2. Admin membuat Purchase Order ke Supplier.
3. Saat barang tiba, Admin mencatat Pembelian (Purchase), yang otomatis menambah stok (ProductStock).
4. Jika pembayaran belum lunas, sistem mencatat Utang Supplier (SupplierDebt).

### 5.3 Alur Kerja SuperAdmin dengan AI
1. SuperAdmin membuka halaman AI Intelligence di dashboard.
2. Dashboard langsung menampilkan peringatan anomali (misal: Penurunan penjualan 20% di Cabang X).
3. SuperAdmin membaca Rekomendasi AI (misal: Jalankan diskon untuk stok menumpuk).
4. SuperAdmin menggunakan **AI Chat Assistant** untuk menggali lebih dalam ("Tolong rinci produk apa saja yang menumpuk di Cabang X"). AI merespons dengan data tabular yang tepat.

## 6. Persyaratan Teknis & Arsitektur
- **Framework Backend:** Laravel (PHP) dengan arsitektur MVC.
- **Frontend / UI:** Blade Templates, Tailwind CSS (modern, glassmorphism), Alpine.js untuk interaktivitas komponen.
- **Database:** Relational Database (MySQL / PostgreSQL) dengan struktur tabel dinormalisasi.
- **Integrasi AI:** Menggunakan LLM API (seperti OpenAI GPT atau Gemini) dengan teknik Retrieval-Augmented Generation (RAG) untuk membaca database POS dan memberikan respons akurat.
- **Responsive Design:** Mendukung tampilan dekstop untuk back-office dan mobile-first (100dvh, edge-to-edge) untuk kemudahan kontrol via HP.

## 7. Fase Pengembangan & Roadmap
1. **Fase 1 (MVP - Core POS):** Manajemen Pengguna, Master Data, Transaksi POS, Shift Kasir, Pembelian, dan Stok dasar.
2. **Fase 2 (Advanced Finance & Customer):** Utang-Piutang, Poin Loyalti, Retur Penjualan/Pembelian, Stock Opname, Cash Register Lanjutan.
3. **Fase 3 (AI & Analytics):** Integrasi AI Dashboard, AI Recommendation, AI Chat Assistant, dan Laporan Kustom.
4. **Fase 4 (Optimization):** Peningkatan performa UX/UI, Audit Trail ekstensif, dan penyesuaian mobile layout.

---
*Dokumen PRD ini mencakup seluruh fungsionalitas sistem berjalan dan rencana ekspansi. Dokumen dapat diperbarui secara iteratif seiring feedback pengguna dan kebutuhan pasar.*
