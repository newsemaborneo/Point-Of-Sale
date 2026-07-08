<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Sale;
use Illuminate\Http\Request;

class BranchController extends Controller
{

    public function index()
    {
        $branches = Branch::with('warehouses')->paginate(20);
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.form', ['branch' => new Branch()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:branches,code',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        Branch::create($data);

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function edit(Branch $branch)
    {
        return view('branches.form', ['branch' => $branch]);
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $branch->update($data);

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Cabang berhasil dihapus.');
    }

    /** Laporan penjualan per cabang */
    public function report(Request $request, Branch $branch)
    {
        $sales = Sale::where('branch_id', $branch->id)
            ->where('status', 'completed')
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->get();

        $totalSales = $sales->sum('grand_total');
        $totalTransactions = $sales->count();

        return view('branches.report', compact('branch', 'sales', 'totalSales', 'totalTransactions'));
    }
}
