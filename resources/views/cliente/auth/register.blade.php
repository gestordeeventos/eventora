<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta — Eventora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="eventora-body auth-standalone">
    @include('cliente.partials.navbar')

    <div class="auth-standalone-wrap">
        <div class="register-card-pro">
            <header class="register-card-header">
                <h1 class="font-display">Crear una cuenta</h1>
                <p>Regístrate para adquirir tus boletos y gestionar eventos.</p>
            </header>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                @if ($errors->any())
                    <div class="alert alert-error mb-3">
                        <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="fila-dos">
                    <div class="campo">
                        <label for="nombre">Nombre(s)</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="campo">
                        <label for="apellido">Apellido(s)</label>
                        <input type="text" id="apellido" name="apellido" value="{{ old('apellido') }}" required>
                    </div>
                </div>
                <div class="campo">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div class="campo">
                    <label for="telefono">Teléfono celular</label>
                    <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}"
                           placeholder="10 dígitos" maxlength="10" pattern="[0-9]{10}">
                </div>
                <div class="campo">
                    <label for="password">Contraseña</label>
                    <div class="input-password">
                        <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required>
                        <button type="button" class="toggle-pwd" onclick="togglePwd('password')">👁</button>
                    </div>
                </div>
                <div class="campo">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <button type="submit" class="btn-register-submit">Confirmar registro</button>
            </form>

            @include('cliente.partials.social-oauth')

            <p class="register-footer-link">¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
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
