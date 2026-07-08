<x-layouts.app title="Daftar Retur Pembelian">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Daftar Retur Pembelian</h1>
        <p class="text-sm text-slate-500">Lihat semua riwayat retur pembelian.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @if ($returns->isEmpty())
                <div class="text-center py-8 text-slate-500">
                    <p>Tidak ada retur pembelian yang tercatat.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-900">
                            <tr>
                                <th class="px-4 py-3">No. Retur</th>
                                <th class="px-4 py-3">No. Invoice Pembelian</th>
                                <th class="px-4 py-3">Supplier</th>
                                <th class="px-4 py-3">Tanggal Retur</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($returns as $return)
                                <tr>
                                    <td class="px-4 py-4">{{ $return->return_number }}</td>
                                    <td class="px-4 py-4">{{ $return->purchase->invoice_number ?? 'N/A' }}</td>
                                    <td class="px-4 py-4">{{ $return->purchase->supplier->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-4">{{ $return->return_date }}</td>
                                    <td class="px-4 py-4">{{ number_format($return->total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4">
                                        {{-- Tambahkan tautan detail jika ada --}}
                                        <a href="{{ route('purchase-returns.show', $return->id) }}" class="text-indigo-600 hover:text-indigo-800 transition-colors">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $returns->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.app>