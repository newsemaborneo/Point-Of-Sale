<x-layouts.app title="Terima Barang - {{ $purchaseOrder->po_number }}">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Penerimaan Barang</h1>
                <p class="text-sm text-slate-500">PO: {{ $purchaseOrder->po_number }} &middot; Supplier: {{ $purchaseOrder->supplier->name ?? '-' }}</p>
            </div>
            <a href="{{ route('purchases.orders.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                &larr; Kembali ke Purchase Order
            </a>
        </div>

        {{-- PO Info --}}
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Gudang Tujuan</p>
                <p class="font-semibold text-slate-900">{{ $purchaseOrder->warehouse->name ?? '-' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Tanggal Order</p>
                <p class="font-semibold text-slate-900">{{ optional($purchaseOrder->order_date)->format('d M Y') ?? '-' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Status</p>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                    {{ $purchaseOrder->status === 'received' ? 'bg-emerald-100 text-emerald-700' :
                       ($purchaseOrder->status === 'partial' ? 'bg-amber-100 text-amber-700' :
                       ($purchaseOrder->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700')) }}">
                    {{ ucfirst($purchaseOrder->status) }}
                </span>
            </div>
        </div>

        {{-- Form --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('purchases.orders.receive', $purchaseOrder) }}" method="POST" class="space-y-6">
                @csrf

                {{-- Items --}}
                <div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4">Konfirmasi Item Diterima</h2>
                    <div class="space-y-3">
                        @foreach ($purchaseOrder->items as $index => $item)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                    <div class="md:col-span-2">
                                        <p class="text-sm font-bold text-slate-800">{{ $item->product->name ?? 'Produk #' . $item->product_id }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Dipesan: {{ $item->quantity }} &bull; Sudah diterima: {{ $item->received_quantity ?? 0 }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jml Diterima</label>
                                        <input type="number" name="items[{{ $index }}][quantity]"
                                               value="{{ old('items.' . $index . '.quantity', $item->quantity - ($item->received_quantity ?? 0)) }}"
                                               min="0" max="{{ $item->quantity }}"
                                               class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:ring-2 focus:ring-indigo-500 @error('items.' . $index . '.quantity') border-red-500 @enderror" required>
                                        @error('items.' . $index . '.quantity')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Harga Beli (Rp)</label>
                                        <input type="number" name="items[{{ $index }}][price]"
                                               value="{{ old('items.' . $index . '.price', $item->price) }}" min="0"
                                               class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:ring-2 focus:ring-indigo-500 @error('items.' . $index . '.price') border-red-500 @enderror" required>
                                        @error('items.' . $index . '.price')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tanggal & Pembayaran --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="purchase_date" class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Penerimaan</label>
                        <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:ring-2 focus:ring-indigo-500 @error('purchase_date') border-red-500 @enderror" required>
                        @error('purchase_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="paid_amount" class="block text-sm font-semibold text-slate-700 mb-2">Jumlah Dibayar (Rp)</label>
                        <input type="number" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', 0) }}" min="0"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:ring-2 focus:ring-indigo-500 @error('paid_amount') border-red-500 @enderror">
                        <p class="text-xs text-slate-400 mt-1">Biarkan 0 jika akan dicatat sebagai hutang supplier.</p>
                        @error('paid_amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/20">
                        Konfirmasi Penerimaan Barang
                    </button>
                    <a href="{{ route('purchases.orders.index') }}"
                       class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
