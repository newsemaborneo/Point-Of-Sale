<?php

namespace App\Http\Controllers;

use App\Services\Ai\DashboardInsightService;
use App\Services\Ai\GeminiService;
use App\Services\Ai\AiContextBuilderService;
use App\Services\Ai\AiFallbackService;
use App\Services\Ai\AiProactiveAlertService;
use App\Services\Analytics\SalesAnalyticsService;
use App\Services\Analytics\InventoryAnalyticsService;
use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class AiController extends Controller
{
    public function __construct(
        protected DashboardInsightService $dashboardInsightService,
        protected GeminiService $geminiService,
        protected AiContextBuilderService $contextBuilder,
        protected AiFallbackService $fallbackService,
        protected AiProactiveAlertService $alertService,
        protected SalesAnalyticsService $salesAnalytics,
        protected InventoryAnalyticsService $inventoryAnalytics
    ) {}

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $insights        = $this->dashboardInsightService->getInsightsForUser($user);
        $recommendations = $this->dashboardInsightService->getRecommendationsForUser($user);

        // Load conversations sidebar
        $conversations = AiConversation::where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get(['id', 'title', 'updated_at']);

        // Load or create active conversation
        $conversationId = $request->query('conversation');
        $activeConversation = $conversationId
            ? AiConversation::where('user_id', $user->id)->findOrFail($conversationId)
            : AiConversation::where('user_id', $user->id)->orderByDesc('updated_at')->first();

        $chatMessages = $activeConversation
            ? $activeConversation->messages->map(fn($m) => ['role' => $m->role, 'text' => $m->text])->toArray()
            : $this->dashboardInsightService->getChatMessagesForUser($user);

        return view('ai.index', compact('insights', 'recommendations', 'chatMessages', 'conversations', 'activeConversation'));
    }

    // ─── Conversation Management ─────────────────────────────────────

    public function newConversation()
    {
        $conv = AiConversation::create(['user_id' => Auth::id(), 'title' => 'Percakapan Baru']);
        return redirect()->route('ai.index', ['conversation' => $conv->id]);
    }

    public function destroyConversation(AiConversation $conversation)
    {
        abort_if($conversation->user_id !== Auth::id(), 403);
        $conversation->delete();
        return redirect()->route('ai.index');
    }

    // ─── AI Proactive Alerts ─────────────────────────────────────────

    public function getAlerts()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $alerts = Cache::remember("ai_alerts_user_{$user->id}", 300, fn() => $this->alertService->getAlerts($user));
        return response()->json(['alerts' => $alerts]);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message'         => 'required|string|max:1000',
            'conversation_id' => 'nullable|integer',
        ]);

        $prompt = $request->input('message');
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Resolve or create conversation
        $convId       = $request->input('conversation_id');
        $conversation = $convId
            ? AiConversation::where('user_id', $user->id)->find($convId)
            : null;

        if (!$conversation) {
            $conversation = AiConversation::create([
                'user_id' => $user->id,
                'title'   => mb_substr($prompt, 0, 60),
            ]);
        }

        // Save user message
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role'            => 'user',
            'text'            => $prompt,
            'source'          => 'user',
        ]);

        $conversation->touch(); // update updated_at for sidebar ordering

        try {
            // Build comprehensive real-time context of the entire system
            $context = $this->buildSystemContext($user);

            // Try calling the AI microservice first
            $microserviceUrl = config('services.ai.microservice_url');
            $response = null;

            if ($microserviceUrl) {
                try {
                    $geminiModel = \App\Models\Setting::get('gemini_model', config('services.ai.gemini_model', 'gemini-2.5-flash'));
                    $clientResponse = \Illuminate\Support\Facades\Http::timeout(20)
                        ->post($microserviceUrl . '/chat', [
                            'message' => $prompt,
                            'user_id' => $user->id,
                            'context' => $context,
                            'gemini_model' => $geminiModel,
                            'user_name' => $user->name,
                            'user_role' => $user->role->slug ?? 'user',
                        ]);

                    if ($clientResponse->successful()) {
                        $response = $clientResponse->json('text');
                        if (!empty($response)) {
                            $this->saveAiMessage($conversation->id, $response, 'microservice');
                            return response()->json(['role' => 'assistant', 'text' => $response, 'source' => 'microservice', 'conversation_id' => $conversation->id]);
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::error('AI Microservice error', [
                            'status' => $clientResponse->status(),
                            'body' => $clientResponse->body(),
                            'prompt' => $prompt
                        ]);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('AI Microservice connection exception: ' . $e->getMessage(), [
                        'prompt' => $prompt,
                        'exception' => get_class($e)
                    ]);
                }
            }

            // Enhanced Gemini System Instructions
            $systemInstruction = $this->buildSystemInstruction($user);

            // Try direct Gemini service
            if (!$response) {
                $response = $this->geminiService->generate($prompt, $systemInstruction . "\n\n[KONTEKS DATA BISNIS REALTIME]\n" . $context);
                if (!empty($response)) {
                    $this->saveAiMessage($conversation->id, $response, 'gemini');
                    return response()->json(['role' => 'assistant', 'text' => $response, 'source' => 'gemini', 'conversation_id' => $conversation->id]);
                }
            }

            // Fallback to local rules-based engine if both API key/microservice fails
            $response = $this->fallbackChatResponse($prompt, $user, $context);

            if (empty($response)) {
                $response = "Maaf, saya sedang mengalami keterbatasan dalam memproses pertanyaan Anda. Silakan coba dengan pertanyaan yang lebih spesifik, misalnya:\n- 'Berapa omset hari ini?'\n- 'Produk mana yang paling laku hari ini?'\n- 'Stok produk apa yang kritis?'\n- 'Berapa pertumbuhan penjualan bulan ini?'";
            }

            $this->saveAiMessage($conversation->id, $response, 'fallback');
            return response()->json(['role' => 'assistant', 'text' => $response, 'source' => 'fallback', 'conversation_id' => $conversation->id]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Chat fatal error: ' . $e->getMessage(), [
                'prompt' => $request->input('message'),
                'user_id' => $user->id ?? null,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'role' => 'assistant',
                'text' => 'Maaf, terjadi kesalahan saat memproses pertanyaan Anda. Tim teknis kami telah diberitahu. Silakan coba kembali dalam beberapa saat.',
                'source' => 'error'
            ]);
        }
    }

    protected function saveAiMessage(int $conversationId, string $text, string $source): void
    {
        AiMessage::create([
            'conversation_id' => $conversationId,
            'role'            => 'assistant',
            'text'            => $text,
            'source'          => $source,
        ]);
    }

    /**
     * Build enhanced system instruction for AI.
     */
    protected function buildSystemInstruction(User $user): string
    {
        $role = $user->role->name ?? 'User';
        $branchName = $user->branch?->name ?? 'Semua Cabang';

        return "Kamu adalah AI Chat Assistant LAKUPOS, bagian dari Modul Kecerdasan Buatan (AI Intelligence) 
pada sistem Point of Sales (POS) terintegrasi multi-cabang.

IDENTITAS:
- Nama: AI Chat Assistant LAKUPOS
- Pembuat: Dibuat dan diciptakan oleh tim pengembangan Newsem Aborneo, Mahasiswa Informatika
- Tujuan: Menjawab pertanyaan natural language seputar operasional bisnis berdasarkan data riil 
  dari sistem POS (pendekatan Retrieval-Augmented Generation / RAG), serta memberikan insight dan 
  rekomendasi proaktif untuk pengambilan keputusan.
- Pengguna saat ini: {$role} di cabang {$branchName}

OTORISASI BERDASARKAN PERAN (RBAC):
- SuperAdmin / Pemilik Bisnis: akses penuh seluruh cabang, seluruh modul, dan laporan keuangan tingkat atas.
- Admin: akses ke master data (produk, supplier, pelanggan), stok opname, dan laporan manajerial 
  di cabang yang menjadi tanggung jawabnya.
- Supervisor: akses ke operasional harian cabang tertentu, termasuk persetujuan pembatalan transaksi 
  dan pengawasan pergerakan kas di cabangnya.
- Cashier (Kasir): akses terbatas pada data transaksi penjualan dan shift kasirnya sendiri saja — 
  jangan berikan data cabang lain, data keuangan tingkat manajerial, atau data supplier/utang kepada Kasir.
- Selalu sesuaikan cakupan data yang kamu tampilkan dengan level otorisasi peran pengguna saat ini. 
  Jika pengguna dengan role terbatas (mis. Cashier) menanyakan data di luar wewenangnya, jelaskan 
  bahwa informasi tersebut memerlukan akses SuperAdmin/Admin, jangan tampilkan datanya.

CAKUPAN DATA YANG DAPAT KAMU ANALISIS (sesuai modul sistem):
1. Penjualan & POS: transaksi (Sale/SaleItem), promosi, voucher, retur penjualan (SaleReturn)
2. Pelanggan & Piutang: profil pelanggan, poin loyalti (Customer Point), piutang pelanggan (CustomerDebt)
3. Pembelian & Utang: Purchase Order, pembelian (Purchase), utang supplier (SupplierDebt), retur pembelian
4. Inventaris & Stok: stok real-time per cabang/gudang (ProductStock), pergerakan stok, hasil stok opname
5. Keuangan & Kas: shift kasir (buka/tutup), pergerakan kas (CashMovement), rekonsiliasi pembayaran
6. Laporan: laporan penjualan, laporan stok, laba rugi kotor, laporan pajak, performa kasir
7. Aktivitas Sistem: log aktivitas pengguna (untuk keperluan audit, hanya untuk SuperAdmin/Admin)

ATURAN MENJAWAB PERTANYAAN SPESIFIK CABANG:
- Jika pengguna menyebut nama cabang tertentu, gunakan data cabang tersebut secara spesifik dari 
  bagian \"BREAKDOWN PER CABANG\" pada konteks — jangan gunakan angka gabungan seluruh cabang.
- Jika pengguna bertanya \"semua cabang\" atau \"per cabang\", tampilkan rincian tiap cabang secara terpisah.
- Jika data cabang yang diminta tidak tersedia di konteks, katakan dengan jujur bahwa data tidak ditemukan.

ATURAN AKURASI ISTILAH BISNIS:
- Bedakan \"produk terlaris\" (data penjualan/Sale) dari \"stok produk\" (data inventaris/ProductStock) — 
  meski sama-sama menyebut \"produk\", jawab sesuai topik yang benar-benar ditanyakan.
- Bedakan \"laba kotor\" (Gross Profit, sebelum biaya operasional) dari \"laba bersih\" (Net Profit, setelah 
  seluruh biaya operasional). Jika hanya data laba kotor tersedia di konteks, jelaskan hal ini kepada 
  pengguna, jangan menyamakan keduanya.
- Bedakan \"utang pelanggan\" (CustomerDebt/piutang toko) dari \"utang ke supplier\" (SupplierDebt) — 
  keduanya arah utang yang berlawanan, jangan tertukar.
- Bedakan \"Purchase Order\" (rencana pesanan) dari \"Purchase\" (pembelian yang sudah diterima dan tercatat 
  menambah stok) saat menjawab pertanyaan seputar pembelian.

GAYA KOMUNIKASI:
- Ramah, profesional, dan proaktif — konsisten dengan peran AI Recommendation yang memberi saran 
  strategis (contoh: restock produk hampir habis, promosi untuk produk lambat terjual).
- Gunakan Bahasa Indonesia yang baik dan formal.
- Sertakan data konkret dan angka akurat, HANYA dari konteks (RAG) yang diberikan — jangan pernah 
  mengarang data yang tidak ada dalam konteks.
- Format jawaban dengan markup (heading, bold, bullet points), gunakan tabel untuk data tabular 
  jika relevan (contoh: rincian produk per cabang).
- Berikan rekomendasi aksi yang konkret di akhir respons bila relevan, selaras dengan fitur 
  AI Recommendation pada sistem.

PANDUAN RESPONS:
- Ringkas: maksimal 300 kata untuk pertanyaan umum
- Detail: maksimal 500 kata untuk analisis mendalam
- Jika data tidak tersedia atau di luar wewenang role pengguna, jelaskan alasannya dengan jujur
- Gunakan format mata uang Rp dengan pemisah ribuan titik (contoh: Rp 1.234.567)
- Untuk persentase, gunakan 1 desimal (contoh: 12,5%)
- Sertakan nama cabang/gudang dalam respons untuk konteks yang lebih jelas pada sistem multi-cabang

ATURAN VALIDASI DATA SEBELUM MENJAWAB:
- Sebelum menjawab pertanyaan laba/rugi, penjualan, atau metrik keuangan lain untuk cabang tertentu, 
  periksa apakah data yang tersedia di konteks benar-benar spesifik untuk cabang yang ditanyakan. 
  Jika konteks yang diberikan hanya berisi data agregat seluruh cabang (bukan breakdown per cabang), 
  jangan menyajikannya seolah-olah itu data khusus cabang yang diminta.
- Jika kamu tidak menemukan bagian data yang secara eksplisit berlabel nama cabang yang ditanyakan 
  (misal \"East Branch\" atau \"Central Branch\") di dalam konteks, katakan dengan jujur: 
  \"Data laba/rugi spesifik untuk cabang [nama cabang] belum tersedia di sistem saat ini\" — 
  jangan tampilkan angka gabungan semua cabang sebagai jawaban.
- Sebelum menjawab, cek juga: apakah angka yang akan kamu berikan mungkin sama persis dengan jawaban 
  untuk cabang lain yang sebelumnya pernah ditanyakan dalam percakapan ini? Jika ya, itu adalah tanda 
  data tersebut kemungkinan besar bukan data spesifik cabang — sampaikan hal ini ke pengguna alih-alih 
  menjawab seolah angka tersebut valid.
- Sebelum menjawab pertanyaan finansial per cabang, pastikan data di konteks memang punya breakdown 
  per cabang yang jelas. Jika hanya ada data gabungan semua cabang, jangan sajikan sebagai jawaban 
  cabang spesifik — katakan datanya belum tersedia untuk cabang tersebut.

PRIORITAS:
1. Akurasi data adalah yang paling penting — lebih baik jujur \"data tidak tersedia\" daripada menebak
2. Hormati batasan otorisasi (RBAC) sesuai role pengguna
3. Jangan spekulasi jika data tidak jelas atau ambigu
4. Fokus pada insight yang actionable dan selaras dengan alur kerja operasional POS (buka/tutup shift, 
   restock, approval transaksi, dsb.)
5. Bantu pengguna (SuperAdmin/Admin/Supervisor/Cashier) membuat keputusan bisnis yang lebih baik 
   sesuai wewenang mereka masing-masing

VISUALISASI GRAFIK:
- Jika responmu mengandung beberapa nilai numerik yang layak divisualisasikan (misalnya: perbandingan 
  produk, performa per cabang, distribusi kategori, jam sibuk, dsb.), tambahkan blok data grafik di 
  AKHIR responsmu dengan format TEPAT berikut (satu baris, tanpa spasi tambahan):
  <!--CHART:{type:bar,title:Judul Grafik,labels:[Label1,Label2],data:[nilai1,nilai2],unit:unit}-->
  (Gunakan tanda kutip ganda standar JSON di dalam blok tersebut)
- Untuk data keuangan (Rp), tambahkan currency:true di dalam JSON.
- Untuk data stok kritis, tambahkan color:danger di dalam JSON.
- Jangan buat grafik jika hanya ada 1 data poin, atau jika pertanyaan bersifat naratif (tidak ada angka komparatif).
- Pastikan jumlah elemen di labels selalu sama dengan jumlah elemen di data.";
    }

    protected function buildSystemContext(User $user): string
    {
        return $this->contextBuilder->build($user);
    }

    protected function fallbackChatResponse(string $prompt, User $user, string $context): string
    {
        return $this->fallbackService->getResponse($prompt, $user, $context);
    }

    public function dashboardData()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $insights = \Illuminate\Support\Facades\Cache::remember("ai_insights_user_{$user->id}", now()->addMinutes(10), function () use ($user) {
            return $this->dashboardInsightService->getInsightsForUser($user);
        });

        $recommendations = \Illuminate\Support\Facades\Cache::remember("ai_recommendations_user_{$user->id}", now()->addMinutes(10), function () use ($user) {
            return $this->dashboardInsightService->getRecommendationsForUser($user);
        });

        return response()->json([
            'insights' => $insights,
            'recommendations' => $recommendations,
        ]);
    }

    public function getSystemContextApi(Request $request)
    {
        $token = $request->header('Authorization') ?: $request->input('token');
        $expectedToken = config('services.ai.microservice_token', 'super-secret-ai-token');
        if ($token !== 'Bearer ' . $expectedToken && $token !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = $request->input('user_id');
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $context = $this->buildSystemContext($user);
        $geminiModel = \App\Models\Setting::get('gemini_model', config('services.ai.gemini_model', 'gemini-2.5-flash'));

        return response()->json([
            'context' => $context,
            'gemini_model' => $geminiModel,
            'user' => [
                'name' => $user->name,
                'role' => $user->role->slug ?? 'user',
                'branch_id' => $user->branch_id,
                'branch_name' => $user->branch?->name ?? 'Semua Cabang',
            ],
            'raw_data' => [
                'sales_today' => $this->salesAnalytics->getTodayMetrics($user->branch_id, $user->hasRole('admin') || $user->hasRole('supervisor')),
                'inventory_health' => $this->inventoryAnalytics->getInventoryHealthSummary($user->branch_id, $user->hasRole('admin') || $user->hasRole('supervisor')),
            ]
        ]);
    }
}
