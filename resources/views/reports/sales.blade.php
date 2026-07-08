<x-layouts.app title="Laporan Penjualan">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Laporan Penjualan</h1>
                <p class="text-sm text-slate-500">Ringkasan penjualan dan transaksi completed.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
        </div>

        {{-- Tombol Cetak dan Ekspor Excel --}}
        <div class="flex justify-end gap-4 mb-4">
            {{-- Tombol Ekspor Excel --}}
            <a href="{{ route('reports.sales', array_merge(request()->query(), ['export' => 'excel'])) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"> {{-- Excel Icon --}}
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v4.59L7.3 9.29a.75.75 0 00-1.06 1.06l3.25 3.25a.75.75 0 001.06 0l3.25-3.25a.75.75 0 10-1.06-1.06l-1.97 1.97V6.75z" clip-rule="evenodd" />
                </svg>
                Ekspor Excel
            </a>

            {{-- Tombol Cetak --}}
            <a href="{{ route('reports.sales', array_merge(request()->query(), ['print' => 'true'])) }}"
               target="_blank" {{-- Buka di tab baru untuk pratinjau cetak --}}
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"> {{-- Print Icon --}}
                    <path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V4a2 2 0 00-2-2H5zm0 2h10v7H5V4zm0 9h10v3H5v-3z" clip-rule="evenodd" />
                </svg>
                Cetak
            </a>
        </div>

        {{-- Filter Form --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('reports.sales') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-{{ $branches->isNotEmpty() ? 3 : 2 }}">
                <div>
                    <label for="date_from" class="mb-1 block text-sm font-medium text-slate-700">Dari Tanggal</label> {{-- Label for date_from --}}
                    <input type="date" name="date_from" id="date_from" value="{{ old('date_from', request('date_from')) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900 @error('date_from') border-red-500 @enderror">
                    @error('date_from')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-sm font-medium text-slate-700">Sampai Tanggal</label> {{-- Label for date_to --}}
                    <input type="date" name="date_to" id="date_to" value="{{ old('date_to', request('date_to')) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900 @error('date_to') border-red-500 @enderror">
                    @error('date_to')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Filter cabang: hanya tampil untuk admin/supervisor. User biasa otomatis terkunci ke cabangnya sendiri. --}}
                @if ($branches->isNotEmpty())
                    <div>
                        <label for="branch_id" class="mb-1 block text-sm font-medium text-slate-700">Cabang</label> {{-- Label for branch_id --}}
                        <select name="branch_id" id="branch_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900">
                            <option value="">Semua Cabang</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
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

        {{-- Summary Statistics --}}
        <div class="grid gap-6 md:grid-cols-2">
            <div class="bg-indigo-50 p-4 rounded-xl shadow-sm">
                <p class="text-sm text-indigo-700">
                    Total Pendapatan
                    @if (!$branches->isNotEmpty() && $userBranchName)
                        <span class="font-normal">({{ $userBranchName }})</span>
                    @elseif ($selectedBranchId)
                        <span class="font-normal">({{ $branches->firstWhere('id', $selectedBranchId)->name ?? '-' }})</span>
                    @else
                        <span class="font-normal">(Semua Cabang)</span>
                    @endif
                </p>
                <p class="text-2xl font-bold text-indigo-900">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
            </div>
            <div class="bg-emerald-50 p-4 rounded-xl shadow-sm">
                <p class="text-sm text-emerald-700">Total Transaksi</p>
                <p class="text-2xl font-bold text-emerald-900">{{ $totalTransactions }}</p>
            </div>
        </div>

        {{-- Sales Details Table --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Detail Penjualan</h2>
            @if ($sales->isEmpty())
                <div class="text-center py-8 text-slate-500">
                    <p>Tidak ada data penjualan untuk periode ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-900">
                            <tr>
                                <th class="px-4 py-3">Invoice</th>
                                @if ($branches->isNotEmpty())
                                    <th class="px-4 py-3">Cabang</th>
                                @endif
                                <th class="px-4 py-3">Pelanggan</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($sales as $sale)
                                <tr>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('transactions.show', $sale) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $sale->invoice_number }}
                                        </a>
                                    </td>
                                    @if ($branches->isNotEmpty())
                                        <td class="px-4 py-4">{{ $sale->branch->name ?? '-' }}</td>
                                    @endif
                                    <td class="px-4 py-4">{{ $sale->customer->name ?? 'Umum' }}</td>
                                    <td class="px-4 py-4">{{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4">{{ $sale->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sale->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('transactions.receipt', $sale) }}" class="text-blue-600 hover:text-blue-800 transition-colors">Cetak Struk</a>
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