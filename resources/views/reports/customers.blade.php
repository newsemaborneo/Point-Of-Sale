<x-layouts.app title="Laporan Pelanggan">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Laporan Pelanggan</h1>
                <p class="text-sm text-slate-500">Data pelanggan dan nilai transaksi setiap pelanggan.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Kembali</a>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <form action="{{ route('reports.customers') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-2 mb-6">
                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('supervisor'))
                <div>
                    <label for="branch_id" class="mb-1 block text-sm font-medium text-slate-700">Cabang</label>
                    <select name="branch_id" id="branch_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-900">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="md:col-span-{{ (Auth::user()->hasRole('admin') || Auth::user()->hasRole('supervisor')) ? '2' : '1' }} flex justify-end">
                    <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
                </div>
            </form>
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3">Nama Pelanggan</th>
                        <th class="px-4 py-3">Total Transaksi</th>
                        <th class="px-4 py-3">Jumlah Pembelian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($data as $row)
                        <tr>
                            <td class="px-4 py-4">{{ $row->customer_name ?? $row->name ?? '-' }}</td>
                            <td class="px-4 py-4">Rp {{ number_format($row->total_spent ?? $row->total_transactions ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-4">{{ $row->purchase_count ?? $row->order_count ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada data pelanggan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
