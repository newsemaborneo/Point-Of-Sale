<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\CustomerDebtPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerService
{
    /**
     * Membuat pelanggan baru dengan kode member otomatis.
     */
    public function createCustomer(array $data): Customer
    {
        $data['member_code'] = 'MBR-' . Str::upper(Str::random(6));
        return Customer::create($data);
    }

    /**
     * Memperbarui data pelanggan.
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        return $customer;
    }

    /**
     * Memproses pembayaran piutang pelanggan.
     */
    public function payCustomerDebt(CustomerDebt $debt, array $data): CustomerDebtPayment
    {
        return DB::transaction(function () use ($debt, $data) {
            $payment = CustomerDebtPayment::create([
                'customer_debt_id' => $debt->id,
                'amount'           => $data['amount'],
                'paid_date'        => now()->toDateString(),
                'note'             => $data['note'] ?? null,
            ]);

            $debt->paid_amount += $data['amount'];
            $debt->status = $debt->paid_amount >= $debt->amount ? 'paid' : 'partial';
            $debt->save();

            return $payment;
        });
    }
    
}
