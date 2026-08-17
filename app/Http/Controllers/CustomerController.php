<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\PayCustomerDebtRequest;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request)
    {
        $customers = Customer::when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
            ->orWhere('phone', 'like', "%{$request->search}%"))
            ->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $memberTypes = \App\Models\MemberType::all();
        return view('customers.form', ['customer' => new Customer(), 'memberTypes' => $memberTypes]);
    }

    public function store(StoreCustomerRequest $request)
    {
        $this->customerService->createCustomer($request->validated());
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
        $memberTypes = \App\Models\MemberType::all();
        return view('customers.form', ['customer' => $customer, 'memberTypes' => $memberTypes]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->customerService->updateCustomer($customer, $request->validated());
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus.');
    }

    public function purchaseHistory(Customer $customer)
    {
        $sales = $customer->sales()->with('items.product')->latest()->paginate(15);
        return view('customers.purchase-history', compact('customer', 'sales'));
    }

    public function debts(Customer $customer)
    {
        $debts = $customer->debts()->with('payments')->get();
        return view('customers.debts', compact('customer', 'debts'));
    }

    public function payDebt(PayCustomerDebtRequest $request, CustomerDebt $debt)
    {
        $this->customerService->payCustomerDebt($debt, $request->validated());
        return back()->with('success', 'Pembayaran piutang berhasil disimpan.');
    }
}
