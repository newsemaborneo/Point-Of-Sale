<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

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
            'product'    => new Product(),
            'categories' => Category::orderBy('name')->get(),
            'suppliers'  => Supplier::orderBy('name')->get(),
            'units'      => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $photo = $request->file('photo');
        $this->productService->createProduct($request->validated(), $photo, $request->user()?->id);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        return view('products.show', ['product' => $product->load(['category', 'unit', 'supplier', 'stocks.warehouse'])]);
    }

    public function edit(Product $product)
    {
        return view('products.form', [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
            'suppliers'  => Supplier::orderBy('name')->get(),
            'units'      => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $photo = $request->file('photo');
        $this->productService->updateProduct($product, $request->validated(), $photo, $request->user()?->id);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->productService->deleteProduct($product, $request->user()?->id);
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function findByBarcode(string $barcode)
    {
        $product = Product::where('barcode', $barcode)->orWhere('sku', $barcode)->firstOrFail();
        return response()->json($product);
    }
}
