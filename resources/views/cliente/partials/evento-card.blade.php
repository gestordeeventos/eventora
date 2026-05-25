@props(['evento'])

@php
    $tipo = strtolower($evento->tipoEvento->nombre ?? 'evento');
    $mediaClass = match (true) {
        str_contains($tipo, 'acad') => 'evento-media-academico',
        str_contains($tipo, 'cult') => 'evento-media-cultural',
        str_contains($tipo, 'soc') => 'evento-media-social',
        default => 'evento-media-default',
    };
    $portada = $evento->portadaUrl();
@endphp
<article class="evento-card-pro">
    @if ($portada)
        <div class="evento-card-media evento-card-media-img" style="background-image: url('{{ $portada }}')" role="img" aria-label="Portada de {{ $evento->titulo }}"></div>
    @else
        <div class="evento-card-media {{ $mediaClass }}" aria-hidden="true"></div>
    @endif
    <span class="evento-tag">{{ strtoupper($evento->tipoEvento->nombre) }}</span>
    <h3 class="evento-card-title font-display">{{ $evento->titulo }}</h3>
    <p class="evento-card-desc">{{ Str::limit($evento->descripcion ?? 'Evento exclusivo Eventora.', 140) }}</p>
    <div class="evento-card-footer">
        <div class="evento-precio">
            @if ($evento->precioDesde())
                ${{ number_format($evento->precioDesde(), 2) }} <span>MXN</span>
            @else
                <span class="text-muted">Consultar precio</span>
            @endif
        </div>
        <div class="evento-card-actions">
            <a href="{{ route('eventos.show', $evento) }}" class="btn-evento-secondary">Ver detalles</a>
            @auth
                @if (auth()->user()->isCliente() && $evento->boletos->isNotEmpty())
                    <a href="{{ route('reservas.create', $evento) }}" class="btn-evento-primary">Comprar</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-evento-primary">Comprar</a>
            @endauth
        </div>
    </div>
</article>
