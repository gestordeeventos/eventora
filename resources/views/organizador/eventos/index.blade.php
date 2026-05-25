@extends('layouts.app')

@section('title', 'Mis eventos')

@section('content')
<h1 class="page-title font-display">Mis eventos</h1>
<a href="{{ route('organizador.eventos.create') }}" class="btn-gold mb-4 d-inline-block">+ Nuevo evento</a>

<div class="card-eventora">
    <div class="card-eventora-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Título</th><th>Tipo</th><th>Fecha</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse ($eventos as $evento)
                    <tr>
                        <td>{{ $evento->titulo }}</td>
                        <td>{{ $evento->tipoEvento->nombre }}</td>
                        <td>{{ $evento->fecha_inicio->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($evento->estado) }}</td>
                        <td><a href="{{ route('eventos.show', $evento) }}" class="btn-outline btn-sm">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Sin eventos creados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $eventos->links() }}
@endsection
