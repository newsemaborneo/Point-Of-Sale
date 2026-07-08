<x-layouts.app title="Manajemen Gudang">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Manajemen Gudang</h1>
                <p class="text-sm text-slate-500">Kelola daftar gudang yang tersedia.</p>
            </div>
            <a href="{{ route('warehouses.create') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">Tambah Gudang</a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @if ($warehouses->isEmpty())
                <div class="text-center py-8 text-slate-500">
                    <p>Tidak ada gudang yang terdaftar.</p>
                    <a href="{{ route('warehouses.create') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-900">Tambahkan Gudang Baru</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-900">
                            <tr>
                                <th class="px-4 py-3">Nama Gudang</th>
                                <th class="px-4 py-3">Cabang</th>
                                <th class="px-4 py-3">Alamat</th>
                                <th class="px-4 py-3">Utama</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($warehouses as $warehouse)
                                <tr>
                                    <td class="px-4 py-4">{{ $warehouse->name }}</td>
                                    <td class="px-4 py-4">{{ $warehouse->branch->name ?? 'Tidak Terhubung' }}</td>
                                    <td class="px-4 py-4">{{ $warehouse->address ?? '-' }}</td>
                                    <td class="px-4 py-4">
                                        @if ($warehouse->is_main)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Ya</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Tidak</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('warehouses.edit', $warehouse) }}" class="text-indigo-600 hover:text-indigo-800 transition-colors">Edit</a>
                                            <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus gudang ini?');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 transition-colors">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $warehouses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>