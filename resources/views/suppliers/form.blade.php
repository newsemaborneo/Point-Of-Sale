<x-layouts.app :title="$title">
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $title }}</h1>
            <p class="text-sm text-slate-500">{{ $supplier->exists ? 'Perbarui data supplier.' : 'Tambahkan supplier baru untuk pembelian dan hutang.' }}</p>
        </div>

        <form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="space-y-6 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            @csrf
            @if ($supplier->exists)
                @method('PUT')
            @endif

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Nama Supplier</span>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Kode</span>
                    <input type="text" name="code" value="{{ old('code', $supplier->code) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Telepon</span>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Email</span>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                </label>
            </div>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Alamat</span>
                <textarea name="address" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('address', $supplier->address) }}</textarea>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Contact Person</span>
                <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            </label>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('suppliers.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-slate-700 hover:bg-slate-100">Kembali</a>
                <button type="submit" class="rounded-md bg-slate-900 px-5 py-2 text-white hover:bg-slate-700">{{ $supplier->exists ? 'Simpan Perubahan' : 'Simpan Supplier' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>
