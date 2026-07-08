<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Branch;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with('branch')->paginate(20);
        return view('warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('warehouses.form', [
            'warehouse' => new Warehouse(),
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'is_main' => 'boolean',
        ]);

        Warehouse::create($data);

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.form', [
            'warehouse' => $warehouse,
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'is_main' => 'boolean',
        ]);

        $warehouse->update($data);

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dihapus.');
    }
}
