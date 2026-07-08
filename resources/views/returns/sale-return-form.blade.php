<x-layouts.app title="Buat Retur Penjualan">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Buat Retur Penjualan</h1>
                <p class="text-sm text-slate-500">Formulir untuk mencatat retur dari transaksi penjualan.</p>
            </div>
            <a href="{{ route('transactions.show', $sale) }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali ke Detail Penjualan</a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Detail Penjualan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700">
                <div>
                    <p><span class="font-medium">Invoice:</span> {{ $sale->invoice_number }}</p>
                    <p><span class="font-medium">Pelanggan:</span> {{ $sale->customer->name ?? 'Umum' }}</p>
                    <p><span class="font-medium">Tanggal Penjualan:</span> {{ $sale->created_at->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p><span class="font-medium">Total Penjualan:</span> Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</p>
                    <p><span class="font-medium">Status:</span> {{ ucfirst($sale->status) }}</p>
                </div>
            </div>

            <form action="{{ route('sales.return', $sale) }}" method="POST" class="mt-6">
                @csrf

                <h2 class="text-lg font-semibold text-slate-900 mb-4">Item yang Diretur</h2>
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-900">
                            <tr>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3">Harga Satuan</th>
                                <th class="px-4 py-3">Jumlah Terjual</th>
                                <th class="px-4 py-3">Jumlah Retur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($sale->items as $index => $item)
                                <tr>
                                    <td class="px-4 py-4">{{ $item->product->name ?? 'Produk Dihapus' }}</td>
                                    <td class="px-4 py-4">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4">{{ $item->quantity }}</td>
                                    <td class="px-4 py-4">
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                        <input type="number"
                                               name="items[{{ $index }}][quantity]"
                                               value="{{ old('items.' . $index . '.quantity', 0) }}"
                                               min="0"
                                               max="{{ $item->quantity }}"
                                               class="w-24 rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                                        @error("items.{$index}.quantity")
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada item dalam penjualan ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mb-6">
                    <label for="refund_method" class="block text-sm font-medium text-slate-700 mb-1">Metode Pengembalian Dana</label>
                    <select name="refund_method" id="refund_method" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm">
                        <option value="cash" {{ old('refund_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="store_credit">Kredit Toko</option>
                        <option value="bank_transfer">Transfer Bank</option>
                    </select>
                    @error('refund_method')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-slate-700 mb-1">Alasan Retur (Opsional)</label>
                    <textarea name="reason" id="reason" rows="3" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm @error('reason') border-red-500 @enderror" placeholder="Misal: Barang rusak, salah ukuran, dll.">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                        Proses Retur
                    </button>
                    <a href="{{ route('transactions.show', $sale) }}" class="rounded-xl border border-slate-300 bg-slate-50 px-5 py-2.5 text-sm text-slate-700 hover:bg-slate-100">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>