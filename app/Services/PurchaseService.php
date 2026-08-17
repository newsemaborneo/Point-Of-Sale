<?php

namespace App\Services;

use App\Models\ProductStock;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\SupplierDebt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseService
{
    /**
     * Membuat Purchase Order (PO) baru.
     */
    public function createPurchaseOrder(array $data, ?int $userId = null): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);

            $po = PurchaseOrder::create([
                'po_number'     => 'PO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'supplier_id'   => $data['supplier_id'],
                'warehouse_id'  => $data['warehouse_id'],
                'user_id'       => $userId,
                'order_date'    => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'status'        => 'draft',
                'total'         => $total,
                'note'          => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $item['quantity'],
                    'price'             => $item['price'],
                    'subtotal'          => $item['quantity'] * $item['price'],
                ]);
            }

            return $po->load('items.product');
        });
    }

    /**
     * Memproses penerimaan barang dari Purchase Order.
     */
    public function receiveGoods(PurchaseOrder $purchaseOrder, array $data, ?int $userId = null): Purchase
    {
        return DB::transaction(function () use ($data, $purchaseOrder, $userId) {
            $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);
            $paid  = $data['paid_amount'] ?? 0;

            $purchase = Purchase::create([
                'invoice_number'    => 'PUR-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id'       => $purchaseOrder->supplier_id,
                'warehouse_id'      => $purchaseOrder->warehouse_id,
                'user_id'           => $userId,
                'purchase_date'     => $data['purchase_date'],
                'total'             => $total,
                'paid_amount'       => $paid,
                'payment_status'    => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
            ]);

            foreach ($data['items'] as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'price'       => $item['price'],
                    'subtotal'    => $item['quantity'] * $item['price'],
                ]);

                $stock = ProductStock::firstOrCreate([
                    'product_id'   => $item['product_id'],
                    'warehouse_id' => $purchaseOrder->warehouse_id,
                ], ['quantity' => 0]);

                $before = $stock->quantity;
                $stock->increment('quantity', $item['quantity']);

                StockMovement::create([
                    'product_id'      => $item['product_id'],
                    'warehouse_id'    => $purchaseOrder->warehouse_id,
                    'type'            => 'purchase',
                    'quantity'        => $item['quantity'],
                    'quantity_before' => $before,
                    'quantity_after'  => $before + $item['quantity'],
                    'reference_type'  => Purchase::class,
                    'reference_id'    => $purchase->id,
                    'user_id'         => $userId,
                ]);

                PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)
                    ->where('product_id', $item['product_id'])
                    ->increment('received_quantity', $item['quantity']);
            }

            if ($purchase->payment_status !== 'paid') {
                SupplierDebt::create([
                    'supplier_id' => $purchaseOrder->supplier_id,
                    'purchase_id' => $purchase->id,
                    'amount'      => $total,
                    'paid_amount' => $paid,
                    'status'      => $paid > 0 ? 'partial' : 'unpaid',
                ]);
            }

            $purchaseOrder->update(['status' => 'received']);

            return $purchase->load('items.product');
        });
    }

    /**
     * Membuat Pembelian Langsung (Tanpa PO).
     */
    public function createDirectPurchase(array $data, ?int $userId = null): Purchase
    {
        return DB::transaction(function () use ($data, $userId) {
            $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);
            $paid  = $data['paid_amount'] ?? 0;

            $purchase = Purchase::create([
                'invoice_number' => 'PUR-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'supplier_id'    => $data['supplier_id'],
                'warehouse_id'   => $data['warehouse_id'],
                'user_id'        => $userId,
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

                $stock = ProductStock::firstOrCreate(
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
                    'user_id'         => $userId,
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
    }
}
