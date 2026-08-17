<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stock\StartStockOpnameRequest;
use App\Http\Requests\Stock\StockAdjustmentRequest;
use App\Http\Requests\Stock\StockInRequest;
use App\Http\Requests\Stock\StockOutRequest;
use App\Http\Requests\Stock\TransferStockRequest;
use App\Http\Requests\Stock\UpdateStockOpnameItemRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function createStockInForm()
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('layouts.in', compact('products', 'warehouses'));
    }

    public function createStockOutForm()
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('layouts.out', compact('products', 'warehouses'));
    }

    public function createTransferForm()
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('layouts.transfer', compact('products', 'warehouses'));
    }

    public function createAdjustmentForm()
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('layouts.adjustment', compact('products', 'warehouses'));
    }

    public function stockIn(StockInRequest $request)
    {
        $this->stockService->stockIn($request->validated(), $request->user()?->id);
        return redirect()->back()->with('success', 'Stok masuk berhasil disimpan.');
    }

    public function stockOut(StockOutRequest $request)
    {
        $this->stockService->stockOut($request->validated(), $request->user()?->id);
        return redirect()->back()->with('success', 'Stok keluar berhasil disimpan.');
    }

    public function transfer(TransferStockRequest $request)
    {
        $this->stockService->transferStock($request->validated(), $request->user()?->id);
        return redirect()->back()->with('success', 'Transfer stok berhasil.');
    }

    public function adjustment(StockAdjustmentRequest $request)
    {
        $this->stockService->directAdjustment($request->validated(), $request->user()?->id);
        return redirect()->back()->with('success', 'Penyesuaian stok berhasil disimpan.');
    }

    public function history(Request $request)
    {
        $movements = StockMovement::with(['product', 'warehouse', 'destinationWarehouse', 'user'])
            ->when($request->product_id, fn ($q) => $q->where('product_id', $request->product_id))
            ->when($request->warehouse_id, fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(20);

        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $types = ['in', 'out', 'transfer', 'adjustment', 'purchase', 'sale', 'sale_return', 'purchase_return', 'opname'];

        return view('stock.history', compact('movements', 'products', 'warehouses', 'types'));
    }

    public function lowStockAlert()
    {
        $lowStockProducts = Product::with('stocks')
            ->get()
            ->filter(fn (Product $p) => $p->isLowStock())
            ->values();

        return view('stock.low-alert', compact('lowStockProducts'));
    }

    public function startOpname(StartStockOpnameRequest $request)
    {
        $this->stockService->startOpname($request->validated(), $request->user()?->id);
        return redirect()->route('dashboard')->with('success', 'Stock opname dimulai.');
    }

    public function updateOpnameItem(UpdateStockOpnameItemRequest $request, StockOpnameItem $item)
    {
        $this->stockService->updateOpnameItem($item, $request->validated());
        return redirect()->back()->with('success', 'Item stock opname berhasil diperbarui.');
    }

    public function completeOpname(Request $request, StockOpname $stockOpname)
    {
        $this->stockService->completeOpname($stockOpname, $request->user()?->id);
        return redirect()->route('dashboard')->with('success', 'Stock opname selesai.');
    }

    public function createOpnameForm()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        return view('layouts.opname', compact('warehouses'));
    }
}
