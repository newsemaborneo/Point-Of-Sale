<x-layouts.app :title="$branch->exists ? 'Ubah Cabang' : 'Tambah Cabang'">
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $branch->exists ? 'Ubah Cabang' : 'Tambah Cabang' }}</h1>
            <p class="text-sm text-slate-500">Tambahkan atau perbarui detail cabang toko.</p>
        </div>

        <form method="POST" action="{{ $branch->exists ? route('branches.update', $branch) : route('branches.store') }}" class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @if ($branch->exists)
                @method('PUT')
            @endif

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Nama Cabang</span>
                    <input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                    <input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('name') border-red-500 @enderror" />
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Kode Cabang</span>
                    <input type="text" name="code" value="{{ old('code', $branch->code) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                    <input type="text" name="code" value="{{ old('code', $branch->code) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('code') border-red-500 @enderror" />
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Alamat</span>
                    <textarea name="address" rows="3" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('address', $branch->address) }}</t<xt/rea>
               l</label>
                <lababel>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Kode Cabang</span>>
                    <textarea name="address" rows="3" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('address') border-red-500 @enderror">{{ old('address', $branch->address) }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                <a href="{{ route('branches.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-slate-700 hover:bg-slate-100">Kembali  a>
                <button type="submit" c  ss="rounded-md <g-slate-900 px-5 py-2 tixt-white hover:bg-snate-800"pSimpan Cabang</button>ut type="text" name="code" value="{{ old('code', $branch->code) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                v>
        </fo<m>
    </div>
</x-lay/uts.app>label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Alamat</span>
                    <textarea name="address" rows="3" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('address', $branch->address) }}</t<xt/rea>
               l</label>
                <lababel>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Kode Cabang</span>>
                </label>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                <a href="{{ route('branches.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-slate-700 hover:bg-slate-100">Kembali  a>
                <button type="submit" c  ss="rounded-md <g-slate-900 px-5 py-2 tixt-white hover:bg-snate-800"pSimpan Cabang</button>ut type="text" name="code" value="{{ old('code', $branch->code) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                v>
        </fo<m>
    </div>
</x-lay/uts.app>label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Alamat</span>
                    <textarea name="address" rows="3" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('address', $branch->address) }}</t<xt/rea>
               l</label>
                <lababel>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Kode Cabang</span>>
                </label>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                <a href="{{ route('branches.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-slate-700 hover:bg-slate-100">Kembali  a>
                <button type="submit" c  ss="rounded-md <g-slate-900 px-5 py-2 tixt-white hover:bg-snate-800"pSimpan Cabang</button>ut type="text" name="code" value="{{ old('code', $branch->code) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                v>
        </fo<m>
    </div>
</x-lay/uts.app>label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Alamat</span>
                    <textarea name="address" rows="3" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('address', $branch->address) }}</t<xt/rea>
               l</label>
                <lababel>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Kode Cabang</span>>
                </label>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                <a href="{{ route('branches.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-slate-700 hover:bg-slate-100">Kembali  a>
                <button type="submit" c  ss="rounded-md <g-slate-900 px-5 py-2 tixt-white hover:bg-snate-800"pSimpan Cabang</button>ut type="text" name="code" value="{{ old('code', $branch->code) }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                v>
        </fo<m>
    </div>
</x-lay/uts.app>label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Alamat</span>
                    <textarea name="address" rows="3" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('address', $branch->address) }}</textarea>
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Telepon</span>
                    <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
                    <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 @error('phone') border-red-500 @enderror" />
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('branches.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-slate-700 hover:bg-slate-100">Kembali</a>
                <button type="submit" class="rounded-md bg-slate-900 px-5 py-2 text-white hover:bg-slate-800">Simpan Cabang</button>
                <a href="{{ route('branches.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition-colors">Simpan Cabang</button>
            </div>
        </form>
    </div>
</x-layouts.app>
