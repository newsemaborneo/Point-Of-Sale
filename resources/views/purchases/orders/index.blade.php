<x-layouts.app title="Purchase Orders">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Purchase Orders</h1>
                <p class="text-sm text-slate-500">Lihat daftar purchase order dan status penerimaan barang.</p>
            </div>
            <a href="{{ route('purchases.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Ke Faktur Pembelian</a>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3">PO Number</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Gudang</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-4">{{ $order->po_number }}</td>
                            <td class="px-4 py-4">{{ $order->supplier->name ?? '-' }}</td>
                            <td class="px-4 py-4">{{ $order->warehouse->name ?? '-' }}</td>
                            <td class="px-4 py-4">{{ optional($order->order_date)->format('d M Y') }}</td>
                            <td class="px-4 py-4">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-4">{{ ucfirst($order->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada purchase order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </div>
</x-layouts.app>
