<x-layouts.app title="Riwayat Stok"> {{-- Assuming this file is resources/views/stock/history.blade.php --}}
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Riwayat Pergerakan Stok</h1>
        <p class="text-sm text-slate-500">Lihat semua catatan masuk, keluar, transfer, dan penyesuaian stok.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('stock.history') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label for="product_id" class="mb-1 block text-sm font-medium text-slate-700">Produk</label>
                    <select name="product_id" id="product_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900">
                        <option value="">Semua Produk</option>
                        @foreach($products as $product) {{-- $products should be passed from controller --}}
                            <option value="{{ $product->id }}" {{ old('product_id', request('product_id')) == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="warehouse_id" class="mb-1 block text-sm font-medium text-slate-700">Gudang</label>
                    <select name="warehouse_id" id="warehouse_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $warehouse) {{-- $warehouses should be passed from controller --}}
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id', request('warehouse_id')) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="type" class="mb-1 block text-sm font-medium text-slate-700">Tipe Pergerakan</label>
                    <select name="type" id="type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900">
                        <option value="">Semua Tipe</option>
                        @foreach($types as $type) {{-- $types should be passed from controller --}}
                            <option value="{{ $type }}" {{ old('type', request('type')) == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="date_from" class="mb-1 block text-sm font-medium text-slate-700">Dari Tanggal</label>
                    <input type="date" name="date_from" id="date_from" value="{{ old('date_from', request('date_from')) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900 @error('date_from') border-red-500 @enderror">
                    @error('date_from')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-sm font-medium text-slate-700">Sampai Tanggal</label>
                    <input type="date" name="date_to" id="date_to" value="{{ old('date_to', request('date_to')) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900 @error('date_to') border-red-500 @enderror">
                    @error('date_to')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-span-full flex justify-end">
                    <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">Filter</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-900">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Gudang</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Jumlah</th>
                            <th class="px-4 py-3">Stok Sebelum</th>
                            <th class="px-4 py-3">Stok Sesudah</th>
                            <th class="px-4 py-3">Catatan</th>
                            <th class="px-4 py-3">Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="px-4 py-4">{{ $movement->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-4">{{ $movement->product->name ?? 'N/A' }}</td>
                                <td class="px-4 py-4">{{ $movement->warehouse->name ?? 'N/A' }}</td>
                                <td class="px-4 py-4">{{ ucfirst($movement->type) }}</td>
                                <td class="px-4 py-4">{{ $movement->quantity }}</td>
                                <td class="px-4 py-4">{{ $movement->quantity_before }}</td>
                                <td class="px-4 py-4">{{ $movement->quantity_after }}</td>
                                <td class="px-4 py-4">{{ $movement->note }}</td>
                                <td class="px-4 py-4">{{ $movement->user->name ?? 'Sistem' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada riwayat pergerakan stok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $movements->links() }}</div>
        </div>
    </div>
</x-layouts.app>