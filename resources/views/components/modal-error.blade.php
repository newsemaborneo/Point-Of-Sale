@props(['title' => 'Error', 'subtitle' => 'Akses ditolak.', 'message' => 'Terjadi kesalahan.', 'redirectUrl' => null])

@php
    $redirectUrl = $redirectUrl ?? route('dashboard');
@endphp

<div class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-modal="true" role="dialog">
    {{-- Overlay --}}
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    {{-- Center modal --}}
    <div class="relative w-full max-w-md transform rounded-2xl bg-white text-left align-middle shadow-xl transition-all">
        <div class="p-8 text-center">
            {{-- Icon --}}

            {{-- Content --}}
            <div class="mt-4">
                <h3 class="text-lg font-bold leading-6 text-slate-900">{{ $title }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
            </div>

            {{-- Message & Hint --}}
            <div class="mt-5 rounded-lg bg-slate-50 p-4 text-left">
                <p class="text-sm text-slate-700">{{ $message }}</p>
                <p class="mt-2 text-xs text-slate-500">
                    Jika Anda merasa ini adalah sebuah kesalahan, silakan hubungi administrator sistem.
                </p>
            </div>

            {{-- Footer/Action --}}
            <a href="{{ $redirectUrl }}"
               class="mt-6 inline-flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
