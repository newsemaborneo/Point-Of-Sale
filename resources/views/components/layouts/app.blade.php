@props(['title', 'hideHeader' => false, 'hideSidebar' => false, 'noScroll' => false, 'noPadding' => false])
<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'POS App') }}</title>
    @include('includes.vite-assets')
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body x-data="{ sidebarOpen: window.innerWidth >= 1024 }" @resize.window="if (window.innerWidth >= 1024) { sidebarOpen = true } else { sidebarOpen = false }" class="bg-slate-50 text-slate-800 {{ $noScroll ? 'h-[100dvh] overflow-hidden' : 'min-h-[100dvh]' }} selection:bg-indigo-500/30 selection:text-indigo-900">
    {{-- Sidebar --}}
    @if(!$hideSidebar)
        @include('layouts.sidebar')
    @endif

    {{-- Main Content --}}
    <div :class="sidebarOpen && !{{ $hideSidebar ? 'true' : 'false' }} ? 'lg:ml-72' : 'ml-0'" class="flex-1 flex flex-col {{ $noScroll ? 'h-[100dvh]' : 'min-h-[100dvh]' }} transition-all duration-300">
        {{-- Header --}}
        @if(!$hideHeader)
        <header :class="sidebarOpen ? 'lg:left-72' : 'left-0'" class="fixed top-0 left-0 right-0 bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200/60 z-30 transition-all duration-300">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    {{-- Hamburger Menu (Toggle Sidebar) --}}
                    <button @click="sidebarOpen = !sidebarOpen" type="button" class="-ml-2 p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    {{-- Optional Breadcrumbs/Title can go here --}}
                    <h1 class="text-xl font-bold text-slate-800">{{ $title ?? 'Dashboard' }}</h1>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <div class="flex items-center gap-3">
                            <div class="hidden md:flex flex-col items-end">
                                <span class="text-sm font-semibold text-slate-900">{{ auth()->user()->name ?? 'Pengguna' }}</span>
                                <span class="text-xs text-slate-500">{{ auth()->user()->role->name ?? 'Kasir' }}</span>
                            </div>
                            <button type="button" class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md shadow-indigo-500/20 ring-2 ring-white hover:ring-indigo-100 transition-all">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </button>
                        </div>
                    @endauth
                </div>
            </div>
        </header>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 flex flex-col min-h-0 mx-auto w-full {{ $noPadding ? 'p-0 max-w-none' : 'max-w-7xl ' . ($hideHeader ? 'p-0 sm:p-6 lg:p-8' : 'px-4 pt-24 pb-8 sm:px-6 lg:px-8') }}">
            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-sm backdrop-blur-sm flex items-start gap-3 animate-fade-in-down">
                    <div class="mt-0.5 rounded-full bg-emerald-100 p-1">
                        <svg class="h-4 w-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                    </div>
                    <div class="text-sm font-medium text-emerald-800">{{ session('success') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50/50 p-4 shadow-sm backdrop-blur-sm flex items-start gap-3 animate-fade-in-down">
                    <div class="mt-0.5 rounded-full bg-rose-100 p-1">
                        <svg class="h-4 w-4 text-rose-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                    </div>
                    <div class="text-sm font-medium text-rose-800">
                        <ul class="list-disc space-y-1 pl-4 marker:text-rose-400">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <style>
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down {
            animation: fadeInDown 0.4s ease-out forwards;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    @stack('scripts')
</body>
</html>