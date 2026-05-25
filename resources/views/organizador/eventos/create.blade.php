@extends('layouts.app')

@section('title', 'Crear evento')

@section('content')
<h1 class="page-title font-display">Crear evento</h1>

<form method="POST" action="{{ route('organizador.eventos.store') }}" class="card-eventora">
    <div class="card-eventora-body">
        @csrf
        @if ($errors->any())
            <div class="alert alert-error mb-3">@foreach ($errors->all() as $e) {{ $e }} @endforeach</div>
        @endif

        <div class="campo">
            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" value="{{ old('titulo') }}" required>
        </div>
        <div class="campo">
            <label for="id_tipo_evento">Tipo</label>
            <select id="id_tipo_evento" name="id_tipo_evento" required>
                @foreach ($tipos as $tipo)
                    <option value="{{ $tipo->id_tipo_evento }}" @selected(old('id_tipo_evento') == $tipo->id_tipo_evento)>{{ $tipo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
        </div>
        <div class="fila-dos">
            <div class="campo">
                <label for="fecha_inicio">Inicio</label>
                <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
            </div>
            <div class="campo">
                <label for="fecha_fin">Fin (opcional)</label>
                <input type="datetime-local" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin') }}">
            </div>
        </div>
        <div class="fila-dos">
            <div class="campo">
                <label for="lugar">Lugar</label>
                <input type="text" id="lugar" name="lugar" value="{{ old('lugar') }}" required>
            </div>
            <div class="campo">
                <label for="ciudad">Ciudad</label>
                <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad') }}">
            </div>
        </div>
        <div class="fila-dos">
            <div class="campo">
                <label for="capacidad_max">Capacidad</label>
                <input type="number" id="capacidad_max" name="capacidad_max" value="{{ old('capacidad_max', 100) }}" min="1" required>
            </div>
            <div class="campo">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    <option value="borrador">Borrador</option>
                    <option value="publicado">Publicado</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn-primary" style="width:auto;padding:12px 32px;">Guardar evento</button>
    </div>
</form>
@endsection
