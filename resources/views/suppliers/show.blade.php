<x-layouts.app title="Supplier Detail">
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ $supplier->name }}</h1>
                    <p class="text-sm text-slate-500">Detail supplier dan riwayat pembelian/hutang.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('suppliers.index') }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-slate-700 hover:bg-slate-100">Kembali</a>
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="rounded-md bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Ubah Supplier</a>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">Kode</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $supplier->code ?? '-' }}</div>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">Telepon</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $supplier->phone ?? '-' }}</div>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">Email</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $supplier->email ?? '-' }}</div>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">Contact Person</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $supplier->contact_person ?? '-' }}</div>
                </div>
            </div>

            <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-6">
                <h2 class="text-base font-semibold text-slate-900">Alamat</h2>
                <p class="mt-2 text-sm text-slate-700">{{ $supplier->address ?? 'Tidak tersedia' }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Riwayat Pembelian</h2>
                            <p class="text-sm text-slate-500">Semua pembelian supplier ini.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700">{{ $supplier->purchases->count() }} transaksi</span>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($supplier->purchases as $purchase)
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="text-sm text-slate-500">Invoice</div>
                                        <div class="mt-1 font-medium text-slate-900">{{ $purchase->invoice_number ?? '–' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-slate-500">Total</div>
                                        <div class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($purchase->total, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div class="text-sm text-slate-500">Tanggal</div>
                                    <div class="text-sm text-slate-700">{{ optional($purchase->purchase_date)->format('d M Y') ?? '-' }}</div>
                                    <div class="text-sm text-slate-500">Status Pembayaran</div>
                                    <div class="text-sm text-slate-700">{{ ucfirst($purchase->payment_status ?? 'belum') }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada pembelian untuk supplier ini.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Hutang Supplier</h2>
                        <p class="text-sm text-slate-500">Saldo dan status hutang supplier.</p>
                    </div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-sm text-amber-800">{{ $supplier->debts->count() }} item</span>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($supplier->debts as $debt)
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div class="text-sm text-slate-500">Nomor Hutang</div>
                                    <div class="mt-1 font-medium text-slate-900">#{{ $debt->id }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-slate-500">Status</div>
                                    <div class="mt-1 font-semibold text-slate-900">{{ ucfirst($debt->status) }}</div>
                                </div>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div>
                                    <div class="text-sm text-slate-500">Jumlah</div>
                                    <div class="text-sm text-slate-700">Rp {{ number_format($debt->amount, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">Terbayar</div>
                                    <div class="text-sm text-slate-700">Rp {{ number_format($debt->paid_amount ?? 0, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Tidak ada hutang tercatat untuk supplier ini.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
