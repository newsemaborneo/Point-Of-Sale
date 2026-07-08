<x-layouts.app title="Hutang Pelanggan - {{ $customer->name }}">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Hutang Pelanggan</h1>
                <p class="text-sm text-slate-500">Pelanggan: <strong>{{ $customer->name }}</strong></p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('customers.show', $customer) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200
                          bg-white px-4 py-2.5 text-sm font-semibold text-slate-700
                          hover:bg-slate-50 transition-colors">
                    ← Kembali ke Detail
                </a>
                <a href="{{ route('customers.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200
                          bg-white px-4 py-2.5 text-sm font-semibold text-slate-700
                          hover:bg-slate-50 transition-colors">
                    Daftar Pelanggan
                </a>
            </div>
        </div>

        {{-- Debt Summary --}}
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center">
                <p class="text-2xl font-bold text-slate-900">{{ $debts->total() }}</p>
                <p class="text-sm text-slate-600">Total Hutang</p>
            </div>
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-center">
                <p class="text-2xl font-bold text-red-600">Rp {{ number_format($debts->where('status', 'unpaid')->sum('amount'), 0, ',', '.') }}</p>
                <p class="text-sm text-red-700">Belum Dibayar</p>
            </div>
            <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-center">
                <p class="text-2xl font-bold text-yellow-600">Rp {{ number_format($debts->where('status', 'partial')->sum(fn($debt) => $debt->amount - $debt->paid_amount), 0, ',', '.') }}</p>
                <p class="text-sm text-yellow-700">Dibayar Sebagian</p>
            </div>
            <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-center">
                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($debts->where('status', 'paid')->sum('paid_amount'), 0, ',', '.') }}</p>
                <p class="text-sm text-green-700">Sudah Lunas</p>
            </div>
        </div>

        {{-- Debt List --}}
        @if($debts->count() > 0)
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Daftar Hutang</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-medium text-slate-900">Tanggal</th>
                                <th class="px-4 py-3 font-medium text-slate-900">Invoice</th>
                                <th class="px-4 py-3 font-medium text-slate-900 text-right">Jumlah Hutang</th>
                                <th class="px-4 py-3 font-medium text-slate-900 text-right">Dibayar</th>
                                <th class="px-4 py-3 font-medium text-slate-900 text-right">Sisa</th>
                                <th class="px-4 py-3 font-medium text-slate-900">Status</th>
                                <th class="px-4 py-3 font-medium text-slate-900">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($debts as $debt)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $debt->created_at->format('d/m/Y') }}<br>
                                    <span class="text-xs text-slate-500">{{ $debt->created_at->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ $debt->sale->invoice_number ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-slate-900">
                                    Rp {{ number_format($debt->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-700">
                                    Rp {{ number_format($debt->paid_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium
                                    {{ ($debt->amount - $debt->paid_amount) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    Rp {{ number_format($debt->amount - $debt->paid_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                           {{ $debt->status === 'paid' ? 'bg-green-100 text-green-800' :
                                              ($debt->status === 'partial' ? 'bg-yellow-100 text-yellow-800' :
                                               'bg-red-100 text-red-800') }}">
                                        {{ $debt->status === 'paid' ? 'Lunas' :
                                           ($debt->status === 'partial' ? 'Sebagian' : 'Belum Bayar') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($debt->status !== 'paid')
                                        <button onclick="openPaymentModal({{ $debt->id }}, '{{ $debt->sale->invoice_number ?? 'N/A' }}', {{ $debt->amount - $debt->paid_amount }})"
                                                class="rounded-md border border-emerald-300 bg-emerald-50 px-3 py-1
                                                       text-xs text-emerald-700 hover:bg-emerald-100 font-medium">
                                            Bayar
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">Lunas</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            @if($debts->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $debts->links() }}
            </div>
            @endif
        </div>
        @else
        <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900 mb-2">Tidak Ada Hutang</h3>
            <p class="text-slate-500">{{ $customer->name }} tidak memiliki hutang yang tercatat.</p>
        </div>
        @endif
    </div>

    {{-- Payment Modal --}}
    <div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closePaymentModal()"></div>

            <div class="relative w-full max-w-md transform rounded-3xl bg-white p-6 shadow-xl transition-all">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Bayar Hutang</h3>
                    <p class="text-sm text-slate-500" id="paymentInvoice">Invoice: -</p>
                </div>

                <form action="" method="POST" id="paymentForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Sisa Hutang</label>
                        <div class="mt-1 text-xl font-bold text-red-600" id="remainingDebt">Rp 0</div>
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-slate-700">Jumlah Pembayaran</label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01" required
                               class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900
                                      focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                               placeholder="Masukkan jumlah pembayaran">
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closePaymentModal()"
                                class="flex-1 rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm font-medium
                                       text-slate-700 hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold
                                       text-white hover:bg-emerald-700 transition-colors">
                            Bayar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openPaymentModal(debtId, invoice, remaining) {
            document.getElementById('paymentInvoice').textContent = 'Invoice: ' + invoice;
            document.getElementById('remainingDebt').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(remaining);
            document.getElementById('amount').value = remaining;
            document.getElementById('amount').max = remaining;
            document.getElementById('paymentForm').action = '{{ url('/customer-debts') }}/' + debtId + '/pay';
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePaymentModal();
            }
        });
    </script>
</x-layouts.app>
