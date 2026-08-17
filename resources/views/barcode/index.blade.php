<x-layouts.app title="Manajemen Barcode & QR Code">
    <div class="space-y-6 sm:space-y-8 animate-fade-in-down">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Manajemen Barcode & QR Code</h1>
                <p class="text-sm font-medium text-slate-500 mt-1">Cetak label barcode untuk produk atau generate QR Code kustom.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Kolom Kiri: Pilih Produk untuk Cetak Barcode -->
            <div class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white shadow-sm flex flex-col">
                <div class="p-6 sm:p-8 border-b border-slate-100 flex-shrink-0 flex justify-between items-center flex-wrap gap-4">
                    <h2 class="text-xl font-bold text-slate-800">Cetak Barcode Produk</h2>
                    
                    <form action="{{ route('barcode.index') }}" method="GET" class="flex gap-2 w-full sm:w-auto">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, SKU, barcode..." class="w-full sm:w-64 rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-700">Cari</button>
                    </form>
                </div>
                
                <div class="p-0 flex-1 overflow-x-auto">
                    <form action="{{ route('barcode.print') }}" method="POST" target="_blank" id="printBarcodeForm">
                        @csrf
                        <table class="w-full text-left text-sm text-slate-500">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-700">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-bold">
                                        <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                    <th scope="col" class="px-6 py-4 font-bold">Produk</th>
                                    <th scope="col" class="px-6 py-4 font-bold">SKU / Barcode</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-right">Harga</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($products as $product)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-800">{{ $product->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $product->category->name ?? 'Tanpa Kategori' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-800">{{ $product->sku ?? '-' }}</div>
                                            <div class="text-xs text-slate-500">{{ $product->barcode ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium text-slate-800">
                                            Rp {{ number_format($product->sale_price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                            Tidak ada produk yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <div class="p-6 border-t border-slate-100 bg-slate-50 flex items-center justify-between flex-wrap gap-4 rounded-b-3xl">
                            <div class="flex items-center gap-3">
                                <label for="copies" class="text-sm font-medium text-slate-700">Jumlah Kopi/Produk:</label>
                                <input type="number" name="copies" id="copies" value="1" min="1" max="100" class="w-20 rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.728 9.75l-4.5 4.5m0 0l4.5 4.5m-4.5-4.5h16.5m-16.5-6h16.5m-16.5 12h16.5" />
                                </svg>
                                Cetak Label Terpilih
                            </button>
                        </div>
                    </form>
                </div>
                <div class="p-4 border-t border-slate-100">
                    {{ $products->links() }}
                </div>
            </div>

            <!-- Kolom Kanan: Generate QR Code -->
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm flex flex-col h-fit">
                <div class="p-6 sm:p-8 border-b border-slate-100">
                    <h2 class="text-xl font-bold text-slate-800">Custom QR Code</h2>
                    <p class="text-sm text-slate-500 mt-1">Buat QR Code dari teks bebas (link, teks instruksi, dll).</p>
                </div>
                
                <div class="p-6 sm:p-8">
                    <form action="{{ route('barcode.qrcode') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="content" class="block text-sm font-medium leading-6 text-slate-900">Konten Teks / URL</label>
                                <div class="mt-2">
                                    <textarea id="content" name="content" rows="3" class="block w-full rounded-xl border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Masukkan teks atau URL..." required>{{ session('qr_content') }}</textarea>
                                </div>
                                @error('content')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <button type="submit" class="w-full justify-center inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-700 transition-all">
                                Generate QR Code
                            </button>
                        </div>
                    </form>

                    @if(session('qr_content'))
                        <div class="mt-8 p-6 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col items-center text-center animate-fade-in-up">
                            <h3 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wider">Hasil QR Code</h3>
                            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate(session('qr_content')) !!}
                            </div>
                            <p class="mt-4 text-xs font-medium text-slate-500 break-all">{{ session('qr_content') }}</p>
                            
                            <!-- Tombol print kecil untuk cetak QR -->
                            <button onclick="printQr()" class="mt-4 text-indigo-600 hover:text-indigo-800 text-sm font-semibold flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                    <path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0118 8.653v4.668a2.25 2.25 0 01-2.241 2.245H4.241A2.25 2.25 0 012 13.321V8.653a2.212 2.212 0 011.874-2.198.815.815 0 01.126-.015V2.75zm1.5 0v2.852c.5-.046 1.006-.086 1.513-.121V2.5h3.974v2.981c.507.035 1.013.075 1.513.121V2.75a.25.25 0 00-.25-.25h-6.5a.25.25 0 00-.25.25zm-2.074 7.6a.75.75 0 00-1.042 1.082l2.25 2.25a.75.75 0 001.06 0l2.25-2.25a.75.75 0 10-1.06-1.06l-.97.97V8.5a.75.75 0 00-1.5 0v2.842l-.97-.97z" clip-rule="evenodd" />
                                </svg>
                                Cetak QR Code
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.product-checkbox');

            if(selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });
            }

            document.getElementById('printBarcodeForm').addEventListener('submit', function(e) {
                const checked = document.querySelectorAll('.product-checkbox:checked').length;
                if (checked === 0) {
                    e.preventDefault();
                    alert('Silakan pilih minimal 1 produk untuk dicetak barcodenya.');
                }
            });
        });

        function printQr() {
            const printWindow = window.open('', '', 'height=600,width=800');
            const qrContent = document.querySelector('.bg-white.p-4.rounded-xl.shadow-sm').innerHTML;
            
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Cetak QR Code</title>
                        <style>
                            body { display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                            .qr-container { text-align: center; font-family: sans-serif; }
                            svg { width: 300px; height: 300px; }
                        </style>
                    </head>
                    <body>
                        <div class="qr-container">
                            ${qrContent}
                            <p style="margin-top: 20px; font-size: 14px; color: #555;">{{ session('qr_content') }}</p>
                        </div>
                        <script>
                            window.onload = function() { window.print(); window.close(); }
                        <\/script>
                    </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
    @endpush
</x-layouts.app>
