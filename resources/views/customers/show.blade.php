<x-layouts.app title="Detail Pelanggan">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $customer->name }}</h1>
                <p class="text-sm text-slate-500">Detail informasi dan riwayat transaksi pelanggan</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <a href="{{ route('customers.edit', $customer) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5
                          text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121
                               2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154
                               1.262a.5.5 0 01-.65-.65z" />
                        <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010
                               3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75
                               18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5
                               0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
                    </svg>
                    Edit Data
                </a>
                <a href="{{ route('customers.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200
                          bg-white px-4 py-2.5 text-sm font-semibold text-slate-700
                          hover:bg-slate-50 transition-colors">
                    ← Kembali ke Daftar
                </a>
            </div>
        </div>

        {{-- Customer Info Cards --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Basic Info --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Informasi Dasar</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-slate-500">Nama</span>
                        <p class="text-slate-900 font-medium">{{ $customer->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-slate-500">Telepon</span>
                        <p class="text-slate-900">{{ $customer->phone ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-slate-500">Email</span>
                        <p class="text-slate-900">{{ $customer->email ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-slate-500">Tipe Member</span>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                               {{ $customer->member_type === 'platinum' ? 'bg-purple-100 text-purple-800' :
                                  ($customer->member_type === 'gold' ? 'bg-yellow-100 text-yellow-800' :
                                   ($customer->member_type === 'silver' ? 'bg-gray-100 text-gray-800' :
                                    'bg-green-100 text-green-800')) }}">
                            {{ ucfirst($customer->member_type ?? 'regular') }}
                        </span>
                    </div>
                    @if($customer->address)
                    <div>
                        <span class="text-sm font-medium text-slate-500">Alamat</span>
                        <p class="text-slate-900">{{ $customer->address }}</p>
                    </div>
                    @endif
                    @if($customer->date_of_birth)
                    <div>
                        <span class="text-sm font-medium text-slate-500">Tanggal Lahir</span>
                        <p class="text-slate-900">{{ $customer->date_of_birth->format('d/m/Y') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Statistics --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Statistik</h3>
                <div class="space-y-4">
                    <div class="text-center p-4 bg-emerald-50 rounded-2xl">
                        <p class="text-2xl font-bold text-emerald-600">{{ $totalTransactions }}</p>
                        <p class="text-sm text-emerald-700">Total Transaksi</p>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-2xl">
                        <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</p>
                        <p class="text-sm text-blue-700">Total Pembelian</p>
                    </div>
                    @if($totalTransactions > 0)
                    <div class="text-center p-4 bg-purple-50 rounded-2xl">
                        <p class="text-2xl font-bold text-purple-600">Rp {{ number_format($totalPurchases / $totalTransactions, 0, ',', '.') }}</p>
                        <p class="text-sm text-purple-700">Rata-rata per Transaksi</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-3">
                    <a href="{{ route('customers.purchaseHistory', $customer) }}"
                       class="block w-full rounded-xl bg-slate-100 px-4 py-3 text-center text-sm font-medium
                              text-slate-700 hover:bg-slate-200 transition-colors">
                        Lihat Riwayat Pembelian
                    </a>
                    <a href="{{ route('customers.debts', $customer) }}"
                       class="block w-full rounded-xl bg-slate-100 px-4 py-3 text-center text-sm font-medium
                              text-slate-700 hover:bg-slate-200 transition-colors">
                        Lihat Hutang
                    </a>
                    <button onclick="window.print()"
                            class="block w-full rounded-xl bg-slate-100 px-4 py-3 text-center text-sm font-medium
                                   text-slate-700 hover:bg-slate-200 transition-colors">
                        Cetak Info Pelanggan
                    </button>
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        @if($customer->sales && $customer->sales->count() > 0)
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Transaksi Terbaru</h3>
                <a href="{{ route('customers.purchaseHistory', $customer) }}"
                   class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    Lihat Semua →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-slate-900">Tanggal</th>
                            <th class="px-4 py-3 font-medium text-slate-900">Invoice</th>
                            <th class="px-4 py-3 font-medium text-slate-900 text-right">Total</th>
                            <th class="px-4 py-3 font-medium text-slate-900">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($customer->sales as $sale)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-700">
                                {{ $sale->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                {{ $sale->invoice_number }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-slate-900">
                                Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                       {{ $sale->payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                                          ($sale->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800' :
                                           'bg-red-100 text-red-800') }}">
                                    {{ $sale->payment_status === 'paid' ? 'Lunas' :
                                       ($sale->payment_status === 'partial' ? 'Sebagian' : 'Belum Bayar') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900 mb-2">Belum Ada Transaksi</h3>
            <p class="text-slate-500">Pelanggan ini belum pernah melakukan pembelian.</p>
        </div>
        @endif
    </div>
</x-layouts.app>
