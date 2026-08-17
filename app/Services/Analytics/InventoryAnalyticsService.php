<?php

namespace App\Services\Analytics;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SaleItem;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryAnalyticsService
{
    /**
     * Mendapatkan daftar produk yang stoknya menipis atau di bawah min_stock.
     */
    public function getLowStockProducts(?int $userBranchId = null, bool $isAdminOrSupervisor = true)
    {
        if (!$isAdminOrSupervisor && $userBranchId) {
            $warehouseIdsInBranch = Warehouse::where('branch_id', $userBranchId)->pluck('id');

            return Product::select('products.*')
                ->joinSub(function ($query) use ($warehouseIdsInBranch) {
                    $query->from('product_stocks')
                        ->select('product_id', DB::raw('SUM(quantity) as total_warehouse_stock'))
                        ->whereIn('warehouse_id', $warehouseIdsInBranch)
                        ->groupBy('product_id');
                }, 'warehouse_stocks', function ($join) {
                    $join->on('products.id', '=', 'warehouse_stocks.product_id');
                })
                ->whereColumn('warehouse_stocks.total_warehouse_stock', '<=', 'products.min_stock')
                ->with(['category', 'unit', 'supplier', 'stocks' => fn($q) => $q->whereIn('warehouse_id', $warehouseIdsInBranch)])
                ->get();
        }

        return Product::with(['category', 'unit', 'supplier', 'stocks'])
            ->get()
            ->filter(fn (Product $p) => $p->isLowStock())
            ->values();
    }

    /**
     * Menghitung kecepatan penjualan (Sales Velocity) per hari untuk suatu produk.
     */
    public function getSalesVelocity(int $productId, int $days = 7): float
    {
        $startDate = Carbon::now()->subDays($days);

        $totalQtySold = SaleItem::where('product_id', $productId)
            ->whereHas('sale', function ($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate)->where('status', 'completed');
            })
            ->sum('quantity');

        return $days > 0 ? round($totalQtySold / $days, 2) : 0;
    }

    /**
     * Mendapatkan analisis stok kritis beserta perkiraan hari stok akan habis.
     */
    public function getCriticalStockAnalysis(?int $userBranchId = null, bool $isAdminOrSupervisor = true): array
    {
        $lowStockProducts = $this->getLowStockProducts($userBranchId, $isAdminOrSupervisor);
        $analysis = [];

        foreach ($lowStockProducts as $product) {
            $currentStock = $product->totalStock();
            $velocity = $this->getSalesVelocity($product->id, 7); // Rata-rata 7 hari

            $daysRemaining = $velocity > 0 ? round($currentStock / $velocity, 1) : null;
            $recommendedRestock = max(($product->min_stock * 3) - $currentStock, 10);

            $analysis[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category->name ?? '-',
                'current_stock' => $currentStock,
                'min_stock' => $product->min_stock,
                'avg_daily_sales' => $velocity,
                'estimated_days_left' => $daysRemaining,
                'status' => $currentStock == 0 ? 'CRITICAL_EMPTY' : ($daysRemaining !== null && $daysRemaining <= 1 ? 'CRITICAL_URGENT' : 'WARNING_LOW'),
                'recommended_restock_qty' => $recommendedRestock,
                'supplier_name' => $product->supplier->name ?? '-',
                'supplier_id' => $product->supplier_id,
            ];
        }

        return $analysis;
    }

    /**
     * Ringkasan kesehatan inventaris secara umum.
     */
    public function getInventoryHealthSummary(?int $userBranchId = null, bool $isAdminOrSupervisor = true): array
    {
        $lowStockItems = $this->getLowStockProducts($userBranchId, $isAdminOrSupervisor);
        $totalProducts = Product::where('is_active', true)->count();
        $outOfStockCount = $lowStockItems->filter(fn($p) => $p->totalStock() == 0)->count();

        return [
            'total_active_products' => $totalProducts,
            'low_stock_count' => $lowStockItems->count(),
            'out_of_stock_count' => $outOfStockCount,
            'healthy_stock_count' => max($totalProducts - $lowStockItems->count(), 0),
        ];
    }
    /**
     * Predictive Restocking: Menggunakan velocity dan multiplier musiman (misal akhir pekan).
     */
    public function getPredictiveRestocking(?int $userBranchId = null, bool $isAdminOrSupervisor = true): ?array
    {
        // Ambil produk dengan velocity tertinggi (terlaris)
        $topProducts = Product::with(['category', 'unit', 'supplier', 'stocks'])->get();
        if ($topProducts->isEmpty()) return null;

        $predictions = [];
        $dayOfWeek = now()->dayOfWeek; // 0 = Sunday, 1 = Monday ... 5 = Friday, 6 = Saturday
        $isApproachingWeekend = in_array($dayOfWeek, [4, 5]); // Thursday or Friday
        $weekendMultiplier = $isApproachingWeekend ? 1.5 : 1.0;

        foreach ($topProducts as $product) {
            $velocity = $this->getSalesVelocity($product->id, 7);
            if ($velocity < 1) continue;

            $currentStock = $product->totalStock();
            $predictedDemand3Days = $velocity * $weekendMultiplier * 3;

            // Jika stok saat ini kurang dari prediksi permintaan 3 hari ke depan
            if ($currentStock < $predictedDemand3Days && $currentStock > 0) {
                $predictions[] = [
                    'product' => $product,
                    'current_stock' => $currentStock,
                    'velocity' => $velocity,
                    'predicted_demand' => round($predictedDemand3Days),
                    'is_weekend_spike' => $isApproachingWeekend
                ];
            }
        }

        // Sort by predicted demand gap
        usort($predictions, fn($a, $b) => ($b['predicted_demand'] - $b['current_stock']) <=> ($a['predicted_demand'] - $a['current_stock']));

        return $predictions[0] ?? null;
    }
}
