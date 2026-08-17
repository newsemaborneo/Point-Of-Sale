<?php

namespace App\Services\Analytics;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Branch;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SalesAnalyticsService
{
    /**
     * Mendapatkan zona waktu lokal toko (default Asia/Jakarta)
     */
    public function getStoreTimezone(): string
    {
        return 'Asia/Jakarta';
    }

    /**
     * Menghitung jam mulai kalkulasi "hari ini" berdasarkan jam operasional toko.
     */
    public function getCalculationStartTime(): Carbon
    {
        $storeHoursEnabled = (bool) Setting::get('store_hours_enabled', false);
        $storeOpenTimeStr  = Setting::get('store_open_time', '08:00');
        $now = now();

        if ($storeHoursEnabled) {
            $openTimeCarbon = Carbon::createFromTimeString($storeOpenTimeStr);
            $todayOpenTime  = $now->copy()->setTime($openTimeCarbon->hour, $openTimeCarbon->minute, 0);

            if ($now->greaterThanOrEqualTo($todayOpenTime)) {
                return $todayOpenTime;
            } else {
                return $todayOpenTime->copy()->subDay();
            }
        }

        return $now->copy()->startOfDay();
    }

    /**
     * Memeriksa apakah toko sedang buka.
     */
    public function isStoreOpen(): bool
    {
        $storeHoursEnabled = (bool) Setting::get('store_hours_enabled', false);
        if (!$storeHoursEnabled) {
            return true;
        }

        $storeOpenTimeStr  = Setting::get('store_open_time', '08:00');
        $storeCloseTimeStr = Setting::get('store_close_time', '21:00');
        $storeLocalTimezone = $this->getStoreTimezone();
        $now = now();

        $openTimeLocal  = Carbon::createFromTimeString($storeOpenTimeStr, $storeLocalTimezone)->setDate($now->year, $now->month, $now->day);
        $closeTimeLocal = Carbon::createFromTimeString($storeCloseTimeStr, $storeLocalTimezone)->setDate($now->year, $now->month, $now->day);

        $openTimeApp  = $openTimeLocal->copy()->timezone($now->timezone);
        $closeTimeApp = $closeTimeLocal->copy()->timezone($now->timezone);

        if ($closeTimeApp->lessThan($openTimeApp)) {
            // Shift overnight
            return $now->greaterThanOrEqualTo($openTimeApp) || $now->lessThan($closeTimeApp);
        }

        return $now->greaterThanOrEqualTo($openTimeApp) && $now->lessThan($closeTimeApp);
    }

    /**
     * Mendapatkan metrik penjualan hari ini (Omzet, Transaksi, Average Value, dll).
     */
    public function getTodayMetrics(?int $userBranchId = null, bool $isAdminOrSupervisor = true): array
    {
        $startTime = $this->getCalculationStartTime();

        $todaySales = Sale::where('created_at', '>=', $startTime)
            ->where('status', 'completed');

        if (!$isAdminOrSupervisor && $userBranchId) {
            $todaySales->where('branch_id', $userBranchId);
        }

        $totalRevenue = (clone $todaySales)->sum('grand_total');
        $totalTransactions = (clone $todaySales)->count();
        $avgTransactionValue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Perbandingan dengan periode kemarin (24 jam sebelumnya)
        $yesterdayStart = $startTime->copy()->subDay();
        $yesterdaySales = Sale::whereBetween('created_at', [$yesterdayStart, $startTime])
            ->where('status', 'completed');

        if (!$isAdminOrSupervisor && $userBranchId) {
            $yesterdaySales->where('branch_id', $userBranchId);
        }

        $yesterdayRevenue = $yesterdaySales->sum('grand_total');
        $revenueGrowth = 0;
        if ($yesterdayRevenue > 0) {
            $revenueGrowth = (($totalRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100;
        } elseif ($totalRevenue > 0) {
            $revenueGrowth = 100;
        }

        return [
            'total_revenue' => (float) $totalRevenue,
            'total_transactions' => (int) $totalTransactions,
            'avg_transaction_value' => (float) $avgTransactionValue,
            'yesterday_revenue' => (float) $yesterdayRevenue,
            'revenue_growth_percent' => round($revenueGrowth, 1),
            'calculation_start_time' => $startTime,
            'store_is_open' => $this->isStoreOpen(),
        ];
    }

    /**
     * Mendapatkan daftar produk paling laris.
     */
    public function getBestSellers(int $limit = 5, ?int $userBranchId = null, bool $isAdminOrSupervisor = true, ?Carbon $startTime = null)
    {
        $startTime = $startTime ?? $this->getCalculationStartTime();

        return SaleItem::selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->whereHas('sale', function ($q) use ($startTime, $isAdminOrSupervisor, $userBranchId) {
                $q->where('created_at', '>=', $startTime)->where('status', 'completed');
                if (!$isAdminOrSupervisor && $userBranchId) {
                    $q->where('branch_id', $userBranchId);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with(['product.category', 'product.unit'])
            ->limit($limit)
            ->get();
    }

    /**
     * Mendapatkan data grafik penjualan 7 hari terakhir.
     */
    public function getWeeklySalesChartData(?int $userBranchId = null, bool $isAdminOrSupervisor = true, ?string $userBranchName = null): array
    {
        $storeLocalTimezone = $this->getStoreTimezone();
        Carbon::setLocale('id');

        $nowInStoreTimezone = Carbon::now($storeLocalTimezone);
        $chartStartDateLocal = $nowInStoreTimezone->copy()->subDays(6)->startOfDay();
        $chartEndDateLocal = $nowInStoreTimezone->copy()->endOfDay();

        $chartStartDateUtc = $chartStartDateLocal->copy()->setTimezone('UTC');
        $chartEndDateUtc = $chartEndDateLocal->copy()->setTimezone('UTC');

        $salesChartLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateLocal = $nowInStoreTimezone->copy()->subDays($i);
            $salesChartLabels[] = $dateLocal->translatedFormat('D, d M');
        }

        $branchColorPalette = [
            '#6366f1', '#10b981', '#f59e0b', '#ef4444',
            '#0ea5e9', '#8b5cf6', '#ec4899', '#14b8a6',
        ];

        $groupSalesByLocalDate = function ($salesCollection) use ($storeLocalTimezone) {
            $grouped = [];
            foreach ($salesCollection as $sale) {
                $localDate = Carbon::parse($sale->created_at)
                    ->timezone($storeLocalTimezone)
                    ->format('Y-m-d');
                $grouped[$localDate] = ($grouped[$localDate] ?? 0) + (float) $sale->grand_total;
            }
            return $grouped;
        };

        $salesChartDatasets = [];
        $salesChartData = array_fill(0, 7, 0);

        if ($isAdminOrSupervisor) {
            $branches = Branch::orderBy('name')->get();

            foreach ($branches as $index => $branch) {
                $branchSales = Sale::select('created_at', 'grand_total')
                    ->where('status', 'completed')
                    ->where('branch_id', $branch->id)
                    ->whereBetween('created_at', [$chartStartDateUtc, $chartEndDateUtc])
                    ->get();

                $branchSalesByDate = $groupSalesByLocalDate($branchSales);

                $branchData = [];
                for ($i = 6; $i >= 0; $i--) {
                    $dateKey = $nowInStoreTimezone->copy()->subDays($i)->format('Y-m-d');
                    $branchData[] = $branchSalesByDate[$dateKey] ?? 0;
                }

                if (array_sum($branchData) <= 0) {
                    continue;
                }

                $salesChartDatasets[] = [
                    'label' => $branch->name,
                    'data'  => $branchData,
                    'color' => $branchColorPalette[$index % count($branchColorPalette)],
                ];
            }

            foreach ($salesChartDatasets as $dataset) {
                foreach ($dataset['data'] as $i => $value) {
                    $salesChartData[$i] += $value;
                }
            }
        } else {
            $salesQuery = Sale::select('created_at', 'grand_total')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$chartStartDateUtc, $chartEndDateUtc]);

            if ($userBranchId) {
                $salesQuery->where('branch_id', $userBranchId);
            }

            $rawSales = $salesQuery->get();
            $salesByDate = $groupSalesByLocalDate($rawSales);

            $salesChartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $dateKey = $nowInStoreTimezone->copy()->subDays($i)->format('Y-m-d');
                $salesChartData[] = $salesByDate[$dateKey] ?? 0;
            }

            $salesChartDatasets[] = [
                'label' => $userBranchName ?? 'Cabang Saya',
                'data'  => $salesChartData,
                'color' => $branchColorPalette[0],
            ];
        }

        $salesChart = collect($salesChartLabels)->map(function ($label, $index) use ($salesChartData) {
            return (object) ['date' => $label, 'total' => $salesChartData[$index] ?? 0];
        })->values();

        return [
            'labels'   => $salesChartLabels,
            'data'     => $salesChartData,
            'datasets' => $salesChartDatasets,
            'chart'    => $salesChart,
        ];
    }
}
