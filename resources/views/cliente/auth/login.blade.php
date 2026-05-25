<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Eventora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="eventora-body auth-standalone">
    @include('cliente.partials.navbar')

    <div class="auth-standalone-wrap">
        <div class="register-card-pro">
            <header class="register-card-header">
                <h1 class="font-display">Bienvenido</h1>
                <p>Inicia sesión para adquirir boletos y gestionar tus eventos.</p>
            </header>

            @if (session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error mb-3">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error mb-3">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="campo">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="campo">
                    <label for="password">Contraseña</label>
                    <div class="input-password">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-pwd" onclick="togglePwd('password')">👁</button>
                    </div>
                </div>
                <div class="campo d-flex align-items-center gap-2">
                    <input type="checkbox" id="remember" name="remember" style="width:auto;">
                    <label for="remember" class="remember-label">Recordarme en este equipo</label>
                </div>
                <button type="submit" class="btn-register-submit">Iniciar sesión</button>
            </form>

            @include('cliente.partials.social-oauth')

            <p class="register-footer-link">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>
        </div>
    </div>

    <script>
    function togglePwd(id) {
        const el = document.getElementById(id);
        el.type = el.type === 'password' ? 'text' : 'password';
    }
    </script>
</body>
</html>
