@extends('layouts.admin')

@section('title', 'Usuarios')

@section('content')
<header class="admin-page-header d-flex flex-wrap justify-content-between align-items-end gap-3">
    <div>
        <h1 class="admin-page-title font-display">Usuarios</h1>
        <p class="admin-page-subtitle">Administradores y clientes con acceso al sistema.</p>
    </div>
    <a href="{{ route('admin.usuarios.create') }}" class="btn-admin-gold">+ Nuevo usuario</a>
</header>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $u)
                    <tr>
                        <td>
                            <div class="admin-user-cell">
                                <div class="admin-user-avatar-wrap" aria-hidden="true">
                                    @if ($u->tieneFotoPerfil())
                                        <img src="{{ $u->fotoPerfilUrl() }}" alt="" class="admin-user-avatar"
                                             loading="lazy"
                                             onerror="this.closest('.admin-user-avatar-wrap').classList.add('is-fallback'); this.remove();">
                                    @endif
                                    <span class="admin-user-avatar admin-user-avatar-inicial @if($u->tieneFotoPerfil()) is-hidden @endif"
                                          style="background-color: {{ $u->colorAvatar() }}">
                                        {{ $u->inicialesAvatar() }}
                                    </span>
                                </div>
                                @if ($u->isProtectedAdmin())
                                    <span class="admin-user-name">{{ $u->nombreCompleto() }}</span>
                                @else
                                    <a href="{{ route('admin.usuarios.edit', $u) }}" class="admin-user-name-link">
                                        {{ $u->nombreCompleto() }}
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge-admin">{{ ucfirst($u->rol) }}</span></td>
                        <td>{{ $u->activo ? 'Activo' : 'Inactivo' }}</td>
                        <td class="text-end">
                            <div class="admin-actions-cell justify-content-end">
                                @if ($u->isProtectedAdmin())
                                    <span class="badge-admin-protegida">Cuenta protegida</span>
                                @else
                                    <a href="{{ route('admin.usuarios.edit', $u) }}" class="btn-admin-outline-sm">Editar</a>
                                    <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar permanentemente a {{ $u->nombreCompleto() }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-admin-danger-outline">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No hay usuarios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($usuarios->hasPages())
        <div class="mt-3">{{ $usuarios->links() }}</div>
    @endif
</div>
@endsection

