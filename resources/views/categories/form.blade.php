<x-layouts.app :title="$category->exists ? 'Ubah Kategori' : 'Tambah Kategori'">
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $category->exists ? 'Ubah Kategori' : 'Tambah Kategori' }}</h1>
            <p class="text-sm text-slate-500">Kelola struktur kategori untuk produk Anda.</p>
        </div>

        <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @if ($category->exists)
                @method('PUT')
            @endif

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Nama Kategori</span>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Kategori Induk</span>
                    <select name="parent_id" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        <option value="">Tidak ada</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('categories.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-slate-700 hover:bg-slate-100">Kembali</a>
                <button type="submit" class="rounded-md bg-slate-900 px-5 py-2 text-white hover:bg-slate-800">Simpan Kategori</button>
            </div>
        </form>
    </div>
</x-layouts.app>
