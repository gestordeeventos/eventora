@extends('layouts.cliente')

@section('title', 'Compra exitosa')
@section('body-class', 'cliente-catalog')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const url = @json(route('cliente.ticket', $reserva));
    window.open(url, '_blank', 'noopener,noreferrer');
});
</script>
@endpush

@section('content')
<div class="cliente-container cliente-page">
    <div class="perfil-card-pro text-center pago-exito-card">
        <div class="pago-exito-icon">✓</div>
        <h1 class="page-title-pro font-display">¡Pago completado!</h1>
        <p class="page-subtitle-pro">Tu ticket se abrió en una nueva pestaña. También puedes descargarlo cuando quieras.</p>

        <div class="pago-ticket-code">
            <span class="perfil-label">Código de ticket</span>
            <strong class="font-display">{{ $reserva->codigo_ticket }}</strong>
        </div>

        <p class="mb-1"><strong>{{ $reserva->evento->titulo }}</strong></p>
        <p class="text-muted small mb-4">
            Pagado el {{ $reserva->pagado_at?->format('d/m/Y H:i') }}
            @if ($reserva->ultimos4_tarjeta)
                · Tarjeta **** {{ $reserva->ultimos4_tarjeta }}
            @endif
        </p>

        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="{{ route('cliente.ticket', $reserva) }}" target="_blank" rel="noopener" class="btn-gold">Ver ticket PDF</a>
            <a href="{{ route('cliente.compras.index') }}" class="btn-nav-outline">Historial de compras</a>
            <a href="{{ route('cliente.eventos.index') }}" class="btn-nav-outline">Seguir explorando</a>
        </div>
    </div>
</div>
@endsection
