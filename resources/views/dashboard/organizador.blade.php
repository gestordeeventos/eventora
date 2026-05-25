@extends('layouts.app')

@section('title', 'Panel organizador')

@section('content')
<h1 class="page-title font-display">Mis eventos</h1>
<p class="page-subtitle">{{ $totalEventos }} evento(s) creados</p>

<div class="mb-4">
    <a href="{{ route('organizador.eventos.create') }}" class="btn-gold">+ Crear evento</a>
</div>

<div class="grid-eventos">
    @forelse ($misEventos as $evento)
        <article class="card-eventora">
            <div class="card-eventora-body">
                <span class="badge">{{ $evento->tipoEvento->nombre }}</span>
                <h3 class="h5 mt-2">{{ $evento->titulo }}</h3>
                <p class="small text-muted">{{ ucfirst($evento->estado) }} · {{ $evento->fecha_inicio->format('d/m/Y') }}</p>
                <a href="{{ route('eventos.show', $evento) }}" class="btn-outline btn-sm">Ver</a>
            </div>
        </article>
    @empty
        <p>No has creado eventos. <a href="{{ route('organizador.eventos.create') }}">Crear el primero</a></p>
    @endforelse
</div>
@endsection
