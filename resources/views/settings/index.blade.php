<x-layouts.app title="Pengaturan Sistem">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Pengaturan Sistem</h1>
                <p class="text-sm text-slate-500">Kelola berbagai pengaturan aplikasi Anda.</p>
            </div>
        </div>

        {{-- Pengaturan Umum --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Pengaturan Umum</h2>
            <form action="{{ route('settings') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                @php
                    $generalSettings = $settings->where('group', 'general')->keyBy('key');
                @endphp

                {{-- Company Name --}}
                <div>
                    <label for="company_name" class="mb-1 block text-sm font-medium text-slate-700">Nama Perusahaan</label>
                    <input type="text" name="settings[company_name][value]" id="company_name"
                           value="{{ old('settings.company_name.value', $generalSettings->get('company_name')->value ?? '') }}"
                           class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm" required>
                    <input type="hidden" name="settings[company_name][key]" value="company_name">
                    <input type="hidden" name="settings[company_name][group]" value="general">
                    @error('settings.company_name.value')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Company Address --}}
                <div>
                    <label for="company_address" class="mb-1 block text-sm font-medium text-slate-700">Alamat Perusahaan</label>
                    <textarea name="settings[company_address][value]" id="company_address" rows="2"
                              class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm">{{ old('settings.company_address.value', $generalSettings->get('company_address')->value ?? '') }}</textarea>
                    <input type="hidden" name="settings[company_address][key]" value="company_address">
                    <input type="hidden" name="settings[company_address][group]" value="general">
                    @error('settings.company_address.value')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Company Phone --}}
                <div>
                    <label for="company_phone" class="mb-1 block text-sm font-medium text-slate-700">Telepon Perusahaan</label>
                    <input type="text" name="settings[company_phone][value]" id="company_phone"
                           value="{{ old('settings.company_phone.value', $generalSettings->get('company_phone')->value ?? '') }}"
                           class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm">
                    <input type="hidden" name="settings[company_phone][key]" value="company_phone">
                    <input type="hidden" name="settings[company_phone][group]" value="general">
                    @error('settings.company_phone.value')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Default Currency --}}
                <div>
                    <label for="default_currency" class="mb-1 block text-sm font-medium text-slate-700">Mata Uang Default</label>
                    <input type="text" name="settings[default_currency][value]" id="default_currency"
                           value="{{ old('settings.default_currency.value', $generalSettings->get('default_currency')->value ?? '') }}"
                           class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm">
                    <input type="hidden" name="settings[default_currency][key]" value="default_currency">
                    <input type="hidden" name="settings[default_currency][group]" value="general">
                    @error('settings.default_currency.value')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Simpan Pengaturan Umum
                    </button>
                </div>
            </form>
        </div>

        {{-- Upload Logo Toko --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Upload Logo Toko</h2>
            <form action="{{ route('settings.logo') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="logo" class="mb-1 block text-sm font-medium text-slate-700">Pilih File Logo</label>
                    <input type="file" name="logo" id="logo" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm">
                    @error('logo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    @if ($storeLogo)
                        <p class="mt-2 text-sm text-slate-500">Logo saat ini:</p>
                        <img src="{{ asset('storage/' . $storeLogo) }}" alt="Store Logo" class="mt-1 h-20 w-auto object-contain">
                    @endif
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Upload Logo
                    </button>
                </div>
            </form>
        </div>

        {{-- Jam Operasional Toko --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Jam Operasional Toko</h2>
            <p class="text-sm text-slate-500 mb-4">Atur jam buka & tutup — ditampilkan otomatis di layar kasir (POS).</p>
            <form action="{{ route('settings.store-hours') }}" method="POST" class="space-y-4">
                @csrf
                @method('POST') {{-- Menggunakan POST karena route updateStoreHours adalah POST --}}

                <div>
                    <label for="store_hours_enabled" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="checkbox" name="store_hours_enabled" id="store_hours_enabled" value="1" {{ $storeHours['enabled'] ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        Aktifkan Jadwal Otomatis
                    </label>
                    <p class="text-xs text-slate-500 mt-1">Jika dimatikan, status toko selalu dianggap buka.</p>
                    @error('store_hours_enabled')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="store_open_time" class="mb-1 block text-sm font-medium text-slate-700">Jam Buka</label>
                        <input type="time" name="store_open_time" id="store_open_time"
                               value="{{ old('store_open_time', $storeHours['open_time']) }}"
                               class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm" required>
                        <p class="text-xs text-slate-500 mt-1">Mulai dihitung sebagai status BUKA</p>
                        @error('store_open_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="store_close_time" class="mb-1 block text-sm font-medium text-slate-700">Jam Tutup</label>
                        <input type="time" name="store_close_time" id="store_close_time"
                               value="{{ old('store_close_time', $storeHours['close_time']) }}"
                               class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm" required>
                        <p class="text-xs text-slate-500 mt-1">Mulai dihitung sebagai status TUTUP</p>
                        @error('store_close_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p class="text-sm text-slate-500 mt-4">Toko akan otomatis ditandai BUKA pukul {{ \Carbon\Carbon::parse($storeHours['open_time'])->format('H:i') }} dan TUTUP pukul {{ \Carbon\Carbon::parse($storeHours['close_time'])->format('H:i') }} setiap hari.</p>

                {{-- Preset Cepat (contoh, bisa diimplementasikan dengan JS) --}}
                <div class="mt-4">
                    <h4 class="text-sm font-medium text-slate-700 mb-2">Preset Cepat</h4>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="preset-button rounded-md border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100" data-open="08:00" data-close="17:00">08:00 – 17:00 (Pagi)</button>
                        <button type="button" class="preset-button rounded-md border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100" data-open="09:00" data-close="21:00">09:00 – 21:00 (Normal)</button>
                        <button type="button" class="preset-button rounded-md border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100" data-open="10:00" data-close="22:00">10:00 – 22:00 (Mall)</button>
                        <button type="button" class="preset-button rounded-md border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100" data-open="20:00" data-close="04:00">20:00 – 04:00 (Malam)</button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Simpan Jam Operasional
                    </button>
                </div>
            </form>
        </div>

        {{-- Backup Database --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Backup Database</h2>
            <p class="text-sm text-slate-500 mb-4">Buat salinan cadangan database Anda.</p>
            <form action="{{ route('audit.backup') }}" method="POST">
                @csrf
                <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    Mulai Backup
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.preset-button').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('store_open_time').value = this.dataset.open;
                    document.getElementById('store_close_time').value = this.dataset.close;
                });
            });
        </script>
    @endpush
</x-layouts.app>