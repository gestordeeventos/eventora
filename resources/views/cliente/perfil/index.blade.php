@extends('layouts.cliente')

@push('vite-extra')
    @vite(['resources/js/cliente-perfil-avatar.js'])
@endpush

@section('title', 'Mi perfil')
@section('body-class', 'cliente-catalog')

@section('content')
<div class="cliente-container cliente-page">
    <header class="perfil-header">
        <h1 class="page-title-pro font-display">Mi perfil</h1>
        <p class="page-subtitle-pro">Edita tus datos, foto y gestiona tu cuenta.</p>
    </header>

    <div class="perfil-card-pro">
        <div class="perfil-user-row">
            <div class="perfil-avatar-wrap">
                <div class="perfil-avatar-inner">
                    <span id="perfil-avatar-initial" class="perfil-avatar-inicial"
                          style="@if(! $user->tieneFotoPerfil()) background-color: {{ $user->colorAvatar() }}; @endif"
                          @if($user->tieneFotoPerfil()) hidden @endif>
                        {{ $user->inicialesAvatar() }}
                    </span>
                    <img id="perfil-avatar-display"
                         class="perfil-avatar-img"
                         alt="Foto de {{ $user->nombreCompleto() }}"
                         data-url="{{ $user->fotoPerfilUrl() }}"
                         @if($user->fotoPerfilUrl()) src="{{ $user->fotoPerfilUrl() }}" @else hidden @endif>
                </div>
                <button type="button" class="perfil-avatar-edit-btn" id="avatar-cambiar-btn" title="Cambiar foto">+</button>
            </div>
            <div>
                <h2 class="perfil-name">{{ $user->nombreCompleto() }}</h2>
                <span class="badge-rol">Cliente</span>
            </div>
        </div>

        <form method="POST" action="{{ route('cliente.perfil.update') }}" class="perfil-foto-form">
            @csrf
            @method('PATCH')
            <input type="file" id="avatar_file" accept="image/jpeg,image/jpg,image/png" hidden>
            <input type="hidden" name="avatar_data" id="avatar_data" value="">
            <input type="hidden" name="avatar_remove" id="avatar_remove" value="0">

            <div id="avatar-crop-wrap" class="avatar-crop-wrap" hidden>
                <p class="perfil-foto-hint">Recorta tu foto en cuadrado</p>
                <div class="avatar-crop-container">
                    <img id="avatar-crop-image" alt="Recortar foto de perfil">
                </div>
                <div class="avatar-crop-actions">
                    <button type="button" class="btn-gold btn-sm" id="avatar-aplicar-recorte">Aplicar recorte</button>
                    <button type="button" class="btn-nav-outline btn-sm" id="avatar-quitar">Quitar foto</button>
                </div>
            </div>
            <div class="perfil-foto-save-row">
                <button type="submit" class="btn-gold btn-sm">Guardar foto</button>
            </div>
        </form>
    </div>

    <div class="perfil-card-pro mt-4">
        <h2 class="perfil-section-title font-display h5">Datos personales</h2>
        <form method="POST" action="{{ route('cliente.perfil.datos') }}" class="perfil-datos-form">
            @csrf
            @method('PATCH')
            <div class="fila-dos">
                <div class="campo">
                    <label for="nombre">Nombre(s)</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $user->nombre) }}" required>
                </div>
                <div class="campo">
                    <label for="apellido">Apellido(s)</label>
                    <input type="text" id="apellido" name="apellido" value="{{ old('apellido', $user->apellido) }}" required>
                </div>
            </div>
            <div class="campo">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="campo">
                <label for="telefono">Teléfono (10 dígitos)</label>
                <input type="tel" id="telefono" name="telefono" maxlength="10"
                       value="{{ old('telefono', $user->telefono) }}" pattern="[0-9]{10}">
            </div>
            <p class="perfil-foto-hint">Cambiar contraseña (opcional)</p>
            <div class="fila-dos">
                <div class="campo">
                    <label for="password">Nueva contraseña</label>
                    <input type="password" id="password" name="password" autocomplete="new-password">
                </div>
                <div class="campo">
                    <label for="password_confirmation">Confirmar</label>
                    <input type="password" id="password_confirmation" name="password_confirmation">
                </div>
            </div>
            <button type="submit" class="btn-gold btn-sm">Guardar datos</button>
        </form>
    </div>

    @if ($pendientes->isNotEmpty())
        <div class="perfil-card-pro mt-4">
            <h2 class="perfil-section-title font-display h5">Pagos pendientes</h2>
            @foreach ($pendientes as $reserva)
                <div class="boleto-item">
                    <div>
                        <strong>{{ $reserva->evento->titulo }}</strong>
                        <p class="small text-muted mb-0">Pendiente de pago · ${{ number_format($reserva->total, 2) }}</p>
                    </div>
                    <a href="{{ route('cliente.pago.show', $reserva) }}" class="btn-gold btn-sm">Pagar ahora</a>
                </div>
            @endforeach
        </div>
    @endif

    <div class="perfil-card-pro mt-4">
        <h2 class="perfil-section-title font-display h5">Cuenta</h2>
        <p class="text-muted small">Registrado el {{ $user->created_at?->format('d/m/Y') ?? '—' }}</p>
        <a href="{{ route('cliente.compras.index') }}" class="btn-nav-outline btn-sm me-2">Ver historial de compras</a>

        <details class="cuenta-peligro mt-4">
            <summary class="text-danger">Eliminar cuenta permanentemente</summary>
            <form method="POST" action="{{ route('cliente.perfil.destroy') }}" class="mt-3"
                  onsubmit="return confirm('¿Seguro? Se borrarán tus compras y datos. Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <p class="small text-muted">Escribe <strong>ELIMINAR</strong> para confirmar.</p>
                <div class="campo">
                    <input type="text" name="confirmar" placeholder="ELIMINAR" required autocomplete="off">
                </div>
                <button type="submit" class="btn-nav-outline text-danger">Eliminar mi cuenta</button>
            </form>
        </details>
    </div>
</div>
@endsection
