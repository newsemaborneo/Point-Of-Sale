<x-layouts.app title="Transaksi POS">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Transaksi POS</h1>
                <p class="text-sm text-slate-500">Kelola penjualan, transaksi yang ditahan, dan cetak struk.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transactions.index') }}" class="rounded-md border border-slate-300 bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">Segarkan</a>
            </div>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3 font-medium">Invoice</th>
                        <th class="px-4 py-3 font-medium">Pelanggan</th>
                        <th class="px-4 py-3 font-medium">Total</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Tanggal</th>
                        <th class="px-4 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($transactions as $sale)
                        <tr>
                            <td class="px-4 py-4 font-medium text-slate-900">{{ $sale->invoice_number ?? '-' }}</td>
                            <td class="px-4 py-4">{{ $sale->customer->name ?? 'Umum' }}</td>
                            <td class="px-4 py-4">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-4">{{ ucfirst($sale->status) }}</td>
                            <td class="px-4 py-4">{{ optional($sale->created_at)->format('d M Y') }}</td>
                            <td class="px-4 py-4 text-slate-700">
                                <a href="{{ route('transactions.show', $sale) }}" class="rounded-md border border-slate-300 bg-slate-50 px-3 py-2 hover:bg-slate-100">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $transactions->links() }}</div>
    </div>
</x-layouts.app>
