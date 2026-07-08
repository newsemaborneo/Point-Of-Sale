<x-layouts.app title="Riwayat Pembelian - {{ $customer->name }}">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Riwayat Pembelian</h1>
                <p class="text-sm text-slate-500">Pelanggan: <strong>{{ $customer->name }}</strong></p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('customers.show', $customer) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200
                          bg-white px-4 py-2.5 text-sm font-semibold text-slate-700
                          hover:bg-slate-50 transition-colors">
                    ← Kembali ke Detail
                </a>
                <a href="{{ route('customers.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200
                          bg-white px-4 py-2.5 text-sm font-semibold text-slate-700
                          hover:bg-slate-50 transition-colors">
                    Daftar Pelanggan
                </a>
            </div>
        </div>

        {{-- Customer Summary --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center">
                <p class="text-2xl font-bold text-emerald-600">{{ $sales->total() }}</p>
                <p class="text-sm text-slate-600">Total Transaksi</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($sales->sum('grand_total'), 0, ',', '.') }}</p>
                <p class="text-sm text-slate-600">Total Pembelian</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center">
                @if($sales->count() > 0)
                    <p class="text-2xl font-bold text-purple-600">Rp {{ number_format($sales->sum('grand_total') / $sales->count(), 0, ',', '.') }}</p>
                @else
                    <p class="text-2xl font-bold text-purple-600">Rp 0</p>
                @endif
                <p class="text-sm text-slate-600">Rata-rata Transaksi</p>
            </div>
        </div>

        {{-- Transaction List --}}
        @if($sales->count() > 0)
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Daftar Transaksi</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-medium text-slate-900">Tanggal</th>
                                <th class="px-4 py-3 font-medium text-slate-900">Invoice</th>
                                <th class="px-4 py-3 font-medium text-slate-900">Items</th>
                                <th class="px-4 py-3 font-medium text-slate-900 text-right">Subtotal</th>
                                <th class="px-4 py-3 font-medium text-slate-900 text-right">Diskon</th>
                                <th class="px-4 py-3 font-medium text-slate-900 text-right">Total</th>
                                <th class="px-4 py-3 font-medium text-slate-900">Status</th>
                                <th class="px-4 py-3 font-medium text-slate-900">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($sales as $sale)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $sale->created_at->format('d/m/Y') }}<br>
                                    <span class="text-xs text-slate-500">{{ $sale->created_at->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ $sale->invoice_number }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="max-w-xs">
                                        @foreach($sale->items->take(2) as $item)
                                            <div class="text-xs text-slate-600">
                                                {{ $item->quantity }}x {{ $item->product->name ?? 'Produk Dihapus' }}
                                            </div>
                                        @endforeach
                                        @if($sale->items->count() > 2)
                                            <div class="text-xs text-slate-400">
                                                +{{ $sale->items->count() - 2 }} item lainnya
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-700">
                                    Rp {{ number_format($sale->subtotal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-700">
                                    Rp {{ number_format($sale->discount_amount ?? 0, 0, ',', '.') }}
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
                                <td class="px-4 py-3">
                                    <div class="flex gap-1">
                                        <button onclick="viewTransaction('{{ $sale->invoice_number }}')"
                                                class="rounded-md border border-slate-300 bg-slate-50 px-2 py-1
                                                       text-xs text-slate-700 hover:bg-slate-100">
                                            Detail
                                        </button>
                                        @if($sale->payment_status !== 'paid')
                                        <a href="#"
                                           class="rounded-md border border-emerald-300 bg-emerald-50 px-2 py-1
                                                  text-xs text-emerald-700 hover:bg-emerald-100">
                                            Bayar
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            @if($sales->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $sales->links() }}
            </div>
            @endif
        </div>
        @else
        <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900 mb-2">Belum Ada Transaksi</h3>
            <p class="text-slate-500">{{ $customer->name }} belum pernah melakukan pembelian.</p>
        </div>
        @endif
    </div>

    {{-- Transaction Detail Modal Placeholder --}}
    <script>
        function viewTransaction(invoiceNumber) {
            // TODO: Implement transaction detail modal
            alert('Detail transaksi ' + invoiceNumber + ' akan ditampilkan di sini');
        }
    </script>
</x-layouts.app>
