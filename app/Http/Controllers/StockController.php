<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\Warehouse; // Tambahkan baris ini
use App\Models\StockOpnameItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockController extends Controller
{
    // 3. Manajemen Stok: masuk, keluar, transfer antar gudang, adjustment, opname, riwayat, minimum stok

    /** Menampilkan form untuk stok masuk manual */
    public function createStockInForm()
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('layouts.in', compact('products', 'warehouses'));
    }

    /** Menampilkan form untuk stok keluar manual */
    public function createStockOutForm()
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('layouts.out', compact('products', 'warehouses'));
    }

    /** Menampilkan form untuk transfer stok antar gudang */
    public function createTransferForm()
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('layouts.transfer', compact('products', 'warehouses'));
    }

    /** Menampilkan form untuk penyesuaian stok */
    public function createAdjustmentForm()
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('layouts.adjustment', compact('products', 'warehouses'));
    }

    protected function adjustStock(int $productId, int $warehouseId, int $qtyChange, string $type, ?int $refId = null, ?string $refType = null, ?string $note = null, ?int $userId = null): ProductStock
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
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'quantity' => $qtyChange,
                'quantity_before' => $before,
                'quantity_after' => $stock->quantity,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'note' => $note,
                'user_id' => $userId,
            ]);

            return $stock;
        });
    }

    /** Stok masuk manual */
    public function stockIn(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $this->adjustStock(
            $data['product_id'], $data['warehouse_id'], $data['quantity'],
            'in', null, null, $data['note'] ?? null, $request->user()?->id
        );

        return redirect()->back()->with('success', 'Stok masuk berhasil disimpan.');
    }

    /** Stok keluar manual */
    public function stockOut(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $this->adjustStock(
            $data['product_id'], $data['warehouse_id'], -$data['quantity'],
            'out', null, null, $data['note'] ?? null, $request->user()?->id
        );

        return redirect()->back()->with('success', 'Stok keluar berhasil disimpan.');
    }

    /** Transfer stok antar gudang */
    public function transfer(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $request) {
            $this->adjustStock($data['product_id'], $data['from_warehouse_id'], -$data['quantity'], 'transfer', null, null, $data['note'] ?? 'Transfer keluar', $request->user()?->id);
            $this->adjustStock($data['product_id'], $data['to_warehouse_id'], $data['quantity'], 'transfer', null, null, $data['note'] ?? 'Transfer masuk', $request->user()?->id);
        });

        return redirect()->back()->with('success', 'Transfer stok berhasil.');
    }

    /** Penyesuaian stok (set langsung ke jumlah tertentu) */
    public function adjustment(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'actual_quantity' => 'required|integer|min:0',
            'note' => 'nullable|string',
        ]);

        $stock = ProductStock::firstOrCreate(
            ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']],
            ['quantity' => 0]
        );
        $diff = $data['actual_quantity'] - $stock->quantity;

        $this->adjustStock($data['product_id'], $data['warehouse_id'], $diff, 'adjustment', null, null, $data['note'] ?? null, $request->user()?->id);

        return redirect()->back()->with('success', 'Penyesuaian stok berhasil disimpan.');
    }

    /** Riwayat perubahan stok */
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

    /** Notifikasi stok habis / menipis */
    public function lowStockAlert()
    {
        $lowStockProducts = Product::with('stocks')
            ->get()
            ->filter(fn (Product $p) => $p->isLowStock())
            ->values();

        // Jika Anda ingin menampilkan ini di halaman terpisah, gunakan view berikut:
        return view('stock.low-alert', compact('lowStockProducts'));

        // Jika Anda ingin tetap redirect ke laporan stok umum dengan filter,
        // Anda perlu menyesuaikan laporan stok untuk menerima parameter ini.
        // return redirect()->route('reports.stock')->with('info', 'Notifikasi stok habis / menipis ditampilkan.');
    }

    // --- Stock Opname ---

    public function startOpname(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'opname_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $opname = DB::transaction(function () use ($data, $request) {
            $opname = StockOpname::create([
                'code' => 'SO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4)),
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => $request->user()?->id,
                'status' => 'draft',
                'opname_date' => $data['opname_date'],
                'note' => $data['note'] ?? null,
            ]);

            $stocks = ProductStock::where('warehouse_id', $data['warehouse_id'])->get();
            foreach ($stocks as $stock) {
                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $stock->product_id,
                    'system_quantity' => $stock->quantity,
                    'actual_quantity' => $stock->quantity,
                    'difference' => 0,
                ]);
            }

            return $opname;
        });

        return redirect()->route('dashboard')->with('success', 'Stock opname dimulai.');
    }

    public function updateOpnameItem(Request $request, StockOpnameItem $item)
    {
        $data = $request->validate(['actual_quantity' => 'required|integer|min:0', 'note' => 'nullable|string']);
        $item->actual_quantity = $data['actual_quantity'];
        $item->difference = $data['actual_quantity'] - $item->system_quantity;
        $item->note = $data['note'] ?? $item->note;
        $item->save();

        return redirect()->back()->with('success', 'Item stock opname berhasil diperbarui.');
    }

    public function completeOpname(Request $request, StockOpname $stockOpname)
    {
        return DB::transaction(function () use ($stockOpname, $request) {
            foreach ($stockOpname->items as $item) {
                if ($item->difference !== 0) {
                    $this->adjustStock(
                        $item->product_id, $stockOpname->warehouse_id, $item->difference,
                        'opname', $stockOpname->id, StockOpname::class,
                        'Stock opname ' . $stockOpname->code, $request->user()?->id
                    );
                }
            }
            $stockOpname->update(['status' => 'completed']);
            return $stockOpname->load('items.product');
        });

        return redirect()->route('dashboard')->with('success', 'Stock opname selesai.');
    }

    /** Menampilkan form untuk memulai stock opname */
    public function createOpnameForm()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        return view('layouts.opname', compact('warehouses'));
    }
}
