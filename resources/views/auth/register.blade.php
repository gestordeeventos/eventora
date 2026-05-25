@extends('layouts.auth')

@section('title', 'Crear cuenta')
@section('brand-tagline', 'Regístrate para explorar eventos y comprar boletos.')

@section('content')
<div class="card-header">
    <h1>Crear cuenta</h1>
    <p>Únete a Eventora como cliente</p>
</div>

<form method="POST" action="{{ route('register') }}">
    @csrf
    @if ($errors->any())
        <div class="alert alert-error">
            <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="fila-dos">
        <div class="campo">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
        </div>
        <div class="campo">
            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" value="{{ old('apellido') }}" required>
        </div>
    </div>

    <div class="campo">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
    </div>

    <div class="campo">
        <label for="password">Contraseña</label>
        <div class="input-password">
            <input type="password" id="password" name="password" required>
            <button type="button" class="toggle-pwd" onclick="togglePassword('password')">👁</button>
        </div>
    </div>

    <div class="campo">
        <label for="password_confirmation">Confirmar contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
    </div>

    <button type="submit" class="btn-primary">Crear cuenta</button>
</form>

<div class="card-footer">
    <p>¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
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
