<x-layouts.app title="Penyesuaian Stok">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Form Penyesuaian Stok</h1>
        <p class="text-sm text-slate-500">Isi formulir di bawah untuk menyesuaikan jumlah stok produk.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('stock.adjustment') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="product_id" class="mb-2 block text-sm font-medium text-slate-700">Produk</label>
                    <select name="product_id" id="product_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900" required>
                        <option value="">Pilih Produk</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="warehouse_id" class="mb-2 block text-sm font-medium text-slate-700">Gudang</label>
                    <select name="warehouse_id" id="warehouse_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900" required>
                        <option value="">Pilih Gudang</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="actual_quantity" class="mb-2 block text-sm font-medium text-slate-700">Jumlah Aktual</label>
                    <input type="number" name="actual_quantity" id="actual_quantity" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900" required min="0">
                </div>
                <div>
                    <label for="note" class="mb-2 block text-sm font-medium text-slate-700">Catatan (Opsional)</label>
                    <textarea name="note" id="note" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900"></textarea>
                </div>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700">Simpan Penyesuaian</button>
            </form>
        </div>
    </div>
</x-layouts.app>