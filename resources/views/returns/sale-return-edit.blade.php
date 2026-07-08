<x-layouts.app title="Edit Retur Penjualan">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Edit Retur Penjualan</h1>
                <p class="text-sm text-slate-500">Ubah informasi retur penjualan {{ $saleReturn->return_number }}.</p>
            </div>
            <a href="{{ route('sale-returns.show', $saleReturn->id) }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali ke Detail Retur</a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Informasi Retur</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700 mb-6">
                <div>
                    <p><span class="font-medium">No. Retur:</span> {{ $saleReturn->return_number }}</p>
                    <p><span class="font-medium">Tanggal Retur:</span> {{ \Carbon\Carbon::parse($saleReturn->return_date)->format('d M Y') }}</p>
                    <p><span class="font-medium">Total Retur:</span> Rp {{ number_format($saleReturn->total, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p><span class="font-medium">No. Invoice Penjualan:</span> {{ $saleReturn->sale->invoice_number ?? 'N/A' }}</p>
                    <p><span class="font-medium">Pelanggan:</span> {{ $saleReturn->sale->customer->name ?? 'Umum' }}</p>
                    <p><span class="font-medium">Kasir/User:</span> {{ $saleReturn->user->name ?? 'N/A' }}</p>
                </div>
            </div>

            <form action="{{ route('sale-returns.update', $saleReturn->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <h3 class="text-lg font-semibold text-slate-800">Item yang Diretur</h3>
                <div class="overflow-x-auto mb-6">
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

                <div class="mb-6">
                    <label for="refund_method" class="block text-sm font-medium text-slate-700 mb-1">Metode Pengembalian Dana</label>
                    <select name="refund_method" id="refund_method" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm">
                        <option value="cash" {{ old('refund_method', $saleReturn->refund_method) == 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="store_credit" {{ $saleReturn->refund_method == 'store_credit' ? 'selected' : '' }}>Kredit Toko</option>
                        <option value="bank_transfer" {{ $saleReturn->refund_method == 'bank_transfer' ? 'selected' : '' }}>Transfer Bank</option>
                    </select>
                    @error('refund_method')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-slate-700 mb-1">Alasan Retur (Opsional)</label>
                    <textarea name="reason" id="reason" rows="3" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm @error('reason') border-red-500 @enderror" placeholder="Misal: Barang rusak, salah ukuran, dll.">{{ old('reason', $saleReturn->reason) }}</textarea>
                    @error('reason')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                        Perbarui Retur
                    </button>
                    <a href="{{ route('sale-returns.show', $saleReturn->id) }}" class="rounded-xl border border-slate-300 bg-slate-50 px-5 py-2.5 text-sm text-slate-700 hover:bg-slate-100">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>