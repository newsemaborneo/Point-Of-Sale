<x-layouts.app title="Laporan Kas">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Laporan Kas</h1>
                <p class="text-sm text-slate-500">Ringkasan pergerakan kas masuk dan kas keluar.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('reports.cash') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
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

            <div class="grid gap-6 md:grid-cols-2 mt-6">
                <div class="bg-emerald-50 p-4 rounded-xl shadow-sm">
                    <p class="text-sm text-emerald-700">Total Kas Masuk</p>
                    <p class="text-2xl font-bold text-emerald-900">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
                </div>
                <div class="bg-rose-50 p-4 rounded-xl shadow-sm">
                    <p class="text-sm text-rose-700">Total Kas Keluar</p>
                    <p class="text-2xl font-bold text-rose-900">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
                </div>
            </div>

            <h2 class="text-lg font-semibold text-slate-800 mb-4 mt-6">Detail Pergerakan Kas</h2>
            @if ($movements->isEmpty())
                <div class="text-center py-8 text-slate-500">
                    <p>Tidak ada data pergerakan kas untuk periode ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-900">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Tipe</th>
                                <th class="px-4 py-3">Jumlah</th>
                                <th class="px-4 py-3">Kategori</th>
                                <th class="px-4 py-3">Deskripsi</th>
                                <th class="px-4 py-3">Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($movements as $movement)
                                <tr>
                                    <td class="px-4 py-4">{{ $movement->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $movement->type === 'in' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ ucfirst($movement->type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">Rp {{ number_format($movement->amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4">{{ $movement->category ?? '-' }}</td>
                                    <td class="px-4 py-4">{{ $movement->description ?? '-' }}</td>
                                    <td class="px-4 py-4">{{ $movement->user->name ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>