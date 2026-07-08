<?php
namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // 7. Supplier: data, pembelian, hutang, riwayat pembelian
    // Catatan: menggunakan Blade web view, bukan API JSON.
    // Rekomendasi package (belum di-install, hanya komentar):
    // - milon/barcode
    // - simplesoftwareio/simple-qrcode
    // - barryvdh/laravel-dompdf
    // - maatwebsite/excel
    // - spatie/laravel-backup
    // - WhatsApp Business API / Fonnte / Wablas integrations
    // Desain: stok di-manage per gudang (product_stocks) dengan audit stock_movements.
    // Transaksi POS harus mendukung diskon item, pajak, voucher, split payment, hold/resume.
    // Poin loyalitas: 1 poin per Rp 10.000, laporan laba rugi = penjualan - HPP - cash out.

    public function index(Request $request)
    {
        $suppliers = Supplier::when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
            ->orWhere('code', 'like', "%{$request->search}%")
            ->orWhere('phone', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.form', [
            'supplier' => new Supplier(),
            'title' => 'Tambah Supplier',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:suppliers,code',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string',
        ]);

        Supplier::create($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchases.items.product', 'debts']);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.form', [
            'supplier' => $supplier,
            'title' => 'Ubah Supplier',
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:suppliers,code,' . $supplier->id,
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string',
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }

    public function purchaseHistory(Supplier $supplier)
    {
        $purchases = $supplier->purchases()->with('items.product')->latest()->paginate(15);

        return view('suppliers.purchase-history', compact('supplier', 'purchases'));
    }

    public function debts(Supplier $supplier)
    {
        $debts = $supplier->debts()->with('payments')->get();

        return view('suppliers.debts', compact('supplier', 'debts'));
    }
}
