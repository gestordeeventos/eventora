<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel') — Eventora Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('vite-extra')
</head>
<body class="eventora-body admin-body">
    <div class="admin-shell">
        @include('admin.partials.sidebar')
        <div class="admin-content-wrap">
            <main class="admin-main">
                @if (session('success'))
                    <div class="alert alert-success admin-alert" data-auto-dismiss>
                        <span>{{ session('success') }}</span>
                        <button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-error admin-alert">
                        <span>{{ session('error') }}</span>
                        <button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @include('admin.partials.delete-modal')
    @stack('scripts')
</body>
</html>
