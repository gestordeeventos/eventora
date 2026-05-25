@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
<h1 class="page-title font-display">Mi perfil</h1>

@if ($user->isProtectedAdmin())
    <div class="alert alert-info mb-4">
        Esta es la cuenta de administrador principal. Sus datos y contraseña están bloqueados y no pueden modificarse desde el sistema.
    </div>

    <div class="card-eventora" style="max-width: 520px;">
        <div class="card-eventora-body">
            <h2 class="h5 font-display mb-3">Datos del administrador</h2>
            <dl class="mb-0">
                <dt class="text-muted small text-uppercase">Nombre</dt>
                <dd class="mb-3">{{ $user->nombre }} {{ $user->apellido }}</dd>
                <dt class="text-muted small text-uppercase">Correo</dt>
                <dd class="mb-3">{{ $user->email }}</dd>
                <dt class="text-muted small text-uppercase">Teléfono</dt>
                <dd class="mb-3">{{ $user->telefono ?? '—' }}</dd>
                <dt class="text-muted small text-uppercase">Rol</dt>
                <dd class="mb-0">{{ ucfirst($user->rol) }}</dd>
            </dl>
        </div>
    </div>
@else
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card-eventora">
                <div class="card-eventora-body">
                    <h2 class="h5 font-display mb-3">Datos personales</h2>
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf @method('patch')
                        <div class="fila-dos">
                            <div class="campo">
                                <label for="nombre">Nombre</label>
                                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $user->nombre) }}" required>
                            </div>
                            <div class="campo">
                                <label for="apellido">Apellido</label>
                                <input type="text" id="apellido" name="apellido" value="{{ old('apellido', $user->apellido) }}" required>
                            </div>
                        </div>
                        <div class="campo">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="campo">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $user->telefono) }}">
                        </div>
                        <button type="submit" class="btn-gold">Guardar</button>
                        @if (session('status') === 'profile-updated')
                            <span class="text-success ms-2 small">Guardado.</span>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card-eventora">
                <div class="card-eventora-body">
                    <h2 class="h5 font-display mb-3">Cambiar contraseña</h2>
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
