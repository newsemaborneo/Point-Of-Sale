<x-layouts.app title="Produk">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Produk</h1>
                <p class="text-sm text-slate-500">Kelola produk, kategori, supplier, dan persediaan Anda.</p>
            </div>
            <a href="{{ route('products.create') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">Tambah Produk</a>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">SKU / Barcode</th>
                        <th class="px-4 py-3 font-medium">Kategori</th>
                        <th class="px-4 py-3 font-medium">Supplier</th>
                        <th class="px-4 py-3 font-medium">Stok</th>
                        <th class="px-4 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-4 py-4 font-medium text-slate-900">{{ $product->name }}</td>
                            <td class="px-4 py-4">{{ $product->sku ?? $product->barcode ?? '-' }}</td>
                            <td class="px-4 py-4">{{ $product->category->name ?? '-' }}</td>
                            <td class="px-4 py-4">{{ $product->supplier->name ?? '-' }}</td>
                            <td class="px-4 py-4">{{ $product->totalStock() }}</td>
                            <td class="px-4 py-4 text-slate-700">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('products.show', $product) }}" class="rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm font-semibold hover:bg-slate-100 transition-colors">Detail</a>
                                    <a href="{{ route('products.edit', $product) }}" class="rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm font-semibold hover:bg-slate-100 transition-colors">Ubah</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada produk yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $products->links() }}</div>
    </div>
</x-layouts.app>
