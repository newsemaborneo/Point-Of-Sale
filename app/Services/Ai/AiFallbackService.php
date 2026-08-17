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

        if ($this->isComparisonQuery($promptLower)) {
            return $this->handleComparisonIntent($promptLower, $user, $isAdminOrSupervisor, $branchName);
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

        return $this->getDefaultMenu();
    }

    protected function isInterceptQuery(string $prompt): bool
    {
        return str_contains($prompt, 'strategi') || str_contains($prompt, 'rekomendasi') || str_contains($prompt, 'saran') || str_contains($prompt, 'halu');
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
                now()->subMonth()->translatedFormat('MMMM') => now()->subMonth()->month,
                now()->translatedFormat('MMMM')             => now()->month,
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
        $text .= "- **" . ucfirst($names[0]) . ":** Rp " . number_format($totalA, 0, ',', '.') . "\n";
        $text .= "- **" . ucfirst($names[1]) . ":** Rp " . number_format($totalB, 0, ',', '.') . "\n";
        $text .= "\n📈 Omset " . ucfirst($names[1]) . " **{$trend}** dibandingkan " . ucfirst($names[0]) . ".";

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
        return str_contains($prompt, 'stok') || str_contains($prompt, 'barang') || str_contains($prompt, 'produk') || str_contains($prompt, 'habis') || str_contains($prompt, 'limit') || str_contains($prompt, 'mati') || str_contains($prompt, 'tidak laku') || str_contains($prompt, 'dead stock');
    }

    protected function handleInventoryIntent(string $promptLower, User $user, bool $isAdminOrSupervisor, string $branchName): string
    {
        if (str_contains($promptLower, 'dead stock') || str_contains($promptLower, 'tidak laku') || str_contains($promptLower, 'mati')) {
            $thirtyDaysAgo = now()->subDays(30);
            $deadStockItems = \App\Models\Product::whereNotIn('id', function($q) use ($thirtyDaysAgo, $user, $isAdminOrSupervisor) {
                $q->select('product_id')->from('sale_items')->join('sales', 'sale_items.sale_id', '=', 'sales.id')->where('sales.created_at', '>=', $thirtyDaysAgo);
                if (!$isAdminOrSupervisor && $user->branch_id) {
                    $q->where('sales.branch_id', $user->branch_id);
                }
            })->limit(5)->get();

            if ($deadStockItems->isEmpty()) {
                return "Kabar baik! Tidak ada produk dead-stock di cabang {$branchName} (semua produk laku terjual dalam 30 hari terakhir).";
            }

            $text = "Berdasarkan data 30 hari terakhir, berikut adalah produk dead-stock (tidak laku) di {$branchName}:\n";
            foreach ($deadStockItems as $p) {
                $text .= "- **{$p->name}** (SKU: {$p->sku})\n";
            }
            $text .= "\n💡 **Rekomendasi:** Anda bisa mempertimbangkan promo diskon atau paket *bundling* untuk mencairkan produk ini menjadi uang tunai kembali.";
            return $text;
        }

        if (str_contains($promptLower, 'lihat') || str_contains($promptLower, 'daftar') || str_contains($promptLower, 'tampilkan') || str_contains($promptLower, 'apa saja')) {
            $summary = $this->inventoryAnalytics->getInventoryHealthSummary($user->branch_id, $isAdminOrSupervisor);
            return "Saat ini di cabang {$branchName} terdapat {$summary['total_active_products']} produk aktif ({$summary['healthy_stock_count']} stok dalam tingkat aman, {$summary['low_stock_count']} produk stok menipis, dan {$summary['out_of_stock_count']} produk kehabisan stok). Anda dapat melihat daftar produk selengkapnya melalui menu **Produk** pada bilah navigasi.";
        }

        $criticalStock = $this->inventoryAnalytics->getCriticalStockAnalysis($user->branch_id, $isAdminOrSupervisor);
        if (count($criticalStock) > 0) {
            $text = "Tentu, saya bantu analisanya. Berdasarkan data pantauan stok di cabang {$branchName}, terdapat beberapa produk yang stoknya sudah menipis dan perlu segera Anda perhatikan:\n\n";
            $chartLabels = [];
            $chartData   = [];
            foreach (array_slice($criticalStock, 0, 5) as $product) {
                $daysText = $product['estimated_days_left'] !== null ? "diperkirakan habis dalam {$product['estimated_days_left']} hari" : "berpotensi habis sangat segera";
                $text .= "- **{$product['name']}**: Tersisa {$product['current_stock']} unit ({$daysText})\n";
                $chartLabels[] = $product['name'];
                $chartData[]   = (int) $product['current_stock'];
            }
            $text .= "\n💡 **Rekomendasi:** Sebaiknya Anda segera membuat *Purchase Order* (PO) kepada supplier terkait sebelum stok benar-benar kosong, untuk menghindari potensi hilangnya omset dari pelanggan yang mencari produk tersebut.";
            $chart = json_encode(['type' => 'bar', 'title' => 'Stok Kritis (unit tersisa)', 'labels' => $chartLabels, 'data' => $chartData, 'unit' => 'unit', 'color' => 'danger']);
            return $text . "\n<!--CHART:{$chart}-->";
        }
        return "Kabar baik! Seluruh stok produk di cabang {$branchName} saat ini dalam kondisi aman dan berada di atas batas safety stock.";
    }

    protected function isSalesQuery(string $prompt): bool
    {
        if (str_contains($prompt, 'tidak laku') || str_contains($prompt, 'dead stock')) {
            return false;
        }
        return str_contains($prompt, 'penjualan') || str_contains($prompt, 'omset') || str_contains($prompt, 'transaksi') || str_contains($prompt, 'pendapatan') || str_contains($prompt, 'laku') || str_contains($prompt, 'terlaris') || str_contains($prompt, 'omzet') || str_contains($prompt, 'kategori') || str_contains($prompt, 'jam sibuk') || str_contains($prompt, 'ramai') || str_contains($prompt, 'peak hour');
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

        if (str_contains($promptLower, 'jam sibuk') || str_contains($promptLower, 'ramai') || str_contains($promptLower, 'peak hour')) {
            $peakHours = \App\Models\Sale::where('status', 'completed')
                ->whereDate('created_at', now()->toDateString())
                ->when(!$isAdminOrSupervisor && $user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
                ->select(\Illuminate\Support\Facades\DB::raw('HOUR(created_at) as hour'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_count'))
                ->groupBy('hour')
                ->orderByDesc('total_count')
                ->limit(3)
                ->get();
            if ($peakHours->isEmpty()) {
                return "Belum ada transaksi yang cukup hari ini untuk menentukan jam sibuk.";
            }
            $text = "Berikut adalah 3 Jam Sibuk (Peak Hours) hari ini di {$branchName}:\n";
            $chartLabels = [];
            $chartData   = [];
            foreach ($peakHours as $h) {
                $hourLabel = str_pad($h->hour, 2, '0', STR_PAD_LEFT) . ':00';
                $text .= "- **Jam " . str_pad($h->hour, 2, '0', STR_PAD_LEFT) . ":00 - " . str_pad($h->hour, 2, '0', STR_PAD_LEFT) . ":59** ({$h->total_count} transaksi)\n";
                $chartLabels[] = $hourLabel;
                $chartData[]   = (int) $h->total_count;
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
            if (str_contains($promptLower, 'terlaris') || str_contains($promptLower, 'laku') || str_contains($promptLower, 'produk')) {
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
        
        $text = "Baik, ini hasil analisa performa penjualan hari ini untuk cabang {$branchName}:\n\n";
        $text .= "Hari ini Anda telah mengumpulkan omset sebesar **Rp " . number_format($metrics['total_revenue'], 0, ',', '.') . "** dari {$metrics['total_transactions']} transaksi. ";
        $text .= "Tren hari ini menunjukkan pergerakan yang **{$trend}** dengan selisih {$sign}{$growth}% dibandingkan performa omset kemarin.\n\n";

        $chartLabels = [];
        $chartData   = [];
        if ($bestSellers->count() > 0) {
            $topProduct = $bestSellers->first();
            $text .= "🏆 **Produk Terlaris:**\n";
            foreach ($bestSellers as $item) {
                $text .= "- {$item->product->name} ({$item->total_qty} unit terjual)\n";
                $chartLabels[] = $item->product->name;
                $chartData[]   = (int) $item->total_qty;
            }
            $text .= "\n💡 **Rekomendasi:** Produk **{$topProduct->product->name}** sedang sangat diminati hari ini. Pastikan ketersediaan stoknya aman di rak depan, atau gunakan daya tariknya untuk mempromosikan barang lain yang kurang laku (strategi penempatan berdampingan).";
        } else {
            $text .= "\n💡 **Rekomendasi:** Belum ada data penjualan produk yang menonjol hari ini. Anda mungkin bisa mengingatkan tim toko untuk lebih aktif menawarkan produk unggulan kepada pelanggan yang datang.";
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
        return str_contains($prompt, 'laba') || str_contains($prompt, 'rugi') || str_contains($prompt, 'profit') || str_contains($prompt, 'keuntungan') || str_contains($prompt, 'margin') || str_contains($prompt, 'metode pembayaran') || str_contains($prompt, 'bayar') || str_contains($prompt, 'payment');
    }

    protected function handleFinanceIntent(string $promptLower, User $user, bool $isAdminOrSupervisor, string $branchName): string
    {
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
        return "Berikut Laporan Keuangan Laba/Rugi bulan ini:\n" .
               "- **Pendapatan Kotor (Revenue):** Rp " . number_format($profitSummary['total_revenue'], 0, ',', '.') . "\n" .
               "- **HPP (COGS):** Rp " . number_format($profitSummary['total_cogs'], 0, ',', '.') . "\n" .
               "- **Diskon Diberikan:** Rp " . number_format($profitSummary['total_discounts'], 0, ',', '.') . "\n" .
               "- **Laba Kotor (Gross Profit):** Rp " . number_format($profitSummary['gross_profit'], 0, ',', '.') . "\n" .
               "- **Margin Keuntungan:** {$profitSummary['profit_margin_percent']}%\n";
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
        
        $text = "👥 **Informasi Pelanggan (Cabang: {$branchName}):**\n\n";
        $text .= "- **Total Pelanggan Terdaftar:** {$totalCustomers} orang\n";
        $text .= "- **Pelanggan Baru Bulan Ini:** {$customerSummary['new_customers_this_month']} orang\n\n";
        
        if ($topCustomers->count() > 0) {
            $text .= "🏆 **Top 5 Pelanggan Berbelanja Terbanyak:**\n";
            foreach ($topCustomers as $c) {
                $text .= "> **" . strip_tags($c->name) . "** (Total Belanja: Rp " . number_format($c->total_spent, 0, ',', '.') . ")\n";
            }
        } else {
            $text .= "Belum ada data transaksi pelanggan yang mencukupi.\n";
        }
        return $text;
    }

    protected function getDefaultMenu(): string
    {
        return "👋 **Halo! Saya asisten AI LAKUPOS.**\n"
             . "Saya dibuat dan diciptakan oleh tim pengembangan Newsem Aborneo, Mahasiswa Informatika, untuk membantu Anda menganalisis metrik bisnis dengan cepat.\n\n"
             . "💡 *Ketik salah satu kata kunci di bawah ini:*\n\n"
             . "📦 **Stok & Produk**\n> `cek stok barang`, `produk habis`, `daftar produk`, `produk tidak laku`\n\n"
             . "💰 **Penjualan & Keuangan**\n> `omset hari ini`, `penjualan bulan ini`, `produk terlaris`, `kategori terlaris`, `laba rugi`, `hutang pelanggan`, `metode pembayaran`\n\n"
             . "👥 **Pelanggan**\n> `jumlah pelanggan`, `daftar pelanggan aktif`, `pelanggan baru bulan ini`\n\n"
             . "🏪 **Performa & Operasional**\n> `performa cabang`, `jam sibuk hari ini`\n\n"
             . "🎟️ **Promosi & Diskon**\n> `voucher aktif`, `promo diskon`";
    }
}
