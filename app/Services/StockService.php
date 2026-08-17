<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockService
{
    /**
     * Penyesuaian/Perubahan stok umum dengan pencatatan pergerakan.
     */
    public function adjustStock(int $productId, int $warehouseId, int $qtyChange, string $type, ?int $refId = null, ?string $refType = null, ?string $note = null, ?int $userId = null): ProductStock
    {
        return DB::transaction(function () use ($productId, $warehouseId, $qtyChange, $type, $refId, $refType, $note, $userId) {
            $stock = ProductStock::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );

            $before = $stock->quantity;
            $stock->quantity = $before + $qtyChange;
            $stock->save();

            StockMovement::create([
                'product_id'      => $productId,
                'warehouse_id'    => $warehouseId,
                'type'            => $type,
                'quantity'        => $qtyChange,
                'quantity_before' => $before,
                'quantity_after'  => $stock->quantity,
                'reference_type'  => $refType,
                'reference_id'    => $refId,
                'note'            => $note,
                'user_id'         => $userId,
            ]);

            return $stock;
        });
    }

    /**
     * Memproses stok masuk manual.
     */
    public function stockIn(array $data, ?int $userId = null): ProductStock
    {
        return $this->adjustStock(
            $data['product_id'],
            $data['warehouse_id'],
            $data['quantity'],
            'in',
            null,
            null,
            $data['note'] ?? 'Stok masuk manual',
            $userId
        );
    }

    /**
     * Memproses stok keluar manual.
     */
    public function stockOut(array $data, ?int $userId = null): ProductStock
    {
        return $this->adjustStock(
            $data['product_id'],
            $data['warehouse_id'],
            -$data['quantity'],
            'out',
            null,
            null,
            $data['note'] ?? 'Stok keluar manual',
            $userId
        );
    }

    /**
     * Memproses transfer stok antar gudang.
     */
    public function transferStock(array $data, ?int $userId = null): void
    {
        DB::transaction(function () use ($data, $userId) {
            $this->adjustStock(
                $data['product_id'],
                $data['from_warehouse_id'],
                -$data['quantity'],
                'transfer',
                null,
                null,
                $data['note'] ?? 'Transfer keluar',
                $userId
            );

            $this->adjustStock(
                $data['product_id'],
                $data['to_warehouse_id'],
                $data['quantity'],
                'transfer',
                null,
                null,
                $data['note'] ?? 'Transfer masuk',
                $userId
            );
        });
    }

    /**
     * Memproses penyesuaian stok langsung (Direct Adjustment).
     */
    public function directAdjustment(array $data, ?int $userId = null): ProductStock
    {
        $stock = ProductStock::firstOrCreate(
            ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']],
            ['quantity' => 0]
        );

        $diff = $data['actual_quantity'] - $stock->quantity;

        return $this->adjustStock(
            $data['product_id'],
            $data['warehouse_id'],
            $diff,
            'adjustment',
            null,
            null,
            $data['note'] ?? 'Penyesuaian stok',
            $userId
        );
    }

    /**
     * Memulai sesi Stock Opname baru.
     */
    public function startOpname(array $data, ?int $userId = null): StockOpname
    {
        return DB::transaction(function () use ($data, $userId) {
            $opname = StockOpname::create([
                'code'         => 'SO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4)),
                'warehouse_id' => $data['warehouse_id'],
                'user_id'      => $userId,
                'status'       => 'draft',
                'opname_date'  => $data['opname_date'],
                'note'         => $data['note'] ?? null,
            ]);

            $stocks = ProductStock::where('warehouse_id', $data['warehouse_id'])->get();
            foreach ($stocks as $stock) {
                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id'      => $stock->product_id,
                    'system_quantity' => $stock->quantity,
                    'actual_quantity' => $stock->quantity,
                    'difference'      => 0,
                ]);
            }

            return $opname;
        });
    }

    /**
     * Memperbarui item dalam sesi Stock Opname.
     */
    public function updateOpnameItem(StockOpnameItem $item, array $data): StockOpnameItem
    {
        $item->actual_quantity = $data['actual_quantity'];
        $item->difference      = $data['actual_quantity'] - $item->system_quantity;
        $item->note            = $data['note'] ?? $item->note;
        $item->save();

        return $item;
    }

    /**
     * Menyelesaikan sesi Stock Opname dan menyelaraskan saldo stok.
     */
    public function completeOpname(StockOpname $stockOpname, ?int $userId = null): StockOpname
    {
        return DB::transaction(function () use ($stockOpname, $userId) {
            foreach ($stockOpname->items as $item) {
                if ($item->difference !== 0) {
                    $this->adjustStock(
                        $item->product_id,
                        $stockOpname->warehouse_id,
                        $item->difference,
                        'opname',
                        $stockOpname->id,
                        StockOpname::class,
                        'Stock opname ' . $stockOpname->code,
                        $userId
                    );
                }
            }
            $stockOpname->update(['status' => 'completed']);
            return $stockOpname->load('items.product');
        });
    }
}
