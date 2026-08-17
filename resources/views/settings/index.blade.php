<x-layouts.app title="Pengaturan Sistem">
    <div class="space-y-6">
        <div class="overflow-hidden rounded-3xl border border-indigo-100 bg-gradient-to-r from-indigo-600 via-violet-600 to-sky-600 p-6 shadow-lg shadow-indigo-100/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-indigo-100">Konfigurasi</p>
                    <h1 class="text-2xl font-semibold text-white">Pengaturan Sistem</h1>
                    <p class="mt-1 text-sm text-indigo-100/90">Kelola identitas toko, branding, jam operasional, dan cadangan data.</p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-sm font-medium text-white/90 backdrop-blur-sm">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    Sistem aktif
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500">Profil toko</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900">
                    {{ old('settings.company_name.value', $settings->where('group', 'general')->keyBy('key')->get('company_name')->value ?? 'POS') }}
                </p>
                <p class="mt-1 text-sm text-slate-500">Nama usaha yang tampil di sistem</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500">Status operasional</p>
                <p class="mt-3 text-2xl font-semibold text-emerald-600">
                    {{ $storeHours['enabled'] ? 'Aktif' : 'Manual' }}
                </p>
                <p class="mt-1 text-sm text-slate-500">{{ $storeHours['open_time'] }} - {{ $storeHours['close_time'] }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500">Backup</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900">Rutin</p>
                <p class="mt-1 text-sm text-slate-500">Simpan cadangan data secara aman</p>
            </div>
        </div>

        {{-- Pengaturan Umum --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Pengaturan Umum</h2>
                    <p class="text-sm text-slate-500">Identitas dasar dan konfigurasi utama toko.</p>
                </div>
                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">Umum</span>
            </div>

            <form action="{{ route('settings') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                @php
                    $generalSettings = $settings->where('group', 'general')->keyBy('key');
                @endphp

                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="space-y-2">
                        <label for="company_name" class="block text-sm font-medium text-slate-700">Nama Perusahaan</label>
                        <input type="text" name="settings[company_name][value]" id="company_name"
                               value="{{ old('settings.company_name.value', $generalSettings->get('company_name')->value ?? '') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                        <input type="hidden" name="settings[company_name][key]" value="company_name">
                        <input type="hidden" name="settings[company_name][group]" value="general">
                        @error('settings.company_name.value')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="company_phone" class="block text-sm font-medium text-slate-700">Telepon Perusahaan</label>
                        <input type="text" name="settings[company_phone][value]" id="company_phone"
                               value="{{ old('settings.company_phone.value', $generalSettings->get('company_phone')->value ?? '') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        <input type="hidden" name="settings[company_phone][key]" value="company_phone">
                        <input type="hidden" name="settings[company_phone][group]" value="general">
                        @error('settings.company_phone.value')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="company_address" class="block text-sm font-medium text-slate-700">Alamat Perusahaan</label>
                    <textarea name="settings[company_address][value]" id="company_address" rows="3"
                              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">{{ old('settings.company_address.value', $generalSettings->get('company_address')->value ?? '') }}</textarea>
                    <input type="hidden" name="settings[company_address][key]" value="company_address">
                    <input type="hidden" name="settings[company_address][group]" value="general">
                    @error('settings.company_address.value')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2 max-w-xs">
                    <label for="default_currency" class="block text-sm font-medium text-slate-700">Mata Uang Default</label>
                    <input type="text" name="settings[default_currency][value]" id="default_currency"
                           value="{{ old('settings.default_currency.value', $generalSettings->get('default_currency')->value ?? '') }}"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <input type="hidden" name="settings[default_currency][key]" value="default_currency">
                    <input type="hidden" name="settings[default_currency][group]" value="general">
                    @error('settings.default_currency.value')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        Simpan Pengaturan Umum
                    </button>
                </div>
            </form>
        </div>

        {{-- Upload Logo Toko --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Logo Toko</h2>
                    <p class="text-sm text-slate-500">Upload logo untuk branding yang lebih konsisten.</p>
                </div>
                <span class="rounded-full bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700">Branding</span>
            </div>

            <form action="{{ route('settings.logo') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
                    <div class="flex-1">
                        <label for="logo" class="mb-2 block text-sm font-medium text-slate-700">Pilih File Logo</label>
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-3">
                            <input type="file" name="logo" id="logo" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-indigo-600 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-700">
                        </div>
                        @error('logo')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full max-w-xs rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="mb-2 text-xs font-medium uppercase tracking-[0.2em] text-slate-500">Preview</p>
                        @if ($storeLogo)
                            <img src="{{ asset('storage/' . $storeLogo) }}" alt="Store Logo" class="h-20 w-auto max-w-full object-contain">
                        @else
                            <div class="flex h-20 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white text-sm text-slate-400">
                                Belum ada logo
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        Upload Logo
                    </button>
                </div>
            </form>
        </div>

        {{-- Jam Operasional Toko --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Jam Operasional Toko</h2>
                    <p class="text-sm text-slate-500">Atur jadwal buka dan tutup agar status toko otomatis.</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">Operasional</span>
            </div>

            <form action="{{ route('settings.store-hours') }}" method="POST" class="space-y-5">
                @csrf
                @method('POST')

                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Aktifkan Jadwal Otomatis</p>
                        <p class="text-xs text-slate-500">Jika dimatikan, status toko selalu dianggap buka.</p>
                    </div>
                    <label for="store_hours_enabled" class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" name="store_hours_enabled" id="store_hours_enabled" value="1" {{ $storeHours['enabled'] ? 'checked' : '' }} class="peer sr-only">
                        <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-indigo-600"></span>
                        <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                    </label>
                    @error('store_hours_enabled')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="store_open_time" class="block text-sm font-medium text-slate-700">Jam Buka</label>
                        <input type="time" name="store_open_time" id="store_open_time"
                               value="{{ old('store_open_time', $storeHours['open_time']) }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                        <p class="text-xs text-slate-500">Mulai dihitung sebagai status BUKA</p>
                        @error('store_open_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="store_close_time" class="block text-sm font-medium text-slate-700">Jam Tutup</label>
                        <input type="time" name="store_close_time" id="store_close_time"
                               value="{{ old('store_close_time', $storeHours['close_time']) }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100" required>
                        <p class="text-xs text-slate-500">Mulai dihitung sebagai status TUTUP</p>
                        @error('store_close_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                    Toko akan otomatis ditandai BUKA pukul {{ \Carbon\Carbon::parse($storeHours['open_time'])->format('H:i') }} dan TUTUP pukul {{ \Carbon\Carbon::parse($storeHours['close_time'])->format('H:i') }} setiap hari.
                </div>

                <div>
                    <h4 class="mb-2 text-sm font-medium text-slate-700">Preset Cepat</h4>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="preset-button rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700" data-open="08:00" data-close="17:00">08:00 – 17:00</button>
                        <button type="button" class="preset-button rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700" data-open="09:00" data-close="21:00">09:00 – 21:00</button>
                        <button type="button" class="preset-button rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700" data-open="10:00" data-close="22:00">10:00 – 22:00</button>
                        <button type="button" class="preset-button rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700" data-open="20:00" data-close="04:00">20:00 – 04:00</button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        Simpan Jam Operasional
                    </button>
                </div>
            </form>
        </div>

        {{-- Pengaturan AI Agent --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Pengaturan AI Agent</h2>
                    <p class="text-sm text-slate-500">Konfigurasi model kecerdasan buatan yang digunakan sistem.</p>
                </div>
                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">AI</span>
            </div>

            <form action="{{ route('settings') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                @php
                    $aiSettings = $settings->where('group', 'ai')->keyBy('key');
                    $selectedModel = old('settings.gemini_model.value', $aiSettings->get('gemini_model')->value ?? 'gemini-3.5-flash');
                @endphp

                <div class="space-y-2 max-w-md">
                    <label for="gemini_model" class="block text-sm font-medium text-slate-700">Model AI Generatif</label>
                    <select name="settings[gemini_model][value]" id="gemini_model"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        <option value="gemini-3.5-flash" {{ $selectedModel === 'gemini-3.5-flash' ? 'selected' : '' }}>Gemini 3.5 Flash (Default - Tercepat)</option>
                        <option value="gemini-1.5-flash" {{ $selectedModel === 'gemini-1.5-flash' ? 'selected' : '' }}>Gemini 1.5 Flash (Stabil)</option>
                        <option value="gemini-1.5-pro" {{ $selectedModel === 'gemini-1.5-pro' ? 'selected' : '' }}>Gemini 1.5 Pro (Kemampuan Analitik Tinggi)</option>
                    </select>
                    <input type="hidden" name="settings[gemini_model][key]" value="gemini_model">
                    <input type="hidden" name="settings[gemini_model][group]" value="ai">
                    <p class="text-xs text-slate-500">Pilih model yang sesuai dengan kebutuhan kecepatan respon dan tingkat analisis bisnis.</p>
                    @error('settings.gemini_model.value')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        Simpan Pengaturan AI
                    </button>
                </div>
            </form>
        </div>

        {{-- Backup Database --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Backup Database</h2>
                    <p class="text-sm text-slate-500">Buat salinan cadangan data untuk keamanan sistem.</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">Keamanan</span>
            </div>

            <form action="{{ route('audit.backup') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100">
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
