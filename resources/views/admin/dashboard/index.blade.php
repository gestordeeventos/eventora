@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<header class="admin-page-header">
    <div>
        <h1 class="admin-page-title font-display">Panel administrador</h1>
        <p class="admin-page-subtitle">Vista general del sistema Eventora en tiempo real</p>
    </div>
</header>

<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <span class="admin-stat-icon">📅</span>
        <div>
            <span class="admin-stat-value font-display">{{ $stats['eventos'] }}</span>
            <span class="admin-stat-label">Eventos creados</span>
        </div>
    </div>
    <div class="admin-stat-card">
        <span class="admin-stat-icon">🎟</span>
        <div>
            <span class="admin-stat-value font-display">{{ $stats['boletos_vendidos'] }}</span>
            <span class="admin-stat-label">Boletos vendidos</span>
        </div>
    </div>
    <div class="admin-stat-card">
        <span class="admin-stat-icon">👥</span>
        <div>
            <span class="admin-stat-value font-display">{{ $stats['clientes'] }}</span>
            <span class="admin-stat-label">Clientes registrados</span>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title font-display">Monitoreo de eventos en tiempo real</h2>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nombre del evento</th>
                    <th>Tipo de evento</th>
                    <th>Precio entrada</th>
                    <th>Cupo máximo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($eventos as $evento)
                    @php
                        $precio = $evento->precioDesde();
                        $activo = $evento->estado === 'publicado';
                    @endphp
                    <tr>
                        <td><strong>{{ $evento->titulo }}</strong></td>
                        <td><span class="badge-tipo">{{ $evento->tipoEvento->nombre }}</span></td>
                        <td>{{ $precio ? '$'.number_format($precio, 2).' MXN' : '—' }}</td>
                        <td>{{ $evento->capacidad_max }} lugares</td>
                        <td>
                            @if ($activo)
                                <span class="badge-estado badge-activo">Activo</span>
                            @else
                                <span class="badge-estado badge-inactivo">{{ ucfirst($evento->estado) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No hay eventos registrados aún.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-card-footer">
        <a href="{{ route('admin.eventos.index') }}" class="btn-admin-gold">Gestionar eventos</a>
    </div>
</div>
@endsection
