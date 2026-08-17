<?php

namespace App\Services\Analytics;

use App\Models\Customer;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerAnalyticsService
{
    /**
     * Mendapatkan daftar pelanggan paling banyak berbelanja (Top Spenders).
     */
    public function getTopCustomersBySpend(int $limit = 5, ?int $branchId = null)
    {
        return Customer::select('customers.*', DB::raw('SUM(sales.grand_total) as total_spent'), DB::raw('COUNT(sales.id) as total_orders'))
            ->join('sales', 'customers.id', '=', 'sales.customer_id')
            ->where('sales.status', 'completed')
            ->when($branchId, fn($q) => $q->where('sales.branch_id', $branchId))
            ->groupBy('customers.id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }

    /**
     * Mendapatkan ringkasan statistik pelanggan.
     */
    public function getCustomerSummary(?int $branchId = null): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalCustomers = Customer::when($branchId, fn($q) => $q->whereHas('sales', fn($s) => $s->where('branch_id', $branchId)))->count();
        $newCustomersThisMonth = Customer::where('created_at', '>=', $startOfMonth)
            ->when($branchId, fn($q) => $q->whereHas('sales', fn($s) => $s->where('branch_id', $branchId)))->count();
        
        $totalDebt = \App\Models\CustomerDebt::where('status', '!=', 'paid')
            ->when($branchId, fn($q) => $q->whereHas('sale', fn($s) => $s->where('branch_id', $branchId)))
            ->sum(DB::raw('amount - paid_amount'));

        return [
            'total_customers' => $totalCustomers,
            'new_customers_this_month' => $newCustomersThisMonth,
            'total_customer_debt' => (float) $totalDebt,
        ];
    }

    /**
     * Mendapatkan pelanggan yang berisiko churning (tidak bertransaksi > N hari).
     */
    public function getAtRiskCustomers(int $daysInactive = 30, int $limit = 10)
    {
        $cutoffDate = Carbon::now()->subDays($daysInactive);

        return Customer::select('customers.*', DB::raw('MAX(sales.created_at) as last_transaction_date'))
            ->join('sales', 'customers.id', '=', 'sales.customer_id')
            ->where('sales.status', 'completed')
            ->groupBy('customers.id')
            ->having('last_transaction_date', '<', $cutoffDate)
            ->orderBy('last_transaction_date', 'asc')
            ->limit($limit)
            ->get();
    }
}
