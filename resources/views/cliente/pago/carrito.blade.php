@extends('layouts.cliente')

@section('title', 'Pago del carrito')
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
        <h1 class="page-title-pro font-display">Pago de tu carrito</h1>
        <p class="page-subtitle-pro">{{ $reservas->count() }} {{ $reservas->count() === 1 ? 'orden' : 'órdenes' }} · Simulación segura</p>
    </header>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="perfil-card-pro pago-resumen-card">
                <h2 class="perfil-section-title font-display h5">Resumen</h2>
                @foreach ($reservas as $reserva)
                    <div class="carrito-pago-evento mb-3">
                        <p class="mb-1"><strong>{{ $reserva->evento->titulo }}</strong></p>
                        <p class="small text-muted mb-2">{{ $reserva->evento->fecha_inicio?->format('d/m/Y H:i') }}</p>
                        <ul class="pago-lineas list-unstyled mb-2">
                            @foreach ($reserva->reservaBoletos as $linea)
                                <li class="d-flex justify-content-between small py-1">
                                    <span>{{ $linea->cantidad }}× {{ $linea->boleto->nombre_tipo }}</span>
                                    <span>${{ number_format($linea->subtotal, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="d-flex justify-content-between small fw-semibold">
                            <span>Subtotal evento</span>
                            <span>${{ number_format($reserva->total, 2) }}</span>
                        </div>
                    </div>
                @endforeach
                <div class="pago-total-row">
                    <span>Total carrito</span>
                    <strong>${{ number_format($total, 2) }} MXN</strong>
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

                <form method="POST" action="{{ route('cliente.pago.carrito.procesar', $carrito) }}" class="pago-form" autocomplete="off">
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
                    <button type="submit" class="btn-gold w-100 mt-2">Pagar ${{ number_format($total, 2) }} MXN</button>
                    <a href="{{ route('cliente.compras.index') }}" class="btn-nav-outline d-block text-center mt-3">Pagar después</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
