<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ __('landing.hero.subtitle') }}">
    <meta property="og:title" content="{{ __('landing.meta_title') }}">
    <meta property="og:description" content="{{ __('landing.hero.subtitle') }}">
    <meta property="og:type" content="website">

    <title>{{ __('landing.meta_title') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased text-ceeac-ink bg-white" x-data="landingApp()" x-init="init()">
    @yield('content')

    <script>
        function landingApp() {
            return {
                mobileMenuOpen: false,
                scrolled: false,
                init() {
                    window.addEventListener('scroll', () => {
                        this.scrolled = window.scrollY > 40;
                    });
                    // Intersection Observer for section fade-in
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
                    }, { threshold: 0.1 });
                    document.querySelectorAll('.section-fade').forEach(el => observer.observe(el));
                }
            }
        }
    </script>
</body>
</html>
