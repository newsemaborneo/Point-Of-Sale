<x-layouts.app title="Manajemen Shift Kasir">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Manajemen Shift Kasir</h1>
                <p class="text-sm text-slate-500">Kelola shift kasir Anda dan catat pergerakan kas.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali ke Dashboard</a>
        </div>

        {{-- Bagian Shift Aktif --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-xl font-semibold text-slate-900 mb-5">Shift Saat Ini</h2>

            @if ($currentRegister)
                {{-- Ringkasan shift aktif --}}
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6 mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">
                            <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                            SHIFT AKTIF
                        </span>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/70">Dibuka pada</p>
                            <p class="mt-1.5 text-lg font-semibold text-emerald-900">{{ $currentRegister->opened_at->format('d M Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/70">Saldo Awal</p>
                            <p class="mt-1.5 text-lg font-semibold text-emerald-900">Rp {{ number_format($currentRegister->opening_balance, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/70">Cabang</p>
                            <p class="mt-1.5 text-lg font-semibold text-emerald-900">{{ $currentRegister->branch->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Form Kas Masuk/Keluar --}}
                <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
                    {{-- Kas Masuk --}}
                    <div class="rounded-2xl border border-slate-200 p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                                <svg class="h-5 w-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l-6-6m6 6l6-6" /></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800">Catat Kas Masuk</h3>
                        </div>
                        <form action="{{ route('cash.in') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="cash_register_id" value="{{ $currentRegister->id }}">
                            <div>
                                <label for="amount_in" class="block text-sm font-medium text-slate-700 mb-1.5">Jumlah</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-base text-slate-400">Rp</span>
                                    <input type="number" name="amount" id="amount_in" value="{{ old('amount') }}" required min="1" placeholder="0" class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-base shadow-sm focus:border-indigo-300 focus:bg-white focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('amount') border-red-500 @enderror">
                                </div>
                                @error('amount')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="category_in" class="block text-sm font-medium text-slate-700 mb-1.5">Kategori <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                <input type="text" name="category" id="category_in" value="{{ old('category') }}" placeholder="Misal: Setoran Modal" class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 px-4 text-base shadow-sm focus:border-indigo-300 focus:bg-white focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('category') border-red-500 @enderror">
                                @error('category')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="description_in" class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                <textarea name="description" id="description_in" rows="3" placeholder="Catatan tambahan..." class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 px-4 text-base shadow-sm focus:border-indigo-300 focus:bg-white focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-3.5 border border-transparent text-base font-semibold rounded-xl shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                                Catat Kas Masuk
                            </button>
                            @error('cash_register_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </form>
                    </div>

                    {{-- Kas Keluar --}}
                    <div class="rounded-2xl border border-slate-200 p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-100">
                                <svg class="h-5 w-5 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6 6m6-6l6 6" /></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800">Catat Kas Keluar</h3>
                        </div>
                        <form action="{{ route('cash.out') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="cash_register_id" value="{{ $currentRegister->id }}">
                            <div>
                                <label for="amount_out" class="block text-sm font-medium text-slate-700 mb-1.5">Jumlah</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-base text-slate-400">Rp</span>
                                    <input type="number" name="amount" id="amount_out" value="{{ old('amount') }}" required min="1" placeholder="0" class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-base shadow-sm focus:border-indigo-300 focus:bg-white focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('amount') border-red-500 @enderror">
                                </div>
                                @error('amount')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="category_out" class="block text-sm font-medium text-slate-700 mb-1.5">Kategori <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                <input type="text" name="category" id="category_out" value="{{ old('category') }}" placeholder="Misal: Beli Perlengkapan" class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 px-4 text-base shadow-sm focus:border-indigo-300 focus:bg-white focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('category') border-red-500 @enderror">
                                @error('category')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="description_out" class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                <textarea name="description" id="description_out" rows="3" placeholder="Catatan tambahan..." class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 px-4 text-base shadow-sm focus:border-indigo-300 focus:bg-white focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-3.5 border border-transparent text-base font-semibold rounded-xl shadow-sm text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors">
                                Catat Kas Keluar
                            </button>
                            @error('cash_register_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </form>
                    </div>
                </div>

                {{-- Form Tutup Shift --}}
                <div class="rounded-2xl border border-indigo-200 bg-indigo-50/40 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100">
                            <svg class="h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800">Tutup Shift</h3>
                    </div>
                    <form action="{{ route('cash.close', $currentRegister) }}" method="POST" class="space-y-4 max-w-lg">
                        @csrf
                        <div>
                            <label for="closing_balance" class="block text-sm font-medium text-slate-700 mb-1.5">Saldo Akhir (Fisik)</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-base text-slate-400">Rp</span>
                                <input type="number" name="closing_balance" id="closing_balance" value="{{ old('closing_balance') }}" required min="0" placeholder="0" class="block w-full rounded-xl border-slate-200 bg-white py-3 pl-11 pr-4 text-base shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('closing_balance') border-red-500 @enderror">
                            </div>
                            @error('closing_balance')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="note_close" class="block text-sm font-medium text-slate-700 mb-1.5">Catatan <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <textarea name="note" id="note_close" rows="3" placeholder="Catatan penutupan shift..." class="block w-full rounded-xl border-slate-200 bg-white py-3 px-4 text-base shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('note') border-red-500 @enderror">{{ old('note') }}</textarea>
                            @error('note')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center px-6 py-3.5 border border-transparent text-base font-semibold rounded-xl shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Tutup Shift
                        </button>
                    </form>
                </div>
            @else
                <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-5 shadow-sm mb-8 flex items-start gap-3">
                    <div class="mt-0.5 rounded-full bg-rose-100 p-1.5">
                        <svg class="h-5 w-5 text-rose-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                    </div>
                    <p class="text-base font-medium text-rose-800">Tidak ada shift kasir yang sedang aktif.</p>
                </div>

                {{-- Form Buka Shift --}}
                <div class="rounded-2xl border border-slate-200 p-6 max-w-lg">
                    <h3 class="text-lg font-semibold text-slate-800 mb-5">Buka Shift Baru</h3>
                    <form action="{{ route('cash.open') }}" method="POST" class="space-y-4" id="openShiftForm">
                        @csrf
                        <div>
                            <label for="opening_balance" class="block text-sm font-medium text-slate-700 mb-1.5">Saldo Awal</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-base text-slate-400">Rp</span>
                                <input type="number" name="opening_balance" id="opening_balance" value="{{ old('opening_balance') }}" required min="0" placeholder="0" class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-base shadow-sm focus:border-indigo-300 focus:bg-white focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('opening_balance') border-red-500 @enderror">
                            </div>
                            @error('opening_balance')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('supervisor'))
                            <div>
                                <label for="branch_id" class="block text-sm font-medium text-slate-700 mb-1.5">Cabang <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                <select name="branch_id" id="branch_id" class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 px-4 text-base shadow-sm focus:border-indigo-300 focus:bg-white focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('branch_id') border-red-500 @enderror">
                                    <option value="">Pilih Cabang</option>
                                    @foreach(\App\Models\Branch::all() as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', Auth::user()->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <input type="hidden" name="branch_id" value="{{ Auth::user()->branch_id }}">
                        @endif
                        <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-3.5 border border-transparent text-base font-semibold rounded-xl shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Buka Shift
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Riwayat Shift --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-xl font-semibold text-slate-900 mb-5">Riwayat Shift</h2>
            @if ($registers->isEmpty())
                <div class="text-center py-12 text-slate-500">
                    <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-base">Tidak ada riwayat shift kasir.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3.5">ID</th>
                                <th class="px-4 py-3.5">Kasir</th>
                                <th class="px-4 py-3.5">Cabang</th>
                                <th class="px-4 py-3.5">Dibuka</th>
                                <th class="px-4 py-3.5">Ditutup</th>
                                <th class="px-4 py-3.5 text-right">Saldo Awal</th>
                                <th class="px-4 py-3.5 text-right">Saldo Akhir</th>
                                <th class="px-4 py-3.5 text-right">Selisih</th>
                                <th class="px-4 py-3.5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($registers as $register)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3.5 font-medium text-slate-500">#{{ $register->id }}</td>
                                    <td class="px-4 py-3.5 font-medium text-slate-900">{{ $register->user->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3.5">{{ $register->branch->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3.5">{{ $register->opened_at->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-3.5">{{ $register->closed_at ? $register->closed_at->format('d M Y H:i') : '-' }}</td>
                                    <td class="px-4 py-3.5 text-right tabular-nums">Rp {{ number_format($register->opening_balance, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3.5 text-right tabular-nums">Rp {{ number_format($register->closing_balance, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3.5 text-right tabular-nums font-medium {{ $register->difference < 0 ? 'text-rose-600' : ($register->difference > 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                                        Rp {{ number_format($register->difference, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $register->status === 'open' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                            {{ ucfirst($register->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-5">
                    {{ $registers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Error Modal Structure -->
    <div id="errorModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-6">
                <div>
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.174 3.355 1.945 3.355h17.124c1.771 0 2.816-1.855 1.945-3.355L13.94 2.354a1.125 1.125 0 00-1.988 0L3.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Terjadi Kesalahan!</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="errorModalMessage"></p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <button type="button" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600" onclick="document.getElementById('errorModal').classList.add('hidden')">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showErrorMessage(message) {
            document.getElementById('errorModalMessage').innerText = message;
            document.getElementById('errorModal').classList.remove('hidden');
        }

        // Handle "Buka Shift Baru" form submission via AJAX
        const openShiftForm = document.getElementById('openShiftForm');
        if (openShiftForm) {
            openShiftForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Mencegah pengiriman formulir default

                const formData = new FormData(this);

                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest', // Menunjukkan ini adalah permintaan AJAX
                        'Accept': 'application/json', // Mengharapkan respons JSON
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        // Tangani kesalahan HTTP (misalnya 403, 422, 500)
                        return response.json().then(errorData => {
                            const errorMessage = errorData.message || 'Terjadi kesalahan yang tidak diketahui.';
                            showErrorMessage(errorMessage);
                            throw new Error(errorMessage); // Menyebarkan kesalahan untuk blok catch
                        });
                    }
                    return response.json(); // Mengasumsikan keberhasilan juga mengembalikan JSON
                })
                .then(data => {
                    if (data.success) {
                        window.location.reload(); // Muat ulang halaman untuk menampilkan shift aktif
                    } else {
                        showErrorMessage(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Jika kesalahan tidak ditangkap oleh response.json().then(errorData => ...)
                    // mungkin itu adalah kesalahan jaringan atau respons non-JSON.
                    if (document.getElementById('errorModal').classList.contains('hidden')) {
                        showErrorMessage('Terjadi kesalahan saat memproses permintaan Anda.');
                    }
                });
            });
        }
    </script>
</x-layouts.app>