@extends('layouts.admin')

@push('vite-extra')
    @vite(['resources/js/cliente-perfil-avatar.js'])
@endpush

@section('title', $modo === 'crear' ? 'Nuevo usuario' : 'Editar usuario')

@section('content')
<header class="admin-page-header">
    <a href="{{ route('admin.usuarios.index') }}" class="admin-back-link">← Volver a usuarios</a>
    <h1 class="admin-page-title font-display">{{ $modo === 'crear' ? 'Crear usuario' : 'Editar usuario' }}</h1>
    <p class="admin-page-subtitle">
        @if ($modo === 'editar')
            Modifica los datos de <strong>{{ $usuario->nombreCompleto() }}</strong>
        @else
            Rol administrador o cliente.
        @endif
    </p>
</header>

<div class="admin-card admin-form-card admin-usuario-form-card">
    <form method="POST"
          action="{{ $modo === 'crear' ? route('admin.usuarios.store') : route('admin.usuarios.update', $usuario) }}"
          class="admin-form">
        @csrf
        @if ($modo === 'editar')
            @method('PUT')
        @endif

        @if ($errors->any())
            <div class="alert alert-error mb-3">
                <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @include('partials.avatar-crop', ['usuario' => $usuario])

        <div class="fila-dos">
            <div class="campo">
                <label for="nombre">Nombre(s)</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required>
            </div>
            <div class="campo">
                <label for="apellido">Apellido(s)</label>
                <input type="text" id="apellido" name="apellido" value="{{ old('apellido', $usuario->apellido) }}" required>
            </div>
        </div>
        <div class="campo">
            <label for="email">Correo</label>
            <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
        </div>
        <div class="campo">
            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" maxlength="10" placeholder="10 dígitos"
                   value="{{ old('telefono', $usuario->telefono) }}">
        </div>
        <div class="campo">
            <label for="rol">Rol</label>
            <select id="rol" name="rol" required>
                <option value="cliente" @selected(old('rol', $usuario->rol) === 'cliente')>Cliente</option>
                <option value="admin" @selected(old('rol', $usuario->rol) === 'admin')>Administrador</option>
            </select>
        </div>
        <div class="campo d-flex align-items-center gap-2">
            <input type="checkbox" id="activo" name="activo" value="1"
                   @checked(old('activo', $usuario->activo ?? true)) style="width:auto;">
            <label for="activo" class="mb-0">Cuenta activa</label>
        </div>
        <div class="campo">
            <label for="password">Contraseña {{ $modo === 'editar' ? '(dejar vacío para no cambiar)' : '' }}</label>
            <input type="password" id="password" name="password" autocomplete="new-password" {{ $modo === 'crear' ? 'required' : '' }}>
        </div>
        <div class="campo">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn-admin-gold">Guardar cambios</button>
            <a href="{{ route('admin.usuarios.index') }}" class="btn-admin-outline-sm">Cancelar</a>
        </div>
    </form>
</div>
@endsection

