<x-layouts.app title="Kategori">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Kategori</h1>
                <p class="text-sm text-slate-500">Atur kategori produk untuk memudahkan pencarian.</p>
            </div>
            <a href="{{ route('categories.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-white shadow-sm hover:bg-emerald-700">Tambah Kategori</a>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-4 py-4 font-medium text-slate-900">{{ $category->name }}</td>
                            <td class="px-4 py-4">{{ $category->slug }}</td>
                            <td class="px-4 py-4 text-slate-700">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('categories.edit', $category) }}" class="rounded-md border border-slate-300 bg-slate-50 px-3 py-2 hover:bg-slate-100">Ubah</a>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Hapus kategori ini?')" class="rounded-md bg-rose-600 px-3 py-2 text-white hover:bg-rose-700">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
