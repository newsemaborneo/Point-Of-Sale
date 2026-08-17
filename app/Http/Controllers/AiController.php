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

        $contextSuggestions = $this->getContextAwareSuggestions($user);

        return view('ai.index', compact('chatMessages', 'conversations', 'activeConversation', 'contextSuggestions'));
    }

    protected function getContextAwareSuggestions($user): array
    {
        $suggestions = [];
        
        // 1. Cek stok kritis
        try {
            $criticalStock = $this->inventoryAnalytics->getCriticalStockAnalysis($user->branch_id, true);
            if (count($criticalStock) > 0) {
                $suggestions[] = [
                    'icon' => '⚠️',
                    'label' => 'Ada stok produk kritis: Produk mana yang stoknya kritis?',
                    'category' => 'Stok'
                ];
            }
        } catch (\Exception $e) {}

        // 2. Cek laci kasir terbuka di jam malam
        try {
            $currentHour = (int) now()->format('H');
            $openRegisters = \App\Models\CashRegister::whereNull('closed_at')
                ->when($user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
                ->count();
            if ($openRegisters > 0 && ($currentHour >= 20 || $currentHour < 6)) {
                $suggestions[] = [
                    'icon' => '🏪',
                    'label' => 'Shift kasir belum tutup malam ini: Tampilkan status shift?',
                    'category' => 'Keuangan'
                ];
            }
        } catch (\Exception $e) {}

        // 3. Cek apakah ada dead stock
        try {
            $thirtyDaysAgo = now()->subDays(30);
            $deadStockCount = \App\Models\Product::whereNotIn('id', function($q) use ($thirtyDaysAgo, $user) {
                $q->select('product_id')->from('sale_items')->join('sales', 'sale_items.sale_id', '=', 'sales.id')->where('sales.created_at', '>=', $thirtyDaysAgo);
                if ($user->branch_id) {
                    $q->where('sales.branch_id', $user->branch_id);
                }
            })->count();
            if ($deadStockCount > 0) {
                $suggestions[] = [
                    'icon' => '📦',
                    'label' => 'Ada dead stock: Produk apa yang tidak laku 30 hari terakhir?',
                    'category' => 'Stok'
                ];
            }
        } catch (\Exception $e) {}

        return $suggestions;
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

BATASAN TOPIK PERTANYAAN (CRITICAL RULE):
Kamu HANYA boleh menjawab pertanyaan yang relevan dengan 20 pola saran (suggestions) berikut:
1. Berapa omset hari ini?
2. Produk apa yang paling laris bulan ini?
3. Bandingkan penjualan bulan ini dengan bulan lalu
4. Jam berapa yang paling sibuk hari ini?
5. Siapa pelanggan yang paling banyak berbelanja?
6. Berapa total transaksi bulan ini?
7. Berapa rata-rata nilai transaksi bulan ini?
8. Produk mana yang stoknya kritis atau hampir habis?
9. Produk apa yang tidak laku 30 hari terakhir (dead stock)?
10. Berapa total produk aktif saat ini?
11. Berapa stok produk tertentu saat ini? (Catatan khusus: jika nama produk tidak ada di konteks, jawab bahwa data tidak tercantum di ringkasan AI dan arahkan ke menu Produk)
12. Berapa laba kotor bulan ini?
13. Berapa total HPP (Harga Pokok Penjualan) bulan ini?
14. Berapa total utang toko ke supplier?
15. Metode pembayaran apa yang paling sering digunakan?
16. Berapa margin keuntungan bulan ini?
17. Prediksi omset bulan depan berdasarkan tren 3 bulan terakhir
18. Produk mana yang stoknya diprediksi akan habis paling cepat?
19. Berikan rekomendasi bundling produk yang sering dibeli bersama
20. Apa tren penjualan dalam 3 bulan terakhir?

Jika pengguna menanyakan hal di luar 20 topik di atas (misalnya resep makanan, pemrograman, obrolan santai di luar bisnis LAKUPOS, atau analisis strategi fiktif), jawab dengan sopan bahwa kamu hanya dirancang untuk menjawab 20 pertanyaan analisis bisnis di atas.

ATURAN ANTI-HALUSINASI (SANGAT KETAT):
1. JANGAN PERNAH MENGARANG DATA, ANGKA, ATAU NAMA PRODUK. Semua angka, nama produk, jumlah transaksi, dan persentase yang kamu keluarkan HARUS berasal secara eksponen dari [KONTEKS DATA BISNIS REALTIME] yang disediakan.
2. Jika ada data yang tidak tersedia di konteks (misalnya pengguna bertanya stok produk 'Indomie Soto' tetapi produk tersebut tidak ada di bagian Kesehatan Stok atau forecast di konteks), katakan dengan jujur: \"Maaf, data stok produk tersebut tidak tercantum dalam ringkasan konteks AI saat ini. Silakan cek halaman master Produk untuk melihat data lengkap.\" Jangan sekali-kali mengarang sisa stok atau angka penjualan produk tersebut.
3. Selalu perhatikan batasan akses cabang pengguna saat ini. Jika pengguna bertugas di cabang tertentu, berikan analisis spesifik cabang tersebut.

OTORISASI BERDASARKAN PERAN (RBAC):
- SuperAdmin / Pemilik Bisnis: akses penuh seluruh cabang.
- Admin / Supervisor: akses terbatas pada cabang bertugas.
- Cashier (Kasir): akses terbatas pada penjualan sendiri. Jangan berikan data utang, laba kotor, HPP, atau data rahasia manajerial lainnya ke Cashier.

ATURAN AKURASI ISTILAH BISNIS:
- Bedakan \"produk terlaris\" dari \"stok produk\".
- Bedakan \"laba kotor\" (Gross Profit) dari \"laba bersih\" (Net Profit).
- Bedakan \"utang pelanggan\" (piutang toko) dari \"utang ke supplier\".

GAYA KOMUNIKASI:
- Ramah, profesional, dan proaktif. Gunakan Bahasa Indonesia yang baik dan formal.
- Tampilkan data konkret dan angka akurat. Gunakan tabel untuk data tabular atau list untuk keterbacaan yang baik.

VISUALISASI GRAFIK:
- Jika responmu mengandung data perbandingan numerik, tambahkan blok data grafik di akhir responmu dengan format berikut (satu baris, tanpa spasi tambahan):
  <!--CHART:{\"type\":\"bar\",\"title\":\"Judul Grafik\",\"labels\":[\"Label1\",\"Label2\"],\"data\":[10,20],\"unit\":\"unit\"}-->
- Gunakan format JSON valid dengan tanda kutip ganda pada keys dan string values.";
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
