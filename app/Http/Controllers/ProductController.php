<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // 2. Manajemen Produk: CRUD, kategori, barcode, SKU, harga, diskon, foto, satuan, pajak

    public function index(Request $request)
    {
        $products = Product::with(['category', 'unit', 'supplier'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('sku', 'like', "%{$request->search}%")
                ->orWhere('barcode', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->paginate(20);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.form', [
            'product' => new Product(),
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku',
            'barcode' => 'nullable|string|unique:products,barcode',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:percent,nominal',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'has_expiry' => 'boolean',
            'expired_date' => 'nullable|date',
        ]);

        $data['sku'] = $data['sku'] ?? 'SKU-' . Str::upper(Str::random(8));

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product = Product::create($data);

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'module' => 'Product',
            'action' => 'create',
            'description' => "Membuat produk {$product->name}",
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'new_data' => $product->toArray(),
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        return view('products.show', ['product' => $product->load(['category', 'unit', 'supplier', 'stocks.warehouse'])]);
    }

    public function edit(Product $product)
    {
        return view('products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'purchase_price' => 'sometimes|numeric|min:0',
            'sale_price' => 'sometimes|numeric|min:0',
            'discount_type' => 'nullable|in:percent,nominal',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'has_expiry' => 'boolean',
            'expired_date' => 'nullable|date',
        ]);

        $oldData = $product->toArray();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product->update($data);

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'module' => 'Product',
            'action' => 'update',
            'description' => "Mengubah produk {$product->name}",
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'old_data' => $oldData,
            'new_data' => $product->fresh()->toArray(),
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product)
    {
        $product->delete();

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'module' => 'Product',
            'action' => 'delete',
            'description' => "Menghapus produk {$product->name}",
            'subject_type' => Product::class,
            'subject_id' => $product->id,
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    /** Cari produk via barcode (untuk scan di kasir) */
    public function findByBarcode(string $barcode)
    {
        $product = Product::where('barcode', $barcode)->orWhere('sku', $barcode)->firstOrFail();
        return response()->json($product);
    }
}
