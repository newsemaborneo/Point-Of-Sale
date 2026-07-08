<x-layouts.app title="Struk Penjualan">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Struk Penjualan #{{ $sale->invoice_number }}</h1>
        <p class="text-sm text-slate-500">Detail transaksi penjualan.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm max-w-md mx-auto">
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-slate-900">{{ config('app.name', 'POS App') }}</h2>
                <p class="text-sm text-slate-600">Jl. Contoh No. 123, Kota Anda</p>
                <p class="text-sm text-slate-600">Telp: (021) 12345678</p>
            </div>

            <div class="border-t border-dashed border-slate-300 py-4 text-sm">
                <div class="flex justify-between mb-1">
                    <span>Invoice:</span>
                    <span>{{ $sale->invoice_number }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>Tanggal:</span>
                    <span>{{ $sale->created_at->format('d M Y H:i') }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>Kasir:</span>
                    <span>{{ $sale->user->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>Pelanggan:</span>
                    <span>{{ $sale->customer->name ?? 'Umum' }}</span>
                </div>
            </div>

            <div class="border-t border-dashed border-slate-300 py-4 text-sm">
                <div class="font-semibold mb-2">Item Penjualan:</div>
                @foreach ($sale->items as $item)
                    <div class="flex justify-between">
                        <span>{{ $item->product->name ?? 'Produk Dihapus' }} ({{ $item->quantity }}x)</span>
                        <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-dashed border-slate-300 py-4 text-sm">
                <div class="flex justify-between font-semibold mb-1">
                    <span>Subtotal:</span>
                    <span>{{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>Diskon:</span>
                    <span>{{ number_format($sale->discount_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>Pajak:</span>
                    <span>{{ number_format($sale->tax_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-bold text-lg mt-2">
                    <span>TOTAL:</span>
                    <span>{{ number_format($sale->grand_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between mt-2">
                    <span>Dibayar:</span>
                    <span>{{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Kembali:</span>
                    <span>{{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="border-t border-dashed border-slate-300 pt-4 text-center text-xs text-slate-500">
                <p>Terima kasih telah berbelanja!</p>
                <p>Barang yang sudah dibeli tidak dapat dikembalikan.</p>
            </div>

            <div class="flex justify-center mt-6 gap-4">
                <button onclick="window.print()" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">Cetak Struk</button>
                <a href="{{ route('transactions.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
                {{-- Anda bisa menambahkan form untuk mengirim struk via email/WhatsApp di sini --}}
                {{-- <form action="{{ route('transactions.send-receipt', $sale) }}" method="POST">@csrf<button type="submit">Kirim Struk</button></form> --}}
            </div>
        </div>
    </div>
</x-layouts.app>