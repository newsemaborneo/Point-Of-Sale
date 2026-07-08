<x-layouts.app title="Hutang {{ $supplier->name }}">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Hutang {{ $supplier->name }}</h1>
                <p class="text-sm text-slate-500">Detail hutang supplier dan pembayaran terkait.</p>
            </div>
            <a href="{{ route('suppliers.show', $supplier) }}" class="rounded-md border border-slate-300 bg-slate-50 px-4 py-2 text-slate-700 hover:bg-slate-100">Kembali ke Supplier</a>
        </div>

        <div class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @forelse ($debts as $debt)
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-sm text-slate-500">Nomor Hutang</div>
                            <div class="mt-1 font-medium text-slate-900">#{{ $debt->id }}</div>
                        </div>
                        <div class="text-sm text-slate-500">Status</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ ucfirst($debt->status) }}</div>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div>
                            <div class="text-sm text-slate-500">Total Hutang</div>
                            <div class="mt-1 text-slate-900">Rp {{ number_format($debt->amount, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">Terbayar</div>
                            <div class="mt-1 text-slate-900">Rp {{ number_format($debt->paid_amount ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">Sisa</div>
                            <div class="mt-1 text-slate-900">Rp {{ number_format(max($debt->amount - ($debt->paid_amount ?? 0), 0), 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada hutang supplier yang tercatat.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
