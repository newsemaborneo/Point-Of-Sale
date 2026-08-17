<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ config('app.name', 'POS App') }}</title>
    @include('includes.vite-assets')
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-indigo-500/30 selection:text-indigo-900">
    <div class="flex min-h-screen w-full">
        
        {{-- Left: Branding Section (Hidden on Mobile) --}}
        <div class="hidden lg:flex w-1/2 flex-col justify-between bg-slate-900 p-12 relative overflow-hidden">
            {{-- Abstract Background Elements --}}
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-500/30 blur-[120px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-violet-600/20 blur-[100px]"></div>
            <div class="absolute top-[40%] left-[60%] w-[30%] h-[30%] rounded-full bg-emerald-500/20 blur-[80px]"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-white">{{ config('app.name', 'POS App') }}</span>
                </div>
            </div>

            <div class="relative z-10 space-y-6">
                <h1 class="text-4xl font-extrabold tracking-tight text-white leading-tight sm:text-5xl">
                    Kelola Bisnis Anda <br> <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">Lebih Cerdas & Cepat.</span>
                </h1>
                <p class="text-lg text-slate-300 max-w-md leading-relaxed">
                    Sistem Point of Sales terintegrasi dengan fitur AI Analysis, manajemen inventaris, dan pemantauan multi-cabang secara real-time.
                </p>
                <div class="pt-4 flex items-center gap-4">
                    <div class="flex -space-x-3">
                        <img class="w-10 h-10 rounded-full border-2 border-slate-900" src="https://i.pravatar.cc/100?img=1" alt="User">
                        <img class="w-10 h-10 rounded-full border-2 border-slate-900" src="https://i.pravatar.cc/100?img=2" alt="User">
                        <img class="w-10 h-10 rounded-full border-2 border-slate-900" src="https://i.pravatar.cc/100?img=3" alt="User">
                    </div>
                    <p class="text-sm text-slate-400 font-medium">Dipercaya oleh ratusan kasir</p>
                </div>
            </div>

            <div class="relative z-10 text-sm text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'POS App') }}. All rights reserved.
            </div>
        </div>

        {{-- Right: Login Form Section --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative overflow-hidden bg-white">
            {{-- Subtle mobile background mesh --}}
            <div class="absolute top-[-10%] right-[-10%] w-[60%] h-[60%] rounded-full bg-indigo-50/50 blur-[80px] lg:hidden"></div>

            <div class="w-full max-w-md relative z-10 animate-fade-in-up">
                {{-- Mobile Logo --}}
                <div class="flex lg:hidden items-center justify-center gap-3 mb-10">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-slate-900">{{ config('app.name', 'POS App') }}</span>
                </div>

                <div class="text-center lg:text-left mb-10">
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Masuk ke Akun</h2>
                    <p class="mt-3 text-slate-500">Silakan masukkan email dan password Anda untuk melanjutkan ke dashboard.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl bg-rose-50 p-4 border border-rose-100 flex items-start gap-3">
                        <div class="mt-0.5 rounded-full bg-rose-100 p-1">
                            <svg class="h-4 w-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="text-sm text-rose-800">
                            <p class="font-semibold">Terjadi kesalahan:</p>
                            <ul class="mt-1 list-disc pl-4 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ url('/login') }}" class="space-y-6">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">Alamat Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@perusahaan.com" 
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all placeholder:text-slate-400" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-semibold text-slate-700">Password</label>
                            <a href="#" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">Lupa Password?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </div>
                            <input type="password" name="password" required placeholder="••••••••"
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all placeholder:text-slate-400" />
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember-me" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600 bg-slate-50">
                        <label for="remember-me" class="ml-2 block text-sm text-slate-600">
                            Ingat saya
                        </label>
                    </div>

                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3.5 px-4 border border-transparent rounded-2xl shadow-lg shadow-indigo-500/30 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 transition-all hover:-translate-y-0.5">
                        Masuk ke Sistem
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>

                <div class="mt-10 pt-6 border-t border-slate-100 text-center lg:hidden">
                    <p class="text-xs text-slate-500">&copy; {{ date('Y') }} {{ config('app.name', 'POS App') }}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</body>
</html>
