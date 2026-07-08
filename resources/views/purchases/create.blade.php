<x-layouts.app title="Buat Pembelian Langsung">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Buat Pembelian Langsung</h1>
        <p class="text-sm text-slate-500">Catat pembelian barang yang tidak melalui Purchase Order.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('purchases.store') }}" method="POST" class="space-y-6">
                @csrf
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Informasi Pembelian</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="supplier_id" class="mb-2 block text-sm font-medium text-slate-700">Supplier</label>
                        <select name="supplier_id" id="supplier_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900" required>
                            <option value="">Pilih Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach 
                        </select>
                        @error('supplier_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="warehouse_id" class="mb-2 block text-sm font-medium text-slate-700">Gudang Tujuan</label>
                        <select name="warehouse_id" id="warehouse_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900" required>
                            <option value="">Pilih Gudang</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="purchase_date" class="mb-2 block text-sm font-medium text-slate-700">Tanggal Pembelian</label>
                        <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900" required>
                        @error('purchase_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="note" class="mb-2 block text-sm font-medium text-slate-700">Catatan (Opsional)</label>
                        <textarea name="note" id="note" rows="1" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900">{{ old('note') }}</textarea>
                        @error('note')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <h2 class="text-lg font-semibold text-slate-800 mt-6 mb-4">Item Pembelian</h2>
                <div id="purchase-items-container" class="space-y-4">
                    {{-- Item akan ditambahkan di sini menggunakan JavaScript atau Blade loop jika ada old input --}}
                    <div class="border border-slate-200 p-4 rounded-xl">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Produk</label>
                                <select name="items[0][product_id]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900 @error('items.0.product_id') border-red-500 @enderror" required>
                                    <option value="">Pilih Produk</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('items.0.product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error('items.0.product_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Jumlah</label>
                                <input type="number" name="items[0][quantity]" value="{{ old('items.0.quantity', 1) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900 @error('items.0.quantity') border-red-500 @enderror" min="1" required>
                                @error('items.0.quantity')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Harga Beli</label>
                                <input type="number" name="items[0][price]" value="{{ old('items.0.price', 0) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900 @error('items.0.price') border-red-500 @enderror" min="0" required>
                                @error('items.0.price')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-item-btn" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Tambah Item</button>

                <h2 class="text-lg font-semibold text-slate-800 mt-6 mb-4">Pembayaran</h2>
                <div>
                    <label for="paid_amount" class="mb-2 block text-sm font-medium text-slate-700">Jumlah Dibayar (Rp)</label>
                    <input type="number" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', 0) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 @error('paid_amount') border-red-500 @enderror" min="0">
                    <p class="text-xs text-slate-500 mt-1">Biarkan 0 jika akan dicatat sebagai hutang.</p>
                    @error('paid_amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 mt-6">
                    <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700">Simpan Pembelian</button>
                    <a href="{{ route('purchases.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-5 py-3 text-sm text-slate-700 hover:bg-slate-100">Batal</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let itemIndex = {{ count(old('items', [[]])) }}; // Start index from existing old inputs
            const addItemBtn = document.getElementById('add-item-btn');
            const itemsContainer = document.getElementById('purchase-items-container');
            const products = @json($products);

            addItemBtn.addEventListener('click', function () {
                const newItemHtml = `
                    <div class="border border-slate-200 p-4 rounded-xl">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Produk</label>
                                <select name="items[${itemIndex}][product_id]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900" required>
                                    <option value="">Pilih Produk</option>
                                    ${products.map(product => `<option value="${product.id}">${product.name}</option>`).join('')}
                                </select>
                                @error('items.${itemIndex}.product_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Jumlah</label>
                                <input type="number" name="items[${itemIndex}][quantity]" value="1" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900" min="1" required>
                                @error('items.${itemIndex}.quantity')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Harga Beli</label>
                                <input type="number" name="items[${itemIndex}][price]" value="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900" min="0" required>
                                @error('items.${itemIndex}.price')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="flex justify-end mt-3">
                            <button type="button" class="remove-item-btn text-rose-600 hover:text-rose-800 text-sm">Hapus</button>
                        </div>
                    </div>
                `;
                itemsContainer.insertAdjacentHTML('beforeend', newItemHtml);
                itemIndex++;
            });

            itemsContainer.addEventListener('click', function (e) {
                if (e.target && e.target.classList.contains('remove-item-btn')) {
                    e.target.closest('.border').remove();
                }
            });
        });
    </script>
    @endpush
</x-layouts.app>
