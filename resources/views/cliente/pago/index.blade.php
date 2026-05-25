@extends('layouts.cliente')

@section('title', 'Pago simulado')
@section('body-class', 'cliente-catalog')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const num = document.getElementById('numero_tarjeta');
    const venc = document.getElementById('vencimiento');
    if (num) {
        num.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 16);
            this.value = v.replace(/(.{4})/g, '$1 ').trim();
        });
    }
    if (venc) {
        venc.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 4);
            if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
            this.value = v;
        });
    }
});
</script>
@endpush

@section('content')
<div class="cliente-container cliente-page">
    <header class="perfil-header">
        <h1 class="page-title-pro font-display">Pago con tarjeta</h1>
        <p class="page-subtitle-pro">Simulación segura · No se realiza un cargo real</p>
    </header>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="perfil-card-pro pago-resumen-card">
                <h2 class="perfil-section-title font-display h5">Resumen de orden</h2>
                <p class="mb-1"><strong>{{ $reserva->evento->titulo }}</strong></p>
                <p class="small text-muted mb-3">{{ $reserva->evento->fecha_inicio?->format('d/m/Y H:i') }}</p>
                <ul class="pago-lineas list-unstyled mb-3">
                    @foreach ($reserva->reservaBoletos as $linea)
                        <li class="d-flex justify-content-between small py-1">
                            <span>{{ $linea->cantidad }}× {{ $linea->boleto->nombre_tipo }}</span>
                            <span>${{ number_format($linea->subtotal, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="pago-total-row">
                    <span>Total</span>
                    <strong>${{ number_format($reserva->total, 2) }} MXN</strong>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="perfil-card-pro">
                <div class="pago-sim-badge">🔒 Pago simulado Visa / Mastercard</div>

                @if ($errors->any() || session('error'))
                    <div class="alert alert-error mb-3">
                        {{ session('error') ?? $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('cliente.pago.procesar', $reserva) }}" class="pago-form" autocomplete="off">
                    @csrf
                    <div class="campo">
                        <label for="titular">Nombre del titular</label>
                        <input type="text" id="titular" name="titular" value="{{ old('titular', auth()->user()->nombreCompleto()) }}" required>
                    </div>
                    <div class="campo">
                        <label for="numero_tarjeta">Número de tarjeta</label>
                        <input type="text" id="numero_tarjeta" name="numero_tarjeta"
                               inputmode="numeric" placeholder="4111 1111 1111 1111"
                               value="{{ old('numero_tarjeta') }}" required>
                        <small class="text-muted">Prueba: 4111… (Visa) o 5105… (Mastercard)</small>
                    </div>
                    <div class="fila-dos">
                        <div class="campo">
                            <label for="vencimiento">Vencimiento (MM/AA)</label>
                            <input type="text" id="vencimiento" name="vencimiento"
                                   placeholder="12/28" maxlength="5" value="{{ old('vencimiento') }}" required>
                        </div>
                        <div class="campo">
                            <label for="cvv">CVV</label>
                            <input type="password" id="cvv" name="cvv" inputmode="numeric"
                                   maxlength="4" placeholder="123" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-gold w-100 mt-2">Confirmar pago simulado</button>
                    <a href="{{ route('cliente.eventos.index') }}" class="btn-nav-outline d-block text-center mt-3">Cancelar y volver al catálogo</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
