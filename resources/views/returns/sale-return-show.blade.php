<x-layouts.app title="Detail Retur Penjualan">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Detail Retur Penjualan</h1>
                <p class="text-sm text-slate-500">Informasi lengkap mengenai retur penjualan {{ $saleReturn->return_number }}.</p>
            </div>
            <a href="{{ route('sale-returns.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali ke Daftar Retur</a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Informasi Retur</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700">
                <div>
                    <p><span class="font-medium">No. Retur:</span> {{ $saleReturn->return_number }}</p>
                    <p><span class="font-medium">Tanggal Retur:</span> {{ \Carbon\Carbon::parse($saleReturn->return_date)->format('d M Y') }}</p>
                    <p><span class="font-medium">Total Retur:</span> Rp {{ number_format($saleReturn->total, 0, ',', '.') }}</p>
                    <p><span class="font-medium">Metode Pengembalian:</span> {{ ucfirst(str_replace('_', ' ', $saleReturn->refund_method)) }}</p>
                </div>
                <div>
                    <p><span class="font-medium">No. Invoice Penjualan:</span> {{ $saleReturn->sale->invoice_number ?? 'N/A' }}</p>
                    <p><span class="font-medium">Pelanggan:</span> {{ $saleReturn->sale->customer->name ?? 'Umum' }}</p>
                    <p><span class="font-medium">Kasir/User:</span> {{ $saleReturn->user->name ?? 'N/A' }}</p>
                    <p><span class="font-medium">Alasan Retur:</span> {{ $saleReturn->reason ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Item yang Diretur</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-900">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Harga Satuan</th>
                            <th class="px-4 py-3">Jumlah Retur</th>
                            <th class="px-4 py-3">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($saleReturn->items as $item)
                            <tr>
                                <td class="px-4 py-4">{{ $item->product->name ?? 'Produk Dihapus' }}</td>
                                <td class="px-4 py-4">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-4">{{ $item->quantity }}</td>
                                <td class="px-4 py-4">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada item dalam retur ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('sale-returns.edit', $saleReturn->id) }}" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                Edit Retur
            </a>
            <form action="{{ route('sale-returns.destroy', $saleReturn->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan retur ini? Stok akan dikembalikan.');" class="inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-xl border border-rose-300 bg-rose-50 px-5 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                    Batalkan Retur
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>