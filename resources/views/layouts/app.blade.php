<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'POS App') }}</title>
    @include('includes.vite-assets')
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    <div class="min-h-screen">
        <header class="bg-white shadow-sm border-b border-slate-200">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <a href="{{ url('/') }}" class="text-lg font-semibold text-slate-900">{{ config('app.name', 'POS App') }}</a>
                    <p class="text-sm text-slate-500">Manajemen supplier dan log transaksi pembelian.</p>
                </div>
                <nav class="flex flex-wrap gap-4 text-sm font-medium">
                    <a href="{{ route('dashboard') }}" class="text-slate-700 hover:text-indigo-600">Dashboard</a>
                    <a href="{{ route('products.index') }}" class="text-slate-700 hover:text-indigo-600">Produk</a>
                    <a href="{{ route('categories.index') }}" class="text-slate-700 hover:text-indigo-600">Kategori</a>
                    <a href="{{ route('transactions.pos') }}" class="text-slate-700 hover:text-indigo-600">POS</a>
                    <a href="{{ route('transactions.index') }}" class="text-slate-700 hover:text-indigo-600">Transaksi</a>
                    <a href="{{ route('customers.index') }}" class="text-slate-700 hover:text-indigo-600">Pelanggan</a>
                    <a href="{{ route('suppliers.index') }}" class="text-slate-700 hover:text-indigo-600">Supplier</a>
                    <a href="{{ route('promotions.index') }}" class="text-slate-700 hover:text-indigo-600">Promosi</a>
                    <a href="{{ route('vouchers.index') }}" class="text-slate-700 hover:text-indigo-600">Voucher</a>
                    <a href="{{ route('branches.index') }}" class="text-slate-700 hover:text-indigo-600">Cabang</a>
                    <a href="{{ route('warehouses.index') }}" class="text-slate-700 hover:text-indigo-600">Gudang</a>
                    <a href="{{ route('purchases.orders.index') }}" class="text-slate-700 hover:text-indigo-600">Pembelian</a>
                    <a href="{{ route('users.index') }}" class="text-slate-700 hover:text-indigo-600">Pengguna</a>
                    <a href="{{ route('reports.index') }}" class="text-slate-700 hover:text-indigo-600">Laporan</a>
                    <a href="{{ route('settings') }}" class="text-slate-700 hover:text-indigo-600">Pengaturan</a>
                    <a href="{{ route('notifications.index') }}" class="text-slate-700 hover:text-indigo-600">Notifikasi</a>
                </nav>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-900">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-900">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
