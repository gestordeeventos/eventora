@extends('layouts.app')

@section('title', 'Mis reservas')

@section('content')
<h1 class="page-title font-display">Mis reservas</h1>
<p class="page-subtitle">Historial de compras de boletos</p>

<div class="card-eventora">
    <div class="card-eventora-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th>Evento</th><th>Fecha</th><th>Total</th><th>Estado</th></tr>
            </thead>
            <tbody>
                @forelse ($reservas as $reserva)
                    <tr>
                        <td>{{ $reserva->evento->titulo }}</td>
                        <td>{{ $reserva->created_at->format('d/m/Y') }}</td>
                        <td>${{ number_format($reserva->total, 2) }}</td>
                        <td><span class="badge">{{ ucfirst($reserva->estado) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Sin reservas. <a href="{{ route('eventos.index') }}">Explorar eventos</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $reservas->links() }}
@endsection
