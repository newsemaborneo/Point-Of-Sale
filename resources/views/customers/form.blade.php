<x-layouts.app :title="$customer->exists ? 'Ubah Pelanggan' : 'Tambah Pelanggan'">
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $customer->exists ? 'Ubah Pelanggan' : 'Tambah Pelanggan' }}</h1>
            <p class="text-sm text-slate-500">Masukkan informasi dasar pelanggan untuk pencatatan transaksi.</p>
        </div>

        <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}" class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @if ($customer->exists)
                @method('PUT')
            @endif

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Nama</span>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Telepon</span>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Email</span>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Tipe Member</span>
                    <select name="member_type" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        <option value="regular" {{ old('member_type', $customer->member_type) == 'regular' ? 'selected' : '' }}>Regular</option>
                        <option value="silver" {{ old('member_type', $customer->member_type) == 'silver' ? 'selected' : '' }}>Silver</option>
                        <option value="gold" {{ old('member_type', $customer->member_type) == 'gold' ? 'selected' : '' }}>Gold</option>
                        <option value="platinum" {{ old('member_type', $customer->member_type) == 'platinum' ? 'selected' : '' }}>Platinum</option>
                    </select>
                </label>
            </div>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Alamat</span>
                <textarea name="address" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('address', $customer->address) }}</textarea>
            </label>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('customers.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-slate-700 hover:bg-slate-100">Kembali</a>
                <button type="submit" class="rounded-md bg-slate-900 px-5 py-2 text-white hover:bg-slate-800">Simpan Pelanggan</button>
            </div>
        </form>
    </div>
</x-layouts.app>
