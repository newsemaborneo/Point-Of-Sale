<x-layouts.app title="Pelanggan">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Pelanggan</h1>
                <p class="text-sm text-slate-500">Daftar pelanggan dan riwayat pembelian mereka.</p>
            </div>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-white shadow-sm hover:bg-emerald-700">Tambah Pelanggan</a>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Telepon</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Member</th>
                        <th class="px-4 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="px-4 py-4 font-medium text-slate-900">{{ $customer->name }}</td>
                            <td class="px-4 py-4">{{ $customer->phone ?? '-' }}</td>
                            <td class="px-4 py-4">{{ $customer->email ?? '-' }}</td>
                            <td class="px-4 py-4">{{ ucfirst($customer->member_type ?? 'regular') }}</td>
                            <td class="px-4 py-4 text-slate-700">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('customers.show', $customer) }}" class="rounded-md border border-slate-300 bg-slate-50 px-3 py-2 hover:bg-slate-100">Detail</a>
                                    <a href="{{ route('customers.edit', $customer) }}" class="rounded-md border border-slate-300 bg-slate-50 px-3 py-2 hover:bg-slate-100">Ubah</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada pelanggan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $customers->links() }}</div>
    </div>
</x-layouts.app>
