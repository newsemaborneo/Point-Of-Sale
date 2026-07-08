<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\CashMovement;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel; // Asumsi package ini sudah terinstall
use App\Exports\SalesExport; // Asumsi kelas export ini ada
use App\Exports\PurchasesExport;
use App\Exports\StockMovementExport;
use App\Exports\ProfitLossExport;
use App\Exports\CashMovementExport;
use App\Exports\BestSellingProductsExport;
use App\Exports\CustomersReportExport;
use App\Exports\SuppliersReportExport;

class ReportController extends Controller
{
    // 11. Laporan: penjualan, pembelian, stok, laba rugi, kas, transaksi, produk terlaris,
    //     pelanggan, supplier, export PDF/Excel

    public function index()
    {
        return view('reports.index');
    }


    private function resolveBranchFilter(Request $request): array
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

        $branches = $isAdminOrSupervisor ? Branch::orderBy('name')->get() : collect();

        $selectedBranchId = $isAdminOrSupervisor
            ? $request->input('branch_id')
            : $userBranchId;

        return [
            'isAdminOrSupervisor' => $isAdminOrSupervisor,
            'userBranchId'        => $userBranchId,
            'userBranchName'      => $userBranchName,
            'branches'            => $branches,
            'selectedBranchId'    => $selectedBranchId,
        ];
    }

    /**
     * Helper to handle report output based on request parameters (view, excel, print).
     */
    private function handleReportOutput(Request $request, string $reportType, array $data, string $viewName, string $printViewName, $exportClass = null)
    {
        if ($request->query('export') === 'excel' && $exportClass) {
            return Excel::download(new $exportClass($data), "laporan_{$reportType}_" . now()->format('Ymd_His') . '.xlsx');
        }

        if ($request->query('print') === 'true') {
            return view($printViewName, $data);
        }

        return view($viewName, $data);
    }

    public function sales(Request $request)
    {
        [
            'isAdminOrSupervisor' => $isAdminOrSupervisor,
            'userBranchName'      => $userBranchName,
            'branches'            => $branches,
            'selectedBranchId'    => $selectedBranchId,
        ] = $this->resolveBranchFilter($request);

        $sales = Sale::with(['items', 'customer', 'branch'])
            ->where('status', 'completed')
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
            ->latest('created_at')
            ->get();

        $totalSales = $sales->sum('grand_total');
        $totalTransactions = $sales->count();

        $data = compact(
            'sales',
            'totalSales',
            'totalTransactions',
            'userBranchName',
            'branches',
            'selectedBranchId',
            'isAdminOrSupervisor',
            'request'
        );

        return $this->handleReportOutput($request, 'penjualan', $data, 'reports.sales', 'reports.sales_print', SalesExport::class);
    }

    public function purchases(Request $request)
    {
        [
            'isAdminOrSupervisor' => $isAdminOrSupervisor,
            'userBranchName'      => $userBranchName,
            'branches'            => $branches,
            'selectedBranchId'    => $selectedBranchId,
        ] = $this->resolveBranchFilter($request);

        $purchases = Purchase::with('items.product', 'supplier', 'branch')
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
            ->latest('created_at')
            ->get();

        $totalPurchases = $purchases->sum('total');

        $data = compact(
            'purchases',
            'totalPurchases',
            'branches',
            'selectedBranchId',
            'isAdminOrSupervisor',
            'userBranchName',
            'request'
        );

        return $this->handleReportOutput($request, 'pembelian', $data, 'reports.purchases', 'reports.purchases_print', PurchasesExport::class);
    }

    public function stock(Request $request)
    {
        [
            'isAdminOrSupervisor' => $isAdminOrSupervisor,
            'userBranchName'      => $userBranchName,
            'branches'            => $branches,
            'selectedBranchId'    => $selectedBranchId,
        ] = $this->resolveBranchFilter($request);

        $warehouses = collect();
        $selectedWarehouseId = $request->warehouse_id;

        if ($selectedBranchId) {
            $warehouses = Warehouse::where('branch_id', $selectedBranchId)->get();
        } elseif ($isAdminOrSupervisor) {
            // Admin/supervisor tanpa filter cabang → tampilkan semua gudang
            $warehouses = Warehouse::all();
        } elseif (Auth::user()->branch_id) {
            // Fallback: user biasa yang entah kenapa selectedBranchId-nya kosong
            $warehouses = Warehouse::where('branch_id', Auth::user()->branch_id)->get();
        }

        $movements = StockMovement::with(['product', 'warehouse'])
            ->when($selectedWarehouseId, fn ($q) => $q->where('warehouse_id', $selectedWarehouseId))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($selectedBranchId && !$selectedWarehouseId, function ($query) use ($selectedBranchId) {
                // Filter berdasarkan cabang hanya jika tidak ada gudang spesifik yang dipilih
                $warehouseIds = Warehouse::where('branch_id', $selectedBranchId)->pluck('id');
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->latest('created_at')
            ->get();

        $data = compact(
            'movements',
            'branches',
            'selectedBranchId',
            'warehouses',
            'selectedWarehouseId',
            'isAdminOrSupervisor',
            'userBranchName',
            'request'
        );

        return $this->handleReportOutput($request, 'stok', $data, 'reports.stock', 'reports.stock_print', StockMovementExport::class);
    }

    /** Laporan laba rugi sederhana: pendapatan penjualan - HPP - pengeluaran kas */
    public function profitLoss(Request $request)
    {
        [
            'isAdminOrSupervisor' => $isAdminOrSupervisor,
            'userBranchName'      => $userBranchName,
            'branches'            => $branches,
            'selectedBranchId'    => $selectedBranchId,
        ] = $this->resolveBranchFilter($request);

        $from = $request->date_from ?? now()->startOfMonth()->toDateString();
        $to = $request->date_to ?? now()->endOfMonth()->toDateString();

        $salesQuery = Sale::where('status', 'completed')->whereBetween('created_at', [$from, $to]);
        $saleItemsQuery = SaleItem::whereHas('sale', fn ($q) => $q->where('status', 'completed')->whereBetween('created_at', [$from, $to]));
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

        $expenses = $cashMovementQuery->sum('amount');
        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $expenses;

        $data = compact(
            'from',
            'to',
            'revenue',
            'cogs',
            'expenses',
            'grossProfit',
            'netProfit',
            'branches',
            'selectedBranchId',
            'isAdminOrSupervisor',
            'userBranchName',
            'request'
        );

        return $this->handleReportOutput($request, 'laba_rugi', $data, 'reports.profit-loss', 'reports.profit-loss_print', ProfitLossExport::class);
    }

    public function cash(Request $request)
    {
        [
            'isAdminOrSupervisor' => $isAdminOrSupervisor,
            'userBranchName'      => $userBranchName,
            'branches'            => $branches,
            'selectedBranchId'    => $selectedBranchId,
        ] = $this->resolveBranchFilter($request);

        $movements = CashMovement::with('cashRegister.branch')
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($selectedBranchId, function ($query) use ($selectedBranchId) {
                $query->whereHas('cashRegister', fn ($q) => $q->where('branch_id', $selectedBranchId));
            })
            ->get();

        $totalIn = $movements->where('type', 'in')->sum('amount');
        $totalOut = $movements->where('type', 'out')->sum('amount');

        $data = compact(
            'movements',
            'totalIn',
            'totalOut',
            'branches',
            'selectedBranchId',
            'isAdminOrSupervisor',
            'userBranchName',
            'request'
        );

        return $this->handleReportOutput($request, 'kas', $data, 'reports.cash', 'reports.cash_print', CashMovementExport::class);
    }

    public function bestSellingProducts(Request $request)
    {
        [
            'isAdminOrSupervisor' => $isAdminOrSupervisor,
            'userBranchName'      => $userBranchName,
            'branches'            => $branches,
            'selectedBranchId'    => $selectedBranchId,
        ] = $this->resolveBranchFilter($request);

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

        $reportData = compact(
            'data',
            'branches',
            'selectedBranchId',
            'isAdminOrSupervisor',
            'userBranchName',
            'request'
        );

        return $this->handleReportOutput($request, 'produk_terlaris', $reportData, 'reports.best-selling-products', 'reports.best-selling-products_print', BestSellingProductsExport::class);
    }

    public function customers(Request $request)
    {
        [
            'isAdminOrSupervisor' => $isAdminOrSupervisor,
            'userBranchName'      => $userBranchName,
            'branches'            => $branches,
            'selectedBranchId'    => $selectedBranchId,
        ] = $this->resolveBranchFilter($request);

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

        $reportData = compact(
            'data',
            'branches',
            'selectedBranchId',
            'isAdminOrSupervisor',
            'userBranchName',
            'request'
        );

        return $this->handleReportOutput($request, 'pelanggan', $reportData, 'reports.customers', 'reports.customers_print', CustomersReportExport::class);
    }

    public function suppliers(Request $request)
    {
        [
            'isAdminOrSupervisor' => $isAdminOrSupervisor,
            'userBranchName'      => $userBranchName,
            'branches'            => $branches,
            'selectedBranchId'    => $selectedBranchId,
        ] = $this->resolveBranchFilter($request);

        $data = Purchase::selectRaw('supplier_id, COUNT(*) as total_purchases, SUM(total) as total_amount')
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->groupBy('supplier_id')
            ->with('supplier')
            ->get();

        $reportData = compact(
            'data',
            'branches',
            'selectedBranchId',
            'isAdminOrSupervisor',
            'userBranchName',
            'request'
        );

        return $this->handleReportOutput($request, 'supplier', $reportData, 'reports.suppliers', 'reports.suppliers_print', SuppliersReportExport::class);
    }

    
}