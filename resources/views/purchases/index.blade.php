<x-layouts.app title="Daftar Pembelian">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Daftar Pembelian</h1>
                <p class="text-sm text-slate-500">Kelola purchase order, penerimaan barang, dan faktur pembelian.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center text-sm font-semibold">
                <a href="{{ route('purchases.orders.index') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-600 px-4 py-2.5 text-white shadow-sm hover:bg-slate-700 transition-colors">Order Pembelian</a>
                <a href="{{ route('purchases.create') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-white shadow-sm hover:bg-emerald-700 transition-colors">Buat Pembelian Baru</a>
            </div>
        </div>

        {{-- Filter --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('purchases.index') }}" method="GET" class="flex flex-col gap-4 lg:flex-row lg:items-end">
                <div class="flex-1">
                    <label for="payment_status" class="block text-sm font-medium text-slate-700 mb-2">Filter Status Pembayaran</label>
                    <select name="payment_status" id="payment_status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900">
                        <option value="">Semua Status</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Sebagian</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                    </select> 
                    @error('payment_status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">Filter</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            @if ($purchases->isEmpty())
                <div class="text-center py-12 text-slate-500">
                    <svg class="w-12 h-12 mx-auto mb-4 opacity-40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75c0 .414-.336.75-.75.75H9m4.5 0c0 .414.336.75.75.75h.75m-4.5-12c1.657-2.431 2.398-3.646 2.398-3.646m0 0C11.5 2.904 10.961 2.526 10 2.526c-.96 0-1.5.378-2.398 3.646m0 0h.006v.006h-.006v-.006z" /></svg>
                    <p class="text-sm font-medium">Tidak ada pembelian yang ditemukan.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-900">
                        <tr>
                            <th class="px-4 py-3 font-medium">Invoice</th>
                            <th class="px-4 py-3 font-medium">Supplier</th>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                            <th class="px-4 py-3 font-medium">Dibayar</th>
                            <th class="px-4 py-3 font-medium">Status Pembayaran</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($purchases as $purchase)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-4 font-medium text-slate-900">{{ $purchase->invoice_number }}</td>
                                <td class="px-4 py-4">{{ $purchase->supplier->name ?? 'N/A' }}</td>
                                <td class="px-4 py-4">{{ $purchase->purchase_date ?? '-' }}</td>
                                <td class="px-4 py-4 font-bold text-slate-900">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-4">Rp {{ number_format($purchase->paid_amount ?? 0, 0, ',', '.') }}</td>
                                <td class="px-4 py-4">
                                    @php
                                        $statusColors = [
                                            'paid' => 'bg-emerald-100 text-emerald-700',
                                            'partial' => 'bg-amber-100 text-amber-700',
                                            'unpaid' => 'bg-rose-100 text-rose-700',
                                        ];
                                        $colorClass = $statusColors[$purchase->payment_status] ?? 'bg-slate-100 text-slate-700';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $colorClass }}">
                                        {{ ucfirst($purchase->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-slate-700">
                                    <a href="{{ route('purchases.invoice', $purchase) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">Lihat Invoice</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Pagination --}}
        @if ($purchases->hasPages())
            <div class="mt-4">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
