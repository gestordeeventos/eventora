@extends('layouts.cliente')

@section('title', $evento->titulo)

@section('content')
<div class="cliente-container cliente-page">
    @php
        $backRoute = auth()->check() && auth()->user()->isCliente()
            ? 'cliente.eventos.index'
            : 'eventos.index';
    @endphp
    <a href="{{ route($backRoute) }}" class="back-link">← Volver al catálogo</a>

    <article class="evento-detalle-pro {{ $evento->tienePortada() ? 'evento-detalle-con-portada' : '' }}">
        @if ($evento->portadaUrl())
            <div class="evento-detalle-portada">
                <img src="{{ $evento->portadaUrl() }}" alt="Portada de {{ $evento->titulo }}">
            </div>
        @endif
        <span class="evento-tag">{{ strtoupper($evento->tipoEvento->nombre) }}</span>
        <h1 class="font-display evento-detalle-title">{{ $evento->titulo }}</h1>

        <section class="evento-detalle-block">
            <h2 class="font-display h5">Acerca del evento</h2>
            <p>{{ $evento->descripcion ?? 'Evento organizado por Eventora.' }}</p>
        </section>

        <div class="evento-meta-grid">
            <div class="meta-item">
                <span class="meta-label">Fecha del evento</span>
                <strong>{{ $evento->fecha_inicio?->format('d/m/Y') ?? 'Por confirmar' }}</strong>
            </div>
            <div class="meta-item">
                <span class="meta-label">Horario de acceso</span>
                <strong>{{ $evento->fecha_inicio?->format('H:i') ?? 'Por confirmar' }} hrs</strong>
            </div>
            <div class="meta-item">
                <span class="meta-label">Cupo disponible</span>
                <strong>{{ $evento->cupoDisponible() }} lugares</strong>
            </div>
        </div>

        <div class="meta-item meta-full">
            <span class="meta-label">Dirección / ubicación</span>
            <strong>{{ $evento->lugar }}@if($evento->ciudad), {{ $evento->ciudad }}@endif</strong>
            <p class="small text-muted mb-0">Ubicación asignada en boleto digital.</p>
        </div>

        @if ($evento->boletos->isNotEmpty())
            <section class="evento-detalle-block mt-4">
                <h2 class="font-display h5">Tipos de acceso</h2>
                <ul class="list-boletos">
                    @foreach ($evento->boletos as $boleto)
                        <li>
                            <span>{{ $boleto->nombre_tipo }}</span>
                            <span>${{ number_format($boleto->precio, 2) }} MXN · {{ $boleto->disponibles() }} disp.</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <div class="evento-comprar-bar">
            <div>
                <span class="meta-label">Precio por acceso</span>
                <div class="precio-grande font-display">
                    @if ($evento->precioDesde())
                        ${{ number_format($evento->precioDesde(), 2) }} <small>MXN</small>
                    @else
                        Consultar
                    @endif
                </div>
            </div>
            @auth
                @if (auth()->user()->isCliente() && $evento->boletos->isNotEmpty())
                    <a href="{{ route('reservas.create', $evento) }}" class="btn-comprar-gold">Adquirir accesos</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-comprar-gold">Iniciar sesión para comprar</a>
            @endauth
        </div>
    </article>
</div>
@endsection
