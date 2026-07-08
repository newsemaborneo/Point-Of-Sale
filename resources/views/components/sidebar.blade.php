<nav class="mt-5 px-2 space-y-1">
    <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Menu Utama</p>
    <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
        Dashboard
    </a>

    <p class="px-3 pt-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Manajemen</p>
    <div class="space-y-1">
        @canany(['admin', 'warehouse', 'cashier', 'supervisor'])
        <a href="{{ route('products.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
            Produk
        </a>
        @endcanany
        @canany(['admin', 'warehouse'])
        <a href="{{ route('categories.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
            Kategori
        </a>
        <!-- Stok -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                <span>Stok</span>
                <svg class="h-5 w-5 transform" :class="{ 'rotate-90': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="open" x-transition class="ml-4 space-y-1">
                @canany(['admin', 'warehouse'])
                <a href="{{ route('stock.in.create') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Stok Masuk</a>
                <a href="{{ route('stock.out.create') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Stok Keluar</a>
                <a href="{{ route('stock.transfer.create') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Transfer Stok</a>
                <a href="{{ route('stock.adjustment.create') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Penyesuaian Stok</a>
                <a href="{{ route('stock.opname.create') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Stock Opname</a>
                @endcanany
                @canany(['admin', 'warehouse', 'supervisor'])
                <a href="{{ route('stock.history') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Riwayat Stok</a>
                @endcanany
            </div>
        </div>
        @canany(['admin', 'warehouse'])
        <a href="{{ route('purchases.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
            Pembelian
        </a>
        <a href="{{ route('suppliers.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
            Supplier
        </a>
        @endcanany
        <!-- Retur -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                <span>Retur</span>
                <svg class="h-5 w-5 transform" :class="{ 'rotate-90': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="open" x-transition class="ml-4 space-y-1">
                @canany(['cashier', 'admin', 'supervisor'])
                <a href="{{ route('sale-returns.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Retur Penjualan</a>
                @endcanany
                @canany(['admin', 'warehouse'])
                <a href="{{ route('purchase-returns.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Retur Pembelian</a>
                @endcanany
            </div>
        </div>
        <!-- Cabang & Gudang -->
        @can('admin')
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                <span>Cabang & Gudang</span>
                <svg class="h-5 w-5 transform" :class="{ 'rotate-90': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="open" x-transition class="ml-4 space-y-1">
                <a href="{{ route('branches.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Cabang</a>
                <a href="{{ route('warehouses.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Gudang</a>
            </div>
        </div>
        @endcan
        <!-- Promosi & Voucher -->
        @canany(['admin', 'supervisor'])
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                <span>Promosi & Voucher</span>
                <svg class="h-5 w-5 transform" :class="{ 'rotate-90': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="open" x-transition class="ml-4 space-y-1">
                <a href="{{ route('promotions.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Promosi</a>
                <a href="{{ route('vouchers.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">Voucher</a>
            </div>
        </div>
        @endcanany
        @canany(['admin', 'warehouse', 'cashier', 'supervisor'])
        <a href="{{ route('customers.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
            Daftar Pelanggan
        </a>
        @endcanany
    </div>

    <p class="px-3 pt-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Lainnya</p>
    <div class="space-y-1">
        @canany(['admin', 'supervisor'])
        <a href="{{ route('reports.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
            Laporan & Analisis
        </a>
        @endcanany
        @can('admin')
        <a href="{{ route('audit.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
            Audit Log
        </a>
        <a href="{{ route('users.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
            Pengguna
        </a>
        <a href="{{ route('settings') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
            Pengaturan
        </a>
        @endcan
        @canany(['admin', 'warehouse'])
        <!-- Barcode -->
        <a href="{{ route('barcode.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:bg-slate-50 hover:text-slate-900">
            Manajemen Barcode
        </a>
        @endcanany
    </div>
</nav>
