<x-layouts.app title="Voucher">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Voucher</h1>
                <p class="text-sm text-slate-500">Kelola kode voucher dan kuota diskon.</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openCreateVoucherModal()" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                    + Tambah Voucher
                </button>
                <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
            </div>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3">Kode Voucher</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Nilai</th>
                        <th class="px-4 py-3">Minimal Pembelian</th>
                        <th class="px-4 py-3">Kuota</th>
                        <th class="px-4 py-3">Periode</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($vouchers as $voucher)
                        <tr>
                            <td class="px-4 py-4 font-mono text-sm font-bold text-slate-900">{{ $voucher->code }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $voucher->type === 'percent' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $voucher->type === 'percent' ? 'Persentase' : 'Nominal' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-medium">
                                @if($voucher->type === 'percent')
                                    {{ $voucher->value }}%
                                @else
                                    Rp {{ number_format($voucher->value, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="px-4 py-4">{{ $voucher->min_purchase ? 'Rp ' . number_format($voucher->min_purchase, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-4">
                                @if($voucher->quota)
                                    <span class="text-sm">{{ $voucher->used }}/{{ $voucher->quota }}</span>
                                @else
                                    <span class="text-slate-500 text-sm">Tak terbatas</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm">
                                @if($voucher->start_date || $voucher->end_date)
                                    {{ optional($voucher->start_date)->format('d/m/Y') ?? 'Mulai' }} - {{ optional($voucher->end_date)->format('d/m/Y') ?? 'Selesai' }}
                                @else
                                    <span class="text-slate-500">Selamanya</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <button onclick="copyVoucherCode('{{ $voucher->code }}')"
                                            class="rounded bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-800 hover:bg-indigo-200"
                                            title="Copy Kode">
                                        Salin
                                    </button>
                                    <form action="{{ route('vouchers.destroy', $voucher->id ?? 0) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Yakin ingin menghapus voucher ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-800 hover:bg-red-200">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada voucher.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $vouchers->links() }}</div>
    </div>

    {{-- Create Voucher Modal --}}
    <div id="createVoucherModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="mx-4 max-w-lg rounded-3xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <h2 class="text-xl font-semibold text-slate-900">Tambah Voucher Baru</h2>
                <button onclick="closeCreateVoucherModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('vouchers.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tipe Voucher</label>
                        <select name="type" id="voucherType" onchange="toggleVoucherFields()" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                            <option value="">Pilih Tipe</option>
                            <option value="percent">Diskon Persentase (%)</option>
                            <option value="nominal">Diskon Nominal (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" id="valueLabel">Nilai Diskon</label>
                        <input type="number" name="value" id="voucherValue" step="0.01" min="0" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                        <p class="mt-1 text-xs text-slate-500" id="valueHelper">Masukkan nilai diskon</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Minimal Pembelian</label>
                        <input type="number" name="min_purchase" min="0" step="1000" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" placeholder="Opsional">
                        <p class="mt-1 text-xs text-slate-500">Kosongkan jika tidak ada minimal pembelian</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Maksimal Diskon</label>
                        <input type="number" name="max_discount" min="0" step="1000" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" placeholder="Opsional">
                        <p class="mt-1 text-xs text-slate-500">Untuk voucher persentase</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Kuota Penggunaan</label>
                    <input type="number" name="quota" min="1" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" placeholder="Kosongkan untuk unlimited">
                    <p class="mt-1 text-xs text-slate-500">Berapa kali voucher bisa digunakan</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tanggal Berakhir</label>
                        <input type="date" name="end_date" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="rounded-lg bg-blue-50 p-3">
                    <p class="text-sm text-blue-800">
                        <strong>Info:</strong> Kode voucher akan dibuat otomatis dengan format VCR-XXXXXXXX
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateVoucherModal()" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                        Buat Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateVoucherModal() {
            document.getElementById('createVoucherModal').classList.remove('hidden');
            document.getElementById('createVoucherModal').classList.add('flex');
        }

        function closeCreateVoucherModal() {
            document.getElementById('createVoucherModal').classList.add('hidden');
            document.getElementById('createVoucherModal').classList.remove('flex');
        }

        function toggleVoucherFields() {
            const type = document.getElementById('voucherType').value;
            const valueLabel = document.getElementById('valueLabel');
            const valueHelper = document.getElementById('valueHelper');
            const voucherValue = document.getElementById('voucherValue');

            if (type === 'percent') {
                valueLabel.textContent = 'Persentase Diskon (%)';
                valueHelper.textContent = 'Contoh: 10 untuk diskon 10%';
                voucherValue.setAttribute('max', '100');
            } else if (type === 'nominal') {
                valueLabel.textContent = 'Nominal Diskon (Rp)';
                valueHelper.textContent = 'Contoh: 50000 untuk diskon Rp 50.000';
                voucherValue.removeAttribute('max');
            }
        }

        function copyVoucherCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                // Show temporary success message
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.classList.add('bg-green-100', 'text-green-800');
                button.classList.remove('bg-indigo-100', 'text-indigo-800');

                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('bg-green-100', 'text-green-800');
                    button.classList.add('bg-indigo-100', 'text-indigo-800');
                }, 2000);
            });
        }

        // Close modal when clicking outside
        document.getElementById('createVoucherModal').addEventListener('click', function(e) {
            if (e.target === this) closeCreateVoucherModal();
        });
    </script>
</x-layouts.app>
