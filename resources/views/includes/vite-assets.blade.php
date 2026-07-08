@php
    $manifestExists = file_exists(public_path('build/manifest.json'));
@endphp

@if ($manifestExists)
    @vite(['resources/css/app.css'])
@else
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
    </style>
@endif
