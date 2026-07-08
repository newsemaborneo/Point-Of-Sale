<x-layouts.app title="Form Retur Pembelian">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Form Retur Pembelian</h1>
        <p class="text-sm text-slate-500">Isi formulir di bawah untuk mencatat retur pembelian.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">Detail Pembelian #{{ $purchase->invoice_number }}</h2>
            <p class="text-sm text-slate-600 mb-6">Supplier: {{ $purchase->supplier->name ?? 'N/A' }} | Tanggal: {{ $purchase->purchase_date }} | Total: {{ number_format($purchase->total, 0, ',', '.') }}</p>

            <form action="{{ route('purchases.return', $purchase) }}" method="POST" class="space-y-6">
                @csrf
                <h3 class="text-lg font-semibold text-slate-800">Produk yang Diretur</h3>
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-900">
                            <tr>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3">Harga Satuan</th>
                                <th class="px-4 py-3">Jumlah Dibeli</th>
                                <th class="px-4 py-3">Jumlah Retur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($purchase->items as $item)
                                <tr>
                                    <td class="px-4 py-4">{{ $item->product->name }}</td>
                                    <td class="px-4 py-4">{{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4">{{ $item->quantity }}</td>
                                    <td class="px-4 py-4">
                                        <input type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $item->product_id }}"> {{-- Keep product_id hidden --}}
                                        <input type="number" name="items[{{ $loop->index }}][quantity]" value="{{ old('items.' . $loop->index . '.quantity', 0) }}" min="0" max="{{ $item->quantity }}"
                                               class="w-24 rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 @error('items.' . $loop->index . '.quantity') border-red-500 @enderror">
                                        @error('items.' . $loop->index . '.quantity')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada item dalam pembelian ini.</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    <label for="reason" class="mb-2 block text-sm font-medium text-slate-700">Alasan Retur</label>
                    <textarea name="reason" id="reason" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">Catat Retur Pembelian</button>
            </form>
        </div>
    </div>
</x-layouts.app>