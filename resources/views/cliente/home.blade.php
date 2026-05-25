@extends('layouts.cliente')

@section('title', 'Eventora — Gestor de Eventos')
@section('body-class', 'cliente-landing')

@section('content')
<section class="cliente-hero">
    <div class="cliente-hero-bg"></div>
    <div class="cliente-hero-overlay"></div>
    <div class="cliente-container cliente-hero-content">
        <p class="cliente-eyebrow">Gestión premium de eventos</p>
        <h1 class="font-display cliente-hero-title">Crea experiencias <span>inolvidables</span></h1>
        <p class="cliente-hero-text">
            Explora nuestro catálogo de eventos públicos, adquiere tus boletos digitales de forma segura
            y gestiona tus contrataciones en un solo lugar.
        </p>
        <div class="cliente-hero-cta">
            @auth
                @if (auth()->user()->isCliente())
                    <a href="{{ route('cliente.eventos.index') }}" class="btn-hero-gold">Explorar eventos</a>
                @else
                    <a href="{{ route('eventos.index') }}" class="btn-hero-gold">Explorar eventos</a>
                @endif
            @else
                <a href="{{ route('eventos.index') }}" class="btn-hero-gold">Explorar eventos</a>
                <a href="{{ route('register') }}" class="btn-hero-outline">Crear cuenta</a>
            @endauth
        </div>
    </div>
    <div class="cliente-hero-stats">
        <div class="cliente-container stats-row-pro">
            <div class="stat-block-pro">
                <span class="stat-num font-display">{{ $stats['eventos'] }}</span>
                <span class="stat-lbl">Eventos activos</span>
            </div>
            <div class="stat-divider-pro"></div>
            <div class="stat-block-pro">
                <span class="stat-num font-display">{{ $stats['paquetes'] }}</span>
                <span class="stat-lbl">Paquetes</span>
            </div>
            <div class="stat-divider-pro"></div>
            <div class="stat-block-pro">
                <span class="stat-num font-display">{{ $stats['tipos'] }}</span>
                <span class="stat-lbl">Tipos de evento</span>
            </div>
        </div>
    </div>
</section>

<section class="cliente-section cliente-section-light">
    <div class="cliente-container">
        <h2 class="section-title font-display">Próximos eventos públicos</h2>
        <div class="eventos-grid-pro">
            @forelse ($eventosDestacados as $evento)
                @include('cliente.partials.evento-card', ['evento' => $evento])
            @empty
                <p class="text-muted">Pronto publicaremos nuevos eventos.</p>
            @endforelse
        </div>
        @if ($eventosDestacados->isNotEmpty())
            <div class="text-center mt-4">
                <a href="{{ auth()->check() && auth()->user()->isCliente() ? route('cliente.eventos.index') : route('eventos.index') }}" class="btn-gold">Ver todos los eventos</a>
            </div>
        @endif
    </div>
</section>
@endsection
