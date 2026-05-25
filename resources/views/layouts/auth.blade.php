<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Eventora') — Acceso</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="eventora-body">
<div class="auth-wrapper">
    <aside class="auth-left">
        <div class="brand-block">
            <x-eventora.logo />
            <div class="brand-name">EVENTORA</div>
            <p class="brand-tagline">@yield('brand-tagline', 'Gestión premium de eventos corporativos y sociales.')</p>
        </div>
        <div class="decorative-lines">
            <div class="dec-line"></div>
            <div class="dec-line short"></div>
            <div class="dec-line shorter"></div>
        </div>
    </aside>
    <main class="auth-right">
        <div class="auth-card">
            @yield('content')
        </div>
    </main>
</div>
@stack('scripts')
</body>
</html>
