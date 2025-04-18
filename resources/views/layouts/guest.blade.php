<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sulogoon') }} - {{ $title ?? 'Authentication' }}</title>

    @vite([
        'resources/css/guest.css'
    ])

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

</head>
<body>
    <!-- Mobile menu overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <a href="/" class="logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2ZM11 16l-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 7Z" fill="currentColor"/>
                </svg>
                Sulogoon
            </a>

            <!-- Mobile menu toggle -->
            <div class="mobile-menu-toggle" id="mobileMenuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <ul class="nav-auth-links" id="navLinks">
                <li><a href="{{ route('login') }}">Log in</a></li>
                <li><a href="{{ route('register') }}" class="cta-button">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex items-center justify-center flex-grow">
        {{ $slot }}
    </main>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 600,
            once: true,
            offset: 100
        });
    </script>
        <!-- Vite Scripts -->
    @vite([
        'resources/js/guest.js'
    ])

    @stack('scripts')
</body>
</html>