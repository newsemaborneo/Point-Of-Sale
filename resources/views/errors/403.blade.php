@php
    $msg = null;
    if (isset($exception) && $exception) {
        $msg = method_exists($exception, 'getMessage') ? $exception->getMessage() : null;
    }
    if (!$msg && isset($message)) {
        $msg = $message;
    }
    $msg = $msg ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.';
@endphp

{{-- Render overlay tanpa meng-extend layout untuk menghindari $slot tidak terdefinisi --}}
<x-modal-error
    title="403 - Forbidden"
    :message="$msg"
    redirectUrl="{{ route('dashboard') }}"
/>



