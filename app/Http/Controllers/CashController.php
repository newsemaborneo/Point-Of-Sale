<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cash\CashMovementRequest;
use App\Http\Requests\Cash\CloseCashRegisterRequest;
use App\Http\Requests\Cash\OpenCashRegisterRequest;
use App\Models\CashRegister;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashController extends Controller
{
    protected CashRegisterService $cashRegisterService;

    public function __construct(CashRegisterService $cashRegisterService)
    {
        $this->cashRegisterService = $cashRegisterService;
    }

    public function shiftIndex()
    {
        $currentRegister = CashRegister::where('user_id', Auth::id())
            ->where('status', 'open')
            ->with(['user', 'sales'])
            ->first();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $registers = CashRegister::with(['user', 'sales'])
            ->latest()
            ->when(!$user->hasRole('admin') && !$user->hasRole('supervisor'), function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            })
            ->paginate(20);

        return view('cash.shift', compact('currentRegister', 'registers'));
    }

    public function openRegister(OpenCashRegisterRequest $request)
    {
        try {
            $register = $this->cashRegisterService->openRegister($request->user(), $request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Shift kasir berhasil dibuka. Selamat bertugas!',
                    'register_id' => $register->id,
                ], 200);
            }

            return redirect()->route('cash.shift')->with('success', 'Shift kasir berhasil dibuka. Selamat bertugas!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function closeRegister(CloseCashRegisterRequest $request, CashRegister $cashRegister)
    {
        $this->cashRegisterService->closeRegister($cashRegister, $request->validated());
        return redirect()->route('cash.shift')->with('success', 'Kasir berhasil ditutup.');
    }

    public function cashIn(CashMovementRequest $request)
    {
        $this->cashRegisterService->recordCashIn($request->user(), $request->validated());
        return redirect()->route('cash.shift')->with('success', 'Kas masuk berhasil dicatat.');
    }

    public function cashOut(CashMovementRequest $request)
    {
        $this->cashRegisterService->recordCashOut($request->user(), $request->validated());
        return redirect()->route('cash.shift')->with('success', 'Kas keluar berhasil dicatat.');
    }

    public function currentShift(Request $request)
    {
        return redirect()->route('cash.shift');
    }

    public function history(Request $request)
    {
        return redirect()->route('cash.shift');
    }
}