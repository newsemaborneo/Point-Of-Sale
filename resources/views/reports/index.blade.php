<x-layouts.app title="Laporan & Analisis">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Laporan & Analisis</h1>
                <p class="text-sm text-slate-500">Lihat ringkasan penjualan, pembelian, stok, kas, dan performa produk.</p>
            </div>
        </div>

        {{-- Quick Access Buttons --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Laporan Penjualan --}}
            <a href="{{ route('reports.sales') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-indigo-400 hover:shadow-lg transition-all flex flex-col items-center text-center">
                <div class="p-3 rounded-full bg-indigo-50 text-indigo-600 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /></svg> {{-- Icon for Sales Report --}}
                </div>
                <h2 class="text-lg font-bold text-slate-800">Laporan Penjualan</h2>
                <p class="text-sm text-slate-500 mt-1">Ringkasan dan detail penjualan.</p>
            </a>

            {{-- Laporan Pembelian --}}
            <a href="{{ route('reports.purchases') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-emerald-400 hover:shadow-lg transition-all flex flex-col items-center text-center">
                <div class="p-3 rounded-full bg-emerald-50 text-emerald-600 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5v-1.875a3.375 3.375 0 003.375-3.375h1.5a1.125 1.125 0 011.125 1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375m15.75 0v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 00-1.125 1.125v1.5c0-.621.504-1.125 1.125-1.125h13.5" /></svg> {{-- Icon for Purchase Report --}}
                </div>
                <h2 class="text-lg font-bold text-slate-800">Laporan Pembelian</h2>
                <p class="text-sm text-slate-500 mt-1">Analisis pembelian dari supplier.</p>
            </a>

            {{-- Laporan Stok --}}
            <a href="{{ route('reports.stock') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-amber-400 hover:shadow-lg transition-all flex flex-col items-center text-center">
                <div class="p-3 rounded-full bg-amber-50 text-amber-600 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75c0 .414-.336.75-.75.75H9m4.5 0c0 .414.336.75.75.75h.75m-4.5-12c1.657-2.431 2.398-3.646 2.398-3.646m0 0C11.5 2.904 10.961 2.526 10 2.526c-.96 0-1.5.378-2.398 3.646m0 0h.006v.006h-.006v-.006z" /></svg> {{-- Icon for Stock Report --}}
                </div>
                <h2 class="text-lg font-bold text-slate-800">Laporan Stok</h2>
                <p class="text-sm text-slate-500 mt-1">Pergerakan dan status stok.</p>
            </a>

            {{-- Laporan Laba Rugi --}}
            <a href="{{ route('reports.profit-loss') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-rose-400 hover:shadow-lg transition-all flex flex-col items-center text-center">
                <div class="p-3 rounded-full bg-rose-50 text-rose-600 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{-- Icon for Profit/Loss Report --}}
                </div>
                <h2 class="text-lg font-bold text-slate-800">Laporan Laba Rugi</h2>
                <p class="text-sm text-slate-500 mt-1">Analisis keuntungan dan kerugian.</p>
            </a>

            {{-- Laporan Kas --}}
            <a href="{{ route('reports.cash') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-cyan-400 hover:shadow-lg transition-all flex flex-col items-center text-center">
                <div class="p-3 rounded-full bg-cyan-50 text-cyan-600 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /></svg> {{-- Icon for Cash Report --}}
                </div>
                <h2 class="text-lg font-bold text-slate-800">Laporan Kas</h2>
                <p class="text-sm text-slate-500 mt-1">Pergerakan uang tunai.</p>
            </a>

            {{-- Produk Terlaris --}}
            <a href="{{ route('reports.best-selling-products') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-yellow-400 hover:shadow-lg transition-all flex flex-col items-center text-center">
                <div class="p-3 rounded-full bg-yellow-50 text-yellow-600 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{-- Icon for Best Selling Products --}}
                </div>
                <h2 class="text-lg font-bold text-slate-800">Produk Terlaris</h2>
                <p class="text-sm text-slate-500 mt-1">Produk dengan penjualan tertinggi.</p>
            </a>

            {{-- Laporan Pelanggan --}}
            <a href="{{ route('reports.customers') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-purple-400 hover:shadow-lg transition-all flex flex-col items-center text-center">
                <div class="p-3 rounded-full bg-purple-50 text-purple-600 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.67c.12-.318.232-.636.34-1.952M15 19.128c-1.113 0-2.16-.285-3.07-.786m-7.908-4.062A11.998 11.998 0 0112 2.25c2.68 0 5.18.865 7.148 2.305A9.348 9.348 0 0112 15c-2.68 0-5.18-.865-7.148-2.305" /></svg> {{-- Icon for Customer Report --}}
                </div>
                <h2 class="text-lg font-bold text-slate-800">Laporan Pelanggan</h2>
                <p class="text-sm text-slate-500 mt-1">Analisis data pelanggan.</p>
            </a>

            {{-- Laporan Supplier --}}
            <a href="{{ route('reports.suppliers') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-blue-400 hover:shadow-lg transition-all flex flex-col items-center text-center">
                <div class="p-3 rounded-full bg-blue-50 text-blue-600 mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-16.5 0a3.375 3.375 0 013.375-3.375h1.5a1.125 1.125 0 011.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125H3.375m15.75-4.5v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 00-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125h13.5" /></svg> {{-- Icon for Supplier Report --}}
                </div>
                <h2 class="text-lg font-bold text-slate-800">Laporan Supplier</h2>
                <p class="text-sm text-slate-500 mt-1">Analisis data supplier.</p>
            </a>
        </div>

        {{-- Export Options --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Ekspor Laporan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('reports.sales.export.pdf') }}" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-red-400 hover:bg-red-50 transition-all">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.875 14.25l1.213-2.891a150.666 150.666 0 013.751 7.776M9 6h.008v.008H9V6zm4 0h.008v.008H13V6zm4 0h.008v.008H17V6zM9 12h.008v.008H9V12zm4 0h.008v.008H13V12zm4 0h.008v.008H17V12z" /></svg> {{-- Icon for PDF Export --}}
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Export PDF</h3>
                        <p class="text-xs text-slate-500">Unduh laporan dalam format PDF</p>
                    </div>
                </a>
                <a href="{{ route('reports.sales.export.excel') }}" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-green-400 hover:bg-green-50 transition-all">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0013.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg> {{-- Icon for Excel Export --}}
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Export Excel</h3>
                        <p class="text-xs text-slate-500">Unduh laporan dalam format Excel</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
