<x-layouts.app title="Produk Terlaris">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Produk Terlaris</h1>
                <p class="text-sm text-slate-500">Daftar produk dengan performa penjualan terbaik.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
        </div>

        <div class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <form action="{{ route('reports.best-selling-products') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('supervisor'))
                    <div class="col-span-full md:col-span-1">
                        <label for="branch_id" class="mb-1 block text-sm font-medium text-slate-700">Cabang</label>
                        <select name="branch_id" id="branch_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif
                    <div class="col-span-full flex justify-end">
                        <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">Filter</button>
                    </div>
                </form>
            </div>
            @forelse ($data as $item)
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Produk</p>
                            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $item->product->name ?? 'Produk tidak diketahui' }}</p>
                        </div>
                        <div class="text-sm text-slate-500">Terjual: {{ $item->total_qty }}</div>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">Belum ada data produk terlaris.</div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
