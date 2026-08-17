<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CashRegisterService
{
    /**
     * Membuka shift kasir baru.
     */
    public function openRegister(User $user, array $data): CashRegister 
    {
        $existing = CashRegister::where('user_id', $user->id)->where('status', 'open')->first();
        if ($existing) {
            throw new \Exception('Anda sudah memiliki shift kasir yang masih terbuka');
        }

        return CashRegister::create([
            'user_id'         => $user->id,
            'branch_id'       => $data['branch_id'] ?? $user->branch_id,
            'opening_balance' => $data['opening_balance'],
            'opened_at'       => now(),
            'status'          => 'open',
        ]);
    }

    /**
     * Menutup shift kasir dan menghitung selisih saldo.
     */
    public function closeRegister(CashRegister $cashRegister, array $data): CashRegister
    {
        return DB::transaction(function () use ($cashRegister, $data) {
            $salesCash = Sale::where('cash_register_id', $cashRegister->id)
                ->where('status', 'completed')
                ->whereHas('payments', fn ($q) => $q->where('method', 'cash'))
                ->with('payments')
                ->get()
                ->sum(fn (Sale $s) => $s->payments->where('method', 'cash')->sum('amount'));

            $cashIn  = $cashRegister->movements()->where('type', 'in')->sum('amount');
            $cashOut = $cashRegister->movements()->where('type', 'out')->sum('amount');

            $expectedBalance = $cashRegister->opening_balance + $salesCash + $cashIn - $cashOut;
            $difference      = $data['closing_balance'] - $expectedBalance;

            $cashRegister->update([
                'closing_balance'  => $data['closing_balance'],
                'expected_balance' => $expectedBalance,
                'difference'       => $difference,
                'closed_at'        => now(),
                'status'           => 'closed',
                'note'             => $data['note'] ?? null,
            ]);

            return $cashRegister;
        });
    }

    /**
     * Mencatat transaksi kas masuk (Setoran modal, dll).
     */
    public function recordCashIn(User $user, array $data): CashMovement
    {
        return CashMovement::create([
            'cash_register_id' => $data['cash_register_id'],
            'user_id'          => $user->id,
            'type'             => 'in',
            'amount'           => $data['amount'],
            'category'         => $data['category'] ?? null,
            'description'      => $data['description'] ?? null,
        ]);
    }

    /**
     * Mencatat transaksi kas keluar (Pengeluaran operasional toko).
     */
    public function recordCashOut(User $user, array $data): CashMovement
    {
        return CashMovement::create([
            'cash_register_id' => $data['cash_register_id'],
            'user_id'          => $user->id,
            'type'             => 'out',
            'amount'           => $data['amount'],
            'category'         => $data['category'] ?? null,
            'description'      => $data['description'] ?? null,
        ]);
    }
}
