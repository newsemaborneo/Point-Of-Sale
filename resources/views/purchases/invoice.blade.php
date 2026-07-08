<x-layouts.app title="Invoice Pembelian">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Invoice Pembelian #{{ $purchase->invoice_number }}</h1>
                <p class="text-sm text-slate-500">Detail penerimaan barang dan status pembayaran.</p>
            </div>
            <a href="{{ route('purchases.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm max-w-2xl mx-auto">
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-slate-900">INVOICE PEMBELIAN</h2>
                <p class="text-sm text-slate-600">Nomor: {{ $purchase->invoice_number }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4 border-b border-dashed border-slate-300 pb-4 text-sm">
                <div>
                    <p class="font-semibold text-slate-700">Supplier:</p>
                    <p>{{ $purchase->supplier->name ?? 'N/A' }}</p>
                    <p class="text-slate-600">{{ $purchase->supplier->address ?? '' }}</p>
                    <p class="text-slate-600">Telp: {{ $purchase->supplier->phone ?? '' }}</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-slate-700">Tanggal Pembelian:</p>
                    <p>{{ optional($purchase->purchase_date)->format('d M Y') }}</p>
                    <p class="font-semibold text-slate-700 mt-2">Gudang Tujuan:</p>
                    <p>{{ $purchase->warehouse->name ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="py-4 text-sm">
                <div class="font-semibold mb-2">Item Pembelian:</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-900">
                            <tr>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3">Jumlah</th>
                                <th class="px-4 py-3">Harga Beli</th>
                                <th class="px-4 py-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($purchase->items as $item)
                                <tr>
                                    <td class="px-4 py-4">{{ $item->product->name ?? 'Produk Dihapus' }}</td>
                                    <td class="px-4 py-4">{{ $item->quantity }}</td>
                                    <td class="px-4 py-4">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada item pembelian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border-t border-dashed border-slate-300 py-4 text-sm">
                <div class="flex justify-between font-semibold mb-1">
                    <span>Total Pembelian:</span>
                    <span>Rp {{ number_format($purchase->total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>Jumlah Dibayar:</span>
                    <span>Rp {{ number_format($purchase->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-bold text-lg mt-2">
                    <span>Status Pembayaran:</span>
                    <span class="{{ $purchase->payment_status == 'paid' ? 'text-emerald-600' : ($purchase->payment_status == 'partial' ? 'text-amber-600' : 'text-rose-600') }}">
                        {{ ucfirst($purchase->payment_status) }}
                    </span>
                </div>
                @if ($purchase->payment_status != 'paid')
                    <div class="flex justify-between font-bold text-lg mt-2">
                        <span>Sisa Hutang:</span>
                        <span class="text-rose-600">Rp {{ number_format($purchase->total - $purchase->paid_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            <div class="flex justify-center mt-6 gap-4 print:hidden">
                <button onclick="window.print()" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">Cetak Invoice</button>
                <a href="{{ route('purchases.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
            </div>
        </div>
    </div>
</x-layouts.app>