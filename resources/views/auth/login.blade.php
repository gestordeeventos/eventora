@extends('layouts.auth')

@section('title', 'Iniciar sesión')
@section('brand-tagline', 'En Eventora transformamos ideas en grandes acontecimientos.')

@section('content')
<div class="card-header">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <h1>Bienvenido</h1>
    <p>Inicia sesión para continuar</p>
</div>

<form method="POST" action="{{ route('login') }}">
    @csrf
    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <div class="campo">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
               placeholder="tu@email.com" required autofocus>
    </div>

    <div class="campo">
        <label for="password">Contraseña</label>
        <div class="input-password">
            <input type="password" id="password" name="password" placeholder="••••••••" required>
            <button type="button" class="toggle-pwd" onclick="togglePassword('password')">👁</button>
        </div>
    </div>

    <div class="campo d-flex align-items-center gap-2">
        <input type="checkbox" id="remember" name="remember" style="width:auto;">
        <label for="remember" style="text-transform:none; font-size:13px; margin:0; color:var(--gray);">Recordarme</label>
    </div>

    <button type="submit" class="btn-primary">Iniciar sesión</button>
</form>

<div class="card-footer">
    <p>¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
