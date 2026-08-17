<?php

namespace App\Services\Ai;

use App\Models\User;
use App\Models\Sale;
use App\Models\ProductStock;
use App\Models\Product;
use App\Services\Analytics\SalesAnalyticsService;
use Illuminate\Support\Facades\DB;

/**
 * Memindai kondisi bisnis dan menghasilkan daftar alert proaktif.
 */
class AiProactiveAlertService
{
    public function __construct(protected SalesAnalyticsService $salesAnalytics) {}

    /**
     * Kembalikan semua alert yang relevan untuk user tertentu.
     */
    public function getAlerts(User $user): array
    {
        $isAdmin = $user->hasRole('admin') || $user->hasRole('supervisor');
        $branchId = $isAdmin ? null : $user->branch_id;

        return array_merge(
            $this->checkCriticalStock($branchId),
            $this->checkSalesDrop($user, $isAdmin),
            $this->checkCustomerDebt($isAdmin)
        );
    }

    // ──────────────────────────────────────────────────────────
    // Stok Kritis
    // ──────────────────────────────────────────────────────────
    protected function checkCriticalStock(?int $branchId): array
    {
        $query = ProductStock::with('product')
            ->whereHas('product', fn($q) => $q->where('is_active', true))
            ->whereColumn('quantity', '<=', 'min_stock');

        $alerts = [];
        foreach ($query->get() as $stock) {
            $alerts[] = [
                'type'     => 'warning',
                'icon'     => '📦',
                'title'    => 'Stok Kritis: ' . $stock->product->name,
                'message'  => "Stok tersisa {$stock->quantity} unit — di bawah batas minimum ({$stock->min_stock} unit). Segera buat Purchase Order.",
                'category' => 'stock',
            ];
        }
        return $alerts;
    }

    // ──────────────────────────────────────────────────────────
    // Penurunan Penjualan
    // ──────────────────────────────────────────────────────────
    protected function checkSalesDrop(User $user, bool $isAdmin): array
    {
        $todayMetrics     = $this->salesAnalytics->getTodayMetrics($user->branch_id, $isAdmin);
        $yesterdayRevenue = $this->getYesterdayRevenue($user->branch_id, $isAdmin);

        if ($yesterdayRevenue <= 0) {
            return [];
        }

        $drop = (($yesterdayRevenue - $todayMetrics['total_revenue']) / $yesterdayRevenue) * 100;

        if ($drop >= 30) {
            $dropFormatted = number_format($drop, 1);
            return [[
                'type'     => 'danger',
                'icon'     => '📉',
                'title'    => "Penjualan Turun {$dropFormatted}%",
                'message'  => "Omset hari ini Rp " . number_format($todayMetrics['total_revenue'], 0, ',', '.') . " — turun {$dropFormatted}% dibanding kemarin (Rp " . number_format($yesterdayRevenue, 0, ',', '.') . "). Perlu perhatian!",
                'category' => 'sales',
            ]];
        }

        return [];
    }

    protected function getYesterdayRevenue(?int $branchId, bool $isAdmin): float
    {
        $yesterday = now()->subDay();
        return (float) Sale::where('status', 'completed')
            ->whereDate('created_at', $yesterday->toDateString())
            ->when(!$isAdmin && $branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('grand_total');
    }

    // ──────────────────────────────────────────────────────────
    // Hutang Pelanggan
    // ──────────────────────────────────────────────────────────
    protected function checkCustomerDebt(bool $isAdmin): array
    {
        if (!$isAdmin) {
            return [];
        }

        $debtCount = DB::table('customer_debts')
            ->where('status', 'unpaid')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        if ($debtCount > 0) {
            return [[
                'type'     => 'info',
                'icon'     => '💳',
                'title'    => "{$debtCount} Hutang Pelanggan Jatuh Tempo",
                'message'  => "Terdapat {$debtCount} tagihan pelanggan yang sudah melewati tanggal jatuh tempo. Pertimbangkan untuk menghubungi pelanggan terkait.",
                'category' => 'debt',
            ]];
        }

        return [];
    }
}
