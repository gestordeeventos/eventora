@extends('layouts.app')

@section('title', 'Panel administrador')

@section('content')
<h1 class="page-title font-display">Panel administrador</h1>
<p class="page-subtitle">Vista general del sistema Eventora</p>

<div class="stats-grid">
    <div class="stat-card"><div class="value">{{ $usuarios }}</div><div class="label">Usuarios</div></div>
    <div class="stat-card"><div class="value">{{ $eventos }}</div><div class="label">Eventos</div></div>
    <div class="stat-card"><div class="value">{{ $reservas }}</div><div class="label">Reservas</div></div>
</div>

<h2 class="font-display h5 mb-3">Eventos recientes</h2>
<div class="card-eventora">
    <div class="card-eventora-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Título</th><th>Tipo</th><th>Organizador</th><th>Estado</th></tr></thead>
            <tbody>
                @foreach ($eventosRecientes as $evento)
                    <tr>
                        <td><a href="{{ route('eventos.show', $evento) }}">{{ $evento->titulo }}</a></td>
                        <td>{{ $evento->tipoEvento->nombre }}</td>
                        <td>{{ $evento->organizador->nombreCompleto() }}</td>
                        <td>{{ ucfirst($evento->estado) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
