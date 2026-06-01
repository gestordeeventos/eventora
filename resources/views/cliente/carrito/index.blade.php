@extends('layouts.cliente')

@section('title', 'Mi carrito')
@section('body-class', 'cliente-catalog')

@section('content')
<div class="cliente-container cliente-page">
    <header class="perfil-header d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div>
            <h1 class="page-title-pro font-display">Mi carrito</h1>
            <p class="page-subtitle-pro mb-0">
                @if ($carrito->items->isNotEmpty())
                    {{ $carrito->items->sum('cantidad') }} {{ $carrito->items->sum('cantidad') === 1 ? 'entrada' : 'entradas' }} en {{ $carrito->items->groupBy('id_evento')->count() }} {{ $carrito->items->groupBy('id_evento')->count() === 1 ? 'evento' : 'eventos' }}
                @else
                    Agrega boletos desde el catálogo para continuar.
                @endif
            </p>
        </div>
        <a href="{{ route('cliente.eventos.index') }}" class="btn-nav-outline">Seguir explorando</a>
    </header>

    @if ($carrito->items->isEmpty())
        <div class="perfil-card-pro carrito-empty-state text-center">
            <div class="carrito-empty-icon" aria-hidden="true">🛒</div>
            <h2 class="font-display h5">Tu carrito está vacío</h2>
            <p class="text-muted mb-4">Descubre eventos y agrega los boletos que quieras comprar.</p>
            <a href="{{ route('cliente.eventos.index') }}" class="btn-gold">Ver catálogo</a>
        </div>
    @else
        <div class="carrito-layout">
            <div class="carrito-items-col">
                @foreach ($carrito->items->groupBy('id_evento') as $idEvento => $items)
                    @php $evento = $items->first()->evento; @endphp
                    <section class="perfil-card-pro carrito-evento-grupo mb-4">
                        <div class="carrito-evento-header">
                            @if ($evento->tienePortada())
                                <img src="{{ $evento->portadaUrl() }}" alt="" class="carrito-evento-thumb">
                            @else
                                <div class="carrito-evento-thumb carrito-evento-thumb-placeholder"></div>
                            @endif
                            <div>
                                <span class="carrito-evento-tipo">{{ $evento->tipoEvento->nombre ?? 'Evento' }}</span>
                                <h2 class="carrito-evento-title font-display h6 mb-1">{{ $evento->titulo }}</h2>
                                <p class="small text-muted mb-0">
                                    {{ $evento->fecha_inicio?->format('d/m/Y H:i') }} · {{ $evento->lugar }}
                                </p>
                            </div>
                        </div>

                        @foreach ($items as $item)
                            <article class="carrito-item-row">
                                <div class="carrito-item-info">
                                    <strong>{{ $item->boleto->nombre_tipo }}</strong>
                                    <span class="small text-muted d-block">${{ number_format($item->precio_unitario, 2) }} c/u</span>
                                </div>
                                <div class="carrito-item-actions">
                                    <form method="POST" action="{{ route('cliente.carrito.item.update', $item) }}" class="carrito-qty-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button" class="carrito-qty-btn" data-qty-minus aria-label="Menos">−</button>
                                        <input type="number" name="cantidad" value="{{ $item->cantidad }}" min="1" max="99" class="carrito-qty-input" aria-label="Cantidad">
                                        <button type="button" class="carrito-qty-btn" data-qty-plus aria-label="Más">+</button>
                                        <button type="submit" class="carrito-qty-apply visually-hidden">Actualizar</button>
                                    </form>
                                    <span class="carrito-item-subtotal">${{ number_format($item->subtotal(), 2) }}</span>
                                    <form method="POST" action="{{ route('cliente.carrito.item.destroy', $item) }}" class="d-inline mb-0" onsubmit="return confirm('¿Quitar este boleto del carrito?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="carrito-remove-btn" title="Eliminar">×</button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </section>
                @endforeach

                <form method="POST" action="{{ route('cliente.carrito.vaciar') }}" class="mb-0" onsubmit="return confirm('¿Vaciar todo el carrito?');">
                    @csrf
                    <button type="submit" class="btn-nav-outline btn-sm">Vaciar carrito</button>
                </form>
            </div>

            <aside class="carrito-resumen-col">
                <div class="perfil-card-pro carrito-resumen-card">
                    <h2 class="perfil-section-title font-display h5">Resumen</h2>
                    <ul class="carrito-resumen-lineas list-unstyled mb-3">
                        @foreach ($carrito->items as $item)
                            <li class="d-flex justify-content-between small py-1">
                                <span>{{ $item->cantidad }}× {{ Str::limit($item->boleto->nombre_tipo, 18) }}</span>
                                <span>${{ number_format($item->subtotal(), 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="pago-total-row mb-4">
                        <span>Total</span>
                        <strong>${{ number_format($total, 2) }} MXN</strong>
                    </div>
                    <form method="POST" action="{{ route('cliente.carrito.checkout') }}">
                        @csrf
                        <button type="submit" class="btn-gold w-100">Proceder al pago</button>
                    </form>
                    <p class="carrito-resumen-hint small text-muted mt-3 mb-0">
                        El inventario se confirma al completar el pago simulado.
                    </p>
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.carrito-qty-form').forEach(function (form) {
        const input = form.querySelector('.carrito-qty-input');
        const minus = form.querySelector('[data-qty-minus]');
        const plus = form.querySelector('[data-qty-plus]');
        if (!input) return;
        const submit = () => form.requestSubmit();
        minus?.addEventListener('click', function () {
            const v = Math.max(1, parseInt(input.value, 10) - 1);
            input.value = v;
            submit();
        });
        plus?.addEventListener('click', function () {
            input.value = Math.max(1, parseInt(input.value, 10) + 1);
            submit();
        });
        input.addEventListener('change', submit);
    });
});
</script>
@endpush
