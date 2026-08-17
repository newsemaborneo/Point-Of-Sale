<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

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
            'title'    => 'Tambah Supplier',
        ]);
    }

    public function store(StoreSupplierRequest $request)
    {
        $this->supplierService->createSupplier($request->validated());
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
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
            'title'    => 'Ubah Supplier',
        ]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $this->supplierService->updateSupplier($supplier, $request->validated());
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
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
