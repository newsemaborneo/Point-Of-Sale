@php
    $msg = $message ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.';
@endphp

{{-- Render overlay tanpa extend layout agar tidak mengandalkan $slot --}}
<x-modal-error
    title="403 - Tidak Diizinkan"
    :message="$msg"
    redirectUrl="{{ route('dashboard') }}"
/>



