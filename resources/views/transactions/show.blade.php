<x-layouts.app title="Detail Transaksi">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Detail Transaksi</h1>
                <p class="text-sm text-slate-500">Detail penjualan dan item transaksi.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('sales.return.create', $sale) }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">Buat Retur Penjualan</a>
                <a href="{{ route('transactions.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-slate-500">Invoice</div>
                <div class="mt-2 text-xl font-semibold text-slate-900">{{ $sale->invoice_number ?? '-' }}</div>
                <div class="mt-4 text-sm text-slate-500">Pelanggan</div>
                <div class="mt-1 text-lg font-medium text-slate-900">{{ $sale->customer->name ?? 'Umum' }}</div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-slate-500">Total</div>
                <div class="mt-2 text-xl font-semibold text-slate-900">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</div>
                <div class="mt-4 text-sm text-slate-500">Status</div>
                <div class="mt-1 text-lg font-medium text-slate-900">{{ ucfirst($sale->status) }}</div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Item Transaksi</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-900">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Jumlah</th>
                            <th class="px-4 py-3">Harga</th>
                            <th class="px-4 py-3">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($sale->items as $item)
                            <tr>
                                <td class="px-4 py-4">{{ $item->product->name ?? '-' }}</td>
                                <td class="px-4 py-4">{{ $item->quantity }}</td>
                                <td class="px-4 py-4">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-4">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada item transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
