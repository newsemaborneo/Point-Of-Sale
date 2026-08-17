{{-- Backdrop (Mobile) --}}
<div x-show="sidebarOpen" style="display: none;"
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden transition-opacity"
     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"></div>

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed top-0 left-0 w-72 flex-shrink-0 h-screen bg-slate-900 border-r border-slate-800 flex flex-col transition-transform duration-300 z-50 shadow-2xl"
       aria-label="Sidebar">
    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800/50 bg-slate-900/50 backdrop-blur-sm">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-500/30">
                {{ substr(config('app.name', 'POS'), 0, 1) }}
            </div>
            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-white tracking-tight">{{ config('app.name', 'POS App') }}</a>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden p-2 -mr-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto py-6 custom-scrollbar">
        <nav class="px-4 space-y-8">
            {{-- Main Menu (semua role) --}}
            <div>
                <h3 class="px-3 text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Menu Utama</h3>
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <span>Dashboard</span>
                    </a>

                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor'))
                    <a href="{{ route('ai.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('ai.*') ? 'bg-gradient-to-r from-indigo-600/20 to-violet-600/10 text-indigo-300 ring-1 ring-indigo-500/30' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('ai.*') ? 'text-indigo-300' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.873l-1.16 3.2a.75.75 0 001.45.46l1.01-2.79m-1.3-1.87h.01m8.922-8.922A5.25 5.25 0 1017.7 17.7l-1.99 1.99a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 010-1.06l1.99-1.99A5.25 5.25 0 0017.7 6.95zm-8.408 7.35a.75.75 0 001.06 0l.53-.53m-1.59-4.46l.53-.53a.75.75 0 011.06 0l.53.53M13.5 13.5l1.06 1.06" /></svg>
                        <span>AI Chat</span>
                    </a>
                    @endif

                    {{-- Kasir (POS): hanya role cashier — route transactions.pos di-middleware role:cashier --}}
                    {{-- Pengecekan ganda 'cashier'/'kasir' untuk berjaga-jaga terhadap ketidakkonsistenan nama role di database --}}
                    @if(auth()->user()->hasRole('cashier') || auth()->user()->hasRole('kasir'))
                    <a href="{{ route('transactions.pos') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 mt-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-0.5">
                        <svg class="h-5 w-5 text-white/90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span>Kasir (POS)</span>
                    </a>
                    @endif

                    {{-- Manajemen Shift Kasir: HANYA untuk kasir --}}
                    @if(auth()->user()->hasRole('cashier') || auth()->user()->hasRole('kasir'))
                    <a href="{{ route('cash.shift') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('cash.shift*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('cash.shift*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span>Manajemen Shift Kasir</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Manajemen: hanya admin & warehouse (products/categories/stock/purchases/suppliers di-middleware role:admin,warehouse) --}}
            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('warehouse'))
            <div>
                <h3 class="px-3 text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Manajemen</h3>
                <div class="space-y-1">
                    <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('products.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('products.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                        <span>Produk</span>
                    </a>
                    <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('categories.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('categories.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        <span>Kategori</span>
                    </a>
                    <a href="{{ route('stock.in.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 text-slate-400 hover:bg-slate-800/50 hover:text-white">
                        <svg class="h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>Stok Masuk</span>
                    </a>
                    <a href="{{ route('stock.out.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 text-slate-400 hover:bg-slate-800/50 hover:text-white">
                        <svg class="h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" /></svg>
                        <span>Stok Keluar</span>
                    </a>
                    <a href="{{ route('stock.transfer.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 text-slate-400 hover:bg-slate-800/50 hover:text-white">
                        <svg class="h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                        <span>Transfer Stok</span>
                    </a>
                    <a href="{{ route('stock.adjustment.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 text-slate-400 hover:bg-slate-800/50 hover:text-white">
                        <svg class="h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 100 15 7.5 7.5 0 000-15zM21 21l-5.197-5.197" /></svg>
                        <span>Penyesuaian Stok</span>
                    </a>
                    <a href="{{ route('stock.opname.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 text-slate-400 hover:bg-slate-800/50 hover:text-white">
                        <svg class="h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 21l-3-3m3 3l3-3m-3 3v-3.75m-4.5-9H4.875c-.621 0-1.125.504-1.125 1.125V18c0 .621.504 1.125 1.125 1.125h1.5M4.5 19.125l-1.5-1.5M4.5 19.125l1.5-1.5" /></svg>
                        <span>Stock Opname</span>
                    </a>
                    <a href="{{ route('stock.history') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('stock.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('stock.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75c0 .414-.336.75-.75.75h-3a.75.75 0 01-.75-.75v-6.75m4.5 0c0-.414-.336-.75-.75-.75h-3a.75.75 0 00-.75.75v6.75" /></svg>
                        <span>Riwayat Stok</span>
                    </a>
                    <a href="{{ route('purchases.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('purchases.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('purchases.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5v-1.875a3.375 3.375 0 003.375-3.375h1.5a1.125 1.125 0 011.125 1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375m15.75 0v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125 1.125v-1.5c0-.621.504-1.125 1.125-1.125h13.5" /></svg>
                        <span>Pembelian</span>
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('suppliers.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('suppliers.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-16.5 0a3.375 3.375 0 013.375-3.375h1.5a1.125 1.125 0 011.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125H3.375m15.75-4.5v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 00-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125h13.5" /></svg>
                        <span>Supplier</span>
                    </a>
                </div>
            </div>
            @endif

            {{-- ================================================================ --}}
            {{-- RETUR --}}
            {{-- Retur Penjualan: middleware role:cashier,admin,supervisor pada route sales.return / sale-returns.index --}}
            {{-- Retur Pembelian: middleware role:admin,warehouse pada route purchases.return / purchase-returns.index --}}
            {{-- Section ini muncul jika user berhak melihat setidaknya salah satu jenis retur --}}
            {{-- ================================================================ --}}
            @if(auth()->user()->hasRole('cashier') || auth()->user()->hasRole('kasir') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor') || auth()->user()->hasRole('warehouse'))
            <div>
                <h3 class="px-3 text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Retur</h3>
                <div class="space-y-1">
                    {{-- Retur Penjualan: cashier, admin, supervisor --}}
                    @if(auth()->user()->hasRole('cashier') || auth()->user()->hasRole('kasir') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor'))
                    <a href="{{ route('sale-returns.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('sale-returns.*') || request()->routeIs('sales.return*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('sale-returns.*') || request()->routeIs('sales.return*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                        <span>Retur Penjualan</span>
                    </a>
                    @endif

                    {{-- Retur Pembelian: admin, warehouse --}}
                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('warehouse'))
                    <a href="{{ route('purchase-returns.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('purchase-returns.*') || request()->routeIs('purchases.return*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('purchase-returns.*') || request()->routeIs('purchases.return*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 000 12h3" /></svg>
                        <span>Retur Pembelian</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Cabang & Gudang: hanya admin — resource branches & warehouses di-middleware role:admin --}}
            @if(auth()->user()->hasRole('admin'))
            <div>
                <h3 class="px-3 text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Cabang & Gudang</h3>
                <div class="space-y-1">
                    <a href="{{ route('branches.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('branches.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('branches.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M3.75 7.5h16.5m-16.5 0L12 3m-8.25 4.5L12 3m0 0l8.25 4.5M12 3v18" /></svg>
                        <span>Cabang</span>
                    </a>
                    <a href="{{ route('warehouses.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('warehouses.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('warehouses.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M4.5 21V9.75l7.5-4.5 7.5 4.5V21M9.75 21v-6.75h4.5V21" /></svg>
                        <span>Gudang</span>
                    </a>
                </div>
            </div>
            @endif

            {{-- Promosi & Voucher: admin & supervisor — sesuai middleware role:admin,supervisor --}}
            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor'))
            <div>
                <h3 class="px-3 text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Promosi & Voucher</h3>
                <div class="space-y-1">
                    <a href="{{ route('promotions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('promotions.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('promotions.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.788 1.122H2.25c-.414 0-.75-.336-.75-.75v-9a.75.75 0 01.75-.75h11.372c1.213-1.06 2.85-1.5 4.5-1.5h.375c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125H19.5a3 3 0 00-5.788 1.122M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span>Promosi</span>
                    </a>
                    <a href="{{ route('vouchers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('vouchers.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('vouchers.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.078-.088-.18-.16-.294-.223a4.993 4.993 0 00-1.04-.264 4.993 4.993 0 00-1.04-.264c-.114-.063-.216-.135-.294-.223L4.5 9.75l-.935 1.35a.75.75 0 00.01 1.05l.935 1.35 4.5 6.525c.078.088.18.16.294.223a4.993 4.993 0 001.04.264 4.993 4.993 0 001.04.264c.114.063.216.135.294.223l4.5-6.525.935-1.35a.75.75 0 00-.01-1.05L19.5 9.75l-4.5-6.525zM12 12.75a.75.75 0 100-1.5.75.75 0 000 1.5z" /></svg>
                        <span>Voucher</span>
                    </a>
                </div>
            </div>
            @endif

            {{-- Pelanggan: semua role (route customers.index tidak dibatasi role) --}}
            <div>
                <h3 class="px-3 text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Pelanggan</h3>
                <div class="space-y-1">
                    <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('customers.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('customers.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.67c.12-.318.232-.636.34-1.952M15 19.128c-1.113 0-2.16-.285-3.07-.786m-7.908-4.062A11.998 11.998 0 0112 2.25c2.68 0 5.18.865 7.148 2.305A9.348 9.348 0 0112 15c-2.68 0-5.18-.865-7.148-2.305" /></svg>
                        <span>Daftar Pelanggan</span>
                    </a>
                    <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('transactions.index') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('transactions.index') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.67c.12-.318.232-.636.34-1.952M15 19.128c-1.113 0-2.16-.285-3.07-.786m-7.908-4.062A11.998 11.998 0 0112 2.25c2.68 0 5.18.865 7.148 2.305A9.348 9.348 0 0112 15c-2.68 0-5.18-.865-7.148-2.305" /></svg>
                        <span>Riwayat Transaksi</span>
                    </a>
                </div>
            </div>

            {{-- Lainnya: setiap item dicek terpisah karena middleware role-nya berbeda-beda --}}
            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor'))
            <div>
                <h3 class="px-3 text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Lainnya</h3>
                <div class="space-y-1">
                    {{-- Laporan: admin & supervisor --}}
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('reports.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('reports.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 100 15 7.5 7.5 0 000-15zM21 21l-5.197-5.197" /></svg>
                        <span>Laporan & Analisis</span>
                    </a>

                    {{-- Audit, Pengguna, Pengaturan: admin saja --}}
                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('audit.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('audit.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="h-5 w-5 {{ request()->routeIs('audit.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Audit Log</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="h-5 w-5 {{ request()->routeIs('users.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.67c.12-.318.232-.636.34-1.952M15 19.128c-1.113 0-2.16-.285-3.07-.786m-7.908-4.062A11.998 11.998 0 0112 2.25c2.68 0 5.18.865 7.148 2.305A9.348 9.348 0 0112 15c-2.68 0-5.18-.865-7.148-2.305" /></svg>
                            <span>Pengguna</span>
                        </a>
                        <a href="{{ route('settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('settings') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="h-5 w-5 {{ request()->routeIs('settings') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.438.995s.145.755.438.995l1.003.827c.424.35.534.954.26 1.431l-1.296 2.247a1.125 1.125 0 01-1.37.49l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.127c-.331.183-.581.495-.644.87l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.645-.87a6.52 6.52 0 01-.22-.127c-.324-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 01-1.37-.49l-1.296-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.437-.995s-.145-.755-.437-.995l-1.004-.827a1.125 1.125 0 01-.26-1.431l1.296-2.247a1.125 1.125 0 011.37-.49l1.217.456c.355.133.75.072 1.076-.124.072-.044.146-.087.22-.127.332-.183.582-.495.645-.87l.212-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span>Pengaturan</span>
                        </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Barcode: admin & warehouse — sesuai middleware role:admin,warehouse --}}
            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('warehouse'))
            <div>
                <h3 class="px-3 text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Barcode</h3>
                <div class="space-y-1">
                    <a href="{{ route('barcode.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('barcode.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('barcode.*') ? 'text-indigo-400' : 'text-slate-500' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h16.5m-16.5 2.25h16.5m-16.5 2.25h16.5m-16.5 2.25h16.5m-16.5 2.25h16.5m-16.5 2.25h16.5M3 12h18M3 6h18M3 18h18" /></svg>
                        <span>Manajemen Barcode</span>
                    </a>
                </div>
            </div>
            @endif
        </nav>
    </div>

    <div class="p-4 border-t border-slate-800 bg-slate-900">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-slate-800 hover:bg-rose-500/10 text-slate-300 hover:text-rose-500 rounded-xl text-sm font-medium transition-all duration-200 border border-slate-700 hover:border-rose-500/30 group">
                <svg class="h-5 w-5 transition-transform group-hover:-translate-x-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

<style>
/* Custom scrollbar for sidebar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #334155;
  border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background-color: #475569;
}
</style>
