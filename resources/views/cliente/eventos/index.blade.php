@extends('layouts.cliente')

@section('title', 'Explorar eventos')
@section('body-class', 'cliente-catalog')

@section('content')
@php
    $catalogRoute = auth()->check() && auth()->user()->isCliente()
        ? 'cliente.eventos.index'
        : 'eventos.index';
@endphp
<div class="cliente-container cliente-page">
    <div class="page-header-row">
        <div>
            <h1 class="page-title-pro font-display">Explorar eventos disponibles</h1>
            <p class="page-subtitle-pro">Encuentra y adquiere tus accesos para los mejores eventos</p>
        </div>
        <form method="GET" action="{{ route($catalogRoute) }}" class="search-eventos">
            <input type="search" name="q" value="{{ request('q') }}"
                   placeholder="Buscar por nombre de evento o tipo (ej: académico)...">
        </form>
    </div>

    <div class="eventos-grid-pro">
        @forelse ($eventos as $evento)
            @include('cliente.partials.evento-card', ['evento' => $evento])
        @empty
            <div class="empty-state-pro">
                <p>No encontramos eventos con ese criterio.</p>
                <a href="{{ route($catalogRoute) }}" class="btn-gold btn-sm mt-2">Ver todos</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $eventos->links() }}</div>
</div>
@endsection
