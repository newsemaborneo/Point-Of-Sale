<?php

namespace App\Services\Ai;

use App\Models\User;
use App\Services\Analytics\SalesAnalyticsService;
use App\Services\Analytics\InventoryAnalyticsService;
use App\Services\Analytics\ProfitAnalyticsService;
use App\Services\Analytics\CustomerAnalyticsService;

class AiFallbackService
{
    protected SalesAnalyticsService $salesAnalytics;
    protected InventoryAnalyticsService $inventoryAnalytics;
    protected ProfitAnalyticsService $profitAnalytics;
    protected CustomerAnalyticsService $customerAnalytics;

    public function __construct(
        SalesAnalyticsService $salesAnalytics,
        InventoryAnalyticsService $inventoryAnalytics,
        ProfitAnalyticsService $profitAnalytics,
        CustomerAnalyticsService $customerAnalytics
    ) {
        $this->salesAnalytics = $salesAnalytics;
        $this->inventoryAnalytics = $inventoryAnalytics;
        $this->profitAnalytics = $profitAnalytics;
        $this->customerAnalytics = $customerAnalytics;
    }

    public function getResponse(string $prompt, User $user, string $context): string
    {
        $promptLower = strtolower($prompt);
        $isAdminOrSupervisor = $user->hasRole('admin') || $user->hasRole('supervisor');
        $branchName = $user->branch?->name ?? 'Semua Cabang';

        // Check if user is asking for a specific branch
        if ($isAdminOrSupervisor) {
            $branches = \App\Models\Branch::all();
            foreach ($branches as $b) {
                if (stripos($promptLower, strtolower($b->name)) !== false) {
                    $user->branch_id = $b->id;
                    $branchName = $b->name;
                    $isAdminOrSupervisor = false; // Force the query to filter by this specific branch ID
                    break;
                }
            }
            if (str_contains($promptLower, 'semua cabang')) {
                $user->branch_id = null;
                $branchName = 'Semua Cabang';
                $isAdminOrSupervisor = true;
            }
        }

        if ($this->isInterceptQuery($promptLower)) {
            return "Mohon maaf, saat ini saya sedang berada dalam mode fungsional dasar dan belum bisa menyusun strategi atau analisa kompleks untuk Anda. Namun, saya selalu siap menyajikan metrik terkini! Anda bisa menanyakan hal seperti 'omset hari ini', 'stok barang kritis', 'kategori terlaris', 'jam sibuk', atau 'produk tidak laku'.";
        }

        if ($this->isPredictionQuery($promptLower)) {
            return $this->handlePredictionIntent($promptLower, $user, $isAdminOrSupervisor, $branchName);
        }

        if ($this->isBundlingQuery($promptLower)) {
            return $this->handleBundlingIntent($user, $isAdminOrSupervisor, $branchName);
        }

        if ($this->isComparisonQuery($promptLower)) {
            return $this->handleComparisonIntent($promptLower, $user, $isAdminOrSupervisor, $branchName);
        }

        if ($this->isPurchaseQuery($promptLower)) {
            return $this->handlePurchaseIntent($promptLower, $user, $isAdminOrSupervisor, $branchName);
        }

        if ($this->isCashierShiftQuery($promptLower)) {
            return $this->handleCashierShiftIntent($promptLower, $user, $isAdminOrSupervisor, $branchName);
        }

        if ($this->isSalesQuery($promptLower)) {
            return $this->handleSalesIntent($promptLower, $user, $isAdminOrSupervisor, $branchName);
        }

        if ($this->isInventoryQuery($promptLower)) {
            return $this->handleInventoryIntent($promptLower, $user, $isAdminOrSupervisor, $branchName);
        }

        if ($this->isFinanceQuery($promptLower)) {
            return $this->handleFinanceIntent($promptLower, $user, $isAdminOrSupervisor, $branchName);
        }

        if ($this->isCustomerQuery($promptLower)) {
            return $this->handleCustomerIntent($promptLower, $user, $isAdminOrSupervisor, $branchName);
        }

        if ($this->isPromotionQuery($promptLower)) {
            return $this->handlePromotionIntent();
        }

        if ($this->isBranchPerformanceQuery($promptLower)) {
            return $this->handleBranchPerformanceIntent($user, $branchName, $isAdminOrSupervisor);
        }

        if ($this->isKnowledgeQuery($promptLower)) {
            return $this->handleKnowledgeIntent($promptLower);
        }

        return $this->getDefaultMenu();
    }

    protected function isInterceptQuery(string $prompt): bool
    {
        // Hanya blokir pertanyaan yang benar-benar di luar konteks bisnis
        return str_contains($prompt, 'halu') ||
               str_contains($prompt, 'cerita fiksi') ||
               str_contains($prompt, 'puisi');
    }

    protected function isComparisonQuery(string $prompt): bool
    {
        $hasComparison = str_contains($prompt, 'bandingkan') || str_contains($prompt, ' vs ') || str_contains($prompt, 'dibanding') || str_contains($prompt, 'versus');
        $hasMonth = preg_match('/januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember/i', $prompt);
        $hasPeriod = str_contains($prompt, 'bulan') || str_contains($prompt, 'minggu');
        return $hasComparison && ($hasMonth || $hasPeriod);
    }

    protected function handleComparisonIntent(string $promptLower, User $user, bool $isAdminOrSupervisor, string $branchName): string
    {
        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        $foundMonths = [];
        foreach ($monthMap as $name => $num) {
            if (str_contains($promptLower, $name)) {
                $foundMonths[$name] = $num;
            }
        }

        if (count($foundMonths) < 2) {
            // Default: compare this month vs last month
            $foundMonths = [
                now()->subMonth()->translatedFormat('F') => now()->subMonth()->month,
                now()->translatedFormat('F')             => now()->month,
            ];
        }

        $year = now()->year;
        $labels = [];
        $dataA  = [];
        $dataB  = [];
        $names  = array_keys($foundMonths);
        $nums   = array_values($foundMonths);

        $getRevenue = function (int $month) use ($year, $isAdminOrSupervisor, $user): float {
            return (float) \App\Models\Sale::where('status', 'completed')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
                ->sum('grand_total');
        };

        // Build daily comparison labels across both months
        foreach ([$nums[0], $nums[1]] as $idx => $month) {
            $daysInMonth = now()->setMonth($month)->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $rev = (float) \App\Models\Sale::where('status', 'completed')
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->whereDay('created_at', $d)
                    ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
                    ->sum('grand_total');
                if ($idx === 0) {
                    $labels[] = $d;
                    $dataA[]  = (int) $rev;
                } else {
                    $dataB[] = (int) $rev;
                }
            }
        }

        $totalA = $getRevenue($nums[0]);
        $totalB = $getRevenue($nums[1]);
        $diffPct = $totalA > 0 ? round((($totalB - $totalA) / $totalA) * 100, 1) : 0;
        $trend = $diffPct >= 0 ? "naik {$diffPct}%" : "turun " . abs($diffPct) . "%";

        $text  = "📊 **Perbandingan Penjualan: " . ucfirst($names[0]) . " vs " . ucfirst($names[1]) . "** (Cabang: {$branchName})\n\n";
        $text .= "Berikut adalah rangkuman performa penjualan toko kita:\n";
        $text .= "- Omset di bulan **" . ucfirst($names[0]) . "**: Rp " . number_format($totalA, 0, ',', '.') . "\n";
        $text .= "- Omset di bulan **" . ucfirst($names[1]) . "**: Rp " . number_format($totalB, 0, ',', '.') . "\n\n";
        $text .= "Dari grafik komparasi, penjualan di bulan **" . ucfirst($names[1]) . "** tercatat **{$trend}** dibanding bulan **" . ucfirst($names[0]) . "**. Semoga tim kita bisa terus mempertahankan dan meningkatkan performa ini ya! 🚀";

        $chart = json_encode([
            'type'    => 'comparison',
            'title'   => 'Perbandingan Penjualan Harian',
            'labels'  => $labels,
            'dataA'   => $dataA,
            'dataB'   => $dataB,
            'nameA'   => ucfirst($names[0]),
            'nameB'   => ucfirst($names[1]),
            'currency' => true,
        ]);

        return $text . "\n<!--CHART:{$chart}-->";
    }

    protected function isInventoryQuery(string $prompt): bool
    {
        return str_contains($prompt, 'stok') || str_contains($prompt, 'barang') || str_contains($prompt, 'produk') || str_contains($prompt, 'habis') || str_contains($prompt, 'limit') || str_contains($prompt, 'mati') || str_contains($prompt, 'tidak laku') || str_contains($prompt, 'dead stock') || str_contains($prompt, 'aktif') || str_contains($prompt, 'riwayat') || str_contains($prompt, 'mutasi') || str_contains($prompt, 'perpindahan') || str_contains($prompt, 'movement');
    }

    protected function handleInventoryIntent(string $promptLower, User $user, bool $isAdminOrSupervisor, string $branchName): string
    {
        if (str_contains($promptLower, 'riwayat') || str_contains($promptLower, 'mutasi') || str_contains($promptLower, 'pergerakan') || str_contains($promptLower, 'perpindahan') || str_contains($promptLower, 'movement')) {
            $warehouses = \App\Models\Warehouse::when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))->pluck('id');
            
            $movements = \App\Models\StockMovement::with(['product', 'warehouse', 'user'])
                ->when($warehouses->isNotEmpty(), fn($q) => $q->whereIn('warehouse_id', $warehouses))
                ->latest('created_at')
                ->limit(5)
                ->get();
            
            if ($movements->isEmpty()) {
                return "Belum ada riwayat pergerakan (mutasi) stok yang tercatat di cabang **{$branchName}**.";
            }

            $text = "Berikut adalah 5 riwayat pergerakan (mutasi) stok terbaru di cabang **{$branchName}**:\n\n";
            foreach ($movements as $m) {
                $typeLabels = [
                    'in' => 'Stok Masuk 📥',
                    'out' => 'Stok Keluar 📤',
                    'transfer' => 'Transfer Stok 🔄',
                    'adjustment' => 'Penyesuaian ⚙️',
                    'sale' => 'Penjualan 🛒',
                    'purchase' => 'Pembelian 📦',
                    'opname' => 'Stok Opname 📋',
                    'return' => 'Retur Barang ↩️'
                ];
                $type = $typeLabels[$m->type] ?? strtoupper($m->type);
                $date = $m->created_at->format('d M Y H:i');
                $note = $m->note ? " ({$m->note})" : "";
                
                $text .= "• **{$date}** | {$type}\n";
                $text .= "  - Produk: **{$m->product->name}**\n";
                $text .= "  - Detail: {$m->quantity_before} unit ➔ {$m->quantity_after} unit (Selisih: " . ($m->quantity > 0 ? '+' : '') . "{$m->quantity} unit) oleh **{$m->user->name}**{$note}\n\n";
            }
            
            return $text;
        }

        if (str_contains($promptLower, 'dead stock') || str_contains($promptLower, 'tidak laku') || str_contains($promptLower, 'mati')) {
            $thirtyDaysAgo = now()->subDays(30);
            $deadStockItems = \App\Models\Product::whereNotIn('id', function($q) use ($thirtyDaysAgo, $user, $isAdminOrSupervisor) {
                $q->select('product_id')->from('sale_items')->join('sales', 'sale_items.sale_id', '=', 'sales.id')->where('sales.created_at', '>=', $thirtyDaysAgo);
                if (!$isAdminOrSupervisor && $user->branch_id) {
                    $q->where('sales.branch_id', $user->branch_id);
                }
            })->limit(5)->get();

            if ($deadStockItems->isEmpty()) {
                return "Kabar baik! Tidak ada produk dead-stock (barang macet) di cabang **{$branchName}** selama 30 hari terakhir. Semua produk kita bergerak aktif! 🥳";
            }

            $text = "Berikut adalah daftar produk yang penjualannya agak lambat (dead-stock) di cabang **{$branchName}** selama 30 hari terakhir:\n\n";
            foreach ($deadStockItems as $p) {
                $text .= "- **{$p->name}** (SKU: {$p->sku})\n";
            }
            $text .= "\n💡 **Saran Praktis:** Agar modal usaha Anda tidak mandek terlalu lama di rak, Anda bisa mempertimbangkan untuk membuat promo diskon cuci gudang atau bundling dengan produk terlaris.";
            return $text;
        }

        if (str_contains($promptLower, 'lihat') || str_contains($promptLower, 'daftar') || str_contains($promptLower, 'tampilkan') || str_contains($promptLower, 'apa saja') || str_contains($promptLower, 'aktif')) {
            $summary = $this->inventoryAnalytics->getInventoryHealthSummary($user->branch_id, $isAdminOrSupervisor);
            return "Tentu, saya bantu cek ringkasan stok cabang **{$branchName}** saat ini ya. Ada **{$summary['total_active_products']}** produk aktif terdaftar di sistem.\n\n" .
                   "Rincian kondisinya:\n" .
                   "- **{$summary['healthy_stock_count']}** produk dalam kondisi aman (healthy stock)\n" .
                   "- **{$summary['low_stock_count']}** produk stoknya mulai menipis\n" .
                   "- **{$summary['out_of_stock_count']}** produk saat ini kosong (0 unit)\n\n" .
                   "Anda bisa memantau atau mengupdate detail stok lengkapnya secara real-time di halaman **Produk** pada menu navigasi.";
        }

        $criticalStock = $this->inventoryAnalytics->getCriticalStockAnalysis($user->branch_id, $isAdminOrSupervisor);
        $trulyCritical = array_filter($criticalStock, fn($p) => ($p['current_stock'] ?? 0) > 0);
        $outOfStock    = array_filter($criticalStock, fn($p) => ($p['current_stock'] ?? 0) <= 0);

        if (count($trulyCritical) > 0 || count($outOfStock) > 0) {
            $text = "Saya sudah memeriksa kondisi inventaris di cabang **{$branchName}**. Ada beberapa produk yang membutuhkan perhatian cepat dari Anda:\n\n";
            $chartLabels = [];
            $chartData   = [];

            if (count($trulyCritical) > 0) {
                $text .= "⚠️ **Stok Menipis (Hampir Habis):**\n";
                foreach (array_slice($trulyCritical, 0, 5) as $product) {
                    $daysText = isset($product['estimated_days_left']) && $product['estimated_days_left'] > 0
                        ? "diperkirakan habis dalam **{$product['estimated_days_left']} hari**"
                        : "berpotensi habis sangat segera";
                    $text .= "- **{$product['name']}**: Tersisa {$product['current_stock']} unit ({$daysText})\n";
                    $text .= "[ACTION:po:product_id={$product['product_id']}:qty={$product['recommended_restock_qty']}:label=Restock {$product['name']}]\n";
                    $chartLabels[] = $product['name'];
                    $chartData[]   = (int) $product['current_stock'];
                }
            }

            if (count($outOfStock) > 0) {
                $text .= "\n🔴 **Stok Kosong (0 unit):**\n";
                foreach (array_slice($outOfStock, 0, 5) as $product) {
                    $text .= "- **{$product['name']}**: Kosong — segera lakukan reorder\n";
                    $text .= "[ACTION:po:product_id={$product['product_id']}:qty={$product['recommended_restock_qty']}:label=Buat PO {$product['name']}]\n";
                }
            }

            $text .= "\n💡 **Saran:** Sangat direkomendasikan untuk segera membuat *Purchase Order* (PO) ke supplier agar produk di atas tidak sampai kehabisan stok terlalu lama dan mengganggu transaksi penjualan.";

            if (!empty($chartData)) {
                $chart = json_encode(['type' => 'bar', 'title' => 'Stok Kritis (unit tersisa)', 'labels' => $chartLabels, 'data' => $chartData, 'unit' => 'unit', 'color' => 'danger']);
                return $text . "\n<!--CHART:{$chart}-->";
            }
            return $text;
        }
        return "Semua stok produk di cabang **{$branchName}** saat ini dalam kondisi aman dan di atas batas safety stock. Kerjaan bagus, pertahankan! 👍";
    }

    protected function isSalesQuery(string $prompt): bool
    {
        if (str_contains($prompt, 'tidak laku') || str_contains($prompt, 'dead stock')) {
            return false;
        }
        return str_contains($prompt, 'penjualan') || str_contains($prompt, 'omset') || str_contains($prompt, 'transaksi') || str_contains($prompt, 'pendapatan') || str_contains($prompt, 'laku') || str_contains($prompt, 'laris') || str_contains($prompt, 'omzet') || str_contains($prompt, 'kategori') || str_contains($prompt, 'jam sibuk') || str_contains($prompt, 'sibuk') || str_contains($prompt, 'ramai') || str_contains($prompt, 'peak hour');
    }

    protected function handleSalesIntent(string $promptLower, User $user, bool $isAdminOrSupervisor, string $branchName): string
    {
        if (str_contains($promptLower, 'kategori')) {
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            $categorySales = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->where('sales.status', 'completed')
                ->whereBetween('sales.created_at', [$startOfMonth, $endOfMonth])
                ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('sales.branch_id', $user->branch_id))
                ->select('categories.name', \Illuminate\Support\Facades\DB::raw('SUM(sale_items.quantity) as total_qty'), \Illuminate\Support\Facades\DB::raw('SUM(sale_items.subtotal) as total_revenue'))
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get();
            if ($categorySales->isEmpty()) {
                return "Belum ada penjualan untuk dikategorikan pada bulan ini.";
            }
            $text = "Berikut adalah 5 Kategori Terlaris bulan ini di {$branchName}:\n";
            $chartLabels = [];
            $chartData   = [];
            foreach ($categorySales as $c) {
                $text .= "- **{$c->name}**: Terjual {$c->total_qty} unit (Omset: Rp " . number_format($c->total_revenue, 0, ',', '.') . ")\n";
                $chartLabels[] = $c->name;
                $chartData[]   = (int) $c->total_qty;
            }
            $chart = json_encode(['type' => 'bar', 'title' => 'Kategori Terlaris Bulan Ini', 'labels' => $chartLabels, 'data' => $chartData, 'unit' => 'unit']);
            return $text . "\n<!--CHART:{$chart}-->";
        }

        if (str_contains($promptLower, 'jam sibuk') || str_contains($promptLower, 'sibuk') || str_contains($promptLower, 'ramai') || str_contains($promptLower, 'peak hour')) {
            $salesToday = \App\Models\Sale::where('status', 'completed')
                ->whereDate('created_at', now()->toDateString())
                ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
                ->get();
                
            if ($salesToday->isEmpty()) {
                return "Belum ada transaksi yang cukup hari ini untuk menentukan jam sibuk.";
            }

            $peakHours = $salesToday->groupBy(function($s) {
                return $s->created_at->format('H');
            })->map(function($group) {
                return $group->count();
            })->sortDesc()->take(3);

            $text = "Berikut adalah 3 Jam Sibuk (Peak Hours) hari ini di {$branchName}:\n";
            $chartLabels = [];
            $chartData   = [];
            foreach ($peakHours as $hour => $count) {
                $hourLabel = $hour . ':00';
                $text .= "- **Jam {$hour}:00 - {$hour}:59** ({$count} transaksi)\n";
                $chartLabels[] = $hourLabel;
                $chartData[]   = $count;
            }
            $chart = json_encode(['type' => 'bar', 'title' => 'Jam Sibuk Hari Ini', 'labels' => $chartLabels, 'data' => $chartData, 'unit' => 'transaksi']);
            return $text . "\n<!--CHART:{$chart}-->";
        }

        $is30Days = str_contains($promptLower, '30 hari') || str_contains($promptLower, 'sebulan terakhir');
        $isThisMonth = str_contains($promptLower, 'bulan') || str_contains($promptLower, 'bulanan');
        
        if ($is30Days || $isThisMonth) {
            $startTime = $is30Days ? now()->subDays(30) : now()->startOfMonth();
            $periodText = $is30Days ? "30 hari terakhir" : "bulan ini (" . now()->translatedFormat('F Y') . ")";

            // If they are asking for products that sold / best sellers
            if (str_contains($promptLower, 'terlaris') || str_contains($promptLower, 'laris') || str_contains($promptLower, 'laku') || str_contains($promptLower, 'produk')) {
                $bestSellers = $this->salesAnalytics->getBestSellers(5, $user->branch_id, $isAdminOrSupervisor, $startTime);
                
                if ($bestSellers->count() === 0) {
                    return "Belum ada produk yang terjual pada {$periodText} di cabang {$branchName}.";
                }
                
                $text = "🏆 **Berikut adalah Produk yang Laku Terjual {$periodText} di cabang {$branchName}:**\n\n";
                $chartLabels = [];
                $chartData   = [];
                foreach ($bestSellers as $item) {
                    $text .= "- **{$item->product->name}**: {$item->total_qty} unit terjual\n";
                    $chartLabels[] = $item->product->name;
                    $chartData[]   = (int) $item->total_qty;
                }
                
                $text .= "\n💡 **Rekomendasi:** Pastikan ketersediaan stok untuk produk-produk unggulan ini tetap terjaga untuk memaksimalkan omset Anda.";
                $chart = json_encode(['type' => 'bar', 'title' => 'Produk Terlaris ' . ucfirst($periodText), 'labels' => $chartLabels, 'data' => $chartData, 'unit' => 'unit']);
                return $text . "\n<!--CHART:{$chart}-->";
            }

            // Otherwise, return overall sales summary
            $monthlySales = \App\Models\Sale::where('status', 'completed')
                ->where('created_at', '>=', $startTime)
                ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id));
            $mTotal = (clone $monthlySales)->sum('grand_total');
            $mCount = (clone $monthlySales)->count();
            $avg = $mCount > 0 ? $mTotal / $mCount : 0;
            $text = "Tentu. Selama **{$periodText}**, cabang {$branchName} telah berhasil mencetak omset sebesar **Rp " . number_format($mTotal, 0, ',', '.') . "** dari total {$mCount} transaksi yang sukses diselesaikan.\n\n";
            $text .= "📊 **Analisa:** Rata-rata nilai belanja per pelanggan (*basket size*) berada di angka Rp " . number_format($avg, 0, ',', '.') . ".\n";
            $text .= "💡 **Rekomendasi:** Jika angka rata-rata ini dirasa masih bisa dimaksimalkan, Anda bisa mencoba strategi *upselling* di kasir atau menawarkan promo *bundling* produk untuk mendongkrak nominal transaksi per pelanggan.";

            // Build daily breakdown chart
            $dailySales = \App\Models\Sale::where('status', 'completed')
                ->where('created_at', '>=', $startTime)
                ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
                ->selectRaw('DATE(created_at) as sale_date, SUM(grand_total) as daily_total')
                ->groupBy('sale_date')
                ->orderBy('sale_date')
                ->get();

            if ($dailySales->count() > 1) {
                $chartLabels = $dailySales->pluck('sale_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray();
                $chartData   = $dailySales->pluck('daily_total')->map(fn($v) => (int) $v)->toArray();
                $chartTitle  = 'Tren Penjualan Harian — ' . ($is30Days ? '30 Hari Terakhir' : now()->translatedFormat('F Y'));
                $chart = json_encode(['type' => 'line', 'title' => $chartTitle, 'labels' => $chartLabels, 'data' => $chartData, 'unit' => 'Rp', 'currency' => true]);
                return $text . "\n<!--CHART:{$chart}-->";
            }

            return $text;
        }

        if (str_contains($promptLower, 'terbaru') || str_contains($promptLower, 'terakhir') || str_contains($promptLower, 'riwayat')) {
            $recent = \App\Models\Sale::with(['customer', 'branch', 'payments'])
                ->where('status', 'completed')
                ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
                ->latest('created_at')
                ->limit(5)
                ->get();
            if ($recent->count() === 0) {
                return "Belum ada transaksi penjualan yang tercatat di sistem.";
            }
            $text = "Berikut adalah 5 transaksi penjualan terbaru di cabang {$branchName}:\n";
            foreach ($recent as $s) {
                $methods = $s->payments->pluck('method')->implode(', ') ?: 'N/A';
                $text .= "- Faktur **{$s->invoice_no}** | " . ($s->customer->name ?? 'Pelanggan Umum') . " | Rp " . number_format($s->grand_total, 0, ',', '.') . " ({$methods}) - {$s->created_at->format('d M Y H:i')}\n";
            }
            return $text;
        }

        $metrics = $this->salesAnalytics->getTodayMetrics($user->branch_id, $isAdminOrSupervisor);
        $bestSellers = $this->salesAnalytics->getBestSellers(3, $user->branch_id, $isAdminOrSupervisor);

        $growth = $metrics['revenue_growth_percent'];
        $trend = $growth >= 0 ? "positif" : "menurun";
        $sign = $growth >= 0 ? '+' : '';
        
        $text = "Tentu! Ini adalah laporan performa penjualan hari ini di cabang **{$branchName}**:\n\n";
        $text .= "Hari ini toko kita berhasil mengumpulkan total omset sebesar **Rp " . number_format($metrics['total_revenue'], 0, ',', '.') . "** yang didapat dari **{$metrics['total_transactions']}** transaksi sukses.\n\n";
        $text .= "Jika dibandingkan dengan kemarin di jam yang sama, tren penjualan kita sedang bergerak **{$trend}** sebesar **{$sign}{$growth}%**. ";

        $chartLabels = [];
        $chartData   = [];
        if ($bestSellers->count() > 0) {
            $topProduct = $bestSellers->first();
            $text .= "Berikut produk yang paling banyak dibeli hari ini:\n";
            foreach ($bestSellers as $item) {
                $text .= "- **{$item->product->name}** (terjual {$item->total_qty} unit)\n";
                $chartLabels[] = $item->product->name;
                $chartData[]   = (int) $item->total_qty;
            }
            $text .= "\n💡 **Tips:** Produk **{$topProduct->product->name}** sedang sangat diminati hari ini. Pastikan ketersediaan stoknya aman di rak depan ya, atau pasang di dekat kasir untuk memaksimalkan penjualannya.";
        } else {
            $text .= "\n💡 **Tips:** Belum ada data penjualan produk yang menonjol hari ini. Anda mungkin bisa menyemangati tim toko untuk lebih aktif menawarkan produk unggulan kepada pelanggan.";
        }
        if (!empty($chartData)) {
            $chart = json_encode(['type' => 'bar', 'title' => 'Produk Terlaris Hari Ini', 'labels' => $chartLabels, 'data' => $chartData, 'unit' => 'unit']);
            $text .= "\n<!--CHART:{$chart}-->";
        }
        return $text;
    }

    protected function isBranchPerformanceQuery(string $prompt): bool
    {
        return str_contains($prompt, 'cabang') || str_contains($prompt, 'outlet') || str_contains($prompt, 'toko');
    }

    protected function handleBranchPerformanceIntent(User $user, string $branchName, bool $isAdminOrSupervisor): string
    {
        if ($isAdminOrSupervisor) {
            $text = "Sebagai Administrator, berikut performa penjualan per cabang hari ini:\n";
            $branches = \App\Models\Branch::all();
            $chartLabels = [];
            $chartData   = [];
            foreach ($branches as $branch) {
                $m = $this->salesAnalytics->getTodayMetrics($branch->id, false);
                $text .= "- **{$branch->name}:** Rp " . number_format($m['total_revenue'], 0, ',', '.') . " ({$m['total_transactions']} transaksi)\n";
                $chartLabels[] = $branch->name;
                $chartData[]   = (int) $m['total_revenue'];
            }
            $chart = json_encode(['type' => 'bar', 'title' => 'Omset Per Cabang Hari Ini (Rp)', 'labels' => $chartLabels, 'data' => $chartData, 'unit' => 'Rp', 'currency' => true]);
            return $text . "\n<!--CHART:{$chart}-->";
        }
        $m = $this->salesAnalytics->getTodayMetrics($user->branch_id, false);
        return "Performa cabang Anda (**{$branchName}**) hari ini mencatat total penjualan Rp " . number_format($m['total_revenue'], 0, ',', '.') . " dari {$m['total_transactions']} transaksi.";
    }

    protected function isPromotionQuery(string $prompt): bool
    {
        return str_contains($prompt, 'promo') || str_contains($prompt, 'voucher') || str_contains($prompt, 'diskon');
    }

    protected function handlePromotionIntent(): string
    {
        $count = \App\Models\Voucher::where('is_active', true)->count();
        return "Saat ini ada {$count} program promosi / voucher aktif dalam sistem LAKUPOS. Anda dapat memantau efektivitas masing-masing promosi melalui menu Promosi di bilah sisi.";
    }

    protected function isFinanceQuery(string $prompt): bool
    {
        return str_contains($prompt, 'laba') || str_contains($prompt, 'rugi') || str_contains($prompt, 'profit') || str_contains($prompt, 'keuntungan') || str_contains($prompt, 'margin') || str_contains($prompt, 'metode pembayaran') || str_contains($prompt, 'bayar') || str_contains($prompt, 'payment') || str_contains($prompt, 'utang') || str_contains($prompt, 'supplier');
    }

    protected function handleFinanceIntent(string $promptLower, User $user, bool $isAdminOrSupervisor, string $branchName): string
    {
        if (str_contains($promptLower, 'utang') || str_contains($promptLower, 'supplier')) {
            $supplierDebt = \App\Models\Purchase::where('status', 'received')
                ->where('payment_status', '!=', 'paid')
                ->sum('amount_due');
            
            return "Informasi Utang saat ini:\n" .
                   "- **Total Utang ke Supplier:** Rp " . number_format($supplierDebt, 0, ',', '.') . "\n";
        }

        if (str_contains($promptLower, 'metode pembayaran') || str_contains($promptLower, 'bayar') || str_contains($promptLower, 'payment')) {
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            $paymentMethods = \App\Models\Payment::join('sales', 'payments.sale_id', '=', 'sales.id')
                ->where('sales.status', 'completed')
                ->whereBetween('sales.created_at', [$startOfMonth, $endOfMonth])
                ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('sales.branch_id', $user->branch_id))
                ->select('payments.method', \Illuminate\Support\Facades\DB::raw('COUNT(payments.id) as total_count'), \Illuminate\Support\Facades\DB::raw('SUM(payments.amount) as total_amount'))
                ->groupBy('payments.method')
                ->get();
            if ($paymentMethods->isEmpty()) {
                return "Belum ada data metode pembayaran bulan ini.";
            }
            $text = "Metode Pembayaran yang Digunakan Bulan Ini:\n";
            foreach ($paymentMethods as $pm) {
                $text .= "- **" . strip_tags($pm->method) . "**: {$pm->total_count} transaksi (Rp " . number_format($pm->total_amount, 0, ',', '.') . ")\n";
            }
            return $text;
        }

        $profitSummary = $this->profitAnalytics->getProfitSummary(now()->startOfMonth(), now()->endOfDay(), $isAdminOrSupervisor ? null : $user->branch_id);
        
        $grossSales = $profitSummary['total_revenue'] + $profitSummary['total_discounts'];
        $netRevenue = $profitSummary['total_revenue'];
        $cogs = $profitSummary['total_cogs'];
        $grossProfit = $profitSummary['gross_profit'];
        $margin = $profitSummary['profit_margin_percent'];

        $text = "Tentu! Ini adalah rangkuman laporan keuangan (Laba/Rugi) untuk cabang **{$branchName}** bulan ini:\n\n";
        $text .= "- **Total Penjualan Kotor (Gross Sales):** Rp " . number_format($grossSales, 0, ',', '.') . "\n";
        $text .= "- **Diskon yang Diberikan:** Rp " . number_format($profitSummary['total_discounts'], 0, ',', '.') . "\n";
        $text .= "- **Pendapatan Bersih (Net Revenue):** Rp " . number_format($netRevenue, 0, ',', '.') . " _(setelah dipotong diskon)_\n";
        $text .= "- **HPP (Harga Pokok Penjualan):** Rp " . number_format($cogs, 0, ',', '.') . "\n";
        $text .= "- **Laba Kotor (Gross Profit):** Rp " . number_format($grossProfit, 0, ',', '.') . " _(Pendapatan Bersih - HPP)_\n";
        $text .= "- **Margin Keuntungan:** **{$margin}%**\n\n";
        $text .= "💡 **Analisa:** Toko kita menghasilkan margin keuntungan kotor sebesar **{$margin}%** bulan ini. Anda dapat melacak rincian transaksi pengeluaran lainnya secara lengkap melalui menu **Laporan Keuangan**.";
        
        return $text;
    }

    protected function isCustomerQuery(string $prompt): bool
    {
        return str_contains($prompt, 'pelanggan') || str_contains($prompt, 'klien') || str_contains($prompt, 'customer') || str_contains($prompt, 'hutang') || str_contains($prompt, 'piutang');
    }

    protected function handleCustomerIntent(string $promptLower, User $user, bool $isAdminOrSupervisor, string $branchName): string
    {
        if (str_contains($promptLower, 'hutang') || str_contains($promptLower, 'piutang')) {
            $customerSummary = $this->customerAnalytics->getCustomerSummary($isAdminOrSupervisor ? null : $user->branch_id);
            return "Informasi Hutang/Piutang saat ini:\n" .
                   "- **Total Hutang Pelanggan (Piutang Toko):** Rp " . number_format($customerSummary['total_customer_debt'], 0, ',', '.') . "\n";
        }

        $totalCustomers = \App\Models\Customer::when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->whereHas('sales', fn($s) => $s->where('branch_id', $user->branch_id)))->count();
        $customerSummary = $this->customerAnalytics->getCustomerSummary($isAdminOrSupervisor ? null : $user->branch_id);
        $topCustomers = $this->customerAnalytics->getTopCustomersBySpend(5, $isAdminOrSupervisor ? null : $user->branch_id);
        
        $text = "👥 **Informasi Pelanggan (Cabang: {$branchName})**\n\n";
        $text .= "- **Total Pelanggan Terdaftar:** {$totalCustomers} orang\n";
        $text .= "- **Pelanggan Baru Bulan Ini:** {$customerSummary['new_customers_this_month']} orang\n\n";
        
        $chartLabels = [];
        $chartData   = [];

        if ($topCustomers->count() > 0) {
            $text .= "🏆 **Top Pelanggan Berbelanja Terbanyak:**\n";
            foreach ($topCustomers as $index => $c) {
                $num = $index + 1;
                $text .= "{$num}. **" . strip_tags($c->name) . "** — Total Belanja: **Rp " . number_format($c->total_spent, 0, ',', '.') . "**\n";
                $chartLabels[] = $c->name;
                $chartData[]   = (int) $c->total_spent;
            }
        } else {
            $text .= "Belum ada data transaksi pelanggan yang mencukupi.\n";
        }

        if (!empty($chartData)) {
            $chart = json_encode(['type' => 'bar', 'title' => 'Top Pelanggan Terbanyak Belanja (Rp)', 'labels' => $chartLabels, 'data' => $chartData, 'unit' => 'Rp', 'currency' => true]);
            $text .= "\n<!--CHART:{$chart}-->";
        }

        return $text;
    }

    protected function isCashierShiftQuery(string $prompt): bool
    {
        return str_contains($prompt, 'shift') || str_contains($prompt, 'kasir buka') || str_contains($prompt, 'kasir tutup') || str_contains($prompt, 'status kasir') || str_contains($prompt, 'laci uang') || str_contains($prompt, 'register');
    }

    protected function handleCashierShiftIntent(string $promptLower, User $user, bool $isAdminOrSupervisor, string $branchName): string
    {
        if (!$isAdminOrSupervisor) {
            // Kasir hanya bisa melihat status shift-nya sendiri
            $myActiveShift = \App\Models\CashRegister::where('user_id', $user->id)
                ->whereNull('closed_at')
                ->first();
            if ($myActiveShift) {
                return "Anda saat ini memiliki **Shift Aktif (Buka)** sejak **{$myActiveShift->opened_at->format('d M Y H:i')}** dengan saldo awal **Rp " . number_format($myActiveShift->opening_balance, 0, ',', '.') . "**. Jangan lupa melakukan tutup register di akhir giliran kerja Anda ya! 😊";
            }
            return "Anda saat ini **tidak memiliki shift aktif (Tutup)**. Silakan lakukan Buka Register terlebih dahulu di menu POS untuk memulai transaksi.";
        }

        // Cari tahu apakah user menyebutkan nama cabang tertentu di chat
        $allBranches = \App\Models\Branch::all();
        $targetBranch = null;
        foreach ($allBranches as $branch) {
            if (str_contains($promptLower, strtolower($branch->name))) {
                $targetBranch = $branch;
                break;
            }
        }

        // Tentukan branchId filter dan nama tampilan cabang
        $branchIdFilter = null;
        $displayName = "Semua Cabang";
        if ($targetBranch) {
            $branchIdFilter = $targetBranch->id;
            $displayName = "Cabang " . $targetBranch->name;
        } elseif (!$user->hasRole('admin') && $user->branch_id) {
            // Jika bukan admin (misal supervisor) dan tidak menyebutkan cabang lain, batasi ke cabangnya sendiri
            $branchIdFilter = $user->branch_id;
            $displayName = "Cabang " . $branchName;
        }

        $openShifts = \App\Models\CashRegister::with(['user', 'branch'])
            ->whereNull('closed_at')
            ->when($branchIdFilter, fn($q) => $q->where('branch_id', $branchIdFilter))
            ->get();

        $cashierUsers = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'cashier'))
            ->when($branchIdFilter, fn($q) => $q->where('branch_id', $branchIdFilter))
            ->get();

        $activeCashierIds = $openShifts->pluck('user_id')->toArray();

        $text = "🏪 **Status Operasional Shift Kasir ({$displayName})**\n\n";

        if ($openShifts->isNotEmpty()) {
            $text .= "🟢 **Kasir yang Sedang AKTIF / BUKA SHIFT (Laci Terbuka):**\n";
            foreach ($openShifts as $shift) {
                $text .= "- **{$shift->user->name}** (Cabang: {$shift->branch->name})\n";
                $text .= "  • Saldo Awal: **Rp " . number_format($shift->opening_balance, 0, ',', '.') . "**\n";
                $text .= "  • Buka Sejak: _" . $shift->opened_at->format('d M H:i') . "_\n";
                $text .= "[ACTION:close_register:register_id={$shift->id}:label=Tutup Register {$shift->user->name}]\n\n";
            }
        } else {
            $text .= "🟢 **Kasir yang Sedang AKTIF / BUKA SHIFT:**\n- _Saat ini tidak ada register laci kasir yang terbuka._\n\n";
        }

        $inactiveCashiers = $cashierUsers->filter(fn($u) => !in_array($u->id, $activeCashierIds));

        if ($inactiveCashiers->isNotEmpty()) {
            $text .= "🔴 **Kasir yang Sedang NON-AKTIF / SHIFT TUTUP (Laci Tertutup):**\n";
            foreach ($inactiveCashiers as $u) {
                $text .= "- **{$u->name}** (Cabang: " . ($u->branch->name ?? 'Semua') . ") — _Offline/Tutup_\n";
            }
        } else {
            $text .= "🔴 **Kasir yang Sedang NON-AKTIF:**\n- _Semua kasir terdaftar sedang aktif bertugas._\n";
        }

        return $text;
    }

    protected function isPurchaseQuery(string $prompt): bool
    {
        return str_contains($prompt, 'pembelian') || str_contains($prompt, 'purchase') || str_contains($prompt, 'po') || str_contains($prompt, 'supplier order');
    }

    protected function handlePurchaseIntent(string $promptLower, User $user, bool $isAdminOrSupervisor, string $branchName): string
    {
        $purchases = \App\Models\Purchase::with(['supplier', 'user'])
            ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
            ->latest('purchase_date')
            ->limit(5)
            ->get();

        if ($purchases->isEmpty()) {
            return "Belum ada transaksi pembelian (procurement/restock) yang tercatat di cabang **{$branchName}**.";
        }

        $text = "Tentu! Berikut adalah 5 transaksi pembelian (restock) terbaru di cabang **{$branchName}**:\n\n";
        foreach ($purchases as $p) {
            $statusLabels = [
                'paid' => 'Lunas 🟢',
                'partial' => 'Sebagian 🟡',
                'due' => 'Belum Lunas 🔴',
                'pending' => 'Tertunda ⏳',
            ];
            $status = $statusLabels[$p->payment_status] ?? strtoupper($p->payment_status);
            $date = \Carbon\Carbon::parse($p->purchase_date)->format('d M Y');
            
            $text .= "• **Faktur {$p->invoice_number}** ({$date})\n";
            $text .= "  - Supplier: **{$p->supplier->name}**\n";
            $text .= "  - Total Pembelian: **Rp " . number_format($p->total, 0, ',', '.') . "** | Status: **{$status}**\n";
            $text .= "  - Dicatat oleh: {$p->user->name}\n\n";
        }

        return $text;
    }

    protected function isKnowledgeQuery(string $prompt): bool
    {
        return str_contains($prompt, 'printer') || str_contains($prompt, 'kertas') || str_contains($prompt, 'thermal') ||
               str_contains($prompt, 'retur') || str_contains($prompt, 'kembali barang') || str_contains($prompt, 'tukar barang') ||
               str_contains($prompt, 'kontrak') || str_contains($prompt, 'tempo supplier') || str_contains($prompt, 'ongkir');
    }

    protected function handleKnowledgeIntent(string $promptLower): string
    {
        $kbPath = base_path('docs/knowledge');
        
        if (str_contains($promptLower, 'printer') || str_contains($promptLower, 'kertas') || str_contains($promptLower, 'thermal')) {
            $filePath = $kbPath . '/printer_kasir.md';
            if (file_exists($filePath)) {
                return file_get_contents($filePath);
            }
        }

        if (str_contains($promptLower, 'retur') || str_contains($promptLower, 'kembali barang') || str_contains($promptLower, 'tukar barang')) {
            $filePath = $kbPath . '/kebijakan_retur.md';
            if (file_exists($filePath)) {
                return file_get_contents($filePath);
            }
        }

        if (str_contains($promptLower, 'kontrak') || str_contains($promptLower, 'tempo supplier') || str_contains($promptLower, 'ongkir')) {
            $filePath = $kbPath . '/kontrak_supplier.md';
            if (file_exists($filePath)) {
                return file_get_contents($filePath);
            }
        }

        return "Maaf, saya tidak menemukan panduan resmi terkait topik tersebut dalam basis pengetahuan LAKUPOS.";
    }

    // ─── PREDICTION HANDLER ───────────────────────────────────────────────
    protected function isPredictionQuery(string $prompt): bool
    {
        return str_contains($prompt, 'prediksi') ||
               str_contains($prompt, 'forecast') ||
               str_contains($prompt, 'perkiraan') ||
               str_contains($prompt, 'akan habis') ||
               str_contains($prompt, 'bulan depan') ||
               str_contains($prompt, 'kedepan') ||
               str_contains($prompt, 'ke depan') ||
               str_contains($prompt, 'tren') ||
               str_contains($prompt, 'proyeksi');
    }

    protected function handlePredictionIntent(string $promptLower, User $user, bool $isAdminOrSupervisor, string $branchName): string
    {
        // ── Prediksi stok akan habis ──────────────────────────────────
        $isStockPrediction = str_contains($promptLower, 'stok') ||
                             str_contains($promptLower, 'barang') ||
                             str_contains($promptLower, 'habis') ||
                             str_contains($promptLower, 'produk') ||
                             str_contains($promptLower, 'akan habis');

        $isSalesPrediction = str_contains($promptLower, 'omset') ||
                             str_contains($promptLower, 'penjualan') ||
                             str_contains($promptLower, 'pendapatan') ||
                             str_contains($promptLower, 'bulan depan');

        // Stock depletion forecast
        if ($isStockPrediction) {
            $thirtyDaysAgo = now()->subDays(30);
            $products = \Illuminate\Support\Facades\DB::table('products')
                ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.min_stock',
                    \Illuminate\Support\Facades\DB::raw('SUM(product_stocks.quantity) as total_stock'),
                    \Illuminate\Support\Facades\DB::raw('(SELECT COALESCE(SUM(si.quantity), 0)
                        FROM sale_items si
                        INNER JOIN sales s ON si.sale_id = s.id
                        WHERE s.status = \'completed\'
                          AND s.created_at >= \'' . $thirtyDaysAgo->toDateTimeString() . '\'
                          ' . (!$isAdminOrSupervisor && $user->branch_id ? 'AND s.branch_id = ' . (int)$user->branch_id : '') . '
                          AND si.product_id = products.id) as sold_last_30d')
                )
                ->groupBy('products.id', 'products.name', 'products.min_stock')
                ->having('total_stock', '>', 0)
                ->orderByDesc('total_stock')
                ->limit(10)
                ->get();

            if ($products->isEmpty()) {
                return "Tidak ada data produk dengan stok tersedia untuk dianalisis.";
            }

            $text = "🔮 **Prediksi Stok Produk — Cabang {$branchName}**\n";
            $text .= "_(Berdasarkan rata-rata penjualan harian 30 hari terakhir)_\n\n";

            $kritis = []; $perhatian = []; $aman = [];
            $chartLabels = []; $chartData = [];

            foreach ($products as $prod) {
                $avgDaily = $prod->sold_last_30d > 0 ? $prod->sold_last_30d / 30 : 0;
                if ($avgDaily > 0) {
                    $daysLeft      = (int) floor($prod->total_stock / $avgDaily);
                    $emptyDate     = now()->addDays($daysLeft)->translatedFormat('d F Y');
                    $forecastMonth = (int) round($avgDaily * 30);
                    if ($daysLeft <= 7)       $kritis[]    = compact('prod', 'daysLeft', 'emptyDate', 'forecastMonth', 'avgDaily');
                    elseif ($daysLeft <= 30)  $perhatian[] = compact('prod', 'daysLeft', 'emptyDate', 'forecastMonth', 'avgDaily');
                    else                      $aman[]      = compact('prod', 'daysLeft', 'emptyDate', 'forecastMonth', 'avgDaily');
                    $chartLabels[] = $prod->name;
                    $chartData[]   = $daysLeft;
                } else {
                    $aman[] = ['prod' => $prod, 'daysLeft' => null, 'emptyDate' => null, 'forecastMonth' => 0, 'avgDaily' => 0];
                }
            }

            if (!empty($kritis)) {
                $text .= "🔴 **KRITIS — Habis dalam 7 hari:**\n";
                foreach ($kritis as $r) {
                    $text .= "- **{$r['prod']->name}**: Stok {$r['prod']->total_stock} unit | Habis ~{$r['daysLeft']} hari lagi ({$r['emptyDate']}) | Prediksi terjual bulan depan: ~{$r['forecastMonth']} unit\n";
                }
            }
            if (!empty($perhatian)) {
                $text .= "\n🟡 **PERLU PERHATIAN — Habis dalam 30 hari:**\n";
                foreach ($perhatian as $r) {
                    $text .= "- **{$r['prod']->name}**: Stok {$r['prod']->total_stock} unit | Habis ~{$r['daysLeft']} hari lagi ({$r['emptyDate']}) | Prediksi terjual bulan depan: ~{$r['forecastMonth']} unit\n";
                }
            }
            if (!empty($aman)) {
                $text .= "\n🟢 **AMAN — Stok cukup lebih dari 30 hari:**\n";
                foreach (array_slice($aman, 0, 5) as $r) {
                    if ($r['daysLeft'] !== null) {
                        $text .= "- **{$r['prod']->name}**: Stok {$r['prod']->total_stock} unit | Habis ~{$r['daysLeft']} hari lagi\n";
                    } else {
                        $text .= "- **{$r['prod']->name}**: Stok {$r['prod']->total_stock} unit | Tidak ada penjualan 30 hari terakhir\n";
                    }
                }
            }

            $text .= "\n⚠️ _Prediksi ini bersifat estimasi berdasarkan rata-rata penjualan harian. Faktor promosi, musiman, atau perubahan permintaan belum diperhitungkan._";

            if (count($chartLabels) > 1) {
                $chart = json_encode(['type' => 'bar', 'title' => 'Estimasi Hari Stok Tersisa', 'labels' => $chartLabels, 'data' => $chartData, 'unit' => 'hari']);
                return $text . "\n<!--CHART:{$chart}-->";
            }
            return $text;
        }

        // ── Prediksi omset bulan depan (WMA 3 bulan) ─────────────────
        $monthlyRevenues = [];
        for ($i = 2; $i >= 0; $i--) {
            $mStart = now()->subMonths($i)->startOfMonth();
            $mEnd   = now()->subMonths($i)->endOfMonth();
            $rev    = (float) \App\Models\Sale::where('status', 'completed')
                ->whereBetween('created_at', [$mStart, $mEnd])
                ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
                ->sum('grand_total');
            $monthlyRevenues[] = ['month' => $mStart->translatedFormat('F Y'), 'revenue' => $rev];
        }

        $weights     = [1, 2, 3];
        $weightedRev = 0;
        foreach ($monthlyRevenues as $idx => $m) {
            $weightedRev += $m['revenue'] * $weights[$idx];
        }
        $predicted = round($weightedRev / array_sum($weights));

        $revsOnly    = array_column($monthlyRevenues, 'revenue');
        $trendChange = ($revsOnly[0] > 0) ? round((($revsOnly[2] - $revsOnly[0]) / $revsOnly[0]) * 100, 1) : 0;
        $trendLabel  = $trendChange >= 0 ? "📈 naik {$trendChange}%" : "📉 turun " . abs($trendChange) . "%";
        $nextMonth   = now()->addMonth()->translatedFormat('F Y');

        $text = "🔮 **Prediksi Omset Bulan Depan ({$nextMonth}) — Cabang {$branchName}**\n\n";
        $text .= "**Data historis 3 bulan terakhir:**\n";
        foreach ($monthlyRevenues as $m) {
            $text .= "- {$m['month']}: **Rp " . number_format($m['revenue'], 0, ',', '.') . "**\n";
        }
        $text .= "\n**Tren 3 bulan:** {$trendLabel}\n";
        $text .= "\n**Prediksi omset {$nextMonth}: Rp " . number_format($predicted, 0, ',', '.') . "**\n";
        $text .= "_(Metode: Weighted Moving Average 3 bulan — bobot bulan terbaru lebih tinggi)_\n";
        $text .= "\n⚠️ _Prediksi ini bersifat estimasi. Faktor eksternal seperti promosi, hari libur, atau perubahan pasar belum diperhitungkan._";

        $chartLabels = array_column($monthlyRevenues, 'month');
        $chartLabels[] = "Prediksi {$nextMonth}";
        $chartData   = array_column($monthlyRevenues, 'revenue');
        $chartData[] = $predicted;
        $chart = json_encode(['type' => 'bar', 'title' => 'Tren & Prediksi Omset (Rp)', 'labels' => $chartLabels, 'data' => $chartData, 'unit' => 'Rp', 'currency' => true]);
        return $text . "\n<!--CHART:{$chart}-->";
    }

    // ─── BUNDLING HANDLER ─────────────────────────────────────────────────
    protected function isBundlingQuery(string $prompt): bool
    {
        return str_contains($prompt, 'bundling') ||
               str_contains($prompt, 'paket') ||
               str_contains($prompt, 'sering dibeli bersama') ||
               str_contains($prompt, 'kombinasi produk');
    }

    protected function handleBundlingIntent(User $user, bool $isAdminOrSupervisor, string $branchName): string
    {
        // Cari pasangan produk yang sering muncul dalam transaksi yang sama
        $startOfMonth = now()->subDays(60);
        $pairData = \Illuminate\Support\Facades\DB::table('sale_items as a')
            ->join('sale_items as b', function ($join) {
                $join->on('a.sale_id', '=', 'b.sale_id')
                     ->whereColumn('a.product_id', '<', 'b.product_id');
            })
            ->join('sales', 'a.sale_id', '=', 'sales.id')
            ->join('products as pa', 'a.product_id', '=', 'pa.id')
            ->join('products as pb', 'b.product_id', '=', 'pb.id')
            ->where('sales.status', 'completed')
            ->where('sales.created_at', '>=', $startOfMonth)
            ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('sales.branch_id', $user->branch_id))
            ->select(
                'pa.name as product_a',
                'pb.name as product_b',
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as freq')
            )
            ->groupBy('a.product_id', 'b.product_id', 'pa.name', 'pb.name')
            ->orderByDesc('freq')
            ->limit(7)
            ->get();

        if ($pairData->isEmpty()) {
            return "Belum ada cukup data transaksi untuk mengidentifikasi kombinasi produk yang sering dibeli bersama di cabang **{$branchName}** (minimal perlu beberapa transaksi dengan 2+ produk).";
        }

        $text = "🛍️ **Rekomendasi Bundling — Produk Sering Dibeli Bersama di {$branchName}**\n";
        $text .= "_(Berdasarkan data 60 hari terakhir)_\n\n";
        foreach ($pairData as $i => $pair) {
            $no = $i + 1;
            $text .= "{$no}. **{$pair->product_a}** + **{$pair->product_b}** → dibeli bersama **{$pair->freq}x**\n";
        }
        $text .= "\n💡 **Rekomendasi:**\n";
        $text .= "- Buat paket bundling untuk pasangan produk paling sering (No. 1-3) dengan harga sedikit lebih hemat\n";
        $text .= "- Tempatkan produk-produk ini berdekatan di rak untuk mendorong pembelian spontan\n";
        $text .= "- Tawarkan promo *buy 1 get discount* untuk kombinasi ini saat jam sibuk";
        return $text;
    }

    protected function getDefaultMenu(): string
    {
        return "👋 **Halo! Saya Asisten AI LAKUPOS.**\n\n"
             . "Senang bisa menyapa Anda! Saya diciptakan oleh rekan-rekan mahasiswa Informatika Newsem Aborneo khusus untuk menemani dan membantu Anda memantau perkembangan bisnis dengan mudah.\n\n"
             . "💡 **Apa yang ingin Anda ketahui hari ini?** Anda bisa mengetik pertanyaan langsung di kolom chat (manfaatkan juga saran autocomplete yang muncul saat Anda mengetik), atau klik/pilih contoh pertanyaan analisis populer di bawah ini:\n\n"
             . "| 📁 Kategori | 🔍 Contoh Pertanyaan Analisis |\n"
             . "| :--- | :--- |\n"
             . "| **📦 Stok & Produk** | • `Produk mana yang stoknya kritis atau hampir habis?`<br>• `Produk apa yang tidak laku 30 hari terakhir (dead stock)?`<br>• `Berapa total produk aktif saat ini?` |\n"
             . "| **🔮 Prediksi & Forecast** | • `Prediksi omset bulan depan berdasarkan tren 3 bulan terakhir`<br>• `Produk mana yang stoknya diprediksi akan habis paling cepat?`<br>• `Apa tren penjualan dalam 3 bulan terakhir?` |\n"
             . "| **💰 Penjualan & Keuangan** | • `Berapa omset hari ini?`<br>• `Berapa laba kotor bulan ini?`<br>• `Metode pembayaran apa yang paling sering digunakan?`<br>• `Berapa HPP (Harga Pokok Penjualan) bulan ini?` |\n"
             . "| **👥 Pelanggan & Supplier** | • `Siapa pelanggan yang paling banyak berbelanja?`<br>• `Berapa total utang toko ke supplier?` |\n"
             . "| **🛍️ Bundling Produk** | • `Berikan rekomendasi bundling produk yang sering dibeli bersama` |";
    }
}
