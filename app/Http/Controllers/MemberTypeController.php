<?php

namespace App\Http\Controllers;

use App\Models\MemberType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberTypeController extends Controller
{
    public function index()
    {
        $memberTypes = MemberType::orderBy('minimum_spend', 'asc')->get();
        return view('member-types.index', compact('memberTypes'));
    }

    public function create()
    {
        return view('member-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'minimum_spend' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        MemberType::create($validated);

        return redirect()->route('member-types.index')->with('success', 'Tipe Member berhasil ditambahkan.');
    }

    public function edit(MemberType $memberType)
    {
        return view('member-types.edit', compact('memberType'));
    }

    public function update(Request $request, MemberType $memberType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'minimum_spend' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $memberType->update($validated);

        return redirect()->route('member-types.index')->with('success', 'Tipe Member berhasil diperbarui.');
    }

    public function destroy(MemberType $memberType)
    {
        // Check if there are customers using this member type
        if ($memberType->customers()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus tipe member ini karena masih digunakan oleh pelanggan.');
        }

        $memberType->delete();

        return redirect()->route('member-types.index')->with('success', 'Tipe Member berhasil dihapus.');
    }
}
