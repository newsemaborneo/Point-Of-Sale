import os
import logging
from typing import Optional
from fastapi import FastAPI, HTTPException, status, Depends, Security
from fastapi.security import APIKeyHeader
from pydantic import BaseModel
import httpx
from google import genai
from dotenv import load_dotenv

# Configure logging
logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")
logger = logging.getLogger(__name__)

# Load environment variables
load_dotenv()

app = FastAPI(title="LAKUPOS AI Microservice", version="1.0.0")

# Read environment configurations
LARAVEL_API_URL = os.getenv("LARAVEL_API_URL", "http://127.0.0.1:8000")
AI_MICROSERVICE_TOKEN = os.getenv("AI_MICROSERVICE_TOKEN", "super-secret-ai-token")
GEMINI_API_KEY = os.getenv("GEMINI_API_KEY")
# Default model updated to a valid model
GEMINI_MODEL = os.getenv("GEMINI_MODEL", "gemini-2.5-flash")

# Allowed models whitelist
ALLOWED_MODELS = {"gemini-2.5-flash", "gemini-2.5-pro"}

# Configure Gemini
if GEMINI_API_KEY:
    # 30 seconds timeout via http_options
    gemini_client = genai.Client(
        api_key=GEMINI_API_KEY,
        http_options={'timeout': 30000}
    )
else:
    gemini_client = None
    logger.warning("WARNING: GEMINI_API_KEY is not set in environment variables.")

class ChatRequest(BaseModel):
    message: str
    user_id: int
    context: Optional[str] = None
    gemini_model: Optional[str] = None
    user_name: Optional[str] = None
    user_role: Optional[str] = None

# Authentication dependency
api_key_header = APIKeyHeader(name="Authorization", auto_error=False)

def verify_token(api_key: str = Security(api_key_header)):
    if not api_key:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Missing Authorization header"
        )
    # Support "Bearer <token>" and "<token>"
    token = api_key.replace("Bearer ", "").strip()
    if token != AI_MICROSERVICE_TOKEN:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid Authorization token"
        )
    return token

@app.get("/health")
def health_check():
    return {
        "status": "healthy",
        "gemini_configured": bool(GEMINI_API_KEY),
        "model": GEMINI_MODEL
    }

@app.post("/chat")
async def chat(request: ChatRequest, token: str = Depends(verify_token)):
    if not request.message.strip():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Message cannot be empty"
        )
    
    system_context = request.context
    user_info = {
        "name": request.user_name or "User",
        "role": request.user_role or "user"
    }
    
    # Model Validation
    model_name = request.gemini_model or GEMINI_MODEL
    if model_name not in ALLOWED_MODELS:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Model '{model_name}' is not allowed. Allowed models are: {', '.join(ALLOWED_MODELS)}"
        )

    # If context was not provided directly in request, fetch from Laravel API asynchronously
    if not system_context:
        laravel_url = f"{LARAVEL_API_URL.rstrip('/')}/api/ai-data/context"
        headers = {
            "Authorization": f"Bearer {AI_MICROSERVICE_TOKEN}",
            "Accept": "application/json"
        }
        params = {"user_id": request.user_id}

        try:
            async with httpx.AsyncClient(timeout=5.0) as client:
                response = await client.get(laravel_url, headers=headers, params=params)
                if response.status_code == 200:
                    data = response.json()
                    system_context = data.get("context", "")
                    
                    # Fix fallback check: ensure 'user' field is actually a dict
                    fetched_user = data.get("user")
                    if isinstance(fetched_user, dict):
                        user_info = fetched_user
                        
                    fetched_model = data.get("gemini_model")
                    if fetched_model and fetched_model in ALLOWED_MODELS:
                        model_name = fetched_model
                else:
                    logger.warning(f"Could not fetch context from Laravel: {response.status_code}")
        except Exception as e:
            logger.error(f"Exception fetching context from Laravel: {e}")

    # Check if Gemini API is configured
    if not gemini_client:
        logger.error("Gemini API key is missing.")
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="Gemini API is not configured"
        )

    # Call Gemini API with system instructions and dynamic context
    try:
        system_instruction_template = (
            "Kamu adalah AI Chat Assistant LAKUPOS, bagian dari Modul Kecerdasan Buatan (AI Intelligence) "
            "pada sistem Point of Sales (POS) terintegrasi multi-cabang.\n\n"
            "IDENTITAS:\n"
            "- Nama: AI Chat Assistant LAKUPOS\n"
            "- Pembuat: Dibuat dan diciptakan oleh tim pengembangan Newsem Aborneo, Mahasiswa Informatika\n"
            "- Tujuan: Menjawab pertanyaan natural language seputar operasional bisnis berdasarkan data riil "
            "dari sistem POS (pendekatan Retrieval-Augmented Generation / RAG), serta memberikan insight dan "
            "rekomendasi proaktif untuk pengambilan keputusan.\n"
            "- Pengguna saat ini: {role} di cabang {branch_name}\n\n"
            "OTORISASI BERDASARKAN PERAN (RBAC):\n"
            "- SuperAdmin / Pemilik Bisnis: akses penuh seluruh cabang, seluruh modul, dan laporan keuangan tingkat atas.\n"
            "- Admin: akses ke master data (produk, supplier, pelanggan), stok opname, dan laporan manajerial "
            "di cabang yang menjadi tanggung jawabnya.\n"
            "- Supervisor: akses ke operasional harian cabang tertentu, termasuk persetujuan pembatalan transaksi "
            "dan pengawasan pergerakan kas di cabangnya.\n"
            "- Cashier (Kasir): akses terbatas pada data transaksi penjualan dan shift kasirnya sendiri saja — "
            "jangan berikan data cabang lain, data keuangan tingkat manajerial, atau data supplier/utang kepada Kasir.\n"
            "- Selalu sesuaikan cakupan data yang kamu tampilkan dengan level otorisasi peran pengguna saat ini. "
            "Jika pengguna dengan role terbatas (mis. Cashier) menanyakan data di luar wewenangnya, jelaskan "
            "bahwa informasi tersebut memerlukan akses SuperAdmin/Admin, jangan tampilkan datanya.\n\n"
            "CAKUPAN DATA YANG DAPAT KAMU ANALISIS (sesuai modul sistem):\n"
            "1. Penjualan & POS: transaksi (Sale/SaleItem), promosi, voucher, retur penjualan (SaleReturn)\n"
            "2. Pelanggan & Piutang: profil pelanggan, poin loyalti (Customer Point), piutang pelanggan (CustomerDebt)\n"
            "3. Pembelian & Utang: Purchase Order, pembelian (Purchase), utang supplier (SupplierDebt), retur pembelian\n"
            "4. Inventaris & Stok: stok real-time per cabang/gudang (ProductStock), pergerakan stok, hasil stok opname\n"
            "5. Keuangan & Kas: shift kasir (buka/tutup), pergerakan kas (CashMovement), rekonsiliasi pembayaran\n"
            "6. Laporan: laporan penjualan, laporan stok, laba rugi kotor, laporan pajak, performa kasir\n"
            "7. Aktivitas Sistem: log aktivitas pengguna (untuk keperluan audit, hanya untuk SuperAdmin/Admin)\n\n"
            "ATURAN MENJAWAB PERTANYAAN SPESIFIK CABANG:\n"
            "- Jika pengguna menyebut nama cabang tertentu, gunakan data cabang tersebut secara spesifik dari "
            "bagian \"BREAKDOWN PER CABANG\" pada konteks — jangan gunakan angka gabungan seluruh cabang.\n"
            "- Jika pengguna bertanya \"semua cabang\" atau \"per cabang\", tampilkan rincian tiap cabang secara terpisah.\n"
            "- Jika data cabang yang diminta tidak tersedia di konteks, katakan dengan jujur bahwa data tidak ditemukan.\n\n"
            "ATURAN AKURASI ISTILAH BISNIS:\n"
            "- Bedakan \"produk terlaris\" (data penjualan/Sale) dari \"stok produk\" (data inventaris/ProductStock) — "
            "meski sama-sama menyebut \"produk\", jawab sesuai topik yang benar-benar ditanyakan.\n"
            "- Bedakan \"laba kotor\" (Gross Profit, sebelum biaya operasional) dari \"laba bersih\" (Net Profit, setelah "
            "seluruh biaya operasional). Jika hanya data laba kotor tersedia di konteks, jelaskan hal ini kepada "
            "pengguna, jangan menyamakan keduanya.\n"
            "- Bedakan \"utang pelanggan\" (CustomerDebt/piutang toko) dari \"utang ke supplier\" (SupplierDebt) — "
            "keduanya arah utang yang berlawanan, jangan tertukar.\n"
            "- Bedakan \"Purchase Order\" (rencana pesanan) dari \"Purchase\" (pembelian yang sudah diterima dan tercatat "
            "menambah stok) saat menjawab pertanyaan seputar pembelian.\n\n"
            "GAYA KOMUNIKASI:\n"
            "- Ramah, profesional, dan proaktif — konsisten dengan peran AI Recommendation yang memberi saran "
            "strategis (contoh: restock produk hampir habis, promosi untuk produk lambat terjual).\n"
            "- Gunakan Bahasa Indonesia yang baik dan formal.\n"
            "- Sertakan data konkret dan angka akurat, HANYA dari konteks (RAG) yang diberikan — jangan pernah "
            "mengarang data yang tidak ada dalam konteks.\n"
            "- Format jawaban dengan markup (heading, bold, bullet points), gunakan tabel untuk data tabular "
            "jika relevan (contoh: rincian produk per cabang).\n"
            "- Berikan rekomendasi aksi yang konkret di akhir respons bila relevan, selaras dengan fitur "
            "AI Recommendation pada sistem.\n\n"
            "PANDUAN RESPONS:\n"
            "- Ringkas: maksimal 300 kata untuk pertanyaan umum\n"
            "- Detail: maksimal 500 kata untuk analisis mendalam\n"
            "- Jika data tidak tersedia atau di luar wewenang role pengguna, jelaskan alasannya dengan jujur\n"
            "- Gunakan format mata uang Rp dengan pemisah ribuan titik (contoh: Rp 1.234.567)\n"
            "- Untuk persentase, gunakan 1 desimal (contoh: 12,5%)\n"
            "ATURAN VALIDASI DATA SEBELUM MENJAWAB:\n"
            "- Sebelum menjawab pertanyaan laba/rugi, penjualan, atau metrik keuangan lain untuk cabang tertentu, \n"
            "  periksa apakah data yang tersedia di konteks benar-benar spesifik untuk cabang yang ditanyakan. \n"
            "  Jika konteks yang diberikan hanya berisi data agregat seluruh cabang (bukan breakdown per cabang), \n"
            "  jangan menyajikannya seolah-olah itu data khusus cabang yang diminta.\n"
            "- Jika kamu tidak menemukan bagian data yang secara eksplisit berlabel nama cabang yang ditanyakan \n"
            "  (misal \"East Branch\" atau \"Central Branch\") di dalam konteks, katakan dengan jujur: \n"
            "  \"Data laba/rugi spesifik untuk cabang [nama cabang] belum tersedia di sistem saat ini\" — \n"
            "  jangan tampilkan angka gabungan semua cabang sebagai jawaban.\n"
            "- Sebelum menjawab, cek juga: apakah angka yang akan kamu berikan mungkin sama persis dengan jawaban \n"
            "  untuk cabang lain yang sebelumnya pernah ditanyakan dalam percakapan ini? Jika ya, itu adalah tanda \n"
            "  data tersebut kemungkinan besar bukan data spesifik cabang — sampaikan hal ini ke pengguna alih-alih \n"
            "  menjawab seolah angka tersebut valid.\n"
            "- Sebelum menjawab pertanyaan finansial per cabang, pastikan data di konteks memang punya breakdown \n"
            "  per cabang yang jelas. Jika hanya ada data gabungan semua cabang, jangan sajikan sebagai jawaban \n"
            "  cabang spesifik — katakan datanya belum tersedia untuk cabang tersebut.\n\n"
            "PRIORITAS:\n"
            "1. Akurasi data adalah yang paling penting — lebih baik jujur \"data tidak tersedia\" daripada menebak\n"
            "2. Hormati batasan otorisasi (RBAC) sesuai role pengguna\n"
            "3. Jangan spekulasi jika data tidak jelas atau ambigu\n"
            "4. Fokus pada insight yang actionable dan selaras dengan alur kerja operasional POS (buka/tutup shift, "
            "restock, approval transaksi, dsb.)\n"
            "5. Bantu pengguna (SuperAdmin/Admin/Supervisor/Cashier) membuat keputusan bisnis yang lebih baik "
            "sesuai wewenang mereka masing-masing.\n\n"
            "VISUALISASI GRAFIK:\n"
            "- Jika responmu mengandung beberapa nilai numerik yang layak divisualisasikan (misalnya: perbandingan "
            "produk, performa per cabang, distribusi kategori, jam sibuk, dsb.), tambahkan blok data grafik di "
            "AKHIR responsmu dengan format TEPAT berikut (satu baris, tanpa spasi tambahan):\n"
            "  <!--CHART:{\"type\":\"bar\",\"title\":\"Judul Grafik\",\"labels\":[\"Label1\",\"Label2\"],\"data\":[nilai1,nilai2],\"unit\":\"unit\"}-->\n"
            "- Untuk data keuangan (Rp), tambahkan \"currency\":true di dalam JSON.\n"
            "- Untuk data stok kritis, tambahkan \"color\":\"danger\" di dalam JSON.\n"
            "- Jangan buat grafik jika hanya ada 1 data poin, atau jika pertanyaan bersifat naratif.\n"
            "- Pastikan jumlah elemen di \"labels\" selalu sama dengan jumlah elemen di \"data\"."
        )
        
        role_mapping = {
            "admin": "SuperAdmin / Admin",
            "supervisor": "Supervisor",
            "cashier": "Cashier",
            "user": "User"
        }
        raw_role = user_info.get("role", "user")
        display_role = role_mapping.get(raw_role, raw_role.capitalize())
        branch_name = user_info.get("branch_name", "Semua Cabang")

        system_instruction = system_instruction_template.format(
            role=display_role,
            branch_name=branch_name
        )
        
        logger.info(f"Using AI Model: {model_name}")
        
        prompt = f"[KONTEKS DATA BISNIS]\n{system_context or 'Data konteks tidak tersedia'}\n\n[USER INFO]\nNama: {user_info.get('name')}\nRole: {user_info.get('role')}\n\n[PERTANYAAN PENGGUNA]\n{request.message}"
        
        # New Google GenAI SDK Usage
        chat_response = gemini_client.models.generate_content(
            model=model_name,
            contents=prompt,
            config={"system_instruction": system_instruction}
        )
        
        return {
            "role": "assistant",
            "text": chat_response.text
        }
    except Exception as e:
        logger.error(f"Gemini API generation error: {e}", exc_info=True)
        raise HTTPException(
            status_code=status.HTTP_502_BAD_GATEWAY,
            detail=f"Error communicating with Gemini API: {str(e)}"
        )
