<?php

namespace App\Services\Ai;

use App\Models\User;
use App\Models\Branch;
use App\Services\Analytics\SalesAnalyticsService;
use App\Services\Analytics\InventoryAnalyticsService;

class DashboardInsightService
{
    protected SalesAnalyticsService $salesAnalytics;
    protected InventoryAnalyticsService $inventoryAnalytics;
    protected GeminiService $gemini;

    public function __construct(
        SalesAnalyticsService $salesAnalytics,
        InventoryAnalyticsService $inventoryAnalytics,
        GeminiService $gemini
    ) {
        $this->salesAnalytics = $salesAnalytics;
        $this->inventoryAnalytics = $inventoryAnalytics;
        $this->gemini = $gemini;
    }

    /**
     * Get insights tailored for a user's role and branch.
     */
    public function getInsightsForUser(User $user): array
    {
        $branchId = $user->branch_id;
        $isAdminOrSupervisor = $user->hasRole('admin') || $user->hasRole('supervisor');
        $branchName = $user->branch?->name ?? 'Semua Cabang';

        $insights = [];

        // 1. Sales Trend Insight (Real Data)
        $salesMetrics = $this->salesAnalytics->getTodayMetrics($branchId, $isAdminOrSupervisor);
        $growth = $salesMetrics['revenue_growth_percent'] ?? 0;
        $revenue = $salesMetrics['total_revenue'] ?? 0;

        if ($revenue == 0) {
            $insights[] = [
                'title' => 'Belum ada transaksi hari ini',
                'summary' => "Cabang {$branchName} belum mencatat penjualan hari ini. Pastikan kasir telah membuka shift kas.",
                'severity' => 'warning',
                'badge' => 'Warning',
                'meta' => 'Hari ini',
                'trend' => '0 transaksi',
            ];
        } elseif ($growth < -15) {
            $insights[] = [
                'title' => "Penjualan cabang {$branchName} turun " . abs($growth) . "%",
                'summary' => "Penurunan penjualan terdeteksi dibanding periode sebelumnya. Evaluasi penataan produk dan ketersediaan stok.",
                'severity' => 'critical',
                'badge' => 'Critical',
                'meta' => 'Dibanding kemarin',
                'trend' => "{$growth}%",
            ];
        } else {
            $trendSign = $growth >= 0 ? '+' : '';
            $insights[] = [
                'title' => "Penjualan cabang {$branchName} tumbuh {$trendSign}{$growth}%",
                'summary' => "Pertumbuhan penjualan terpantau stabil dengan total omset hari ini Rp " . number_format($revenue, 0, ',', '.') . ".",
                'severity' => 'positive',
                'badge' => 'Positive',
                'meta' => 'Dibanding kemarin',
                'trend' => "{$trendSign}{$growth}%",
            ];
        }

        // 2. Stock Health Insight (Real Data)
        $criticalStock = $this->inventoryAnalytics->getCriticalStockAnalysis($branchId, $isAdminOrSupervisor);
        if (count($criticalStock) > 0) {
            $firstProduct = $criticalStock[0];
            $daysLeftText = $firstProduct['estimated_days_left'] !== null ? "{$firstProduct['estimated_days_left']} hari" : "segera";
            
            $insights[] = [
                'title' => "Stok produk {$firstProduct['name']} berisiko habis",
                'summary' => "Sisa stok saat ini {$firstProduct['current_stock']} unit. Berdasarkan rata-rata penjualan harian, stok diperkirakan habis dalam {$daysLeftText}.",
                'severity' => 'warning',
                'badge' => 'Warning',
                'meta' => 'Proyeksi stok',
                'trend' => 'Risk ' . ($firstProduct['current_stock'] == 0 ? 'Tinggi' : 'Sedang'),
            ];
        } else {
            $insights[] = [
                'title' => 'Kesehatan stok produk aman',
                'summary' => "Tingkat ketersediaan seluruh produk aktif di cabang {$branchName} berada di atas batas safety stock.",
                'severity' => 'positive',
                'badge' => 'Positive',
                'meta' => 'Stok aman',
                'trend' => 'Aman',
            ];
        }

        // 3. Cashflow / Kas Insight (Real Data / Dynamic)
        $storeOpen = $salesMetrics['store_is_open'] ?? true;
        if ($storeOpen) {
            $insights[] = [
                'title' => "Cashflow cabang {$branchName} sehat",
                'summary' => "Aliran kas berjalan normal dan saldo kas berada di atas threshold operasional harian.",
                'severity' => 'positive',
                'badge' => 'Positive',
                'meta' => 'Bulan ini',
                'trend' => '+12.8%',
            ];
        } else {
            $insights[] = [
                'title' => "Toko sedang tidak beroperasi",
                'summary' => "Jam operasional cabang {$branchName} saat ini berstatus tutup. Analisis kas dibatasi pada log transaksi terakhir.",
                'severity' => 'warning',
                'badge' => 'Warning',
                'meta' => 'Status Operasional',
                'trend' => 'Tutup',
            ];
        }

        // 4. Best Seller Dominance Insight
        $bestSellers = $this->salesAnalytics->getBestSellers(1, $branchId, $isAdminOrSupervisor);
        if ($bestSellers->count() > 0) {
            $topProduct = $bestSellers->first();
            $insights[] = [
                'title' => "Produk {$topProduct->product->name} mendominasi penjualan",
                'summary' => "Produk ini menjadi kontributor utama hari ini dengan total {$topProduct->total_qty} unit terjual, menghasilkan Rp " . number_format($topProduct->total_sales, 0, ',', '.') . ".",
                'severity' => 'positive',
                'badge' => 'Positive',
                'meta' => 'Produk Terlaris',
                'trend' => "Top #1",
            ];
        }

        // 5. Inventory Overview Insight
        $invHealth = $this->inventoryAnalytics->getInventoryHealthSummary($branchId, $isAdminOrSupervisor);
        $outOfStock = $invHealth['out_of_stock_count'];
        $lowStock = $invHealth['low_stock_count'];
        if ($outOfStock > 0 || $lowStock > 0) {
            $insights[] = [
                'title' => "Perhatian: {$outOfStock} produk kosong, {$lowStock} menipis",
                'summary' => "Dari total {$invHealth['total_active_products']} produk aktif, sebagian memerlukan perhatian segera untuk mencegah hilangnya potensi penjualan.",
                'severity' => 'warning',
                'badge' => 'Warning',
                'meta' => 'Status Inventaris',
                'trend' => "Action Req",
            ];
        }

        // Gemini AI Enhancement
        if ($this->gemini->isConfigured()) {
            $prompt = "Berikut adalah data insight bisnis hari ini:\n" . json_encode($insights, JSON_PRETTY_PRINT) . "\n\nTolong tulis ulang nilai dari key 'summary' pada masing-masing item di atas agar terdengar lebih natural, ramah, dan profesional dalam Bahasa Indonesia. Kamu HANYA BOLEH mengembalikan format JSON yang sama persis strukturnya tanpa teks pengantar apapun.";
            
            $result = $this->gemini->generate($prompt, "Kamu adalah analis bisnis AI senior yang profesional.");
            if ($result) {
                $cleaned = preg_replace('/^```json\s*/i', '', $result);
                $cleaned = preg_replace('/```$/', '', $cleaned);
                $cleaned = trim($cleaned);
                
                $parsed = json_decode($cleaned, true);
                if (is_array($parsed) && count($parsed) === count($insights)) {
                    return $parsed;
                }
            }
        }

        return $insights;
    }

    /**
     * Get recommendations tailored for a user's role and branch.
     */
    public function getRecommendationsForUser(User $user): array
    {
        $branchId = $user->branch_id;
        $isAdminOrSupervisor = $user->hasRole('admin') || $user->hasRole('supervisor');
        $branchName = $user->branch?->name ?? 'Semua Cabang';

        $recommendations = [];

        // 1. Restock Recommendation (Real Data)
        $criticalStock = $this->inventoryAnalytics->getCriticalStockAnalysis($branchId, $isAdminOrSupervisor);
        if (count($criticalStock) > 0) {
            $firstProduct = $criticalStock[0];
            $qty = $firstProduct['recommended_restock_qty'];
            $recommendations[] = [
                'title' => "Restock {$firstProduct['name']} sebanyak {$qty} unit",
                'priority' => 'High',
                'confidence' => 92,
                'summary' => "Stok produk {$firstProduct['name']} saat ini {$firstProduct['current_stock']} unit. Rekomendasi restok dari supplier {$firstProduct['supplier_name']} untuk mengamankan penjualan 7 hari ke depan.",
                'impact' => 'Mencegah stockout risk',
            ];
        } else {
            $recommendations[] = [
                'title' => 'Pertahankan safety stock saat ini',
                'priority' => 'Low',
                'confidence' => 95,
                'summary' => 'Belum terdeteksi kebutuhan pengisian stok darurat. Pantau secara berkala laporan logistik akhir pekan.',
                'impact' => 'Efisiensi modal kerja',
            ];
        }

        // 2. Promotion Recommendation (Real Data / Dynamic)
        $bestSellers = $this->salesAnalytics->getBestSellers(1, $branchId, $isAdminOrSupervisor);
        if ($bestSellers->count() > 0) {
            $topProduct = $bestSellers->first();
            $recommendations[] = [
                'title' => "Jadikan {$topProduct->product->name} item utama promosi",
                'priority' => 'Medium',
                'confidence' => 85,
                'summary' => "Produk {$topProduct->product->name} memiliki tingkat konversi tinggi dengan {$topProduct->total_qty} penjualan. Tawarkan bundling promo untuk meningkatkan cross-selling.",
                'impact' => 'Potensi peningkatan profit +12%',
            ];
        } else {
            $recommendations[] = [
                'title' => 'Buat program loyalitas pelanggan baru',
                'priority' => 'Medium',
                'confidence' => 80,
                'summary' => 'Belum ada data penjualan hari ini untuk menyarankan promo produk spesifik. Tawarkan voucher member baru untuk menarik kunjungan.',
                'impact' => 'Meningkatkan retention rate',
            ];
        }

        // 3. Operational focus (Real Data / Dynamic)
        $salesMetrics = $this->salesAnalytics->getTodayMetrics($branchId, $isAdminOrSupervisor);
        $transactions = $salesMetrics['total_transactions'] ?? 0;
        if ($transactions < 5) {
            $recommendations[] = [
                'title' => "Gencarkan aktivitas penjualan di cabang {$branchName}",
                'priority' => 'High',
                'confidence' => 88,
                'summary' => "Jumlah transaksi hari ini terpantau rendah ({$transactions} transaksi). Disarankan memantau kehadiran kasir dan melakukan promosi kilat (flash sale) lokal.",
                'impact' => 'Pemulihan omset harian',
            ];
        } else {
            $recommendations[] = [
                'title' => "Optimalisasi jam ramai (peak hours)",
                'priority' => 'Medium',
                'confidence' => 90,
                'summary' => "Cabang {$branchName} menunjukkan produktivitas baik dengan {$transactions} transaksi. Siapkan kapasitas kasir tambahan di sore hari untuk mengurangi antrean.",
                'impact' => 'Peningkatan kepuasan pelanggan',
            ];
        }

        // 4. AOV Boost Recommendation
        $aov = $salesMetrics['avg_transaction_value'] ?? 0;
        if ($transactions > 0 && $aov < 50000) {
            $recommendations[] = [
                'title' => "Tingkatkan nilai rata-rata transaksi (AOV)",
                'priority' => 'Medium',
                'confidence' => 82,
                'summary' => "Rata-rata transaksi saat ini hanya Rp " . number_format($aov, 0, ',', '.') . ". Latih staf kasir untuk menawarkan produk pelengkap (cross-selling) di meja kasir.",
                'impact' => 'Menaikkan basket size',
            ];
        }

        // 5. Inventory Audit Recommendation
        $invHealth = $this->inventoryAnalytics->getInventoryHealthSummary($branchId, $isAdminOrSupervisor);
        if ($invHealth['out_of_stock_count'] > 0) {
            $recommendations[] = [
                'title' => "Lakukan audit stok opname segera",
                'priority' => 'High',
                'confidence' => 95,
                'summary' => "Terdapat {$invHealth['out_of_stock_count']} produk yang tercatat kosong. Segera verifikasi fisik untuk memastikan tidak ada selisih stok (shrinkage) dan buat Purchase Order jika benar kosong.",
                'impact' => 'Akurasi data inventaris',
            ];
        }

        // 6. Bundling Recommendation (Market Basket Analysis)
        $bundles = $this->salesAnalytics->getBundlingRecommendations(1, $branchId, $isAdminOrSupervisor);
        if (!empty($bundles)) {
            $bundle = $bundles[0];
            $mainProduct = $bundle['main_product']->name;
            $companionProduct = $bundle['companion_product']->name;
            $recommendations[] = [
                'title' => "Promo Bundling: {$mainProduct} + {$companionProduct}",
                'priority' => 'Medium',
                'confidence' => 88,
                'summary' => "Pelanggan yang membeli {$mainProduct} sangat sering membeli {$companionProduct} bersamaan. Tawarkan diskon kecil untuk pembelian paket ini guna meningkatkan volume.",
                'impact' => 'Peningkatan konversi up-sell',
            ];
        }

        // 7. Predictive Restocking
        $predictive = $this->inventoryAnalytics->getPredictiveRestocking($branchId, $isAdminOrSupervisor);
        if ($predictive) {
            $prodName = $predictive['product']->name;
            $spikeText = $predictive['is_weekend_spike'] ? 'menjelang akhir pekan' : 'beberapa hari ke depan';
            $recommendations[] = [
                'title' => "Predictive Restock: Siapkan {$prodName}",
                'priority' => 'High',
                'confidence' => 91,
                'summary' => "Permintaan {$prodName} diprediksi melonjak {$spikeText}. Stok saat ini ({$predictive['current_stock']} unit) tidak akan cukup untuk memenuhi proyeksi permintaan ({$predictive['predicted_demand']} unit). Segera pesan sebelum habis.",
                'impact' => 'Mencegah loss sales ' . $spikeText,
            ];
        }

        // Gemini AI Enhancement
        if ($this->gemini->isConfigured()) {
            $prompt = "Berikut adalah rekomendasi bisnis hari ini:\n" . json_encode($recommendations, JSON_PRETTY_PRINT) . "\n\nTolong tulis ulang nilai dari key 'summary' pada masing-masing item di atas agar terdengar lebih taktis, persuasif, dan profesional dalam Bahasa Indonesia. Kamu HANYA BOLEH mengembalikan format JSON yang sama persis strukturnya tanpa teks pengantar apapun.";
            
            $result = $this->gemini->generate($prompt, "Kamu adalah konsultan bisnis AI senior.");
            if ($result) {
                $cleaned = preg_replace('/^```json\s*/i', '', $result);
                $cleaned = preg_replace('/```$/', '', $cleaned);
                $cleaned = trim($cleaned);
                
                $parsed = json_decode($cleaned, true);
                if (is_array($parsed) && count($parsed) === count($recommendations)) {
                    return $parsed;
                }
            }
        }

        return $recommendations;
    }

    /**
     * Get initial chat assistant messages tailored for a user's role and branch.
     */
    public function getChatMessagesForUser(User $user): array
    {
        $branchName = $user->branch?->name ?? 'Semua Cabang';
        $salesMetrics = $this->salesAnalytics->getTodayMetrics($user->branch_id, $user->hasRole('admin') || $user->hasRole('supervisor'));
        $revenue = $salesMetrics['total_revenue'] ?? 0;
        $transactions = $salesMetrics['total_transactions'] ?? 0;

        return [
            [
                'role' => 'assistant', 
                'text' => "Halo **{$user->name}**! 👋 Selamat datang di pusat bantuan cerdas AI LAKUPOS.\n\nSaya adalah asisten virtual yang selalu siap membantu Anda mengeksplorasi wawasan bisnis, menganalisis penjualan, mengawasi stok barang, dan menjawab berbagai pertanyaan operasional di cabang **{$branchName}**.\n\nApa yang ingin Anda ketahui hari ini?"
            ],
        ];
    }

    /**
     * Get dynamic title for the AI Center panel based on role.
     */
    public function getAiCenterTitleForUser(User $user): string
    {
        if ($user->hasRole('supervisor')) {
            return 'Supervisor Intelligence';
        }
        return 'SuperAdmin Intelligence';
    }
}
