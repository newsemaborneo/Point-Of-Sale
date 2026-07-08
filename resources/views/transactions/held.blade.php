<x-layouts.app title="Transaksi Ditahan">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Transaksi Ditahan</h1>
        <p class="text-sm text-slate-500">Daftar transaksi yang sedang ditahan dan belum diselesaikan.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @if ($heldSales->isEmpty())
                <div class="text-center py-8 text-slate-500">
                    <p>Tidak ada transaksi yang ditahan saat ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-900">
                            <tr>
                                <th class="px-4 py-3">Invoice</th>
                                <th class="px-4 py-3">Pelanggan</th>
                                <th class="px-4 py-3">Kasir</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($heldSales as $sale)
                                <tr>
                                    <td class="px-4 py-4">{{ $sale->invoice_number }}</td>
                                    <td class="px-4 py-4">{{ $sale->customer->name ?? 'Umum' }}</td>
                                    <td class="px-4 py-4">{{ $sale->user->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-4">{{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4">{{ $sale->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('transactions.show', $sale) }}" class="text-indigo-600 hover:text-indigo-800 transition-colors">
                                                Lihat Detail
                                            </a>
                                            <form action="{{ route('transactions.resume', $sale) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="text-emerald-600 hover:text-emerald-900 ml-2">
                                                    Lanjutkan
                                                </button>
                                            </form>
                                            {{-- Tambahkan tombol hapus jika diperlukan --}}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $heldSales->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
