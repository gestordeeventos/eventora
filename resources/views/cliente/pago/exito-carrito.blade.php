@extends('layouts.cliente')

@section('title', 'Compra exitosa')
@section('body-class', 'cliente-catalog')

@section('content')
<div class="cliente-container cliente-page">
    <div class="perfil-card-pro text-center pago-exito-card mb-4">
        <div class="pago-exito-icon">✓</div>
        <h1 class="page-title-pro font-display">¡Pago completado!</h1>
        <p class="page-subtitle-pro">
            {{ $reservas->count() }} {{ $reservas->count() === 1 ? 'ticket generado' : 'tickets generados' }}.
            Revisa tu correo para las confirmaciones.
        </p>
        <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
            <a href="{{ route('cliente.compras.index') }}" class="btn-gold">Historial de compras</a>
            <a href="{{ route('cliente.eventos.index') }}" class="btn-nav-outline">Seguir explorando</a>
        </div>
    </div>

    <div class="perfil-card-pro">
        <h2 class="perfil-section-title font-display h5 mb-3">Tus tickets</h2>
        @foreach ($reservas as $reserva)
            <div class="compra-item">
                <div class="compra-item-main">
                    <strong>{{ $reserva->evento->titulo }}</strong>
                    <p class="small text-muted mb-1">
                        Código <code>{{ $reserva->codigo_ticket }}</code>
                        · {{ $reserva->pagado_at?->format('d/m/Y H:i') }}
                    </p>
                    @foreach ($reserva->reservaBoletos as $linea)
                        <span class="small d-block text-muted">{{ $linea->cantidad }}× {{ $linea->boleto->nombre_tipo }}</span>
                    @endforeach
                </div>
                <div class="compra-item-actions text-end">
                    <div class="boleto-total mb-2">${{ number_format($reserva->total, 2) }}</div>
                    <a href="{{ route('cliente.ticket', $reserva) }}" target="_blank" rel="noopener" class="btn-gold btn-sm">Ticket PDF</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
