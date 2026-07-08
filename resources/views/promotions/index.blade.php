<x-layouts.app title="Promo">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Promo</h1>
                <p class="text-sm text-slate-500">Kelola promo diskon dan bundling produk.</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openCreateModal()" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                    + Tambah Promo
                </button>
                <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">Kembali</a>
            </div>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-4 py-3">Nama Promo</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Nilai</th>
                        <th class="px-4 py-3">Produk terkait</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($promotions as $promotion)
                        <tr>
                            <td class="px-4 py-4 font-medium">{{ $promotion->name }}</td>
                            <td class="px-4 py-4">{{ ucfirst(str_replace('_', ' ', $promotion->type)) }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $promotion->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $promotion->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                @if($promotion->type === 'percent_discount')
                                    {{ $promotion->value }}%
                                @elseif($promotion->type === 'nominal_discount')
                                    Rp {{ number_format($promotion->value, 0, ',', '.') }}
                                @elseif($promotion->type === 'buy_x_get_y')
                                    Beli {{ $promotion->buy_qty }} Gratis {{ $promotion->get_qty }}
                                @else
                                    {{ $promotion->value ?? '-' }}
                                @endif
                            </td>
                            <td class="px-4 py-4">{{ $promotion->products->pluck('name')->join(', ') ?: 'Semua Produk' }}</td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <button onclick="openEditModal({{ $promotion->id }}, '{{ $promotion->name }}', {{ $promotion->is_active ? 'true' : 'false' }})"
                                            class="rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 hover:bg-blue-200">
                                        Edit
                                    </button>
                                    <form action="{{ route('promotions.destroy', $promotion) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Yakin ingin menghapus promo ini?')">
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
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada promo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $promotions->links() }}</div>
    </div>

    {{-- Create Promo Modal --}}
    <div id="createModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="mx-4 max-w-2xl rounded-3xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <h2 class="text-xl font-semibold text-slate-900">Tambah Promo Baru</h2>
                <button onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('promotions.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Nama Promo</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="promoType" class="block text-sm font-medium text-slate-700">Tipe Promo</label>
                        <select name="type" id="promoType" onchange="togglePromoFields()" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none @error('type') border-red-500 @enderror">
                            <option value="">Pilih Tipe</option>
                            <option value="percent_discount" {{ old('type') == 'percent_discount' ? 'selected' : '' }}>Diskon Persentase</option>
                            <option value="nominal_discount" {{ old('type') == 'nominal_discount' ? 'selected' : '' }}>Diskon Nominal</option>
                            <option value="buy_x_get_y" {{ old('type') == 'buy_x_get_y' ? 'selected' : '' }}>Buy X Get Y</option>
                            <option value="bundling" {{ old('type') == 'bundling' ? 'selected' : '' }}>Bundling</option>
                            <option value="happy_hour" {{ old('type') == 'happy_hour' ? 'selected' : '' }}>Happy Hour</option>
                        </select>
                        @error('type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div id="valueField" style="display: none;">
                        <label for="value" class="block text-sm font-medium text-slate-700">Nilai Diskon</label>
                        <input type="number" name="value" id="value" step="0.01" value="{{ old('value') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none @error('value') border-red-500 @enderror">
                        @error('value')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="buyQtyField" style="display: none;">
                        <label for="buy_qty" class="block text-sm font-medium text-slate-700">Beli Qty</label>
                        <input type="number" name="buy_qty" id="buy_qty" min="1" value="{{ old('buy_qty') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none @error('buy_qty') border-red-500 @enderror">
                        @error('buy_qty')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="getQtyField" style="display: none;">
                        <label for="get_qty" class="block text-sm font-medium text-slate-700">Gratis Qty</label>
                        <input type="number" name="get_qty" id="get_qty" min="1" value="{{ old('get_qty') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none @error('get_qty') border-red-500 @enderror">
                        @error('get_qty')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2" id="timeFields" style="display: none;">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-slate-700">Jam Mulai</label>
                        <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none @error('start_time') border-red-500 @enderror">
                        @error('start_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-slate-700">Jam Selesai</label>
                        <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none @error('end_time') border-red-500 @enderror">
                        @error('end_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none @error('end_date') border-red-500 @enderror">
                        @error('end_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Produk (Opsional)</label>
                    <select name="product_ids[]" multiple class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" style="height: 120px;">
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select> 
                    <p class="mt-1 text-xs text-slate-500">Kosongkan untuk menerapkan ke semua produk. Tahan Ctrl untuk pilih multiple.</p>
                    @error('product_ids')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateModal()" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
                        Simpan Promo
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Promo Modal --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="mx-4 max-w-md rounded-3xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <h2 class="text-xl font-semibold text-slate-900">Edit Promo</h2>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="editForm" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="editName" class="block text-sm font-medium text-slate-700">Nama Promo</label>
                    <input type="text" name="name" id="editName" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="editStatus" class="block text-sm font-medium text-slate-700">Status</label>
                    <select name="is_active" id="editStatus" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none @error('is_active') border-red-500 @enderror">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                    @error('is_active')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditModal()" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        Update Promo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
            document.getElementById('createModal').classList.add('flex');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
            document.getElementById('createModal').classList.remove('flex');
        }

        function openEditModal(id, name, isActive) {
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
            document.getElementById('editForm').action = `/promotions/${id}`;
            document.getElementById('editName').value = name;
            document.getElementById('editStatus').value = isActive ? '1' : '0';
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }

        function togglePromoFields() {
            const type = document.getElementById('promoType').value;
            const valueField = document.getElementById('valueField');
            const buyQtyField = document.getElementById('buyQtyField');
            const getQtyField = document.getElementById('getQtyField');
            const timeFields = document.getElementById('timeFields');

            // Hide all first
            valueField.style.display = 'none';
            buyQtyField.style.display = 'none';
            getQtyField.style.display = 'none';
            timeFields.style.display = 'none';

            // Show relevant fields
            if (type === 'percent_discount' || type === 'nominal_discount') {
                valueField.style.display = 'block';
            } else if (type === 'buy_x_get_y') {
                buyQtyField.style.display = 'block';
                getQtyField.style.display = 'block';
            } else if (type === 'happy_hour') {
                valueField.style.display = 'block';
                timeFields.style.display = 'grid';
            }
        }

        // Close modal when clicking outside
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>
</x-layouts.app>
