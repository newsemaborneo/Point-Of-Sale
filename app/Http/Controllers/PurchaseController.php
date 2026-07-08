<?php
namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\SupplierDebt;
use App\Models\StockMovement;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    // 8. Pembelian: Purchase Order, barang diterima, retur pembelian, faktur pembelian

    public function indexPO(Request $request)
    {
        $orders = PurchaseOrder::with(['supplier', 'warehouse'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return view('purchases.orders.index', compact('orders'));
    }

    public function storePO(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $result = DB::transaction(function () use ($data, $request) {
            $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);

            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => $request->user()?->id,
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'status' => 'draft',
                'total' => $total,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            return $po->load('items.product');
        });

        return redirect()->route('purchases.orders.index')->with('success', 'Purchase order berhasil dibuat.');
    }

    public function updatePOStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate(['status' => 'required|in:draft,sent,partial,received,cancelled']);
        $purchaseOrder->update($data);

        return back()->with('success', 'Status purchase order berhasil diperbarui.');
    }

    public function receiveGoods(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'purchase_date' => 'required|date',
        ]);

        $purchase = DB::transaction(function () use ($data, $purchaseOrder, $request) {
            $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);
            $paid = $data['paid_amount'] ?? 0;

            $purchase = Purchase::create([
                'invoice_number' => 'PUR-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'warehouse_id' => $purchaseOrder->warehouse_id,
                'user_id' => $request->user()?->id,
                'purchase_date' => $data['purchase_date'],
                'total' => $total,
                'paid_amount' => $paid,
                'payment_status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
            ]);

            foreach ($data['items'] as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);

                $stock = ProductStock::firstOrCreate([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $purchaseOrder->warehouse_id,
                ], ['quantity' => 0]);

                $before = $stock->quantity;
                $stock->increment('quantity', $item['quantity']);

                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $purchaseOrder->warehouse_id,
                    'type' => 'purchase',
                    'quantity' => $item['quantity'],
                    'quantity_before' => $before,
                    'quantity_after' => $before + $item['quantity'],
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'user_id' => $request->user()?->id,
                ]);

                PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)
                    ->where('product_id', $item['product_id'])
                    ->increment('received_quantity', $item['quantity']);
            }

            if ($purchase->payment_status !== 'paid') {
                SupplierDebt::create([
                    'supplier_id' => $purchaseOrder->supplier_id,
                    'purchase_id' => $purchase->id,
                    'amount' => $total,
                    'paid_amount' => $paid,
                    'status' => $paid > 0 ? 'partial' : 'unpaid',
                ]);
            }

            $purchaseOrder->update(['status' => 'received']);

            return $purchase->load('items.product');
        });

        return redirect()->route('purchases.index')->with('success', 'Barang berhasil diterima dan faktur dibuat.');
    }

    /** Form untuk menerima barang dari Purchase Order */
    public function createReceiveForm(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'items.product']);
        return view('purchases.orders.receive-form', compact('purchaseOrder'));
    }

    /** Form untuk membuat pembelian langsung (tanpa PO) */
    public function createPurchaseForm()
    {
        $suppliers  = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $products   = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'warehouses', 'products'));
    }

    /** Simpan pembelian langsung (tanpa PO) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'   => 'required|exists:suppliers,id',
            'warehouse_id'  => 'required|exists:warehouses,id',
            'purchase_date' => 'required|date',
            'note'          => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
            'paid_amount'   => 'nullable|numeric|min:0',
        ]);

        $purchase = DB::transaction(function () use ($data, $request) {
            $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);
            $paid  = $data['paid_amount'] ?? 0;

            $purchase = Purchase::create([
                'invoice_number' => 'PUR-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'supplier_id'    => $data['supplier_id'],
                'warehouse_id'   => $data['warehouse_id'],
                'user_id'        => $request->user()?->id,
                'purchase_date'  => $data['purchase_date'],
                'total'          => $total,
                'paid_amount'    => $paid,
                'payment_status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                'note'           => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'price'       => $item['price'],
                    'subtotal'    => $item['quantity'] * $item['price'],
                ]);

                $stock  = ProductStock::firstOrCreate(
                    ['product_id' => $item['product_id'], 'warehouse_id' => $data['warehouse_id']],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->increment('quantity', $item['quantity']);

                StockMovement::create([
                    'product_id'      => $item['product_id'],
                    'warehouse_id'    => $data['warehouse_id'],
                    'type'            => 'purchase',
                    'quantity'        => $item['quantity'],
                    'quantity_before' => $before,
                    'quantity_after'  => $before + $item['quantity'],
                    'reference_type'  => Purchase::class,
                    'reference_id'    => $purchase->id,
                    'note'            => $data['note'] ?? null,
                    'user_id'         => $request->user()?->id,
                ]);
            }

            if ($paid < $total) {
                SupplierDebt::create([
                    'supplier_id' => $data['supplier_id'],
                    'purchase_id' => $purchase->id,
                    'amount'      => $total,
                    'paid_amount' => $paid,
                    'status'      => $paid > 0 ? 'partial' : 'unpaid',
                ]);
            }

            return $purchase;
        });

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
