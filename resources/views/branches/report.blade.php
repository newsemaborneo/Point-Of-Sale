 <x-layouts.app title="Laporan Penjualan Cabang">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Laporan Penjualan Cabang: {{ $branch->name }}</h1>
        <p class="text-sm text-slate-500">Ringkasan penjualan untuk cabang {{ $branch->name }}.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('branches.report', $branch) }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="date_from" class="mb-1 block text-sm font-medium text-slate-700">Dari Tanggal</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900">
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-sm font-medium text-slate-700">Sampai Tanggal</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900">
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-indigo-50 p-4 rounded-xl shadow-sm">
                    <p class="text-sm text-indigo-700">Total Pendapatan</p>
                    <p class="text-2xl font-bold text-indigo-900">{{ number_format($totalSales, 0, ',', '.') }}</p>
                </div>
                <div class="bg-emerald-50 p-4 rounded-xl shadow-sm">
                    <p class="text-sm text-emerald-700">Total Transaksi</p>
                    <p class="text-2xl font-bold text-emerald-900">{{ $totalTransactions }}</p>
                </div>
            </div>

            <h2 class="text-lg font-semibold text-slate-800 mb-4">Detail Penjualan</h2>
            @if ($sales->isEmpty())
                <div class="text-center py-8 text-slate-500">
                    <p>Tidak ada data penjualan untuk periode ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-900">
                            <tr>
                                <th class="px-4 py-3">Invoice</th>
                                <th class="px-4 py-3">Pelanggan</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($sales as $sale)
                                <tr>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('transactions.show', $sale) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $sale->invoice_number }}
                                        </a> 
                                    </td>
                                    <td class="px-4 py-4">{{ $sale->customer->name ?? 'Umum' }}</td>
                                    <td class="px-4 py-4">{{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4">{{ $sale->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sale->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Pagination jika diperlukan, tapi saat ini data diambil semua --}}
            @endif
        </div>
    </div>
</x-layouts.app>