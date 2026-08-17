<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchase\ReceiveGoodsRequest;
use App\Http\Requests\Purchase\StoreDirectPurchaseRequest;
use App\Http\Requests\Purchase\StorePurchaseOrderRequest;
use App\Http\Requests\Purchase\UpdatePOStatusRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    protected PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function indexPO(Request $request)
    {
        $orders = PurchaseOrder::with(['supplier', 'warehouse'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return view('purchases.orders.index', compact('orders'));
    }

    public function storePO(StorePurchaseOrderRequest $request)
    {
        $this->purchaseService->createPurchaseOrder($request->validated(), $request->user()?->id);
        return redirect()->route('purchases.orders.index')->with('success', 'Purchase order berhasil dibuat.');
    }

    public function updatePOStatus(UpdatePOStatusRequest $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update($request->validated());
        return back()->with('success', 'Status purchase order berhasil diperbarui.');
    }

    public function receiveGoods(ReceiveGoodsRequest $request, PurchaseOrder $purchaseOrder)
    {
        $this->purchaseService->receiveGoods($purchaseOrder, $request->validated(), $request->user()?->id);
        return redirect()->route('purchases.index')->with('success', 'Barang berhasil diterima dan faktur dibuat.');
    }

    public function createReceiveForm(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'items.product']);
        return view('purchases.orders.receive-form', compact('purchaseOrder'));
    }

    public function createPurchaseForm()
    {
        $suppliers  = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $products   = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(StoreDirectPurchaseRequest $request)
    {
        $purchase = $this->purchaseService->createDirectPurchase($request->validated(), $request->user()?->id);

        return redirect()->route('purchases.index')
            ->with('success', 'Pembelian berhasil dicatat. Invoice: ' . $purchase->invoice_number);
    }

    public function indexPurchases(Request $request)
    {
        $purchases = Purchase::with('supplier')
            ->when($request->payment_status, fn ($q) => $q->where('payment_status', $request->payment_status))
            ->latest()
            ->paginate(20);

        return view('purchases.index', compact('purchases'));
    }

    public function showInvoice(Request $request, Purchase $purchase)
    {
        $purchase->load(['items.product', 'supplier', 'warehouse']);

        return view('purchases.invoice', compact('purchase'));
    }
}
