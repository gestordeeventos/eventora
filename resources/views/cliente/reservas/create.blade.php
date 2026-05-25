@extends('layouts.cliente')

@section('title', 'Adquirir accesos')

@section('content')
<div class="cliente-container cliente-page">
    <a href="{{ route('eventos.show', $evento) }}" class="back-link">← Volver al evento</a>

    <div class="evento-detalle-pro">
        <h1 class="font-display evento-detalle-title">Adquirir accesos</h1>
        <p class="text-muted">{{ $evento->titulo }}</p>

        <form method="POST" action="{{ route('reservas.store', $evento) }}" class="mt-4">
            @csrf
            @foreach ($evento->boletos as $index => $boleto)
                @if ($boleto->disponibles() > 0)
                    <div class="boleto-select-row">
                        <input type="hidden" name="boletos[{{ $index }}][id]" value="{{ $boleto->id_boleto }}">
                        <div>
                            <strong>{{ $boleto->nombre_tipo }}</strong>
                            <span class="d-block small text-muted">${{ number_format($boleto->precio, 2) }} MXN · {{ $boleto->disponibles() }} disponibles</span>
                        </div>
                        <input type="number" name="boletos[{{ $index }}][cantidad]" min="0" max="{{ $boleto->disponibles() }}" value="0" class="qty-input">
                    </div>
                @endif
            @endforeach
            <button type="submit" class="btn-comprar-gold mt-4">Confirmar compra</button>
        </form>
    </div>
</div>
@endsection
