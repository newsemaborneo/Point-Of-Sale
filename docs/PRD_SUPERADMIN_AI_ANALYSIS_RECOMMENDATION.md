# PRD: AI Analysis dan AI Recommendation pada Tampilan SuperAdmin

## 1. Informasi Dokumen
- Versi: 1.0
- Tanggal: 2026-08-15
- Status: Draft untuk implementasi
- Owner: Product / Engineering
- Target: SuperAdmin POS

## 2. Ringkasan Eksekutif
SuperAdmin memerlukan alat analisis yang mampu membaca kondisi bisnis secara cepat dari data transaksi, stok, kas, cabang, supplier, dan performa operasional. Saat ini, keputusan strategis sering diambil berdasarkan laporan manual dan intuisi. Hal ini membuat proses identifikasi masalah seperti produk slow-moving, stok kritis, branch underperform, dan kebutuhan promosi menjadi lambat dan tidak konsisten.

PRD ini merancang fitur AI Analysis dan AI Recommendation pada dashboard SuperAdmin agar:
- SuperAdmin dapat memahami kondisi bisnis secara real-time atau near real-time.
- Sistem mampu mengidentifikasi anomali, peluang, dan risiko dari data operasional.
- Sistem menyarankan langkah tindakan prioritas berdasarkan data yang ada.
- Tampilan tetap sederhana, cepat dipahami, dan bisa diimplementasikan secara bertahap.

Fitur ini tidak dimaksudkan untuk menggantikan keputusan manusia, melainkan sebagai support decision system yang menyajikan insight berbasis data dan rekomendasi prioritas.

---

## 3. Latar Belakang
Aplikasi POS ini mencakup data transaksi, stok, pembelian, kas, cabang, sales, promosi, utang, dan operasional toko. SuperAdmin memiliki tanggung jawab terhadap performa keseluruhan jaringan bisnis, termasuk:
- memantau performance semua cabang,
- menilai kesehatan stok dan penjualan,
- mengawasi aliran kas,
- mengevaluasi efektivitas promosi,
- mengetahui apakah ada branch yang underperform atau tidak sehat.

Saat ini, analisis yang tersedia kemungkinan masih bersifat manual dan fragmented. SuperAdmin perlu mengklik beberapa laporan untuk mendapatkan insight yang sebenarnya. Kondisi ini menyebabkan:
- kecepatan pengambilan keputusan menurun,
- potensi kerugian tidak terdeteksi cepat,
- rekomendasi kebijakan tidak terdokumentasi dengan data,
- pengelolaan cabang dan inventory jadi kurang terukur.

Dengan fitur AI Analysis dan AI Recommendation, dashboard SuperAdmin dapat menjadi pusat insight bisnis yang proaktif.

---

## 4. Tujuan Produk
### 4.1 Tujuan utama
1. Memberikan analisis otomatis terhadap kondisi bisnis dari data POS.
2. Menghasilkan rekomendasi tindakan berbasis data yang dapat diprioritaskan.
3. Membantu SuperAdmin mengambil keputusan strategis dengan lebih cepat dan lebih akurat.
4. Menyediakan tampilan dashboard yang ringkas, jelas, dan dapat dijadikan basis kontrol operasional.

### 4.2 Tujuan bisnis
- Menurunkan waktu analisis laporan manual.
- Mencegah stok habis / overstock yang tidak terduga.
- Meningkatkan efisiensi promosi dan pricing.
- Menjaga cashflow dan kesehatan cabang.
- Mempercepat deteksi branch / produk / kategori yang perlu ditindaklanjuti.

---

## 5. Target Pengguna
### 5.1 Persona utama
#### SuperAdmin
- Memiliki akses lintas cabang dan data sistem secara menyeluruh.
- Bertanggung jawab terhadap strategi, pengendalian, dan evaluasi performa bisnis.
- Menginginkan insight yang cepat, ringkas, dan dapat ditindaklanjuti.

#### Koordinator / Manager cabang (opsional dalam masa depan)
- Memiliki kebutuhan melihat insight cabang tertentu.
- Ingin rekomendasi yang spesifik untuk area operasional mereka.

### 5.2 Kebutuhan utama SuperAdmin
- Melihat ringkasan kesehatan bisnis pada satu layar.
- Mengetahui anomali atau peringatan saat terjadi perubahan signifikan.
- Mendapat rekomendasi prioritas dengan alasan yang jelas.
- Bisa melihat data pendukung atau detail insight jika diperlukan.

---

## 6. Ruang Lingkup
### 6.1 Dalam scope
- Dashboard SuperAdmin dengan panel AI Analysis.
- Panel AI Recommendation.
- Analisis berbasis data penjualan, stok, kas, cabang, promosi, dan utang.
- Rekomendasi yang dapat dikategorikan berdasarkan urgensi.
- Filter periode, cabang, kategori, dan produk.
- Detail insight dan alasan analisis.
- Chatbot AI Assistant yang mampu menjawab pertanyaan natural language seputar performa bisnis secara interaktif, yang jawabannya didasarkan sepenuhnya pada data aktual yang ada (RAG/Data-driven).

### 6.2 Di luar scope untuk fase awal
- Prediksi demand berbasis machine learning yang kompleks.
- Rekomendasi otomatis yang langsung execute tanpa persetujuan manusia.
- Integrasi pada semua modul non-POS.

---

## 7. User Stories
### 7.1 AI Analysis
1. Sebagai SuperAdmin, saya ingin melihat ringkasan performa bisnis harian/mingguan/bulanan agar cepat mengetahui kondisi usaha.
2. Sebagai SuperAdmin, saya ingin sistem memberi peringatan saat ada penurunan penjualan atau kebutuhan stok secara mendadak agar saya dapat mengambil tindakan cepat.
3. Sebagai SuperAdmin, saya ingin melihat produk, kategori, atau cabang yang anomali agar prioritas saya lebih tepat.
4. Sebagai SuperAdmin, saya ingin melihat tren cashflow dan performa cabang agar bisa mengidentifikasi risiko operasional.
5. Sebagai SuperAdmin, saya ingin bisa berinteraksi melalui fitur Chat AI dan menanyakan pertanyaan spesifik (misal: "Cabang mana yang penjualannya paling rendah hari ini?") di mana AI akan membalas secara presisi berdasarkan data riil dari sistem POS.

### 7.2 AI Recommendation
1. Sebagai SuperAdmin, saya ingin menerima rekomendasi prioritas seperti restock, promosi, atau pengurangan stok agar dapat bertindak dengan cepat.
2. Sebagai SuperAdmin, saya ingin melihat alasan dan data pendukung rekomendasi agar saya percaya dengan arahan yang diberikan.
3. Sebagai SuperAdmin, saya ingin rekomendasi diurutkan berdasarkan urgensi agar saya dapat fokus pada masalah paling penting.
4. Sebagai SuperAdmin, saya ingin mengetahui dampak estimasi dari rekomendasi agar keputusan lebih terukur.

---

## 8. Prinsip Fungsional
### 8.1 AI Analysis
AI Analysis bertugas menganalisis data historis dan data saat ini untuk menghasilkan insight seperti:
- tren penjualan per cabang,
- penurunan penjualan dibanding periode sebelumnya,
- produk slow-moving,
- produk fast-moving dengan stok menipis,
- stockout risk,
- branch underperform,
- cashflow negatif atau tidak sehat,
- promosi yang tidak efektif,
- kategori dengan margin turun.

### 8.2 AI Recommendation
AI Recommendation akan menilai insight yang sudah terbentuk dan menghasilkan rekomendasi berupa action items prioritas. Contohnya:
- Restock produk X karena ada peningkatan permintaan dan stok menipis.
- Batasi promosi Y karena tidak menghasilkan ROI yang cukup.
- Transfer kas dari cabang A ke pusat karena surplus kas berlebih.
- Fokus penjualan pada kategori Z karena share penjualan meningkat.
- Evaluasi jam kerja karyawan pada cabang B karena beban operasional tinggi.

### 8.3 Tingkat kepercayaan rekomendasi
Setiap rekomendasi harus memiliki minimal metadata:
- jenis rekomendasi,
- prioritas (high/medium/low),
- confidence score,
- alasan utama,
- scope (seluruh jaringan atau cabang tertentu),
- data yang menjadi dasar.

---

## 9. Functional Requirements
### 9.1 Dashboard SuperAdmin
- Menampilkan ringkasan KPI utama:
  - total penjualan hari ini,
  - total penjualan bulan ini,
  - jumlah transaksi,
  - total keuntungan,
  - stok kritis,
  - cabang yang perlu perhatian,
  - pendapatan per cabang.
- Menyediakan filter:
  - tanggal,
  - cabang,
  - kategori produk,
  - status outlet,
  - periode: harian, mingguan, bulanan.

### 9.2 Panel AI Analysis
- Menampilkan insight utama dalam bentuk card atau list.
- Setiap insight memiliki:
  - judul,
  - deskripsi singkat,
  - level risiko/positif,
  - periode banding,
  - status: normal / warning / critical,
  - tombol detail / lihat data.
- Contoh insight:
  - Penjualan cabang X turun 18% dibanding periode sebelumnya.
  - Stok produk A berisiko habis dalam 3 hari.
  - Margin kategori skincare turun 11% dalam 30 hari terakhir.

### 9.3 Panel AI Recommendation
- Menampilkan daftar rekomendasi prioritas.
- Format minimal:
  - Judul rekomendasi
  - Deskripsi singkat
  - Prioritas
  - Confidence score
  - Alasan data
  - Tindakan yang mungkin diambil
  - Tombol “Lihat detail”
- Contoh:
  - High priority: Restok 25 unit produk B sebelum 3 hari ke depan.
  - Medium priority: Tingkatkan promosi bundle produk A dengan ROAS 1.7x.

### 9.4 Detail insight/rekomendasi
- Halaman atau modal detail yang menampilkan:
  - data base line,
  - komparasi periode,
  - grafik mini atau trend,
  - daftar produk/cabang terkait,
  - batasan analisis,
  - informasi yang digunakan untuk menghasilkan insight.

### 9.5 Akses dan otorisasi
- Hanya SuperAdmin yang dapat melihat AI Analysis dan AI Recommendation di dashboard utama.
- Jika ada peran manager cabang, fitur dapat dibatasi ke scope cabang masing-masing di tahap selanjutnya.

---

## 10. Non-Functional Requirements
### 10.1 Performance
- Dashboard utama harus load dalam <= 3 detik untuk data harian.
- Insight dapat dihitung secara async agar tidak menghambat render dashboard.

### 10.2 Reliability
- Jika AI service gagal, sistem tetap menampilkan data dasar dan status “Insight tidak tersedia” bukan error fatal.
- Sistem harus memiliki fallback analytic rules untuk menghindari dashboard blank.

### 10.3 Scalability
- Fungsi analisis harus dapat memperluas cakupan data dari harian ke mingguan dan bulanan.
- Arsitektur harus memungkinkan penambahan sumber data baru tanpa perlu rewrite total.

### 10.4 Security
- Data analitik tidak boleh menampilkan info sensitif atau data yang tidak dibenarkan untuk role tertentu.
- Semua endpoint AI atau insight harus dijaga dengan auth dan permission.

### 10.5 Maintainability
- Logika analisis harus dipisah dari controller dan view.
- Dapat menggunakan service layer dan repository pattern.

---

## 11. Data yang Digunakan
### 11.1 Data utama
- Sales / transaksi harian
- Item penjualan
- Stok produk per cabang
- Pembelian dan retur
- Pembayaran kas dan cash movement
- Data branch
- Data supplier
- Data promosi / voucher
- Data kategori & product bundle
- Data pelanggan / utang pelanggan

### 11.2 Data yang diperlukan untuk AI
- Trend penjualan per periode
- Trend stock per produk
- Demand rate per produk / kategori
- Average sales per periode
- Margin per produk
- Cash balance per cabang
- ROI promosi
- Aktivitas opname / stock adjustment

### 11.3 Kondisi data
- Data minimal harus tersedia selama 30 hari untuk analisis tren dasar.
- Untuk insight berbasis seasonal, diperlukan data 3-6 bulan agar lebih stabil.

---

## 12. AI Logic yang Disarankan
### 12.1 Analisis rules-based dulu
Untuk fase awal, AI dapat dibangun dengan pendekatan hybrid:
- rules-based analytics untuk insight yang jelas dan konsisten,
- optional LLM / AI helper untuk merangkum insight dan menulis rekomendasi dalam bahasa yang lebih natural.

Contoh rules:
- Jika stok produk < safety stock dan demand 7 hari > average sales 7 hari, maka alert stockout risk.
- Jika penjualan cabang turun > 15% dibanding periode sebelumya dan margin turun > 5%, maka alert underperform.
- Jika promo tertentu menghasilkan ROI < 1.0, maka rekomendasi evaluasi promo.

### 12.2 Struktur keluaran
Setiap insight dan rekomendasi harus memiliki format yang konsisten:
- type
- title
- summary
- severity
- score
- affected_entities
- period
- evidence
- action

---

## 13. UX / UI Requirements
### 13.1 Layout dashboard
Dashboard SuperAdmin harus menampilkan:
1. KPI ringkasan
2. AI Analysis panel
3. AI Recommendation panel
4. Trend chart/analytical chart
5. Tabel cabang / produk prioritas

### 13.2 Design principle
- Tampilan harus fokus pada insight, bukan data mentah.
- Prioritaskan informasi “apa yang perlu ditangani” di bagian paling atas.
- Gunakan warna yang konsisten:
  - red = critical / risk
  - yellow = warning
  - green = positive opportunity
  - blue = neutral / informational

### 13.3 Interaksi
- User dapat klik insight untuk melihat detail.
- User dapat filter periode dan cabang.
- User dapat klik rekomendasi untuk melihat justification dan impact estimate.
- User dapat menandai rekomendasi sebagai “ditinjau” / “dijalankan” di versi lanjutan.

---

## 14. Acceptance Criteria
### 14.1 AI Analysis
- [ ] SuperAdmin dapat melihat sekurang-kurangnya 3 insight utama di dashboard.
- [ ] Setiap insight memiliki status dan formula dasar analisis.
- [ ] Sistem mampu menampilkan insight berdasarkan filter periode dan cabang.
- [ ] Jika data tidak mencukupi, sistem menampilkan status “insight unavailable” dan bukan error.

### 14.2 AI Recommendation
- [ ] SuperAdmin dapat melihat daftar rekomendasi dengan prioritas.
- [ ] Setiap rekomendasi memiliki alasan data dan tingkat keyakinan.
- [ ] Rekomendasi dapat diurutkan berdasarkan urgensi.
- [ ] User dapat melihat detail rekomendasi sebelum mengambil keputusan.

### 14.3 UX
- [ ] Desain dashboard mudah dipahami dalam waktu < 30 detik.
- [ ] Informasi paling penting ada di area paling atas.
- [ ] Terdapat kontras visual yang jelas untuk masalah kritis.

---

## 15. Analisis Risiko dan Asumsi
### 15.1 Risiko
- Data tidak konsisten antar cabang.
- Stok / sales data tidak mencukupi untuk analisis robust.
- AI rekomendasi terlalu spesifik atau tidak sesuai logika bisnis.
- User merasa rekomendasi terlalu “otomatis” dan tidak terkontrol.

### 15.2 Mitigasi
- Validasi input data sebelum proses analisis.
- Gunakan rules-based intelligence dulu sebelum memakai LLM.
- Selalu tampilkan alasan logis di setiap rekomendasi.
- Pastikan keputusan tetap di tangan manusia.

### 15.3 Asumsi
- Data transaksi dan stok sudah tersedia di database aplikasi.
- Terdapat minimal 1 bulan data untuk insight dasar.
- SuperAdmin memiliki akses ke semua cabang.
- Fitur ini akan dikembangkan secara bertahap dari MVP ke enhancement.

---

## 16. MVP Scope
### Fase 1: MVP
- Dashboard SuperAdmin dengan KPI summary
- AI Analysis panel dengan 5 insight utama
- AI Recommendation panel dengan 3-5 rekomendasi prioritas
- Filter cabang dan tanggal
- Detail modal untuk insight dan rekomendasi
- Rules-based analytics

### Fase 2: Enhancement
- AI summary dalam bahasa natural
- Prediksi kebutuhan stok dan tren penjualan
- Integrasi dengan notifikasi / alert
- Track status rekomendasi yang sudah ditindaklanjuti

### Fase 3: Advanced
- Multi-branch prediction
- Budget and cashflow optimization recommendation
- More advanced forecasting models
- Personalized insight per role

---

## 17. Struktur Implementasi yang Disarankan
Untuk memudahkan pengembangan, sistem sebaiknya dibangun dengan pendekatan berikut:
- Controller: menangani request dashboard dan hasil filter
- Service: mengelola logic insight dan rekomendasi
- Analyzer / Predictor classes: memisahkan jenis insight (stok, penjualan, cashflow, branch)
- Recommendation engine: menghasilkan dan mengurutkan rekomendasi
- View / Blade: menampilkan data dalam dashboard
- API / job queue: untuk proses analisis async bila data besar

Contoh struktur:
- App\Services\Ai\DashboardInsightService
- App\Services\Ai\RecommendationService
- App\Services\Ai\Insight\StockRiskAnalyzer
- App\Services\Ai\Insight\SalesTrendAnalyzer
- App\Services\Ai\Insight\BranchHealthAnalyzer
- App\Services\Ai\Recommendation\RestockRecommendation

---

## 18. Contoh Insight dan Rekomendasi
### 18.1 Insight contoh
- Stok produk X akan habis dalam 3 hari berdasarkan rata-rata penjualan 7 hari terakhir.
- Cabang Bandung mengalami penurunan penjualan 18% dibanding bulan lalu.
- Margin kategori snack turun karena biaya pembelian naik 9%.
- Kas cabang Jogja melebihi batas aman dan berpotensi kurang efektif untuk operasional.

### 18.2 Rekomendasi contoh
- Restock produk X untuk cabang Bandung sebelum akhir pekan.
- Evaluasi promo bundling kategori snack karena penjualan tidak naik sesuai target.
- Pindahkan sebagian dana dari cabang Jogja ke pusat untuk menjaga kesehatan cashflow.
- Fokuskan promosi pada produk Y yang memiliki conversion tinggi dan stock cukup.

---

## 19. Keputusan Produk
### 19.1 Prinsip utama
1. AI tidak boleh menggantikan kontrol manusia.
2. Keputusan harus selalu transparan dan dijelaskan dengan data.
3. Rekomendasi harus fokus pada tindakan yang bisa diambil oleh SuperAdmin.
4. Dashboard harus dirancang sebagai operational command center, bukan sekadar dashboard read-only.

### 19.2 Kriteria keberhasilan
- SuperAdmin mampu menemukan masalah utama dalam < 3 menit.
- Insight terjadi berdasarkan data yang valid.
- Rekomendasi dapat dipahami, dipercaya, dan dijalankan.
- Penggunaan dashboard meningkatkan kecepatan pengambilan keputusan.

---

## 20. Ringkasan PRD
Fitur AI Analysis dan AI Recommendation pada tampilan SuperAdmin akan menjadi layer insight strategis di atas data POS yang sudah ada. Fokus implementasi awal adalah pada dashboard yang memberi analisis ringkas, trigger risk, dan rekomendasi prioritas yang actionable. Dengan pendekatan rules-based plus AI summary, pengembangan dapat dimulai cepat namun tetap skalabel untuk pengembangan selanjutnya.

Tujuan jangka panjang adalah menciptakan dashboard SuperAdmin yang bekerja sebagaimana “business intelligence layer” untuk seluruh operasional jaringan.

---

## 21. Catatan Implementasi yang Disarankan
- Mulai dari MVP dengan 3 jenis insight utama: sales, stock, cashflow.
- Tambahkan branch health dan promotion performance di iterasi berikutnya.
- Prioritaskan visual yang jelas dan rekomendasi yang dapat ditindaklanjuti.
- Simpan data insight di tabel terpisah agar bisa dijadikan audit trail dan histori analisis.

## 22. Rekomendasi Produk yang Disarankan Ditambahkan ke PRD
Berikut rekomendasi yang sebaiknya ditambahkan agar PRD lebih lengkap dan siap dipakai sebagai produk serta acuan pengembangan AI:

### 22.1 Governance AI dan kontrol manusia
- AI bersifat advisory, bukan autonomous decision maker.
- Tindakan kritikal seperti pembelian besar, pengurangan stok massal, atau perubahan cashflow harus tetap menunggu konfirmasi manusia.
- Setiap keputusan AI harus bisa dilacak dan dijelaskan.

### 22.2 Explainability dan transparency
- Tiap insight harus menampilkan alasan utama dan data pendukung.
- Tambahkan metadata seperti:
  - basis data yang digunakan,
  - periode pembanding,
  - confidence score,
  - rule atau model yang dipakai,
  - apakah insight bersifat trend, anomaly, atau opportunity.

### 22.3 Data quality dan freshness
- Dashboard AI harus hanya menampilkan data yang terbaru dan valid.
- Setiap analisis harus memperhitungkan missing data, data duplikat, dan gap data.
- Jika data tidak mencukupi, sistem harus menampilkan status “insufficient data” atau fallback rule-based.

### 22.4 Role-based access dan scope
- SuperAdmin: akses ke seluruh jaringan.
- Supervisor: akses ke cabang / area tertentu.
- Kasir: tidak melihat insight strategis kecuali ringkasan operasional yang relevan.
- Akses harus dibuat berdasarkan role dan scope organisasi.

### 22.5 Alerting dan notification
- AI dapat menghasilkan alert real-time untuk:
  - stok kritis,
  - penjualan turun tajam,
  - margin turun,
  - kas tidak sehat,
  - cabang underperform.
- Alert dapat dikirim ke dashboard, email, atau notifikasi internal.

### 22.6 Audit trail dan history
- Setiap insight dan rekomendasi harus disimpan dengan timestamp.
- Data history dibutuhkan untuk menilai apakah rekomendasi benar-benar digunakan.
- Simpan status review seperti:
  - viewed,
  - accepted,
  - rejected,
  - action taken.

### 22.7 Feedback loop
- Tambahkan fitur feedback pengguna pada rekomendasi:
  - useful / not useful,
  - action taken / not taken,
  - reason for rejection.
- Feedback ini dapat digunakan untuk tuning rules atau model AI di masa depan.

### 22.8 KPI keberhasilan AI
- Metrik keberhasilan yang harus diukur:
  - waktu pengambilan keputusan turun,
  - persentase rekomendasi yang ditindaklanjuti,
  - penurunan stok out,
  - peningkatan penjualan pada produk yang di-recommend,
  - efisiensi promosi,
  - tingkat akurasi rekomendasi.

### 22.9 Fallback mechanism
- Ketika AI gagal atau tidak tersedia, sistem tetap harus menampilkan:
  - dashboard dasar,
  - insight rules-based,
  - pemberitahuan bahwa AI sedang memproses ulang.

### 22.10 Timeline implementasi yang disarankan
- Fase 1: rules-based analytics + recommendation + dashboard UI
- Fase 2: bahasa natural AI summary + alert + audit trail
- Fase 3: predictive insight + forecasting + adaptive recommendation

---

## 23. Daftar Pekerjaan Selanjutnya
1. Validasi data yang dibutuhkan dari database yang ada.
2. Definisikan struktur response insight dan recommendation.
3. Design database / table untuk hasil AI insight history.
4. Tambahkan schema audit trail dan feedback recommendation.
5. Buat endpoint dashboard insight untuk SuperAdmin.
6. Implementasikan frontend card panel dan detail modal.
7. Uji dengan data real dari POS.
8. Iterasi berdasarkan feedback user.
9. Tambahkan alerting dan notifikasi prioritas.
10. Ukur KPI efektivitas AI setelah rollout MVP.
