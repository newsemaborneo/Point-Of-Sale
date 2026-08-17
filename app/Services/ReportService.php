<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CashMovement;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function resolveBranchFilter(User $user, ?int $requestBranchId): array
    {
        $userBranchId = $user->branch_id;
        $isAdminOrSupervisor = $user->hasRole('admin') || $user->hasRole('supervisor');

        $userBranchName = null;
        if ($userBranchId) {
            $userBranch = Branch::find($userBranchId);
            if ($userBranch) {
                $userBranchName = $userBranch->name;
            }
        }

        $branches = $isAdminOrSupervisor ? Branch::orderBy('name')->get() : collect();
        $selectedBranchId = $isAdminOrSupervisor ? $requestBranchId : $userBranchId;

        return [
            'isAdminOrSupervisor' => $isAdminOrSupervisor,
            'userBranchId'        => $userBranchId,
            'userBranchName'      => $userBranchName,
            'branches'            => $branches,
            'selectedBranchId'    => $selectedBranchId,
        ];
    }

    public function getSalesReportData(User $user, Request $request): array
    {
        $branchFilter = $this->resolveBranchFilter($user, $request->input('branch_id'));
        $selectedBranchId = $branchFilter['selectedBranchId'];

        $sales = Sale::with(['items', 'customer', 'branch'])
            ->where('status', 'completed')
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
            ->latest('created_at')
            ->get();

        return array_merge($branchFilter, [
            'sales'             => $sales,
            'totalSales'        => $sales->sum('grand_total'),
            'totalTransactions' => $sales->count(),
            'request'           => $request,
        ]);
    }

    public function getPurchasesReportData(User $user, Request $request): array
    {
        $branchFilter = $this->resolveBranchFilter($user, $request->input('branch_id'));
        $selectedBranchId = $branchFilter['selectedBranchId'];

        $purchases = Purchase::with(['items.product', 'supplier', 'branch'])
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
            ->latest('created_at')
            ->get();

        return array_merge($branchFilter, [
            'purchases'      => $purchases,
            'totalPurchases' => $purchases->sum('total'),
            'request'        => $request,
        ]);
    }

    public function getStockReportData(User $user, Request $request): array
    {
        $branchFilter = $this->resolveBranchFilter($user, $request->input('branch_id'));
        $selectedBranchId = $branchFilter['selectedBranchId'];
        $selectedWarehouseId = $request->warehouse_id;

        $warehouses = collect();
        if ($selectedBranchId) {
            $warehouses = Warehouse::where('branch_id', $selectedBranchId)->get();
        } elseif ($branchFilter['isAdminOrSupervisor']) {
            $warehouses = Warehouse::all();
        } elseif ($user->branch_id) {
            $warehouses = Warehouse::where('branch_id', $user->branch_id)->get();
        }

        $movements = StockMovement::with(['product', 'warehouse'])
            ->when($selectedWarehouseId, fn ($q) => $q->where('warehouse_id', $selectedWarehouseId))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($selectedBranchId && !$selectedWarehouseId, function ($query) use ($selectedBranchId) {
                $warehouseIds = Warehouse::where('branch_id', $selectedBranchId)->pluck('id');
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->latest('created_at')
            ->get();

        return array_merge($branchFilter, [
            'movements'           => $movements,
            'warehouses'          => $warehouses,
            'selectedWarehouseId' => $selectedWarehouseId,
            'request'             => $request,
        ]);
    }

    public function getProfitLossReportData(User $user, Request $request): array
    {
        $branchFilter = $this->resolveBranchFilter($user, $request->input('branch_id'));
        $selectedBranchId = $branchFilter['selectedBranchId'];

        $from = $request->date_from ?? now()->startOfMonth()->toDateString();
        $to   = $request->date_to ?? now()->endOfMonth()->toDateString();

        $salesQuery        = Sale::where('status', 'completed')->whereBetween('created_at', [$from, $to]);
        $saleItemsQuery    = SaleItem::whereHas('sale', fn ($q) => $q->where('status', 'completed')->whereBetween('created_at', [$from, $to]));
        $cashMovementQuery = CashMovement::where('type', 'out')->whereBetween('created_at', [$from, $to]);

        if ($selectedBranchId) {
            $salesQuery->where('branch_id', $selectedBranchId);
            $saleItemsQuery->whereHas('sale', fn ($q) => $q->where('branch_id', $selectedBranchId));
            $cashMovementQuery->whereHas('cashRegister', fn ($q) => $q->where('branch_id', $selectedBranchId));
        }

        $revenue = $salesQuery->sum('grand_total');
        $cogs = $saleItemsQuery
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->sum(DB::raw('sale_items.quantity * products.purchase_price'));

        $expenses    = $cashMovementQuery->sum('amount');
        $grossProfit = $revenue - $cogs;
        $netProfit   = $grossProfit - $expenses;

        return array_merge($branchFilter, [
            'from'        => $from,
            'to'          => $to,
            'revenue'     => $revenue,
            'cogs'        => $cogs,
            'expenses'    => $expenses,
            'grossProfit' => $grossProfit,
            'netProfit'   => $netProfit,
            'request'     => $request,
        ]);
    }

    public function getCashReportData(User $user, Request $request): array
    {
        $branchFilter = $this->resolveBranchFilter($user, $request->input('branch_id'));
        $selectedBranchId = $branchFilter['selectedBranchId'];

        $movements = CashMovement::with('cashRegister.branch')
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($selectedBranchId, function ($query) use ($selectedBranchId) {
                $query->whereHas('cashRegister', fn ($q) => $q->where('branch_id', $selectedBranchId));
            })
            ->get();

        return array_merge($branchFilter, [
            'movements' => $movements,
            'totalIn'   => $movements->where('type', 'in')->sum('amount'),
            'totalOut'  => $movements->where('type', 'out')->sum('amount'),
            'request'   => $request,
        ]);
    }

    public function getBestSellingProductsReportData(User $user, Request $request): array
    {
        $branchFilter = $this->resolveBranchFilter($user, $request->input('branch_id'));
        $selectedBranchId = $branchFilter['selectedBranchId'];

        $data = SaleItem::selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->whereHas('sale', function ($q) use ($request, $selectedBranchId) {
                $q->where('status', 'completed');
                $q->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
                  ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->limit(20)
            ->get();

        return array_merge($branchFilter, [
            'data'    => $data,
            'request' => $request,
        ]);
    }

    public function getCustomersReportData(User $user, Request $request): array
    {
        $branchFilter = $this->resolveBranchFilter($user, $request->input('branch_id'));
        $selectedBranchId = $branchFilter['selectedBranchId'];

        $data = Sale::selectRaw('customer_id, COUNT(*) as total_transactions, SUM(grand_total) as total_spent')
            ->whereNotNull('customer_id')
            ->where('status', 'completed')
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->with('customer')
            ->get();

        return array_merge($branchFilter, [
            'data'    => $data,
            'request' => $request,
        ]);
    }

    public function getSuppliersReportData(User $user, Request $request): array
    {
        $branchFilter = $this->resolveBranchFilter($user, $request->input('branch_id'));
        $selectedBranchId = $branchFilter['selectedBranchId'];

        $data = Purchase::selectRaw('supplier_id, COUNT(*) as total_purchases, SUM(total) as total_amount')
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->groupBy('supplier_id')
            ->with('supplier')
            ->get();

        return array_merge($branchFilter, [
            'data'    => $data,
            'request' => $request,
        ]);
    }
}
