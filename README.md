# POS System — AI Integrated

Sistem **Point of Sale (POS)** berbasis Laravel yang dilengkapi dengan fitur manajemen penjualan, inventory, pelanggan, supplier, kas, laporan, serta **AI Assistant berbasis Gemini**.

Project ini dirancang untuk membantu operasional toko sekaligus memberikan analisis bisnis menggunakan Artificial Intelligence.

---

## 🚀 Fitur Utama

### 📦 Point of Sale

* Transaksi penjualan
* Keranjang belanja
* Barcode
* Cetak struk
* Riwayat transaksi
* Retur penjualan
* Manajemen kasir

### 📊 Inventory Management

* Manajemen produk
* Stok produk
* Stock movement
* Stock adjustment
* Peringatan stok minimum
* Purchase / pembelian
* Supplier
* Transfer/pergerakan stok

### 👥 Customer Management

* Data pelanggan
* Member type
* Customer debt / piutang
* Riwayat transaksi pelanggan

### 💰 Cash Management

* Cash register
* Cash movement
* Pemasukan
* Pengeluaran
* Saldo kas

### 📈 Dashboard & Report

* Dashboard penjualan
* Laporan transaksi
* Laporan stok
* Laporan pembelian
* Laporan pelanggan
* Analisis performa bisnis

### 🤖 AI Assistant

Project menyediakan AI Assistant menggunakan **Google Gemini API**.

AI dapat digunakan untuk:

* Menjawab pertanyaan mengenai bisnis
* Menganalisis data penjualan
* Memberikan insight produk
* Membantu analisis stok
* Memberikan rekomendasi bisnis
* Menjawab pertanyaan berdasarkan data POS
* Menyimpan percakapan AI

Arsitektur AI:

```text
User
 │
 ▼
Laravel Application
 │
 ▼
AiController
 │
 ▼
AI Service
 │
 ▼
AI Microservice
 │
 ▼
Google Gemini API
 │
 ▼
AI Response
 │
 ▼
Laravel
 │
 ▼
User
```

---

# 🛠️ Tech Stack

## Backend

* PHP
* Laravel
* MySQL
* Laravel Eloquent ORM
* Laravel Queue
* Laravel Jobs

## Frontend

* Blade
* Bootstrap / CSS
* JavaScript

## AI

* Google Gemini API
* Python
* AI Microservice

## Development Tools

* Git
* GitHub
* Composer
* NPM

---

# 📋 Requirements

Pastikan sudah menginstall:

* PHP 8.2+
* Composer
* Node.js
* NPM
* MySQL
* Python 3.10+
* Git

Cek versi:

```bash
php -v
composer -V
node -v
npm -v
python --version
git --version
```

---

# 📥 Installation

Clone repository:

```bash
git clone <URL_REPOSITORY>
```

Masuk ke directory:

```bash
cd POS
```

Install dependency Laravel:

```bash
composer install
```

Install dependency frontend:

```bash
npm install
```

---

# ⚙️ Environment Configuration

Copy file `.env.example` menjadi `.env`.

Windows:

```bash
copy .env.example .env
```

Linux/macOS:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

---

# 🗄️ Database Configuration

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan konfigurasi dengan database lokal.

Kemudian jalankan migration:

```bash
php artisan migrate
```

Jika project menggunakan seeder:

```bash
php artisan db:seed
```

Atau:

```bash
php artisan migrate --seed
```

---

# 🤖 Gemini API Configuration

Project menggunakan Google Gemini sebagai Large Language Model.

Buat API Key melalui:

**Google AI Studio**

https://aistudio.google.com/app/apikey

Kemudian masukkan API key ke `.env`:

```env
GEMINI_API_KEY=your_api_key_here
```

> ⚠️ Jangan pernah memasukkan API key asli ke GitHub.

Gunakan:

```env
GEMINI_API_KEY=
```

di `.env.example`.

---

# 🧠 AI Microservice

Project memiliki folder:

```text
ai_microservice/
```

Microservice digunakan sebagai service terpisah untuk menangani proses AI.

Struktur sederhananya:

```text
ai_microservice/
├── app/
├── requirements.txt
└── ...
```

Masuk ke directory:

```bash
cd ai_microservice
```

Buat virtual environment:

```bash
python -m venv venv
```

Aktifkan virtual environment pada Windows:

```bash
venv\Scripts\activate
```

Install dependency:

```bash
pip install -r requirements.txt
```

Jalankan microservice sesuai entry point yang tersedia pada project.

Contoh jika menggunakan FastAPI:

```bash
uvicorn main:app --reload
```

---

# ▶️ Menjalankan Laravel

Jalankan server Laravel:

```bash
php artisan serve
```

Aplikasi dapat diakses melalui:

```text
http://127.0.0.1:8000
```

Untuk development frontend:

```bash
npm run dev
```

---

# 🔄 Menjalankan Queue

Jika project menggunakan Laravel Queue:

```bash
php artisan queue:work
```

Queue digunakan untuk menjalankan proses background seperti:

* AI processing
* laporan
* notification
* proses berat lainnya

---

# 📁 Struktur Project

```text
POS/
│
├── app/
│   ├── Exports/
│   ├── Helpers/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   │
│   ├── Jobs/
│   ├── Models/
│   └── Services/
│
├── ai_microservice/
│
├── config/
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── docs/
│   ├── PRD_FULL_POS_SYSTEM.md
│   ├── PRD_SUPERADMIN_AI_ANALYSIS_RECOMMENDATION.md
│   └── AI_AGENT_IMPLEMENTATION_RECOMMENDATION.md
│
├── resources/
│   └── views/
│
├── routes/
│
├── tests/
│
├── .env.example
├── composer.json
└── package.json
```

---

# 🧪 Testing

Menjalankan seluruh test:

```bash
php artisan test
```

Menjalankan test tertentu:

```bash
php artisan test tests/Feature/AiControllerTest.php
```

Unit test:

```bash
php artisan test tests/Unit/
```

---

# 🔐 Security

Jangan commit file `.env`.

Pastikan `.gitignore` memiliki:

```gitignore
.env
.env.*
!.env.example

/vendor/
/node_modules/

/storage/*.key
/storage/logs/
/storage/framework/cache/
/storage/framework/sessions/
/storage/framework/views/
```

API key Gemini harus disimpan menggunakan environment variable:

```env
GEMINI_API_KEY=your_api_key
```

Jangan menulis API key langsung di source code.

---

# 🌿 Git Workflow

Untuk membuat perubahan:

```bash
git checkout -b feature/nama-fitur
```

Setelah selesai:

```bash
git add .
```

Commit:

```bash
git commit -m "feat: add new feature"
```

Push:

```bash
git push origin feature/nama-fitur
```

Kemudian buat **Pull Request** untuk dilakukan code review sebelum masuk ke branch production/main.

Contoh:

```text
feature
   │
   ▼
Pull Request
   │
   ▼
Code Review
   │
   ▼
develop
   │
   ▼
Testing
   │
   ▼
main
```

---

# 📚 Documentation

Dokumentasi project tersedia di:

```text
docs/
```

Dokumen utama:

* `PRD_FULL_POS_SYSTEM.md`
* `PRD_SUPERADMIN_AI_ANALYSIS_RECOMMENDATION.md`
* `AI_AGENT_IMPLEMENTATION_RECOMMENDATION.md`

---

# 🗺️ Future Development

Beberapa pengembangan yang dapat dilakukan:

* [ ] AI Business Assistant
* [ ] AI Sales Prediction
* [ ] AI Stock Prediction
* [ ] Product Recommendation
* [ ] Automatic Business Insight
* [ ] RAG untuk data bisnis
* [ ] Function Calling / AI Tools
* [ ] Multi-branch management
* [ ] Advanced analytics
* [ ] Dashboard AI untuk SuperAdmin
* [ ] Automated reporting
* [ ] Notification berbasis AI

---

# 👨‍💻 Development

Project ini dikembangkan sebagai sistem POS modern yang menggabungkan **operational management + business intelligence + artificial intelligence**.

Konsep utama:

```text
POS
 │
 ├── Sales
 ├── Inventory
 ├── Customer
 ├── Supplier
 ├── Cash
 ├── Reports
 │
 └── AI
      ├── Business Analysis
      ├── Recommendation
      ├── Insight
      └── Assistant
```

---

# 📄 License

Project ini dibuat untuk kebutuhan pengembangan dan pembelajaran.

Tambahkan informasi lisensi sesuai kebutuhan project sebelum digunakan untuk production.
