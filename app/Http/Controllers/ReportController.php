<?php

namespace App\Http\Controllers;

use App\Exports\BestSellingProductsExport;
use App\Exports\CashMovementExport;
use App\Exports\CustomersReportExport;
use App\Exports\ProfitLossExport;
use App\Exports\PurchasesExport;
use App\Exports\SalesExport;
use App\Exports\StockMovementExport;
use App\Exports\SuppliersReportExport;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        return view('reports.index');
    }

    private function handleReportOutput(Request $request, string $reportType, array $data, string $viewName, string $printViewName, $exportClass = null)
    {
        if ($request->query('export') === 'excel' && $exportClass) {
            return Excel::download(new $exportClass($data), "laporan_{$reportType}_" . now()->format('Ymd_His') . '.xlsx');
        }

        if ($request->query('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($printViewName, $data);
            $pdf->setPaper('A4', 'landscape');
            return $pdf->download("laporan_{$reportType}_" . now()->format('Ymd_His') . '.pdf');
        }

        if ($request->query('print') === 'true') {
            return view($printViewName, $data);
        }

        return view($viewName, $data);
    }

    public function sales(Request $request)
    {
        $data = $this->reportService->getSalesReportData(Auth::user(), $request);
        return $this->handleReportOutput($request, 'penjualan', $data, 'reports.sales', 'reports.sales_print', SalesExport::class);
    }

    public function purchases(Request $request)
    {
        $data = $this->reportService->getPurchasesReportData(Auth::user(), $request);
        return $this->handleReportOutput($request, 'pembelian', $data, 'reports.purchases', 'reports.purchases_print', PurchasesExport::class);
    }

    public function stock(Request $request)
    {
        $data = $this->reportService->getStockReportData(Auth::user(), $request);
        return $this->handleReportOutput($request, 'stok', $data, 'reports.stock', 'reports.stock_print', StockMovementExport::class);
    }

    public function profitLoss(Request $request)
    {
        $data = $this->reportService->getProfitLossReportData(Auth::user(), $request);
        return $this->handleReportOutput($request, 'laba_rugi', $data, 'reports.profit-loss', 'reports.profit-loss_print', ProfitLossExport::class);
    }

    public function cash(Request $request)
    {
        $data = $this->reportService->getCashReportData(Auth::user(), $request);
        return $this->handleReportOutput($request, 'kas', $data, 'reports.cash', 'reports.cash_print', CashMovementExport::class);
    }

    public function bestSellingProducts(Request $request)
    {
        $data = $this->reportService->getBestSellingProductsReportData(Auth::user(), $request);
        return $this->handleReportOutput($request, 'produk_terlaris', $data, 'reports.best-selling-products', 'reports.best-selling-products_print', BestSellingProductsExport::class);
    }

    public function customers(Request $request)
    {
        $data = $this->reportService->getCustomersReportData(Auth::user(), $request);
        return $this->handleReportOutput($request, 'pelanggan', $data, 'reports.customers', 'reports.customers_print', CustomersReportExport::class);
    }

    public function suppliers(Request $request)
    {
        $data = $this->reportService->getSuppliersReportData(Auth::user(), $request);
        return $this->handleReportOutput($request, 'supplier', $data, 'reports.suppliers', 'reports.suppliers_print', SuppliersReportExport::class);
    }

    public function exportSalesExcel(Request $request)
    {
        $request->merge(['export' => 'excel']);
        return $this->sales($request);
    }

    public function exportSalesPdf(Request $request)
    {
        $request->merge(['export' => 'pdf']);
        return $this->sales($request);
    }
}