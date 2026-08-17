<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\Ai\DashboardInsightService;
use App\Services\Analytics\InventoryAnalyticsService;
use App\Services\Analytics\SalesAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected SalesAnalyticsService $salesAnalytics;
    protected InventoryAnalyticsService $inventoryAnalytics;
    protected DashboardInsightService $dashboardInsightService;

    public function __construct(
        SalesAnalyticsService $salesAnalytics,
        InventoryAnalyticsService $inventoryAnalytics,
        DashboardInsightService $dashboardInsightService
    ) {
        $this->salesAnalytics = $salesAnalytics;
        $this->inventoryAnalytics = $inventoryAnalytics;
        $this->dashboardInsightService = $dashboardInsightService;
    }

    /**
     * Ringkasan dashboard: pendapatan hari ini, jumlah transaksi,
     * produk terlaris, grafik penjualan, notifikasi stok menipis.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userBranchId = $user->branch_id;
        $isAdminOrSupervisor = $user->hasRole('admin') || $user->hasRole('supervisor');

        $userBranchName = null;
        if ($userBranchId) {
            $userBranch = Branch::find($userBranchId);
            if ($userBranch) {
                $userBranchName = $userBranch->name;
            }
        }

        // 1. Ambil Metrik Penjualan Hari Ini dari Service
        $todayMetrics = $this->salesAnalytics->getTodayMetrics($userBranchId, $isAdminOrSupervisor);
        $totalRevenue = $todayMetrics['total_revenue'];
        $totalTransactions = $todayMetrics['total_transactions'];
        $storeIsOpen = $todayMetrics['store_is_open'];

        // 2. Ambil Produk Terlaris
        $bestSellers = $this->salesAnalytics->getBestSellers(5, $userBranchId, $isAdminOrSupervisor, $todayMetrics['calculation_start_time']);

        // 3. Ambil Data Grafik Penjualan (7 Hari)
        $chartData = $this->salesAnalytics->getWeeklySalesChartData($userBranchId, $isAdminOrSupervisor, $userBranchName);
        $salesChart = $chartData['chart'];
        $salesChartLabels = $chartData['labels'];
        $salesChartData = $chartData['data'];
        $salesChartDatasets = $chartData['datasets'];

        // 4. Ambil Produk Stok Menipis dari Service
        $lowStockProducts = $this->inventoryAnalytics->getLowStockProducts($userBranchId, $isAdminOrSupervisor);

        // 5. Data AI akan dimuat secara async via AJAX untuk performa render dashboard optimal
        $aiInsights = [];
        $aiRecommendations = [];
        $aiCenterTitle = 'AI Intelligence';

        if ($isAdminOrSupervisor) {
            $aiCenterTitle = $this->dashboardInsightService->getAiCenterTitleForUser($user);
        }

        return view('dashboard.index', compact(
            'totalRevenue',
            'totalTransactions',
            'bestSellers',
            'salesChart',
            'salesChartLabels',
            'salesChartData',
            'salesChartDatasets',
            'lowStockProducts',
            'storeIsOpen',
            'userBranchName',
            'aiInsights',
            'aiRecommendations',
            'aiCenterTitle'
        ));
    }
}