<x-layouts.app title="{{ $warehouse->exists ? 'Edit Gudang' : 'Tambah Gudang' }}">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $warehouse->exists ? 'Edit Gudang' : 'Tambah Gudang' }}</h1>
                <p class="text-sm text-slate-500">Masukkan informasi gudang.</p>
            </div>
            <a href="{{ route('warehouses.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ $warehouse->exists ? route('warehouses.update', $warehouse) : route('warehouses.store') }}" method="POST" class="space-y-6">
                @csrf
                @if ($warehouse->exists)
                    @method('PUT')
                @endif

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Nama Gudang <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $warehouse->name) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 @error('name') border-red-500 @enderror" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="branch_id" class="mb-2 block text-sm font-medium text-slate-700">Cabang (Opsional)</label>
                    <select name="branch_id" id="branch_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900">
                        <option value="">Pilih Cabang</option>
                        @foreach($branches as $branch) {{-- $branches should be passed from controller --}}
                            <option value="{{ $branch->id }}" {{ old('branch_id', $warehouse->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option> 
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="address" class="mb-2 block text-sm font-medium text-slate-700">Alamat (Opsional)</label>
                    <textarea name="address" id="address" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 @error('address') border-red-500 @enderror">{{ old('address', $warehouse->address) }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_main" value="0">
                    <input type="checkbox" name="is_main" id="is_main" value="1" {{ old('is_main', $warehouse->is_main) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 @error('is_main') border-red-500 @enderror">
                    <label for="is_main" class="text-sm font-medium text-slate-700">Gudang Utama</label>
                    @error('is_main')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700">Simpan</button>
                    <a href="{{ route('warehouses.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-5 py-3 text-sm text-slate-700 hover:bg-slate-100">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>