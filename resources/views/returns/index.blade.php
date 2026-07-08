<x-layouts.app title="Manajemen Barcode"> {{-- Assuming this file is resources/views/barcode/index.blade.php --}}
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Manajemen Barcode</h1>
        <p class="text-sm text-slate-500">Di sini Anda dapat mengelola fitur barcode dan QR code.</p>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">Pilihan Barcode</h2>
            <ul class="space-y-2">
                <li class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium transition-colors">Generate Barcode Produk (membutuhkan pemilihan produk)</a>
                    <p class="text-xs text-slate-500">Fitur ini belum diimplementasikan sepenuhnya di backend.</p>
                </li>
                <li class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium transition-colors">Generate QR Code (membutuhkan input konten)</a>
                    <p class="text-xs text-slate-500">Fitur ini belum diimplementasikan sepenuhnya di backend.</p>
                </li>
                <li class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium transition-colors">Cetak Label Barcode (membutuhkan pemilihan produk)</a>
                    <p class="text-xs text-slate-500">Fitur ini belum diimplementasikan sepenuhnya di backend.</p>
                </li>
                <li class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium transition-colors">Scan Barcode (membutuhkan input kode)</a>
                    <p class="text-xs text-slate-500">Fitur ini belum diimplementasikan sepenuhnya di backend.</p>
                </li>
            </ul>
        </div>
    </div>
</x-layouts.app>