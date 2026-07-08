<x-layouts.app title="Riwayat Pembelian {{ $supplier->name }}">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Riwayat Pembelian {{ $supplier->name }}</h1>
                <p class="text-sm text-slate-500">Lihat semua transaksi pembelian supplier.</p>
            </div>
            <a href="{{ route('suppliers.show', $supplier) }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-slate-700 hover:bg-slate-100">Kembali ke Supplier</a>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3">Invoice</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Barang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td class="px-4 py-4 font-medium text-slate-900">{{ $purchase->invoice_number ?? '-' }}</td>
                            <td class="px-4 py-4">{{ optional($purchase->purchase_date)->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-4">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-4">{{ ucfirst($purchase->payment_status ?? 'belum') }}</td>
                            <td class="px-4 py-4">{{ $purchase->items->count() }} produk</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada riwayat pembelian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $purchases->links() }}</div>
        </div>
    </div>
</x-layouts.app>
