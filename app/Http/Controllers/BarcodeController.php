<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    // 13. Barcode: generate, cetak, scan, QR Code
    // Rekomendasi package: milon/barcode (untuk barcode Code128/EAN) & simplesoftwareio/simple-qrcode (QR)

    public function index()
    {
        return view('barcode.index'); // Akan mengarahkan ke tampilan manajemen barcode
    }

    /** Generate barcode image (SVG/PNG) untuk 1 produk */
    public function generate(Product $product)
    {
        return redirect()->back()->with('info', 'Fitur generate barcode belum diimplementasikan.');
    }

    /** Generate QR Code (misal untuk struk / kode voucher) */
    public function generateQrCode(Request $request)
    {
        $data = $request->validate(['content' => 'required|string']);

        return redirect()->back()->with('info', 'Fitur generate QR code belum diimplementasikan.');
    }

    /** Cetak label barcode untuk banyak produk sekaligus */
    public function printLabels(Request $request)
    {
        $data = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'copies' => 'nullable|integer|min:1',
        ]);

        return redirect()->back()->with('info', 'Fitur cetak label barcode belum diimplementasikan.');
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
