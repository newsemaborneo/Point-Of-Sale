<x-layouts.app title="Laporan Pembelian">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Laporan Pembelian</h1>
                <p class="text-sm text-slate-500">Ringkasan pembelian dan riwayat transaksi pembelian.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <p class="text-sm text-slate-500">Total Pembelian</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-900">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3">Invoice</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td class="px-4 py-4">{{ $purchase->invoice_number }}</td>
                            <td class="px-4 py-4">{{ $purchase->supplier->name ?? '-' }}</td>
                            <td class="px-4 py-4">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-4">{{ optional($purchase->purchase_date)->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada data pembelian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
