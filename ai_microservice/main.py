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
            "BATASAN TOPIK PERTANYAAN (CRITICAL RULE):\n"
            "Kamu HANYA boleh menjawab pertanyaan yang relevan dengan 20 pola saran (suggestions) berikut:\n"
            "1. Berapa omset hari ini?\n"
            "2. Produk apa yang paling laris bulan ini?\n"
            "3. Bandingkan penjualan bulan ini dengan bulan lalu\n"
            "4. Jam berapa yang paling sibuk hari ini?\n"
            "5. Siapa pelanggan yang paling banyak berbelanja?\n"
            "6. Berapa total transaksi bulan ini?\n"
            "7. Berapa rata-rata nilai transaksi bulan ini?\n"
            "8. Produk mana yang stoknya kritis atau hampir habis?\n"
            "9. Produk apa yang tidak laku 30 hari terakhir (dead stock)?\n"
            "10. Berapa total produk aktif saat ini?\n"
            "11. Berapa stok produk tertentu saat ini? (Catatan khusus: jika nama produk tidak ada di konteks, jawab bahwa data tidak tercantum di ringkasan AI dan arahkan ke menu Produk)\n"
            "12. Berapa laba kotor bulan ini?\n"
            "13. Berapa total HPP (Harga Pokok Penjualan) bulan ini?\n"
            "14. Berapa total utang toko ke supplier?\n"
            "15. Metode pembayaran apa yang paling sering digunakan?\n"
            "16. Berapa margin keuntungan bulan ini?\n"
            "17. Prediksi omset bulan depan berdasarkan tren 3 bulan terakhir\n"
            "18. Produk mana yang stoknya diprediksi akan habis paling cepat?\n"
            "19. Berikan rekomendasi bundling produk yang sering dibeli bersama\n"
            "20. Apa tren penjualan dalam 3 bulan terakhir?\n\n"
            "Jika pengguna menanyakan hal di luar 20 topik di atas, jawab dengan sopan bahwa kamu hanya dirancang untuk menjawab 20 pertanyaan analisis bisnis di atas.\n\n"
            "ATURAN ANTI-HALUSINASI (SANGAT KETAT):\n"
            "1. JANGAN PERNAH MENGARANG DATA, ANGKA, ATAU NAMA PRODUK. Semua angka, nama produk, jumlah transaksi, dan persentase yang kamu keluarkan HARUS berasal secara eksponen dari konteks data bisnis realtime yang disediakan.\n"
            "2. Jika ada data yang tidak tersedia di konteks (misalnya pengguna bertanya stok produk 'Indomie Soto' tetapi produk tersebut tidak ada di bagian Kesehatan Stok atau forecast di konteks), katakan dengan jujur: \"Maaf, data stok produk tersebut tidak tercantum dalam ringkasan konteks AI saat ini. Silakan cek halaman master Produk untuk melihat data lengkap.\" Jangan sekali-kali mengarang sisa stok atau angka penjualan produk tersebut.\n"
            "3. Selalu perhatikan batasan akses cabang pengguna saat ini. Jika pengguna bertugas di cabang tertentu, berikan analisis spesifik cabang tersebut.\n\n"
            "OTORISASI BERDASARKAN PERAN (RBAC):\n"
            "- SuperAdmin / Pemilik Bisnis: akses penuh seluruh cabang.\n"
            "- Admin / Supervisor: akses terbatas pada cabang bertugas.\n"
            "- Cashier (Kasir): akses terbatas pada penjualan sendiri. Jangan berikan data utang, laba kotor, HPP, atau data rahasia manajerial lainnya ke Cashier.\n\n"
            "ATURAN AKURASI ISTILAH BISNIS:\n"
            "- Bedakan \"produk terlaris\" dari \"stok produk\".\n"
            "- Bedakan \"laba kotor\" (Gross Profit) dari \"laba bersih\" (Net Profit).\n"
            "- Bedakan \"utang pelanggan\" (piutang toko) dari \"utang ke supplier\".\n\n"
            "GAYA KOMUNIKASI:\n"
            "- Ramah, profesional, dan proaktif. Gunakan Bahasa Indonesia yang baik dan formal.\n"
            "- Tampilkan data konkret dan angka akurat. Gunakan tabel untuk data tabular atau list untuk keterbacaan yang baik.\n"
            "- DEEP ANALYSIS: Jangan hanya membaca angka. Selalu berikan analisis mendalam (contoh: korelasi antar data, tren, mengapa suatu produk laku/tidak laku) dan berikan saran strategis bisnis (Actionable Insights) untuk meningkatkan performa toko.\n\n"
            "VISUALISASI GRAFIK:\n"
            "- Jika responmu mengandung data perbandingan numerik, tambahkan blok data grafik di akhir responmu dengan format berikut (satu baris, tanpa spasi tambahan):\n"
            "  <!--CHART:{{\"type\":\"bar\",\"title\":\"Judul Grafik\",\"labels\":[\"Label1\",\"Label2\"],\"data\":[10,20],\"unit\":\"unit\"}}-->\n"
            "- Gunakan format JSON valid dengan tanda kutip ganda pada keys dan string values."
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
            config={
                "system_instruction": system_instruction,
                "temperature": 0.4,
                "top_p": 0.95,
                "max_output_tokens": 4096
            }
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
