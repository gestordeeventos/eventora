@extends('layouts.app')

@section('title', 'Eventos')

@section('content')
<h1 class="page-title font-display">Explorar eventos</h1>
<p class="page-subtitle">Encuentra tu próxima experiencia</p>

<div class="grid-eventos">
    @forelse ($eventos as $evento)
        <article class="card-eventora evento-card">
            <div class="card-eventora-body">
                <span class="badge">{{ $evento->tipoEvento->nombre }}</span>
                <h2 class="h5 mt-2">{{ $evento->titulo }}</h2>
                <p class="text-muted small">{{ $evento->lugar }}@if($evento->ciudad), {{ $evento->ciudad }}@endif</p>
                <p class="small mb-3">{{ $evento->fecha_inicio->format('d M Y · H:i') }}</p>
                <a href="{{ route('eventos.show', $evento) }}" class="btn-gold btn-sm">Ver evento</a>
            </div>
        </article>
    @empty
        <p class="text-muted">No hay eventos disponibles.</p>
    @endforelse
</div>

{{ $eventos->links() }}
@endsection
