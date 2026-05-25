@extends('layouts.app')

@section('title', 'Reservar — '.$evento->titulo)

@section('content')
<h1 class="page-title font-display">Reservar boletos</h1>
<p class="page-subtitle">{{ $evento->titulo }}</p>

<form method="POST" action="{{ route('reservas.store', $evento) }}" class="card-eventora">
    <div class="card-eventora-body">
        @csrf
        @foreach ($evento->boletos as $index => $boleto)
            @if ($boleto->disponibles() > 0)
                <div class="campo border-bottom pb-3 mb-3">
                    <input type="hidden" name="boletos[{{ $index }}][id]" value="{{ $boleto->id_boleto }}">
                    <label>{{ $boleto->nombre_tipo }} — ${{ number_format($boleto->precio, 2) }} ({{ $boleto->disponibles() }} disp.)</label>
                    <input type="number" name="boletos[{{ $index }}][cantidad]" min="0" max="{{ $boleto->disponibles() }}" value="0" class="form-control">
                </div>
            @endif
        @endforeach
        <button type="submit" class="btn-primary" style="width:auto;padding:12px 32px;">Confirmar reserva</button>
    </div>
</form>
@endsection
