<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-slate-800 antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
        <div class="mb-6">
            <a href="/" aria-label="Kembali ke beranda">
                <x-application-logo class="h-16 w-16 fill-current text-cyan-700" />
            </a>
        </div>

        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white/95 px-6 py-6 shadow-sm backdrop-blur sm:px-8">
            {{ $slot }}
        </div>
    </div>
</body>

</html>
