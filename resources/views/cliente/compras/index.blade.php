@extends('layouts.cliente')

@section('title', 'Mis compras')
@section('body-class', 'cliente-catalog')

@section('content')
<div class="cliente-container cliente-page">
    <header class="perfil-header d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div>
            <h1 class="page-title-pro font-display">Historial de compras</h1>
            <p class="page-subtitle-pro mb-0">Todas tus órdenes pagadas con ticket descargable.</p>
        </div>
        <a href="{{ route('cliente.eventos.index') }}" class="btn-gold btn-sm">Nueva compra</a>
    </header>

    @if ($pendientes->isNotEmpty())
        <div class="perfil-card-pro mb-4">
            <h2 class="perfil-section-title font-display h5">Órdenes pendientes de pago</h2>
            <p class="small text-muted mb-3">
                Al confirmar la compra se crea la orden; solo aparece en el historial de abajo después de completar el pago simulado.
            </p>
            @foreach ($pendientesPorCarrito as $idCarrito => $grupo)
                @php
                    $totalGrupo = $grupo->sum('total');
                    $carritoModel = $grupo->first()->carrito;
                @endphp
                <div class="compra-item compra-item-pendiente compra-item-carrito-grupo">
                    <div class="compra-item-main">
                        <strong>Carrito · {{ $grupo->count() }} {{ $grupo->count() === 1 ? 'evento' : 'eventos' }}</strong>
                        <ul class="list-unstyled small text-muted mb-2 mt-2">
                            @foreach ($grupo as $reserva)
                                <li>{{ $reserva->evento->titulo }} — ${{ number_format($reserva->total, 2) }}</li>
                            @endforeach
                        </ul>
                        <p class="small text-muted mb-0">Total ${{ number_format($totalGrupo, 2) }} MXN</p>
                    </div>
                    <div class="compra-item-actions text-end">
                        @if ($carritoModel)
                            <a href="{{ route('cliente.pago.carrito', $carritoModel) }}" class="btn-gold btn-sm">Pagar carrito completo</a>
                        @endif
                    </div>
                </div>
            @endforeach
            @foreach ($pendientesSueltas as $reserva)
                <div class="compra-item compra-item-pendiente">
                    <div class="compra-item-main">
                        <strong>{{ $reserva->evento->titulo }}</strong>
                        <p class="small text-muted mb-0">
                            Creada el {{ $reserva->created_at?->format('d/m/Y H:i') }}
                            · Total ${{ number_format($reserva->total, 2) }}
                        </p>
                    </div>
                    <div class="compra-item-actions text-end">
                        <a href="{{ route('cliente.pago.show', $reserva) }}" class="btn-gold btn-sm">Completar pago</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="perfil-card-pro">
        <h2 class="perfil-section-title font-display h5 mb-3">Compras pagadas</h2>
        @forelse ($compras as $compra)
            <div class="compra-item">
                <div class="compra-item-main">
                    <strong>{{ $compra->evento->titulo }}</strong>
                    <p class="small text-muted mb-1">
                        {{ $compra->pagado_at?->format('d/m/Y H:i') }}
                        · Ticket <code>{{ $compra->codigo_ticket }}</code>
                    </p>
                    @foreach ($compra->reservaBoletos as $linea)
                        <span class="small d-block text-muted">{{ $linea->cantidad }}× {{ $linea->boleto->nombre_tipo }}</span>
                    @endforeach
                </div>
                <div class="compra-item-actions text-end">
                    <div class="boleto-total mb-2">${{ number_format($compra->total, 2) }}</div>
                    <a href="{{ route('cliente.ticket', $compra) }}" target="_blank" rel="noopener" class="btn-gold btn-sm">Ticket PDF</a>
                </div>
            </div>
        @empty
            <p class="text-muted mb-3">Aún no tienes compras pagadas.</p>
            <a href="{{ route('cliente.eventos.index') }}" class="btn-gold">Explorar eventos</a>
        @endforelse

        @if ($compras->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $compras->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
