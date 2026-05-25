<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Eventora')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('vite-extra')
</head>
<body class="eventora-body @yield('body-class')">
    @include('cliente.partials.navbar')
    <main class="cliente-main">
        @if (session('success'))
            <div class="cliente-container"><div class="alert alert-success">{{ session('success') }}</div></div>
        @endif
        @if (session('error'))
            <div class="cliente-container"><div class="alert alert-error">{{ session('error') }}</div></div>
        @endif
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
