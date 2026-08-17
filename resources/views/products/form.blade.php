<x-layouts.app :title="$product->exists ? 'Ubah Produk' : 'Tambah Produk'">
    <div class="max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $product->exists ? 'Ubah Produk' : 'Tambah Produk' }}</h1>
            <p class="text-sm text-slate-500">Lengkapi informasi produk dan simpan ke katalog.</p>
        </div>

        <form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" enctype="multipart/form-data" class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @if ($product->exists)
                @method('PUT')
            @endif

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Nama Produk</span>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('name') border-red-500 @enderror" />
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">SKU / Barcode</span>
                    <input type="text" name="sku" id="sku-input" value="{{ old('sku', $product->sku) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all @error('sku') border-red-500 @enderror" />
                    @error('sku')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Kategori</span>
                    <select name="category_id" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('category_id') border-red-500 @enderror">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Supplier</span>
                    <select name="supplier_id" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('supplier_id') border-red-500 @enderror">
                        <option value="">Pilih supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Harga Beli</span>
                    <input type="number" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('purchase_price') border-red-500 @enderror" />
                    @error('purchase_price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Harga Jual</span>
                    <input type="number" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('sale_price') border-red-500 @enderror" />
                    @error('sale_price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Stok Minimum</span>
                    <input type="number" name="min_stock" value="{{ old('min_stock', $product->min_stock) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('min_stock') border-red-500 @enderror" />
                    @error('min_stock')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Foto Produk</span>
                    <input type="file" name="photo" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('photo') border-red-500 @enderror" />
                    @error('photo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Deskripsi</span>
                <textarea name="description" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('description') border-red-500 @enderror">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </label>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('products.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition-colors">Simpan Produk</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let lastKeyTime = Date.now();
            let barcodeBuffer = '';
            const skuInput = document.getElementById('sku-input');

            // Prevent form submission when pressing enter directly inside the SKU field
            if (skuInput) {
                skuInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                });
            }

            document.addEventListener('keydown', e => {
                const currentTime = Date.now();
                const timeDiff = currentTime - lastKeyTime;
                lastKeyTime = currentTime;

                if (e.key.length === 1) { // Normal char
                    if (timeDiff > 50) { 
                        barcodeBuffer = e.key; // Reset if too slow
                    } else {
                        barcodeBuffer += e.key; // Append if fast
                    }
                } else if (e.key === 'Enter') {
                    // If buffer looks like a barcode and was typed very fast
                    if (barcodeBuffer.length >= 3 && timeDiff <= 50) {
                        e.preventDefault(); // Prevent form submission
                        
                        if (skuInput) {
                            skuInput.value = barcodeBuffer;
                            // Visual feedback
                            skuInput.classList.add('ring-4', 'ring-indigo-300', 'bg-indigo-50');
                            setTimeout(() => skuInput.classList.remove('ring-4', 'ring-indigo-300', 'bg-indigo-50'), 500);
                            
                            // Cleanup if scanner accidentally typed into another focused field
                            if (e.target.tagName === 'INPUT' && e.target.id !== 'sku-input') {
                                const val = e.target.value;
                                if (val.endsWith(barcodeBuffer)) {
                                    e.target.value = val.slice(0, -barcodeBuffer.length);
                                }
                            }
                        }
                    }
                    barcodeBuffer = '';
                }
            });
        });
    </script>
    @endpush
</x-layouts.app>
