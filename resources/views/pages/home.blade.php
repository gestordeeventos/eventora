@extends('layouts.guest')

@section('title', 'Eventora — Gestor de Eventos')

@section('content')
<div class="hero-bg-wrap">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>

    <nav class="hero-nav">
        <a href="{{ url('/') }}" class="app-nav-brand text-decoration-none">EVENT<span style="color:var(--gold)">ORA</span></a>
        <div class="d-flex gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-gold">Mi panel</a>
            @else
                <a href="{{ route('login') }}" class="btn-outline">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="btn-gold">Registrarse</a>
            @endauth
        </div>
    </nav>

    <div class="hero-content">
        <p class="hero-eyebrow">Gestión premium de eventos</p>
        <h1 class="hero-title font-display">Tu próximo <span>gran evento</span> empieza aquí</h1>
        <p class="hero-subtitle">Explora eventos, compra boletos y organiza experiencias corporativas y sociales con logística profesional.</p>
        <div class="hero-cta">
            <a href="{{ route('eventos.index') }}" class="btn-gold" style="padding:14px 36px;">Explorar eventos</a>
            @guest
                <a href="{{ route('register') }}" class="btn-outline" style="padding:14px 36px; color:#fff; border-color:rgba(255,255,255,0.3);">Crear cuenta</a>
            @endguest
        </div>
    </div>

    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-value">{{ $stats['eventos'] ?? 0 }}</div>
            <div class="stat-label">Eventos activos</div>
        </div>
        <div class="stat-divider d-none d-md-block"></div>
        <div class="stat-item">
            <div class="stat-value">{{ $stats['paquetes'] ?? 3 }}</div>
            <div class="stat-label">Paquetes</div>
        </div>
        <div class="stat-divider d-none d-md-block"></div>
        <div class="stat-item">
            <div class="stat-value">{{ $stats['tipos'] ?? 4 }}</div>
            <div class="stat-label">Tipos de evento</div>
        </div>
    </div>
</div>
@endsection
