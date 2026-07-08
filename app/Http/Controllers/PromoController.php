<?php
namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\Voucher;
use App\Models\ProductBundle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromoController extends Controller
{
    // 14. Promo: diskon persen/nominal, Buy 1 Get 1, bundling, happy hour, voucher

    public function index(Request $request)
    {
        $promotions = Promotion::with('products')->latest()->paginate(20);
        $products   = \App\Models\Product::orderBy('name')->get(['id', 'name', 'sku']);
        return view('promotions.index', compact('promotions', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:percent_discount,nominal_discount,buy_x_get_y,bundling,happy_hour',
            'value'      => 'nullable|numeric|min:0',
            'buy_qty'    => 'nullable|integer|min:1',
            'get_qty'    => 'nullable|integer|min:1',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'product_ids'   => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $promotion = Promotion::create(collect($data)->except(['product_ids'])->toArray());

        if (!empty($data['product_ids'])) {
            $promotion->products()->sync($data['product_ids']);
        }

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil dibuat.');
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'value'      => 'nullable|numeric|min:0',
            'is_active'  => 'boolean',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ]);
        $promotion->update($data);
        return redirect()->route('promotions.index')->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return redirect()->route('promotions.index')->with('success', 'Promo berhasil dihapus.');
    }

    // --- Voucher ---

    public function indexVoucher()
    {
        $vouchers = Voucher::latest()->paginate(20);
        return view('vouchers.index', compact('vouchers'));
    }

    public function storeVoucher(Request $request)
    {
        $data = $request->validate([
            'type'         => 'required|in:percent,nominal',
            'value'        => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'quota'        => 'nullable|integer|min:1',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',
        ]);
        $data['code'] = 'VCR-' . Str::upper(Str::random(8));

        Voucher::create($data);
        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil dibuat. Kode: ' . $data['code']);
    }

    public function checkVoucher(Request $request)
    {
        $data = $request->validate(['code' => 'required|string', 'subtotal' => 'required|numeric']);
        $voucher = Voucher::where('code', $data['code'])->first();

        if (!$voucher || !$voucher->isValid($data['subtotal'])) {
            return redirect()->back()->with('error', 'Voucher tidak valid atau sudah kedaluwarsa.');
        }

        return redirect()->back()->with('success', 'Voucher valid. Diskon telah diterapkan.');
    }

    public function updateVoucher(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'type'         => 'sometimes|in:percent,nominal',
            'value'        => 'sometimes|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'quota'        => 'nullable|integer|min:0',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',
            'is_active'    => 'boolean',
        ]);

        $voucher->update($data);
        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroyVoucher(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil dihapus.');
    }
    }

