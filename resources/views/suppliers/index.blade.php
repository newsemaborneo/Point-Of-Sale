<x-layouts.app title="Supplier">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Supplier</h1>
                <p class="text-sm text-slate-500">Daftar supplier, riwayat pembelian, dan hutang supplier.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-2">
                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama, kode, telepon"
                        class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-700">Cari</button>
                </form>
                <a href="{{ route('suppliers.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-white shadow-sm hover:bg-emerald-700">Tambah Supplier</a>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Kode</th>
                        <th class="px-4 py-3 font-medium">Telepon</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Kontak</th>
                        <th class="px-4 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="px-4 py-4 font-medium text-slate-900">{{ $supplier->name }}</td>
                            <td class="px-4 py-4">{{ $supplier->code ?? '-' }}</td>
                            <td class="px-4 py-4">{{ $supplier->phone ?? '-' }}</td>
                            <td class="px-4 py-4">{{ $supplier->email ?? '-' }}</td>
                            <td class="px-4 py-4">{{ $supplier->contact_person ?? '-' }}</td>
                            <td class="px-4 py-4 text-slate-700">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-slate-700 hover:bg-slate-100">Detail</a>
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-slate-700 hover:bg-slate-100">Ubah</a>
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Hapus supplier ini?')" class="rounded-md bg-rose-600 px-3 py-2 text-white hover:bg-rose-700">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada supplier yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
            <p>
                Catatan: stok dikelola per gudang melalui tabel <code>product_stocks</code>,
                pergerakan stok disimpan di <code>stock_movements</code> sebagai audit trail.
            </p>
            <p class="mt-2 text-slate-500">
                Rekomendasi package: <code>milon/barcode</code>, <code>simplesoftwareio/simple-qrcode</code>, <code>barryvdh/laravel-dompdf</code>, <code>maatwebsite/excel</code>, <code>spatie/laravel-backup</code>.
            </p>
        </div>

        <div class="mt-6">{{ $suppliers->links() }}</div>
    </div>
</x-layouts.app>
