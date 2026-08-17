<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use Milon\Barcode\Facades\DNS2DFacade as DNS2D;

class BarcodeController extends Controller
{
    // 13. Barcode: generate, cetak, scan, QR Code
    // Rekomendasi package: milon/barcode (untuk barcode Code128/EAN) & simplesoftwareio/simple-qrcode (QR)

    public function index(Request $request)
    {
        $query = Product::query()->where('is_active', true);
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        $products = $query->paginate(20)->appends($request->all());

        return view('barcode.index', compact('products'));
    }

    /** Generate barcode image (SVG/PNG) untuk 1 produk */
    public function generate(Product $product)
    {
        $code = $product->barcode ?: $product->sku;
        if (!$code) return response('No barcode/SKU available', 400);
        return view('barcode.single', compact('product', 'code'));
    }

    public function generateQrCode(Request $request)
    {
        $data = $request->validate(['content' => 'required|string']);

        return redirect()->back()->with('qr_content', $data['content'])->with('success', 'QR Code berhasil dibuat.');
    }

    public function printLabels(Request $request)
    {
        $data = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'copies' => 'nullable|integer|min:1',
        ]);

        $products = Product::whereIn('id', $data['product_ids'])->get();
        $copies = $data['copies'] ?? 1;

        return view('barcode.print', compact('products', 'copies'));
    }

    /** Endpoint scan: cari produk dari hasil scan barcode/QR */
    public function scan(string $code)
    {
        $product = Product::where('barcode', $code)->orWhere('sku', $code)->first();

        if (!$product) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }

        return redirect()->route('products.show', $product)->with('success', 'Produk berhasil ditemukan.');
    }
}
