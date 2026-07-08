<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReturnController extends Controller
{
    // 9. Retur: retur penjualan, retur pembelian, alasan retur, pengembalian uang

    /** Menampilkan form untuk retur penjualan */
    public function createSaleReturnForm(Sale $sale)
    {
        $sale->load('items.product');
        return view('returns.sale-return-form', compact('sale'));
    }

    /** Retur penjualan: barang kembali ke stok, uang dikembalikan ke pelanggan */
    public function storeSaleReturn(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
            'refund_method' => 'required|in:cash,store_credit,bank_transfer',
        ]);

        DB::transaction(function () use ($data, $sale, $request) {
            $total = 0;
            $return = SaleReturn::create([
                'return_number' => 'RTS-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'sale_id' => $sale->id,
                'user_id' => $request->user()?->id,
                'return_date' => now()->toDateString(),
                'reason' => $data['reason'] ?? null,
                'refund_method' => $data['refund_method'],
                'branch_id' => $sale->branch_id, // Tambahkan branch_id dari sale
                'total' => 0,
            ]);

            foreach ($data['items'] as $item) {
                $saleItem = $sale->items()->where('product_id', $item['product_id'])->firstOrFail();
                $subtotal = $saleItem->price * $item['quantity'];
                $total += $subtotal;

                SaleReturnItem::create([
                    'sale_return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $saleItem->price,
                    'subtotal' => $subtotal,
                ]);

                // Barang kembali ke stok gudang asal transaksi
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item['product_id'], 'warehouse_id' => $sale->warehouse_id],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->increment('quantity', $item['quantity']);

                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $sale->warehouse_id,
                    'type' => 'sale_return',
                    'quantity' => $item['quantity'],
                    'quantity_before' => $before,
                    'quantity_after' => $before + $item['quantity'],
                    'reference_type' => SaleReturn::class,
                    'reference_id' => $return->id,
                    'user_id' => $request->user()?->id,
                ]);
            }

            $return->update(['total' => $total]);
        });

        return redirect()->route('sale-returns.index')->with('success', 'Retur penjualan berhasil dicatat.');
    }

    public function indexSaleReturns(Request $request)
    {
        $returnsQuery = SaleReturn::with(['sale', 'sale.customer']);

        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('supervisor')) {
            $returnsQuery->where('branch_id', auth()->user()->branch_id);
        }
        $returns = $returnsQuery->latest()->paginate(20);
        return view('returns.sale-returns', compact('returns'));
    }

    /** Menampilkan form untuk retur pembelian */
    public function createPurchaseReturnForm(Purchase $purchase)
    {
        $purchase->load('items.product');
        return view('returns.purchase-return-form', compact('purchase'));
    }

    /** Retur pembelian: barang keluar dari stok, dikembalikan ke supplier */
    public function storePurchaseReturn(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $purchase, $request) {
            $total = 0;
            $return = PurchaseReturn::create([
                'return_number' => 'RTP-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'purchase_id' => $purchase->id,
                'user_id' => $request->user()?->id,
                'return_date' => now()->toDateString(),
                'reason' => $data['reason'] ?? null,
                'total' => 0,
            ]);

            foreach ($data['items'] as $item) {
                $purchaseItem = $purchase->items()->where('product_id', $item['product_id'])->firstOrFail();
                $subtotal = $purchaseItem->price * $item['quantity'];
                $total += $subtotal;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $purchaseItem->price,
                    'subtotal' => $subtotal,
                ]);

                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item['product_id'], 'warehouse_id' => $purchase->warehouse_id],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->decrement('quantity', $item['quantity']);

                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $purchase->warehouse_id,
                    'type' => 'purchase_return',
                    'quantity' => -$item['quantity'],
                    'quantity_before' => $before,
                    'quantity_after' => $before - $item['quantity'],
                    'reference_type' => PurchaseReturn::class,
                    'reference_id' => $return->id,
                    'user_id' => $request->user()?->id,
                ]);
            }

            $return->update(['total' => $total]);
        });

        return redirect()->route('purchase-returns.index')->with('success', 'Retur pembelian berhasil dicatat.');
    }

    public function indexPurchaseReturns(Request $request)
    {
        $returns = PurchaseReturn::with(['purchase', 'purchase.supplier'])->latest()->paginate(20);
        return view('returns.purchase-returns', compact('returns'));
    }

    // ===== CRUD untuk Sale Returns =====

    public function showSaleReturn(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale.customer', 'items.product', 'user']);
        return view('returns.sale-return-show', compact('saleReturn'));
    }

    public function editSaleReturn(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale', 'items.product']);
        return view('returns.sale-return-edit', compact('saleReturn'));
    }

    public function updateSaleReturn(Request $request, SaleReturn $saleReturn)
    {
        $data = $request->validate([
            'reason' => 'nullable|string',
            'refund_method' => 'required|in:cash,store_credit,bank_transfer',
        ]);

        $saleReturn->update($data);
        return redirect()->route('sale-returns.index')->with('success', 'Data retur penjualan berhasil diperbarui.');
    }

    public function destroySaleReturn(SaleReturn $saleReturn)
    {
        // Kembalikan stok yang sudah dikurangi saat retur
        DB::transaction(function () use ($saleReturn) {
            foreach ($saleReturn->items as $item) {
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $saleReturn->sale->warehouse_id],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->decrement('quantity', $item->quantity);

                // Catat pergerakan stok pembatalan retur
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $saleReturn->sale->warehouse_id,
                    'type' => 'sale_return_cancel',
                    'quantity' => -$item->quantity,
                    'quantity_before' => $before,
                    'quantity_after' => $before - $item->quantity,
                    'reference_type' => SaleReturn::class,
                    'reference_id' => $saleReturn->id,
                    'note' => 'Pembatalan retur penjualan ' . $saleReturn->return_number,
                    'user_id' => auth()->id(),
                ]);
            }

            $saleReturn->delete();
        });

        return redirect()->route('sale-returns.index')->with('success', 'Retur penjualan berhasil dibatalkan.');
    }

    // ===== CRUD untuk Purchase Returns =====

    public function showPurchaseReturn(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase.supplier', 'items.product', 'user']);
        return view('returns.purchase-return-show', compact('purchaseReturn'));
    }

    public function editPurchaseReturn(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase', 'items.product']);
        return view('returns.purchase-return-edit', compact('purchaseReturn'));
    }

    public function updatePurchaseReturn(Request $request, PurchaseReturn $purchaseReturn)
    {
        $data = $request->validate([
            'reason' => 'nullable|string',
        ]);

        $purchaseReturn->update($data);
        return redirect()->route('purchase-returns.index')->with('success', 'Data retur pembelian berhasil diperbarui.');
    }

    public function destroyPurchaseReturn(PurchaseReturn $purchaseReturn)
    {
        // Kembalikan stok yang sudah ditambahkan saat retur
        DB::transaction(function () use ($purchaseReturn) {
            foreach ($purchaseReturn->items as $item) {
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $purchaseReturn->purchase->warehouse_id],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->increment('quantity', $item->quantity);

                // Catat pergerakan stok pembatalan retur
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $purchaseReturn->purchase->warehouse_id,
                    'type' => 'purchase_return_cancel',
                    'quantity' => $item->quantity,
                    'quantity_before' => $before,
                    'quantity_after' => $before + $item->quantity,
                    'reference_type' => PurchaseReturn::class,
                    'reference_id' => $purchaseReturn->id,
                    'note' => 'Pembatalan retur pembelian ' . $purchaseReturn->return_number,
                    'user_id' => auth()->id(),
                ]);
            }

            $purchaseReturn->delete();
        });

        return redirect()->route('purchase-returns.index')->with('success', 'Retur pembelian berhasil dibatalkan.');
    }
}
