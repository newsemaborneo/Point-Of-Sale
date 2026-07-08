<x-layouts.app title="Laporan Laba/Rugi">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Laporan Laba/Rugi</h1>
                <p class="text-sm text-slate-500">Ringkasan pendapatan, HPP, pengeluaran kas, dan laba bersih.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('reports.profit-loss') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label for="date_from" class="mb-1 block text-sm font-medium text-slate-700">Dari Tanggal</label>
                    <input type="date" name="date_from" id="date_from" value="{{ old('date_from', request('date_from', $from)) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900 @error('date_from') border-red-500 @enderror">
                    @error('date_from')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-sm font-medium text-slate-700">Sampai Tanggal</label>
                    <input type="date" name="date_to" id="date_to" value="{{ old('date_to', request('date_to', $to)) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900 @error('date_to') border-red-500 @enderror">
                    @error('date_to')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('supervisor'))
                <div>
                    <label for="branch_id" class="mb-1 block text-sm font-medium text-slate-700">Cabang</label>
                    <select name="branch_id" id="branch_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $selectedBranchId) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
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

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Pendapatan</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">Rp {{ number_format($revenue, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">HPP</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">Rp {{ number_format($cogs, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Pengeluaran Kas</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">Rp {{ number_format($expenses, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Laba Kotor</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">Rp {{ number_format($grossProfit, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Laba Bersih</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</x-layouts.app>