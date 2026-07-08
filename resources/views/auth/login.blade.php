<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ config('app.name', 'POS App') }}</title>
    @include('includes.vite-assets')
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-700 text-white">
    <div class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-6 py-12">
        <div class="grid w-full gap-10 rounded-[2rem] bg-white/5 p-8 shadow-2xl shadow-black/20 backdrop-blur-xl lg:grid-cols-[1.1fr_1fr] lg:p-10">
            <div class="space-y-6">
                <div class="space-y-3">
                    <p class="text-sm uppercase tracking-[0.3em] text-cyan-200/70">POS App Login</p>
                    <h1 class="text-4xl font-semibold tracking-tight text-white">Selamat datang kembali</h1>
                    <p class="max-w-xl text-sm text-slate-300">Masuk ke sistem POS untuk mengelola produk, pelanggan, pembelian, stok, dan laporan secara lengkap.</p>
                </div>
                <div class="grid gap-4 rounded-3xl border border-white/10 bg-slate-950/70 p-6 shadow-xl shadow-black/20">
                    <div class="rounded-3xl bg-slate-900/90 p-4 text-sm text-slate-300">
                        <p class="font-semibold text-white">Petunjuk</p>
                        <p class="mt-2 text-sm leading-6 text-slate-400">Gunakan email dan password yang terdaftar. Jika belum punya akun, hubungi administrator.</p>
                    </div>
                    <div class="rounded-3xl bg-slate-900/90 p-4 text-sm text-slate-300">
                        <p class="font-semibold text-white">Rute Tersedia</p>
                        <p class="mt-2 text-slate-400">Web app dibangun berdasarkan `routes/web.php` dengan halaman dashboard, produk, pelanggan, supplier, cabang, dan laporan.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-slate-950/90 p-8 shadow-2xl shadow-black/20">
                <div class="space-y-3 text-center">
                    <h2 class="text-2xl font-semibold text-white">Masuk ke akun Anda</h2>
                    <p class="text-sm text-slate-400">Isi kredensial untuk melanjutkan.</p>
                </div>

                <form method="POST" action="{{ url('/login') }}" class="mt-8 space-y-5">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-200">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-3xl border border-white/10 bg-slate-900/90 px-4 py-3 text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/30" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-200">Password</label>
                        <input type="password" name="password" required class="w-full rounded-3xl border border-white/10 bg-slate-900/90 px-4 py-3 text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/30" />
                    </div>
                    <button type="submit" class="w-full rounded-3xl bg-cyan-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-cyan-400">Masuk Sekarang</button>
                </form>

                @if ($errors->any())
                    <div class="mt-6 rounded-3xl bg-rose-500/10 p-4 text-sm text-rose-100 ring-1 ring-rose-500/20">
                        <p class="font-semibold text-rose-100">Terjadi kesalahan:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-rose-100">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
