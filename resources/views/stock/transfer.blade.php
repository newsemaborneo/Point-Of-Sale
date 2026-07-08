<x-layouts.app title="Transfer Stok"> {{-- Assuming this file is resources/views/stock/transfer.blade.php --}}
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Form Transfer Stok</h1>
        <p class="text-sm text-slate-500">Isi formulir di bawah untuk memindahkan stok produk antar gudang.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('stock.transfer') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="product_id" class="mb-2 block text-sm font-medium text-slate-700">Produk</label>
                    <select name="product_id" id="product_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900" required>
                        <option value="">Pilih Produk</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach 
                    </select>
                    @error('product_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="from_warehouse_id" class="mb-2 block text-sm font-medium text-slate-700">Dari Gudang</label>
                    <select name="from_warehouse_id" id="from_warehouse_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900" required>
                        <option value="">Pilih Gudang Asal</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('from_warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="to_warehouse_id" class="mb-2 block text-sm font-medium text-slate-700">Ke Gudang</label>
                    <select name="to_warehouse_id" id="to_warehouse_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900" required>
                        <option value="">Pilih Gudang Tujuan</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="quantity" class="mb-2 block text-sm font-medium text-slate-700">Jumlah</label>
                    <input type="number" name="quantity" id="quantity" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900" required min="1">
                </div>
                <div>
                    <label for="note" class="mb-2 block text-sm font-medium text-slate-700">Catatan (Opsional)</label>
                    <textarea name="note" id="note" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900"></textarea>
                </div>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700">Simpan Transfer Stok</button>
            </form>
        </div>
    </div>
</x-layouts.app>