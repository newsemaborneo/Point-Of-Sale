<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\CustomerDebtPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    // 6. Manajemen Pelanggan: data, riwayat pembelian, member, poin, voucher, piutang

    public function index(Request $request)
    {
        $customers = Customer::when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
            ->orWhere('phone', 'like', "%{$request->search}%"))
            ->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.form', ['customer' => new Customer()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'member_type' => 'nullable|in:regular,silver,gold,platinum',
        ]);
        $data['member_code'] = 'MBR-' . Str::upper(Str::random(6));

        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        $totalPurchases = $customer->sales()->sum('grand_total');
        $totalTransactions = $customer->sales()->count();

        return view('customers.show', compact('customer', 'totalPurchases', 'totalTransactions'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.form', ['customer' => $customer]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'member_type' => 'nullable|in:regular,silver,gold,platinum',
        ]);
        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus.');
    }

    /** Riwayat pembelian pelanggan */
    public function purchaseHistory(Customer $customer)
    {
        $sales = $customer->sales()->with('items.product')->latest()->paginate(15);
        return view('customers.purchase-history', compact('customer', 'sales'));
    }

    /** Piutang pelanggan */
    public function debts(Customer $customer)
    {
        $debts = $customer->debts()->with('payments')->get();
        return view('customers.debts', compact('customer', 'debts'));
    }

    public function payDebt(Request $request, CustomerDebt $debt)
    {
        $data = $request->validate(['amount' => 'required|numeric|min:1', 'note' => 'nullable|string']);

        DB::transaction(function () use ($debt, $data) {
            CustomerDebtPayment::create([
                'customer_debt_id' => $debt->id,
                'amount' => $data['amount'],
                'paid_date' => now()->toDateString(),
                'note' => $data['note'] ?? null,
            ]);

            $debt->paid_amount += $data['amount'];
            $debt->status = $debt->paid_amount >= $debt->amount ? 'paid' : 'partial';
            $debt->save();
        });

        return back()->with('success', 'Pembayaran piutang berhasil disimpan.');
    }
}
