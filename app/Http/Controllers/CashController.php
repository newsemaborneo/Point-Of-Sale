<?php
namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashMovement;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashController extends Controller
{

    /** Halaman manajemen shift kasir */
    public function shiftIndex()
    {
        $currentRegister = CashRegister::where('user_id', Auth::id())
            ->where('status', 'open')
            ->with(['user', 'sales'])
            ->first();

        $registers = CashRegister::with(['user', 'sales'])
            ->latest()
            ->when(!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('supervisor'), function ($query) {
                $query->where('branch_id', Auth::user()->branch_id);
            })
            ->paginate(20);

        return view('cash.shift', compact('currentRegister', 'registers'));
    }

    /** Buka kasir (mulai shift) */
    public function openRegister(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $existing = CashRegister::where('user_id', $request->user()->id)->where('status', 'open')->first();
        abort_if($existing, 422, 'Anda sudah memiliki shift kasir yang masih terbuka');

        $register = CashRegister::create([
            'user_id' => $request->user()->id, // Baris ini sudah ada, tidak perlu perubahan.
            'branch_id' => $data['branch_id'] ?? $request->user()->branch_id, // Prioritaskan branch_id dari request, fallback ke branch_id pengguna
            'opening_balance' => $data['opening_balance'],
            'opened_at' => now(),
            'status' => 'open',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shift kasir berhasil dibuka. Selamat bertugas!',
                'register_id' => $register->id
            ], 200);
        }

        return redirect()->route('cash.shift')->with('success', 'Shift kasir berhasil dibuka. Selamat bertugas!');
    }

    /** Tutup kasir (akhiri shift), hitung selisih kas */
    public function closeRegister(Request $request, CashRegister $cashRegister)
    {
        $data = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $salesCash = Sale::where('cash_register_id', $cashRegister->id)
            ->where('status', 'completed')
            ->whereHas('payments', fn ($q) => $q->where('method', 'cash'))
            ->with('payments')
            ->get()
            ->sum(fn (Sale $s) => $s->payments->where('method', 'cash')->sum('amount'));

        $cashIn = $cashRegister->movements()->where('type', 'in')->sum('amount');
        $cashOut = $cashRegister->movements()->where('type', 'out')->sum('amount');

        $expectedBalance = $cashRegister->opening_balance + $salesCash + $cashIn - $cashOut;
        $difference = $data['closing_balance'] - $expectedBalance;

        $cashRegister->update([
            'closing_balance' => $data['closing_balance'],
            'expected_balance' => $expectedBalance,
            'difference' => $difference,
            'closed_at' => now(),
            'status' => 'closed',
            'note' => $data['note'] ?? null,
        ]);

        // Redirect ke halaman shift (bisa diakses cashier, admin, supervisor),
        // bukan ke reports.cash yang biasanya hanya untuk admin/supervisor.
        return redirect()->route('cash.shift')->with('success', 'Kasir berhasil ditutup.');
    }

    /** Kas masuk (di luar penjualan, misal setoran modal) */
    public function cashIn(Request $request)
    {
        $data = $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'amount' => 'required|numeric|min:1',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        $data['type'] = 'in';
        $data['user_id'] = $request->user()->id;
        CashMovement::create($data);

        // Redirect ke halaman shift, bukan reports.cash, agar kasir (non admin/supervisor)
        // tidak terkena 403 saat middleware role menolak akses ke halaman laporan.
        return redirect()->route('cash.shift')->with('success', 'Kas masuk berhasil dicatat.');
    }

    /** Kas keluar (pengeluaran operasional) */
    public function cashOut(Request $request)
    {
        $data = $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'amount' => 'required|numeric|min:1',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        $data['type'] = 'out';
        $data['user_id'] = $request->user()->id;
        CashMovement::create($data);

        // Redirect ke halaman shift, bukan reports.cash, agar kasir (non admin/supervisor)
        // tidak terkena 403 saat middleware role menolak akses ke halaman laporan.
        return redirect()->route('cash.shift')->with('success', 'Kas keluar berhasil dicatat.');
    }

    public function currentShift(Request $request)
    {
        $register = CashRegister::where('user_id', $request->user()->id)->where('status', 'open')->first();
        return redirect()->route('cash.shift');
    }

    public function history(Request $request)
    {
        return redirect()->route('cash.shift');
    }
}