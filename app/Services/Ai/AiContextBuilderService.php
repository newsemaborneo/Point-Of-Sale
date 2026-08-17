<?php

namespace App\Services\Ai;

use App\Models\User;
use App\Services\Analytics\SalesAnalyticsService;
use App\Services\Analytics\InventoryAnalyticsService;
use App\Services\Analytics\ProfitAnalyticsService;
use App\Services\Analytics\CustomerAnalyticsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AiContextBuilderService
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

    public function build(User $user): string
    {
        return Cache::remember("ai_system_context_v2_{$user->id}", now()->addMinutes(5), function () use ($user) {
            $userBranchId = $user->branch_id;
            $isAdminOrSupervisor = $user->hasRole('admin') || $user->hasRole('supervisor');

            // 1. General System Metadata
            $totalBranches = \App\Models\Branch::count();
            $branchesList = \App\Models\Branch::when(!$isAdminOrSupervisor && $userBranchId, fn($q) => $q->where('id', $userBranchId))
                ->get()
                ->map(fn($b) => "- ID: {$b->id}, Nama: " . strip_tags($b->name) . ", Kode: " . strip_tags($b->code))
                ->implode("\n");
            $totalUsers = \App\Models\User::when(!$isAdminOrSupervisor && $userBranchId, fn($q) => $q->where('branch_id', $userBranchId))->count();
            $userRoles = \App\Models\User::with('role')
                ->when(!$isAdminOrSupervisor && $userBranchId, fn($q) => $q->where('branch_id', $userBranchId))
                ->get()
                ->groupBy('role.name')
                ->map(fn($g) => $g->count());
            $rolesFormatted = $userRoles->map(fn($count, $role) => "  * {$role}: {$count} user")->implode("\n");

            // 2. Customers & Suppliers
            $totalCustomers = \App\Models\Customer::when(!$isAdminOrSupervisor && $userBranchId, fn($q) => $q->whereHas('sales', fn($s) => $s->where('branch_id', $userBranchId)))->count();
            $customerSummary = $this->customerAnalytics->getCustomerSummary(!$isAdminOrSupervisor ? $userBranchId : null);
            $topCustomers = $this->customerAnalytics->getTopCustomersBySpend(5, !$isAdminOrSupervisor ? $userBranchId : null);
            $topCustomersFormatted = $topCustomers->map(fn($c) => "  * " . strip_tags($c->name) . " (Email: " . strip_tags($c->email) . ", Belanja: Rp " . number_format($c->total_spent, 0, ',', '.') . ", Transaksi: {$c->total_orders})")->implode("\n");
            if (empty($topCustomersFormatted)) {
                $topCustomersFormatted = "  * Belum ada data pelanggan berbelanja";
            }

            $totalSuppliers = \App\Models\Supplier::when(!$isAdminOrSupervisor && $userBranchId, fn($q) => $q->whereHas('purchases', fn($p) => $p->where('branch_id', $userBranchId)))->count();
            $totalSupplierDebt = \App\Models\SupplierDebt::where('status', '!=', 'paid')
                ->when(!$isAdminOrSupervisor && $userBranchId, fn($q) => $q->whereHas('purchase', fn($p) => $p->where('branch_id', $userBranchId)))
                ->sum(DB::raw('amount - paid_amount'));

            // 3. Products & Stock
            $totalProducts = \App\Models\Product::count();
            $activeProducts = \App\Models\Product::where('is_active', true)->count();
            $stockSummary = $this->inventoryAnalytics->getInventoryHealthSummary($userBranchId, $isAdminOrSupervisor);
            $criticalStock = $this->inventoryAnalytics->getCriticalStockAnalysis($userBranchId, $isAdminOrSupervisor);
            $criticalStockFormatted = collect($criticalStock)->map(fn($item) => "  * " . strip_tags($item['name']) . " (SKU: " . strip_tags($item['sku']) . ", Sisa: {$item['current_stock']} unit, Min: {$item['min_stock']}, Proyeksi Habis: " . ($item['estimated_days_left'] !== null ? $item['estimated_days_left'] . " hari" : "segera") . ", Supplier: " . strip_tags($item['supplier_name']) . ")")->implode("\n");
            if (empty($criticalStockFormatted)) {
                $criticalStockFormatted = "  * Stok aman (0 produk kritis)";
            }

            // 4. Sales Metrics (Today)
            $salesMetrics = $this->salesAnalytics->getTodayMetrics($userBranchId, $isAdminOrSupervisor);
            $bestSellers = $this->salesAnalytics->getBestSellers(5, $userBranchId, $isAdminOrSupervisor);
            $bestSellersFormatted = $bestSellers->map(fn($item) => "  * " . strip_tags($item->product->name) . " (SKU: " . strip_tags($item->product->sku) . ", Terjual: {$item->total_qty} unit, Subtotal: Rp " . number_format($item->total_revenue, 0, ',', '.') . ")")->implode("\n");
            if (empty($bestSellersFormatted)) {
                $bestSellersFormatted = "  * Belum ada penjualan terlaris hari ini";
            }

            // 5. Monthly Sales Report (Bulan Ini vs Bulan Lalu)
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfDay();
            $monthlySalesQuery = \App\Models\Sale::where('status', 'completed')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            if (!$isAdminOrSupervisor && $userBranchId) {
                $monthlySalesQuery->where('branch_id', $userBranchId);
            }
            $monthlyTotalSales = (clone $monthlySalesQuery)->sum('grand_total');
            $monthlyTotalTransactions = (clone $monthlySalesQuery)->count();
            $monthlyAvgTransaction = $monthlyTotalTransactions > 0 ? $monthlyTotalSales / $monthlyTotalTransactions : 0;

            $startOfLastMonth = now()->subMonth()->startOfMonth();
            $endOfLastMonth = now()->subMonth()->endOfMonth();
            $lastMonthSalesQuery = \App\Models\Sale::where('status', 'completed')
                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);
            if (!$isAdminOrSupervisor && $userBranchId) {
                $lastMonthSalesQuery->where('branch_id', $userBranchId);
            }
            $lastMonthTotalSales = $lastMonthSalesQuery->sum('grand_total');
            $monthlySalesGrowth = $lastMonthTotalSales > 0
                ? round((($monthlyTotalSales - $lastMonthTotalSales) / $lastMonthTotalSales) * 100, 1)
                : ($monthlyTotalSales > 0 ? 100 : 0);

            // Breakdown Metode Pembayaran Bulan Ini
            $paymentMethods = \App\Models\Payment::join('sales', 'payments.sale_id', '=', 'sales.id')
                ->where('sales.status', 'completed')
                ->whereBetween('sales.created_at', [$startOfMonth, $endOfMonth])
                ->when(!$isAdminOrSupervisor && $userBranchId, fn($q) => $q->where('sales.branch_id', $userBranchId))
                ->select('payments.method', DB::raw('COUNT(payments.id) as total_count'), DB::raw('SUM(payments.amount) as total_amount'))
                ->groupBy('payments.method')
                ->get()
                ->map(fn($pm) => "  * " . strip_tags($pm->method) . ": " . number_format($pm->total_count) . " transaksi (Rp " . number_format($pm->total_amount, 0, ',', '.') . ")")
                ->implode("\n");
            if (empty($paymentMethods)) {
                $paymentMethods = "  * Belum ada data transaksi bulan ini";
            }

            // Breakdown Penjualan per Cabang Bulan Ini (Untuk Admin/Supervisor)
            $branchSalesBreakdown = "";
            if ($isAdminOrSupervisor) {
                $branchSales = \App\Models\Sale::where('status', 'completed')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->with('branch')
                    ->select('branch_id', DB::raw('COUNT(*) as total_count'), DB::raw('SUM(grand_total) as total_amount'))
                    ->groupBy('branch_id')
                    ->get()
                    ->map(fn($bs) => "  * " . strip_tags($bs->branch->name ?? 'Tanpa Cabang') . ": Rp " . number_format($bs->total_amount, 0, ',', '.') . " ({$bs->total_count} transaksi)")
                    ->implode("\n");
                $branchSalesBreakdown = "Breakdown Penjualan Per Cabang Bulan Ini:\n" . ($branchSales ?: "  * Belum ada data penjualan cabang bulan ini\n") . "\n";
            }

            // 6. Current Month Profit & Loss
            $profitSummary = $this->profitAnalytics->getProfitSummary(now()->startOfMonth(), now()->endOfDay(), $isAdminOrSupervisor ? null : $userBranchId);

            // 7. Recent 5 Sales Transactions
            $recentSalesQuery = \App\Models\Sale::with(['customer', 'branch', 'payments'])
                ->where('status', 'completed');
            if (!$isAdminOrSupervisor && $userBranchId) {
                $recentSalesQuery->where('branch_id', $userBranchId);
            }
            $recentSales = $recentSalesQuery->latest('created_at')->limit(5)->get();
            $recentSalesFormatted = $recentSales->map(fn($s) => "  * Faktur: {$s->invoice_no} | Cabang: " . strip_tags($s->branch->name ?? 'Semua') . " | Pelanggan: " . strip_tags($s->customer->name ?? 'Umum') . " | Total: Rp " . number_format($s->grand_total, 0, ',', '.') . " | Metode: " . ($s->payments->pluck('method')->implode(', ') ?: 'N/A') . " | Tanggal: {$s->created_at->toDateTimeString()}")->implode("\n");
            if (empty($recentSalesFormatted)) {
                $recentSalesFormatted = "  * Belum ada transaksi penjualan baru";
            }

            // 8. Active Promotions / Vouchers
            $activePromotionsCount = \App\Models\Voucher::where('is_active', true)->count();
            $activePromotions = \App\Models\Voucher::where('is_active', true)->limit(5)->get();
            $activePromotionsFormatted = $activePromotions->map(fn($v) => "  * " . strip_tags($v->name) . " (Kode: " . strip_tags($v->code) . ", Diskon: " . ($v->type === 'fixed' ? 'Rp ' . number_format($v->value, 0, ',', '.') : $v->value . '%') . ", Berlaku s/d: {$v->end_date})")->implode("\n");
            if (empty($activePromotionsFormatted)) {
                $activePromotionsFormatted = "  * Belum ada voucher aktif";
            }

            // 9. Active Cash Registers (Shifts)
            $activeShiftsQuery = \App\Models\CashRegister::with(['user', 'branch'])->whereNull('closed_at');
            if (!$isAdminOrSupervisor && $userBranchId) {
                $activeShiftsQuery->where('branch_id', $userBranchId);
            }
            $activeShifts = $activeShiftsQuery->get();
            $activeShiftsFormatted = $activeShifts->map(fn($cr) => "  * Kasir: " . strip_tags($cr->user->name) . " | Cabang: " . strip_tags($cr->branch->name) . " | Saldo Awal: Rp " . number_format($cr->opening_balance, 0, ',', '.') . " | Buka sejak: {$cr->opened_at->toDateTimeString()}")->implode("\n");
            if (empty($activeShiftsFormatted)) {
                $activeShiftsFormatted = "  * Tidak ada shift kasir aktif saat ini";
            }

            // 10. Recent Activity Logs
            $recentLogsQuery = \App\Models\ActivityLog::with(['user']);
            if (!$isAdminOrSupervisor && $userBranchId) {
                $recentLogsQuery->whereHas('user', fn($q) => $q->where('branch_id', $userBranchId));
            }
            $recentLogs = $recentLogsQuery->latest('created_at')->limit(5)->get();
            $recentLogsFormatted = $recentLogs->map(fn($log) => "  * [{$log->created_at->toDateTimeString()}] User: " . strip_tags($log->user->name ?? 'System') . " | Modul: " . strip_tags($log->module) . " | Aksi: " . strip_tags($log->action) . " | Detail: " . strip_tags($log->description))->implode("\n");
            if (empty($recentLogsFormatted)) {
                $recentLogsFormatted = "  * Tidak ada log aktivitas terbaru";
            }

            // Branch name context
            $branchName = $user->branch?->name ?? 'Semua Cabang';
            $sanitizedUserName = strip_tags($user->name);
            $sanitizedRoleName = strip_tags($user->role->name ?? 'User');
            $sanitizedBranchName = strip_tags($branchName);

            $context = "==================================================\n" .
                       "KONTEKS SISTEM LAKUPOS TERBARU & REALTIME\n" .
                       "==================================================\n" .
                       "USER SAAT INI:\n" .
                       "- Nama: {$sanitizedUserName}\n" .
                       "- Role: {$sanitizedRoleName}\n" .
                       "- Cabang Bertugas: {$sanitizedBranchName} (ID: " . ($userBranchId ?? 'Semua') . ")\n\n" .

                       "METADATA SISTEM:\n" .
                       "- Total Cabang Terdaftar: {$totalBranches}\n" .
                       "{$branchesList}\n" .
                       "- Total User Terdaftar: {$totalUsers}\n" .
                       "{$rolesFormatted}\n\n" .

                       "METRIK PELANGGAN & SUPPLIER:\n" .
                       "- Total Pelanggan: {$totalCustomers}\n" .
                       "- Pelanggan Baru Bulan Ini: {$customerSummary['new_customers_this_month']}\n" .
                       "- Total Hutang Pelanggan (Piutang Toko): Rp " . number_format($customerSummary['total_customer_debt'], 0, ',', '.') . "\n" .
                       "- Top 5 Pelanggan Berbelanja Terbanyak:\n{$topCustomersFormatted}\n" .
                       "- Total Supplier: {$totalSuppliers}\n" .
                       "- Total Hutang Toko ke Supplier: Rp " . number_format($totalSupplierDebt, 0, ',', '.') . "\n\n" .

                       "PRODUK & KESEHATAN STOK:\n" .
                       "- Total Produk Terdaftar: {$totalProducts} (Aktif: {$activeProducts})\n" .
                       "- Kesehatan Inventaris:\n" .
                       "  * Stok Tingkat Aman: {$stockSummary['healthy_stock_count']} produk\n" .
                       "  * Stok Menipis/Di bawah Batas: {$stockSummary['low_stock_count']} produk\n" .
                       "  * Stok Habis (0): {$stockSummary['out_of_stock_count']} produk\n" .
                       "- Detail Stok Kritis:\n{$criticalStockFormatted}\n\n";

            // Tambahan Konteks Prediktif & Bundling
            $predictive = $this->inventoryAnalytics->getPredictiveRestocking($userBranchId, $isAdminOrSupervisor);
            if ($predictive) {
                $context .= "- Prediksi Lonjakan Permintaan:\n  * " . strip_tags($predictive['product']->name) . " diprediksi butuh {$predictive['predicted_demand']} unit dalam 3 hari ke depan (stok saat ini {$predictive['current_stock']}).\n\n";
            }
            $bundles = $this->salesAnalytics->getBundlingRecommendations(2, $userBranchId, $isAdminOrSupervisor);
            if (!empty($bundles)) {
                $context .= "- Rekomendasi Bundling Produk (Sering dibeli bersamaan):\n";
                foreach ($bundles as $bundle) {
                    $context .= "  * " . strip_tags($bundle['main_product']->name) . " + " . strip_tags($bundle['companion_product']->name) . "\n";
                }
                $context .= "\n";
            }

            $context .= "METRIK PENJUALAN HARI INI (CABANG: " . ($isAdminOrSupervisor ? 'Semua Cabang' : $sanitizedBranchName) . "):\n" .
                       "- Omset Hari Ini: Rp " . number_format($salesMetrics['total_revenue'], 0, ',', '.') . "\n" .
                       "- Jumlah Transaksi Hari Ini: {$salesMetrics['total_transactions']}\n" .
                       "- Pertumbuhan dibanding kemarin: " . ($salesMetrics['revenue_growth_percent'] >= 0 ? '+' : '') . "{$salesMetrics['revenue_growth_percent']}%\n" .
                       "- Produk Terlaris Hari Ini:\n{$bestSellersFormatted}\n\n";

            // Peak Hours Today
            $salesToday = \App\Models\Sale::where('status', 'completed')
                ->whereDate('created_at', now()->toDateString())
                ->when(!$isAdminOrSupervisor && $userBranchId, fn($q) => $q->where('branch_id', $userBranchId))
                ->get();

            if ($salesToday->isEmpty()) {
                $peakHours = "  * Belum ada data jam sibuk hari ini";
            } else {
                $peakHoursGroups = $salesToday->groupBy(function($s) {
                    return $s->created_at->format('H');
                })->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'revenue' => $group->sum('grand_total')
                    ];
                })->sortByDesc('count')->take(3);

                $peakHoursArray = [];
                foreach ($peakHoursGroups as $hour => $data) {
                    $peakHoursArray[] = "  * Jam {$hour}:00 - {$hour}:59 ({$data['count']} transaksi, Rp " . number_format($data['revenue'], 0, ',', '.') . ")";
                }
                $peakHours = implode("\n", $peakHoursArray);
            }
            $context .= "JAM SIBUK (PEAK HOURS) HARI INI:\n{$peakHours}\n\n";

            // Sales by Category (This Month)
            $categorySales = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->where('sales.status', 'completed')
                ->whereBetween('sales.created_at', [$startOfMonth, $endOfMonth])
                ->when(!$isAdminOrSupervisor && $userBranchId, fn($q) => $q->where('sales.branch_id', $userBranchId))
                ->select('categories.name', DB::raw('SUM(sale_items.quantity) as total_qty'), DB::raw('SUM(sale_items.subtotal) as total_revenue'))
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get()
                ->map(fn($c) => "  * " . strip_tags($c->name) . " (Terjual: {$c->total_qty} unit, Omset: Rp " . number_format($c->total_revenue, 0, ',', '.') . ")")
                ->implode("\n");
            if (empty($categorySales)) {
                $categorySales = "  * Belum ada data kategori terjual bulan ini";
            }
            
            // Dead Stock (Products with 0 sales in last 30 days)
            $thirtyDaysAgo = now()->subDays(30);
            $deadStockItems = \App\Models\Product::whereNotIn('id', function($q) use ($thirtyDaysAgo, $userBranchId, $isAdminOrSupervisor) {
                $q->select('product_id')->from('sale_items')->join('sales', 'sale_items.sale_id', '=', 'sales.id')->where('sales.created_at', '>=', $thirtyDaysAgo);
                if (!$isAdminOrSupervisor && $userBranchId) {
                    $q->where('sales.branch_id', $userBranchId);
                }
            })->limit(5)->get();
            $deadStockFormatted = $deadStockItems->map(fn($p) => "  * " . strip_tags($p->name) . " (SKU: " . strip_tags($p->sku) . ")")->implode("\n");
            if (empty($deadStockFormatted)) {
                $deadStockFormatted = "  * Tidak ada produk dead-stock (semua produk laku dalam 30 hari terakhir)";
            }

            $context .= "LAPORAN PENJUALAN BULAN INI (MULAI " . $startOfMonth->toDateString() . " S/D " . $endOfMonth->toDateString() . "):\n" .
                       "- Total Pendapatan Bulan Ini: Rp " . number_format($monthlyTotalSales, 0, ',', '.') . "\n" .
                       "- Total Transaksi Selesai Bulan Ini: {$monthlyTotalTransactions} transaksi\n" .
                       "- Rata-rata Nilai Transaksi: Rp " . number_format($monthlyAvgTransaction, 0, ',', '.') . "\n" .
                       "- Pertumbuhan dibanding bulan lalu: " . ($monthlySalesGrowth >= 0 ? '+' : '') . "{$monthlySalesGrowth}%\n" .
                       "- Metode Pembayaran Digunakan Bulan Ini:\n{$paymentMethods}\n" .
                       "{$branchSalesBreakdown}\n" .
                       "KATEGORI PRODUK TERLARIS BULAN INI:\n{$categorySales}\n\n" .
                       "PRODUK TIDAK LAKU (DEAD-STOCK > 30 HARI):\n{$deadStockFormatted}\n\n" .

                       "LAPORAN KEUANGAN LABA/RUGI BULAN INI (MULAI {$profitSummary['period_start']} S/D {$profitSummary['period_end']}):\n" .
                       "- Total Pendapatan Kotor (Revenue): Rp " . number_format($profitSummary['total_revenue'], 0, ',', '.') . "\n" .
                       "- Total HPP (Cost of Goods Sold / COGS): Rp " . number_format($profitSummary['total_cogs'], 0, ',', '.') . "\n" .
                       "- Total Diskon yang Diberikan: Rp " . number_format($profitSummary['total_discounts'], 0, ',', '.') . "\n" .
                       "- Laba Kotor (Gross Profit): Rp " . number_format($profitSummary['gross_profit'], 0, ',', '.') . "\n" .
                       "- Margin Keuntungan Bersih: {$profitSummary['profit_margin_percent']}%\n\n" .

                       "TRANSAKSI TERBARU (5 TERAKHIR):\n{$recentSalesFormatted}\n\n" .

                       "PROMOSI & VOUCHER AKTIF:\n- Total Voucher Aktif: {$activePromotionsCount}\n{$activePromotionsFormatted}\n\n" .

                       "SHIFT OPERASIONAL KASIR SAAT INI (BELUM TUTUP):\n{$activeShiftsFormatted}\n\n" .

                       "AKTIVITAS TERBARU SISTEM (5 TERAKHIR):\n{$recentLogsFormatted}\n" .
                       "==================================================\n";

            // ── SALES FORECAST: Next Month Prediction ──────────────────────
            // Ambil data 3 bulan terakhir untuk regresi linear sederhana
            $monthlyRevenues = [];
            for ($i = 2; $i >= 0; $i--) {
                $mStart = now()->subMonths($i)->startOfMonth();
                $mEnd   = now()->subMonths($i)->endOfMonth();
                $rev    = \App\Models\Sale::where('status', 'completed')
                    ->whereBetween('created_at', [$mStart, $mEnd])
                    ->when(!$isAdminOrSupervisor && $userBranchId, fn($q) => $q->where('branch_id', $userBranchId))
                    ->sum('grand_total');
                $monthlyRevenues[] = [
                    'month' => $mStart->translatedFormat('F Y'),
                    'revenue' => $rev,
                ];
            }

            // Hitung prediksi bulan depan dengan rata-rata bergerak tertimbang (weight: 1, 2, 3)
            $weights   = [1, 2, 3];
            $weightSum = array_sum($weights);
            $weightedRev = 0;
            foreach ($monthlyRevenues as $idx => $m) {
                $weightedRev += $m['revenue'] * $weights[$idx];
            }
            $predictedNextMonthRevenue = $weightSum > 0 ? round($weightedRev / $weightSum) : 0;

            // Hitung tren (naik/turun) berdasarkan 3 bulan
            $revsOnly = array_column($monthlyRevenues, 'revenue');
            $trendChange = (count($revsOnly) >= 2 && $revsOnly[0] > 0)
                ? round((($revsOnly[2] - $revsOnly[0]) / $revsOnly[0]) * 100, 1)
                : 0;
            $trendLabel = $trendChange >= 0 ? "naik {$trendChange}%" : "turun " . abs($trendChange) . "%";

            $historicalFormatted = implode("\n", array_map(
                fn($m) => "  * {$m['month']}: Rp " . number_format($m['revenue'], 0, ',', '.'),
                $monthlyRevenues
            ));
            $nextMonthLabel = now()->addMonth()->translatedFormat('F Y');

            $context .= "\n==================================================\n" .
                        "PREDIKSI & FORECAST BISNIS\n" .
                        "==================================================\n" .
                        "PREDIKSI PENJUALAN BULAN DEPAN ({$nextMonthLabel}):\n" .
                        "- Data historis 3 bulan terakhir:\n{$historicalFormatted}\n" .
                        "- Tren 3 bulan: {$trendLabel}\n" .
                        "- Prediksi omset bulan depan: Rp " . number_format($predictedNextMonthRevenue, 0, ',', '.') . "\n" .
                        "  (Metode: Rata-rata bergerak tertimbang (WMA 3 bulan), bobot bulan terbaru lebih tinggi)\n\n";

            // ── STOCK FORECAST: Per-product stock depletion prediction ──────
            // Stok produk ada di tabel product_stocks, bukan kolom di products
            $thirtyDaysAgo2 = now()->subDays(30);

            $productStockForecast = DB::table('products')
                ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                ->select(
                    'products.id',
                    'products.name',
                    DB::raw('SUM(product_stocks.quantity) as total_stock'),
                    DB::raw('(SELECT COALESCE(SUM(si.quantity), 0)
                               FROM sale_items si
                               INNER JOIN sales s ON si.sale_id = s.id
                               WHERE s.status = \'completed\'
                                 AND s.created_at >= \'' . $thirtyDaysAgo2->toDateTimeString() . '\'
                                 ' . (!$isAdminOrSupervisor && $userBranchId ? 'AND s.branch_id = ' . (int) $userBranchId : '') . '
                                 AND si.product_id = products.id) as sold_last_30d')
                )
                ->groupBy('products.id', 'products.name')
                ->having('total_stock', '>', 0)
                ->orderByDesc('total_stock')
                ->limit(10)
                ->get();

            $stockForecastFormatted = '';
            foreach ($productStockForecast as $prod) {
                $sold30 = $prod->sold_last_30d ?? 0;
                $avgDailySales = $sold30 > 0 ? $sold30 / 30 : 0;

                if ($avgDailySales > 0) {
                    $daysUntilEmpty = floor($prod->total_stock / $avgDailySales);
                    $estimatedEmptyDate = now()->addDays($daysUntilEmpty)->translatedFormat('d F Y');
                    $forecastNextMonth  = round($avgDailySales * 30);
                    $status = $daysUntilEmpty <= 7 ? '🔴 KRITIS' : ($daysUntilEmpty <= 30 ? '🟡 PERLU PERHATIAN' : '🟢 AMAN');
                    $stockForecastFormatted .= "  * " . strip_tags($prod->name) .
                        " | Stok: {$prod->total_stock} unit" .
                        " | Rata jual/hari: " . round($avgDailySales, 1) . " unit" .
                        " | Prediksi habis: ~{$daysUntilEmpty} hari ({$estimatedEmptyDate})" .
                        " | Prediksi terjual bulan depan: ~{$forecastNextMonth} unit" .
                        " | Status: {$status}\n";
                } else {
                    $stockForecastFormatted .= "  * " . strip_tags($prod->name) .
                        " | Stok: {$prod->total_stock} unit | Tidak ada penjualan 30 hari terakhir (Dead Stock)\n";
                }
            }

            if (empty(trim($stockForecastFormatted))) {
                $stockForecastFormatted = "  * Tidak ada data produk dengan stok tersedia\n";
            }

            $context .= "PREDIKSI STOK PRODUK (BERDASARKAN RATA-RATA PENJUALAN 30 HARI TERAKHIR):\n" .
                        $stockForecastFormatted .
                        "\n(Catatan: Prediksi di atas didasarkan pada rata-rata penjualan harian 30 hari terakhir. " .
                        "Faktor eksternal seperti promosi, musim, atau perubahan pasar belum diperhitungkan.)\n" .
                        "==================================================\n";

            // 11. Knowledge Base (SOP & Manuals)
            $kbPath = base_path('docs/knowledge');
            $kbContext = "";
            if (is_dir($kbPath)) {
                $files = ['printer_kasir.md', 'kebijakan_retur.md', 'kontrak_supplier.md'];
                foreach ($files as $file) {
                    $filePath = $kbPath . '/' . $file;
                    if (file_exists($filePath)) {
                        $content = file_get_contents($filePath);
                        $kbContext .= "DOKUMEN: {$file}\n{$content}\n---\n";
                    }
                }
            }
            if (!empty($kbContext)) {
                $context .= "PANDUAN OPERASIONAL & SOP RESMI TOKO (KNOWLEDGE BASE):\n" .
                            $kbContext .
                            "==================================================\n";
            }

            return $context;
        });
    }
}
