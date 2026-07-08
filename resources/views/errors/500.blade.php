<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Kesalahan Server</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;margin:0;background:#f7f7f7;color:#111}
        .wrap{max-width:720px;margin:40px auto;padding:20px}
        .card{background:#fff;border:1px solid #e5e5e5;border-radius:12px;padding:24px}
        h1{font-size:24px;margin:0 0 12px}
        p{margin:8px 0;line-height:1.5}
        .muted{color:#666}
        a{color:#0b5ed7;text-decoration:none}
        a:hover{text-decoration:underline}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>500 - Kesalahan Server</h1>
        <p class="muted">Terjadi kesalahan pada sisi server.</p>

        @if (isset($exception) && $exception)
            @php($msg = method_exists($exception,'getMessage') ? $exception->getMessage() : null)
            @if($msg)
                <p>{{ $msg }}</p>
            @endif
        @endif

        <p>
            Kembali ke <a href="{{ route('dashboard') }}">Dashboard</a> atau coba lagi.
        </p>
    </div>
</div>
</body>
</html>

