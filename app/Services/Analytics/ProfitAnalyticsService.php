<?php

namespace App\Services\Analytics;

use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitAnalyticsService
{
    /**
     * Menhitung ringkasan keuntungan (Revenue - COGS - Discount = Net Profit).
     */
    public function getProfitSummary(?Carbon $startDate = null, ?Carbon $endDate = null, ?int $userBranchId = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfDay();

        $salesQuery = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($userBranchId) {
            $salesQuery->where('branch_id', $userBranchId);
        }

        $sales = $salesQuery->with('items.product')->get();

        $totalRevenue = 0;
        $totalCogs = 0;
        $totalDiscounts = 0;
        $totalTaxes = 0;

        foreach ($sales as $sale) {
            $totalRevenue += $sale->grand_total;
            $totalDiscounts += $sale->discount_total;
            $totalTaxes += $sale->tax_total;

            foreach ($sale->items as $item) {
                // Gunakan cost_price jika ada, atau fall back ke purchase_price produk
                $purchasePrice = $item->cost_price ?? ($item->product->purchase_price ?? 0);
                $totalCogs += ($purchasePrice * $item->quantity);
            }
        }

        $grossProfit = $totalRevenue - $totalCogs;
        $profitMarginPercent = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        return [
            'period_start' => $startDate->toDateTimeString(),
            'period_end' => $endDate->toDateTimeString(),
            'total_revenue' => (float) $totalRevenue,
            'total_cogs' => (float) $totalCogs,
            'total_discounts' => (float) $totalDiscounts,
            'gross_profit' => (float) $grossProfit,
            'profit_margin_percent' => round($profitMarginPercent, 2),
        ];
    }

    /**
     * Mendapatkan produk dengan marjin keuntungan terbesar.
     */
    public function getTopProfitProducts(int $limit = 5, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfDay();

        return SaleItem::select('sale_items.product_id', 
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.purchase_price) as total_cost'),
                DB::raw('SUM(sale_items.subtotal - (sale_items.quantity * products.purchase_price)) as total_profit')
            )
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->groupBy('sale_items.product_id')
            ->orderByDesc('total_profit')
            ->with(['product.category'])
            ->limit($limit)
            ->get();
    }
}
