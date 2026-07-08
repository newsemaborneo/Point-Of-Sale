<x-layouts.app title="Detail Produk">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $product->name }}</h1>
                <p class="text-sm text-slate-500">Informasi lengkap produk, stok, dan harga.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.edit', $product) }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">Edit</a>
                <a href="{{ route('products.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm text-slate-500">SKU</h2>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $product->sku }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm text-slate-500">Barcode</h2>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $product->barcode }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm text-slate-500">Kategori</h2>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $product->category->name ?? '-' }}</p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm text-slate-500">Harga Beli</h2>
                <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm text-slate-500">Harga Jual</h2>
                <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Deskripsi</h2>
            <p class="mt-2 text-sm text-slate-700">{{ $product->description ?? 'Tidak tersedia' }}</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Stok Gudang</h2>
                <div class="mt-4 space-y-3">
                    @forelse($product->stocks as $stock)
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">{{ $stock->warehouse->name ?? 'Gudang tidak diketahui' }}</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $stock->quantity }} {{ $product->unit->name ?? '' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Tidak ada stok terdaftar.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Detail Tambahan</h2>
                <dl class="mt-4 space-y-4 text-sm text-slate-700">
                    <div>
                        <dt class="font-medium text-slate-900">Diskon</dt>
                        <dd>{{ $product->discount_type ? ucfirst($product->discount_type) . ' ' . $product->discount_value : 'Tidak ada' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-900">Pajak</dt>
                        <dd>{{ $product->tax_percent ? $product->tax_percent . '%' : 'Tidak ada' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-900">Stok Minimum</dt>
                        <dd>{{ $product->min_stock ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-layouts.app>
