<?php

namespace App\Services;

use App\Models\ProductStock;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReturnService
{
    /**
     * Memproses retur penjualan (Barang kembali ke stok, uang dikembalikan).
     */
    public function processSaleReturn(Sale $sale, array $data, ?int $userId = null): SaleReturn
    {
        return DB::transaction(function () use ($data, $sale, $userId) {
            $total = 0;
            $return = SaleReturn::create([
                'return_number' => 'RTS-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'sale_id'       => $sale->id,
                'user_id'       => $userId,
                'return_date'   => now()->toDateString(),
                'reason'        => $data['reason'] ?? null,
                'refund_method' => $data['refund_method'],
                'branch_id'     => $sale->branch_id,
                'total'         => 0,
            ]);

            foreach ($data['items'] as $item) {
                $saleItem = $sale->items()->where('product_id', $item['product_id'])->firstOrFail();
                $subtotal = $saleItem->price * $item['quantity'];
                $total += $subtotal;

                SaleReturnItem::create([
                    'sale_return_id' => $return->id,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'price'          => $saleItem->price,
                    'subtotal'       => $subtotal,
                ]);

                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item['product_id'], 'warehouse_id' => $sale->warehouse_id],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->increment('quantity', $item['quantity']);

                StockMovement::create([
                    'product_id'      => $item['product_id'],
                    'warehouse_id'    => $sale->warehouse_id,
                    'type'            => 'sale_return',
                    'quantity'        => $item['quantity'],
                    'quantity_before' => $before,
                    'quantity_after'  => $before + $item['quantity'],
                    'reference_type'  => SaleReturn::class,
                    'reference_id'    => $return->id,
                    'user_id'         => $userId,
                ]);
            }

            $return->update(['total' => $total]);
            return $return;
        });
    }

    /**
     * Membatalkan retur penjualan.
     */
    public function cancelSaleReturn(SaleReturn $saleReturn, ?int $userId = null): void
    {
        DB::transaction(function () use ($saleReturn, $userId) {
            foreach ($saleReturn->items as $item) {
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $saleReturn->sale->warehouse_id],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->decrement('quantity', $item->quantity);

                StockMovement::create([
                    'product_id'      => $item->product_id,
                    'warehouse_id'    => $saleReturn->sale->warehouse_id,
                    'type'            => 'sale_return_cancel',
                    'quantity'        => -$item->quantity,
                    'quantity_before' => $before,
                    'quantity_after'  => $before - $item->quantity,
                    'reference_type'  => SaleReturn::class,
                    'reference_id'    => $saleReturn->id,
                    'note'            => 'Pembatalan retur penjualan ' . $saleReturn->return_number,
                    'user_id'         => $userId,
                ]);
            }

            $saleReturn->delete();
        });
    }

    /**
     * Memproses retur pembelian (Barang dikembalikan ke supplier, stok berkurang).
     */
    public function processPurchaseReturn(Purchase $purchase, array $data, ?int $userId = null): PurchaseReturn
    {
        return DB::transaction(function () use ($data, $purchase, $userId) {
            $total = 0;
            $return = PurchaseReturn::create([
                'return_number' => 'RTP-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'purchase_id'   => $purchase->id,
                'user_id'       => $userId,
                'return_date'   => now()->toDateString(),
                'reason'        => $data['reason'] ?? null,
                'total'         => 0,
            ]);

            foreach ($data['items'] as $item) {
                $purchaseItem = $purchase->items()->where('product_id', $item['product_id'])->firstOrFail();
                $subtotal     = $purchaseItem->price * $item['quantity'];
                $total += $subtotal;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id'         => $item['product_id'],
                    'quantity'           => $item['quantity'],
                    'price'              => $purchaseItem->price,
                    'subtotal'           => $subtotal,
                ]);

                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item['product_id'], 'warehouse_id' => $purchase->warehouse_id],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->decrement('quantity', $item['quantity']);

                StockMovement::create([
                    'product_id'      => $item['product_id'],
                    'warehouse_id'    => $purchase->warehouse_id,
                    'type'            => 'purchase_return',
                    'quantity'        => -$item['quantity'],
                    'quantity_before' => $before,
                    'quantity_after'  => $before - $item['quantity'],
                    'reference_type'  => PurchaseReturn::class,
                    'reference_id'    => $return->id,
                    'user_id'         => $userId,
                ]);
            }

            $return->update(['total' => $total]);
            return $return;
        });
    }

    /**
     * Membatalkan retur pembelian.
     */
    public function cancelPurchaseReturn(PurchaseReturn $purchaseReturn, ?int $userId = null): void
    {
        DB::transaction(function () use ($purchaseReturn, $userId) {
            foreach ($purchaseReturn->items as $item) {
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $purchaseReturn->purchase->warehouse_id],
                    ['quantity' => 0]
                );
                $before = $stock->quantity;
                $stock->increment('quantity', $item->quantity);

                StockMovement::create([
                    'product_id'      => $item->product_id,
                    'warehouse_id'    => $purchaseReturn->purchase->warehouse_id,
                    'type'            => 'purchase_return_cancel',
                    'quantity'        => $item->quantity,
                    'quantity_before' => $before,
                    'quantity_after'  => $before + $item->quantity,
                    'reference_type'  => PurchaseReturn::class,
                    'reference_id'    => $purchaseReturn->id,
                    'note'            => 'Pembatalan retur pembelian ' . $purchaseReturn->return_number,
                    'user_id'         => $userId,
                ]);
            }

            $purchaseReturn->delete();
        });
    }
}
