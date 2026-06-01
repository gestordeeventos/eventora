@extends('layouts.app')

@section('title', $evento->titulo)

@section('content')
<div class="card-eventora mb-4">
    <div class="card-eventora-body">
        <span class="badge">{{ $evento->tipoEvento->nombre }}</span>
        <h1 class="page-title font-display mt-2">{{ $evento->titulo }}</h1>
        <p class="text-muted">{{ $evento->lugar }} · {{ $evento->fecha_inicio->format('d/m/Y H:i') }}</p>
        @if ($evento->descripcion)
            <p class="mt-3">{{ $evento->descripcion }}</p>
        @endif
        <p class="small text-muted">Organiza: {{ $evento->organizador->nombreCompleto() }}</p>
    </div>
</div>

@if ($evento->boletos->isNotEmpty())
    <h2 class="font-display h5 mb-3">Boletos</h2>
    <div class="row g-3 mb-4">
        @foreach ($evento->boletos as $boleto)
            <div class="col-md-4">
                <div class="card-eventora h-100">
                    <div class="card-eventora-body">
                        <h3 class="h6">{{ $boleto->nombre_tipo }}</h3>
                        <p class="mb-1"><strong>${{ number_format($boleto->precio, 2) }}</strong></p>
                        <p class="small text-muted">{{ $boleto->disponibles() }} disponibles</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@auth
    @if (auth()->user()->isCliente() && $evento->estado === 'publicado' && $evento->boletos->isNotEmpty())
        <a href="{{ route('reservas.create', $evento) }}" class="btn-gold">Agregar al carrito</a>
    @endif
@else
    <a href="{{ route('login') }}" class="btn-gold">Inicia sesión para reservar</a>
@endauth
@endsection
