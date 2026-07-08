<x-layouts.app title="Stock Opname"> {{-- Assuming this file is resources/views/stock/opname.blade.php --}}
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Form Mulai Stock Opname</h1>
        <p class="text-sm text-slate-500">Isi formulir di bawah untuk memulai proses stock opname.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('stock.opname.start') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="warehouse_id" class="mb-2 block text-sm font-medium text-slate-700">Gudang</label>
                    <select name="warehouse_id" id="warehouse_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900" required>
                        <option value="">Pilih Gudang</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach 
                    </select>
                    @error('warehouse_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="opname_date" class="mb-2 block text-sm font-medium text-slate-700">Tanggal Opname</label>
                    <input type="date" name="opname_date" id="opname_date" value="{{ old('opname_date', now()->toDateString()) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 @error('opname_date') border-red-500 @enderror" required>
                    @error('opname_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="note" class="mb-2 block text-sm font-medium text-slate-700">Catatan (Opsional)</label>
                    <textarea name="note" id="note" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 @error('note') border-red-500 @enderror">{{ old('note') }}</textarea>
                    @error('note')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">Mulai Stock Opname</button>
            </form>
        </div>
    </div>
</x-layouts.app>