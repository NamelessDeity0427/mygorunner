<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sulogoon') }} - {{ $title ?? 'Dashboard' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

    <!-- Vite Styles -->
    @vite([
        'resources/css/app.css',
        'resources/css/nav.css',
        'resources/css/custom.css'
    ])
</head>
<body>
    <div class="app-container">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 stripe-card">
                {{ $header }}
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 400,
            once: true,
            offset: 50
        });
    </script>

    <!-- Vite Scripts -->
    @vite([
        'resources/js/app.js',
        'resources/js/nav.js',
        'resources/js/custom.js'
    ])

    @stack('scripts')
</body>
</html>
