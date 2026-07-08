<x-layouts.app title="Laporan Stok">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Laporan Stok</h1>
                <p class="text-sm text-slate-500">Riwayat mutasi stok dan pergerakan barang.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Gudang</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Jumlah</th>
                        <th class="px-4 py-3">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="px-4 py-4">{{ $movement->product->name ?? '-' }}</td>
                            <td class="px-4 py-4">{{ $movement->warehouse->name ?? '-' }}</td>
                            <td class="px-4 py-4">{{ ucfirst($movement->type) }}</td>
                            <td class="px-4 py-4">{{ $movement->quantity }}</td>
                            <td class="px-4 py-4">{{ optional($movement->created_at)->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada mutasi stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
