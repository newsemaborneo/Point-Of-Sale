<x-layouts.app title="Notifikasi Stok Menipis">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Notifikasi Stok Menipis</h1>
        <p class="text-sm text-slate-500">Daftar produk dengan stok di bawah batas minimum.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @if ($lowStockProducts->isEmpty())
                <div class="text-center py-8 text-slate-500">
                    <p>Tidak ada produk dengan stok menipis saat ini. Semua stok aman!</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-900">
                            <tr>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3">SKU</th>
                                <th class="px-4 py-3">Stok Saat Ini</th>
                                <th class="px-4 py-3">Stok Minimum</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($lowStockProducts as $product)
                                <tr>
                                    <td class="px-4 py-4">{{ $product->name }}</td>
                                    <td class="px-4 py-4">{{ $product->sku }}</td>
                                    <td class="px-4 py-4 text-red-600 font-semibold">{{ $product->totalStock() }}</td>
                                    <td class="px-4 py-4">{{ $product->min_stock }}</td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Kelola Produk
                                        </a>
                                        {{-- Anda bisa menambahkan link untuk stok masuk cepat di sini --}}
                                        <a href="{{ route('stock.in.create', ['product_id' => $product->id]) }}" class="ml-4 text-emerald-600 hover:text-emerald-900">
                                            Stok Masuk
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>