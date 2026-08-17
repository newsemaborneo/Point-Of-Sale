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
- **AI Analysis Dashboard:** Menganalisis kondisi bisnis secara komprehensif dan real-time dengan minimal 5 parameter utama (Penjualan, Stok Kritis, Cashflow, Produk Terlaris, dan Ringkasan Inventaris).
- **AI Recommendation:** Memberikan minimal 5 rekomendasi taktis secara proaktif (misal: restock produk kritis, promosi produk potensial, optimalisasi jam ramai, strategi up-selling/AOV, dan instruksi audit stok).
- **AI Chat Assistant (Data-driven):** Chatbot interaktif dengan Role-Based Access Control (RBAC) yang membedakan jawaban berdasarkan jabatan (SuperAdmin, Admin, Supervisor, Cashier). Mendukung *visualisasi chart* jika jawaban membutuhkan komparasi data.
  - *Typeahead Autocomplete Input:* Fitur saran otomatis saat mengetik di input chat, membantu memandu pengguna ke 20 pertanyaan analisis bisnis yang didukung sistem dengan kecocokan real-time dan highlight teks.
  - *Typewriter Streaming Effect:* Teks balasan AI mengalir secara mulus dengan animasi ketikan bertahap yang adaptif terhadap panjang pesan agar terasa interaktif dan humanis.
  - *Humanistic & Natural Tone:* Gaya bahasa asisten AI disetel agar ramah, menggunakan diksi yang memotivasi, santun, serta menggunakan istilah bisnis lokal yang akurat tanpa halusinasi data.
- **20 Predefined Business Queries:** AI dibatasi secara ketat hanya menjawab 20 domain analisis bisnis berikut untuk menjamin nol halusinasi:
  1. Omset hari ini
  2. Produk terlaris bulan ini
  3. Perbandingan penjualan bulanan
  4. Jam paling sibuk hari ini (peak hours)
  5. Pelanggan paling banyak belanja (top customers)
  6. Total transaksi bulanan
  7. Rata-rata nilai transaksi bulanan (basket size)
  8. Produk stok kritis/hampir habis
  9. Produk tidak laku 30 hari (dead stock)
  10. Total produk aktif saat ini
  11. Stok produk spesifik tertentu (aman/terarah)
  12. Laba kotor bulan ini
  13. Total HPP (Harga Pokok Penjualan) bulan ini
  14. Total utang ke supplier
  15. Metode pembayaran terpopuler
  16. Margin keuntungan bulan ini
  17. Prediksi omset bulan depan (WMA 3 bulan)
  18. Prediksi stok produk habis paling cepat
  19. Rekomendasi bundling produk (affinity analysis)
  20. Tren penjualan 3 bulan terakhir
- **AI Microservice:** Arsitektur terpisah menggunakan Python (FastAPI) untuk mengisolasi beban pemrosesan *prompting* dan berinteraksi secara aman dengan Google Gemini API, dioptimalkan dengan asinkronisasi (AJAX) dan Caching di sisi Laravel agar antarmuka tidak *lagging*.

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

### 5.3 Alur Kerja Pengguna dengan AI Intelligence
1. Pengguna (SuperAdmin/Supervisor/Cashier) membuka halaman AI Intelligence di dashboard.
2. Dashboard melakukan asinkronisasi (*AJAX*) untuk memuat data tanpa memblokir tampilan UI.
3. Dashboard langsung menampilkan 5 *Insight* analisis dan 5 *Rekomendasi* spesifik cabang sesuai otoritas jabatan.
4. Pengguna menggunakan **AI Chat Assistant** untuk menggali lebih dalam. AI merespons dengan jawaban *natural language* dan secara otomatis merender *grafik (chart)* jika terdapat data komparatif yang relevan.

## 6. Persyaratan Teknis & Arsitektur
- **Framework Backend Utama:** Laravel (PHP) dengan arsitektur MVC.
- **Microservice AI:** Python (FastAPI) + Google GenAI SDK untuk pemrosesan AI, terpisah dari sistem inti untuk skalabilitas.
- **Frontend / UI:** Blade Templates, Tailwind CSS (modern, glassmorphism), Alpine.js, dan Chart.js untuk interaktivitas komponen.
- **Database:** Relational Database (MySQL / PostgreSQL) dengan struktur tabel dinormalisasi.
- **Integrasi AI & RAG:** Menggunakan Gemini API secara asinkron (*AJAX / Fetch*) dipadukan dengan teknik *Retrieval-Augmented Generation* (RAG) dinamis berbasis JSON, serta *Caching Redis/File* untuk menghindari bottleneck dan *lag*.
- **Responsive Design:** Mendukung tampilan dekstop untuk back-office dan mobile-first (100dvh, edge-to-edge) untuk kemudahan kontrol via HP.

## 7. Fase Pengembangan & Roadmap
1. **Fase 1 (MVP - Core POS):** Manajemen Pengguna, Master Data, Transaksi POS, Shift Kasir, Pembelian, dan Stok dasar.
2. **Fase 2 (Advanced Finance & Customer):** Utang-Piutang, Poin Loyalti, Retur Penjualan/Pembelian, Stock Opname, Cash Register Lanjutan.
3. **Fase 3 (AI & Analytics):** Integrasi AI Dashboard, AI Recommendation, AI Chat Assistant, dan Laporan Kustom.
4. **Fase 4 (Optimization):** Peningkatan performa UX/UI, Audit Trail ekstensif, dan penyesuaian mobile layout.

---
*Dokumen PRD ini mencakup seluruh fungsionalitas sistem berjalan dan rencana ekspansi. Dokumen dapat diperbarui secara iteratif seiring feedback pengguna dan kebutuhan pasar.*
