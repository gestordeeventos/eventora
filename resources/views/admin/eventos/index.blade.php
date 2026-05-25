@extends('layouts.admin')

@push('vite-extra')
    @vite(['resources/js/admin-portada.js'])
@endpush

@section('title', 'Gestión de eventos')

@section('content')
@php
    $editando = $eventoEditando ?? null;
    $enEdicion = $editando || session('editing_id');
    $eventoId = old('_evento_id', session('editing_id', $editando?->id_evento));
    $boletoEdit = $editando?->boletos->first();
@endphp

<header class="admin-page-header">
    <div>
        <h1 class="admin-page-title font-display">Administración de eventos</h1>
        <p class="admin-page-subtitle">Visualiza, edita o agrega nuevos eventos al catálogo público.</p>
    </div>
</header>

<div class="admin-split-grid">
    <div class="admin-card admin-form-card" id="admin-form-panel">
        <h2 class="admin-card-title font-display" id="admin-form-title">
            {{ $enEdicion ? 'Editar evento' : 'Crear nuevo evento' }}
        </h2>
        @if ($enEdicion && $editando)
            <p class="admin-form-editing-name" id="admin-form-subtitle">{{ $editando->titulo }}</p>
        @else
            <p class="admin-form-editing-name" id="admin-form-subtitle" hidden></p>
        @endif

        <form method="POST"
              id="admin-evento-form"
              action="{{ $enEdicion && $eventoId ? route('admin.eventos.update', $eventoId) : route('admin.eventos.store') }}"
              class="admin-form">
            @csrf
            <span id="admin-method-field">
                @if ($enEdicion && $eventoId)
                    @method('PUT')
                @endif
            </span>
            <input type="hidden" name="_evento_id" id="_evento_id" value="{{ $eventoId }}">

            @if ($errors->any())
                <div class="alert alert-error mb-3">
                    <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="campo">
                <label for="titulo">Título del evento</label>
                <input type="text" id="titulo" name="titulo"
                       value="{{ old('titulo', $editando?->titulo) }}" required>
            </div>
            <div class="campo">
                <label for="id_tipo_evento">Tipo de evento</label>
                <select id="id_tipo_evento" name="id_tipo_evento" required>
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo->id_tipo_evento }}"
                            @selected(old('id_tipo_evento', $editando?->id_tipo_evento) == $tipo->id_tipo_evento)>
                            {{ $tipo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="fila-dos">
                <div class="campo">
                    <label for="precio">Precio ($)</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0"
                           value="{{ old('precio', $boletoEdit?->precio) }}" required>
                </div>
                <div class="campo">
                    <label for="capacidad_max">Cupo máx.</label>
                    <input type="number" id="capacidad_max" name="capacidad_max" min="1"
                           value="{{ old('capacidad_max', $editando?->capacidad_max ?? 100) }}" required>
                </div>
            </div>
            <div class="fila-dos">
                <div class="campo">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" name="fecha"
                           value="{{ old('fecha', $editando?->fecha_inicio?->format('Y-m-d')) }}"
                           min="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="campo">
                    <label for="hora">Hora de acceso</label>
                    <input type="time" id="hora" name="hora"
                           value="{{ old('hora', $editando?->fecha_inicio?->format('H:i') ?? '18:00') }}" required>
                </div>
            </div>
            <p class="admin-datetime-hint">No se permiten fechas u horas anteriores al momento actual.</p>
            <div class="campo">
                <label for="lugar">Lugar / dirección</label>
                <input type="text" id="lugar" name="lugar"
                       value="{{ old('lugar', $editando?->lugar) }}"
                       placeholder="Ej. Auditorio Central" required>
            </div>
            <div class="campo portada-campo">
                <label for="portada_file">Imagen de portada</label>
                <p class="admin-datetime-hint">JPG o PNG. Recorta en cuadrado con zoom y arrastre.</p>
                <input type="file" id="portada_file" accept="image/jpeg,image/jpg,image/png">
                <input type="hidden" name="portada_data" id="portada_data" value="">
                <input type="hidden" name="portada_remove" id="portada_remove" value="0">

                <div id="portada-preview-wrap" class="portada-preview-wrap" @if(! $editando?->portadaUrl()) hidden @endif>
                    <img id="portada-preview" alt="Vista previa portada"
                         src="{{ $editando?->portadaUrl() }}"
                         data-initial="{{ $editando?->portadaUrl() }}">
                    <button type="button" class="btn-admin-danger-outline btn-sm" id="portada-quitar">Quitar imagen</button>
                </div>

                <div id="portada-crop-wrap" class="portada-crop-wrap" hidden>
                    <div class="portada-crop-container">
                        <img id="portada-crop-image" alt="Recortar portada">
                    </div>
                    <p class="admin-datetime-hint">Arrastra para mover · Rueda o pellizco para zoom</p>
                    <button type="button" class="btn-admin-gold btn-sm" id="portada-aplicar-recorte">Aplicar recorte cuadrado</button>
                </div>
            </div>

            <div class="campo">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4">{{ old('descripcion', $editando?->descripcion) }}</textarea>
            </div>
            <div class="admin-form-buttons">
                <button type="button" class="btn-admin-secondary" id="btn-cancelar-edicion" @if(! $enEdicion) hidden @endif>
                    Cancelar
                </button>
                <button type="submit" class="btn-admin-primary" id="btn-submit-evento">
                    {{ $enEdicion ? 'Actualizar cambios' : 'Guardar evento' }}
                </button>
            </div>
        </form>
    </div>

    <div class="admin-card admin-list-card">
        <h2 class="admin-card-title font-display">Lista completa de eventos</h2>
        <div class="admin-table-wrap">
            <table class="admin-table admin-table-compact">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Tipo</th>
                        <th>Precio</th>
                        <th>Fecha / hora</th>
                        <th>Lugar</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($eventos as $evento)
                        @php
                            $boleto = $evento->boletos->first();
                            $precio = $boleto?->precio ?? $evento->precioDesde();
                        @endphp
                        <tr class="admin-event-row {{ (string) $eventoId === (string) $evento->id_evento ? 'row-editing' : '' }}"
                            data-evento-id="{{ $evento->id_evento }}">
                            <td><strong>{{ $evento->titulo }}</strong></td>
                            <td>{{ $evento->tipoEvento->nombre }}</td>
                            <td>{{ $precio ? '$'.number_format($precio, 2) : '—' }}</td>
                            <td>
                                @if ($evento->fecha_inicio)
                                    {{ $evento->fecha_inicio->format('d/m/Y H:i') }} hrs
                                @else
                                    S/F S/H hrs
                                @endif
                            </td>
                            <td>{{ $evento->lugar ?: 'Por confirmar' }}</td>
                            <td class="admin-actions-cell">
                                <button type="button"
                                        class="btn-admin-secondary btn-sm btn-editar-evento"
                                        data-edit-evento
                                        data-id="{{ $evento->id_evento }}"
                                        data-update-url="{{ route('admin.eventos.update', $evento) }}"
                                        data-titulo="{{ htmlspecialchars($evento->titulo, ENT_QUOTES, 'UTF-8') }}"
                                        data-tipo="{{ $evento->id_tipo_evento }}"
                                        data-precio="{{ $boleto?->precio ?? '' }}"
                                        data-cupo="{{ $evento->capacidad_max }}"
                                        data-fecha="{{ $evento->fecha_inicio?->format('Y-m-d') }}"
                                        data-hora="{{ $evento->fecha_inicio?->format('H:i') }}"
                                        data-lugar="{{ htmlspecialchars($evento->lugar ?? '', ENT_QUOTES, 'UTF-8') }}"
                                        data-descripcion="{{ htmlspecialchars($evento->descripcion ?? '', ENT_QUOTES, 'UTF-8') }}"
                                        data-imagen="{{ $evento->portadaUrl() ?? '' }}">
                                    Editar
                                </button>
                                <button type="button" class="btn-admin-danger-outline btn-sm"
                                        data-delete-event
                                        data-delete-url="{{ route('admin.eventos.destroy', $evento) }}"
                                        data-event-title="{{ $evento->titulo }}">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay eventos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('admin-evento-form');
    const methodField = document.getElementById('admin-method-field');
    const eventoIdInput = document.getElementById('_evento_id');
    const formTitle = document.getElementById('admin-form-title');
    const formSubtitle = document.getElementById('admin-form-subtitle');
    const btnSubmit = document.getElementById('btn-submit-evento');
    const btnCancelar = document.getElementById('btn-cancelar-edicion');
    const formPanel = document.getElementById('admin-form-panel');
    const fechaInput = document.getElementById('fecha');
    const horaInput = document.getElementById('hora');
    const storeUrl = @json(route('admin.eventos.store'));

    function ahoraLocal() {
        const d = new Date();
        return {
            fecha: d.toISOString().slice(0, 10),
            hora: String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0'),
        };
    }

    function actualizarMinimosFechaHora() {
        const { fecha, hora } = ahoraLocal();
        fechaInput.min = fecha;
        if (fechaInput.value === fecha) {
            horaInput.min = hora;
        } else {
            horaInput.removeAttribute('min');
        }
    }

    fechaInput.addEventListener('change', actualizarMinimosFechaHora);
    horaInput.addEventListener('change', actualizarMinimosFechaHora);
    actualizarMinimosFechaHora();

    function marcarFilaActiva(id) {
        document.querySelectorAll('.admin-event-row').forEach(row => {
            row.classList.toggle('row-editing', row.dataset.eventoId === String(id));
        });
    }

    function entrarModoCrear() {
        form.action = storeUrl;
        methodField.innerHTML = '';
        eventoIdInput.value = '';
        formTitle.textContent = 'Crear nuevo evento';
        formSubtitle.hidden = true;
        formSubtitle.textContent = '';
        btnSubmit.textContent = 'Guardar evento';
        btnCancelar.hidden = true;
        formPanel.classList.remove('is-editing');
        marcarFilaActiva(null);
        form.reset();
        document.getElementById('capacidad_max').value = '100';
        document.getElementById('hora').value = '18:00';
        actualizarMinimosFechaHora();
        if (window.eventoPortada) {
            window.eventoPortada.reset();
        }
    }

    function entrarModoEditar(btn) {
        form.action = btn.dataset.updateUrl;
        methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';

        eventoIdInput.value = btn.dataset.id;
        document.getElementById('titulo').value = btn.dataset.titulo;
        document.getElementById('id_tipo_evento').value = btn.dataset.tipo;
        document.getElementById('precio').value = btn.dataset.precio;
        document.getElementById('capacidad_max').value = btn.dataset.cupo;
        document.getElementById('fecha').value = btn.dataset.fecha || '';
        document.getElementById('hora').value = btn.dataset.hora || '';
        document.getElementById('lugar').value = btn.dataset.lugar || '';
        document.getElementById('descripcion').value = btn.dataset.descripcion || '';
        if (window.eventoPortada) {
            window.eventoPortada.loadExisting(btn.dataset.imagen || '');
        }

        formTitle.textContent = 'Editar evento';
        formSubtitle.textContent = btn.dataset.titulo;
        formSubtitle.hidden = false;
        btnSubmit.textContent = 'Actualizar cambios';
        btnCancelar.hidden = false;
        formPanel.classList.add('is-editing');
        marcarFilaActiva(btn.dataset.id);
        actualizarMinimosFechaHora();
        formPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    document.querySelectorAll('[data-edit-evento]').forEach(btn => {
        btn.addEventListener('click', () => entrarModoEditar(btn));
    });

    btnCancelar.addEventListener('click', entrarModoCrear);

    @if ($enEdicion && $eventoId)
        formPanel.classList.add('is-editing');
        actualizarMinimosFechaHora();
    @endif
});
</script>
@endpush
