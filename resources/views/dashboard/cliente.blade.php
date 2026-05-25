@extends('layouts.app')

@section('title', 'Mi panel')

@section('content')
<h1 class="page-title font-display">Hola, {{ auth()->user()->nombre }}</h1>
<p class="page-subtitle">Explora eventos y gestiona tus reservas</p>

<div class="stats-grid">
    <div class="stat-card">
        <div class="value">{{ $reservas->count() }}</div>
        <div class="label">Reservas recientes</div>
    </div>
</div>

<h2 class="font-display h5 mb-3">Próximos eventos</h2>
<div class="grid-eventos mb-4">
    @forelse ($proximosEventos as $evento)
        <article class="card-eventora evento-card">
            <div class="card-eventora-body">
                <span class="badge">{{ $evento->tipoEvento->nombre }}</span>
                <h3 class="h5 mt-2">{{ $evento->titulo }}</h3>
                <p class="text-muted small mb-2">{{ $evento->lugar }} · {{ $evento->fecha_inicio->format('d/m/Y H:i') }}</p>
                <a href="{{ route('eventos.show', $evento) }}" class="btn-gold btn-sm">Ver detalle</a>
            </div>
        </article>
    @empty
        <p class="text-muted">No hay eventos publicados por ahora.</p>
    @endforelse
</div>

<h2 class="font-display h5 mb-3">Mis reservas</h2>
@if ($reservas->isEmpty())
    <p class="text-muted">Aún no tienes reservas. <a href="{{ route('eventos.index') }}">Explorar eventos</a></p>
@else
    <div class="card-eventora">
        <div class="card-eventora-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Evento</th><th>Total</th><th>Estado</th></tr></thead>
                <tbody>
                    @foreach ($reservas as $reserva)
                        <tr>
                            <td>{{ $reserva->evento->titulo }}</td>
                            <td>${{ number_format($reserva->total, 2) }}</td>
                            <td><span class="badge">{{ ucfirst($reserva->estado) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
