<x-layouts.app :title="$user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna'">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</h1>
                <p class="text-sm text-slate-500">Lengkapi informasi pengguna dan kelola akses mereka.</p>
            </div>
            <a href="{{ route('users.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                Kembali ke Daftar Pengguna
            </a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" method="POST" class="space-y-6">
                @csrf
                @if ($user->exists)
                    @method('PUT')
                @endif

                {{-- Nama --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                           class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                           class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm" required>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Telepon</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role --}}
                <div>
                    <label for="role_id" class="block text-sm font-medium text-slate-700 mb-1">Peran (Role)</label>
                    <select name="role_id" id="role_id" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm" required>
                        <option value="">Pilih Peran</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Branch --}}
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-slate-700 mb-1">Cabang (Opsional)</label>
                    <select name="branch_id" id="branch_id" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm">
                        <option value="">Tidak Ada Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password (opsional, hanya diisi jika ingin mengubah) --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                        Password (Biarkan kosong jika tidak ingin mengubah)
                    </label>
                    <input type="password" name="password" id="password"
                           class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        {{ $user->exists ? 'Perbarui Pengguna' : 'Tambah Pengguna' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>