<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Models\Evento;
use App\Models\Reserva;
use App\Models\TipoEvento;
use App\Services\EventoPortadaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventoController extends Controller
{
    public function __construct(
        private EventoPortadaService $portadaService
    ) {}

    public function index(Request $request): View
    {
        $eventos = Evento::with(['tipoEvento', 'boletos'])
            ->latest()
            ->get();

        $eventoEditando = null;
        $editId = $request->query('edit') ?? session('editing_id') ?? old('_evento_id');
        if ($editId) {
            $eventoEditando = Evento::with('boletos')->find($editId);
        }

        return view('admin.eventos.index', [
            'eventos' => $eventos,
            'tipos' => TipoEvento::where('activo', true)->orderBy('nombre')->get(),
            'eventoEditando' => $eventoEditando,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEvento($request);
        $imagenUrl = $this->procesarPortada($request);

        DB::transaction(function () use ($validated, $imagenUrl) {
            $evento = Evento::create([
                'id_organizador' => auth()->id(),
                'id_tipo_evento' => $validated['id_tipo_evento'],
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'],
                'lugar' => $validated['lugar'],
                'ciudad' => $validated['ciudad'] ?? null,
                'capacidad_max' => $validated['capacidad_max'],
                'estado' => 'publicado',
                'imagen_url' => $imagenUrl,
                'imagen_updated_at' => $imagenUrl ? now() : null,
            ]);

            Boleto::create([
                'id_evento' => $evento->id_evento,
                'nombre_tipo' => 'General',
                'precio' => $validated['precio'],
                'cantidad_total' => $validated['capacidad_max'],
            ]);
        });

        return redirect()
            ->route('admin.eventos.index')
            ->with('success', '¡Evento creado con éxito con todos sus datos logísticos!');
    }

    public function update(Request $request, Evento $evento): RedirectResponse
    {
        $validated = $this->validateEvento($request, $evento);
        $rutaAnterior = $evento->imagen_url;
        $imagenUrl = $this->procesarPortada($request, $evento);

        DB::transaction(function () use ($validated, $evento, $imagenUrl, $rutaAnterior) {
            $evento->update([
                'id_tipo_evento' => $validated['id_tipo_evento'],
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'],
                'lugar' => $validated['lugar'],
                'ciudad' => $validated['ciudad'] ?? null,
                'capacidad_max' => $validated['capacidad_max'],
                'imagen_url' => $imagenUrl,
                'imagen_updated_at' => $imagenUrl !== $rutaAnterior
                    ? now()
                    : $evento->imagen_updated_at,
            ]);

            $boleto = $evento->boletos()->first();
            if ($boleto) {
                $vendidos = $boleto->cantidad_vendida;
                $boleto->update([
                    'precio' => $validated['precio'],
                    'cantidad_total' => max($validated['capacidad_max'], $vendidos),
                ]);
            } else {
                Boleto::create([
                    'id_evento' => $evento->id_evento,
                    'nombre_tipo' => 'General',
                    'precio' => $validated['precio'],
                    'cantidad_total' => $validated['capacidad_max'],
                ]);
            }
        });

        return redirect()
            ->route('admin.eventos.index')
            ->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy(Evento $evento): RedirectResponse
    {
        if (Reserva::where('id_evento', $evento->id_evento)->exists()) {
            return back()->with('error', 'No se puede eliminar: el evento tiene reservas asociadas.');
        }

        $this->portadaService->eliminarDeEvento($evento);
        $evento->delete();

        return redirect()
            ->route('admin.eventos.index')
            ->with('success', 'Evento eliminado permanentemente.');
    }

    private function procesarPortada(Request $request, ?Evento $evento = null): ?string
    {
        try {
            if ($request->input('portada_remove') === '1') {
                if ($evento) {
                    $this->portadaService->eliminarDeEvento($evento);
                }

                return null;
            }

            if ($request->filled('portada_data')) {
                return $this->portadaService->guardarDesdeBase64(
                    $request->input('portada_data'),
                    $evento?->imagen_url
                );
            }

            return $evento?->imagen_url;
        } catch (\InvalidArgumentException $e) {
            $redirect = $evento
                ? route('admin.eventos.index', ['edit' => $evento->id_evento])
                : route('admin.eventos.index');

            throw ValidationException::withMessages([
                'portada_file' => $e->getMessage(),
            ])->redirectTo($redirect);
        }
    }

    private function validateEvento(Request $request, ?Evento $evento = null): array
    {
        try {
            $validated = $request->validate([
                'titulo' => ['required', 'string', 'max:150'],
                'id_tipo_evento' => ['required', 'exists:tipos_evento,id_tipo_evento'],
                'precio' => ['required', 'numeric', 'min:0'],
                'capacidad_max' => ['required', 'integer', 'min:1'],
                'fecha' => ['required', 'date', 'after_or_equal:today'],
                'hora' => ['required', 'date_format:H:i'],
                'lugar' => ['required', 'string', 'max:200'],
                'ciudad' => ['nullable', 'string', 'max:100'],
                'descripcion' => ['nullable', 'string'],
                'portada_data' => ['nullable', 'string'],
                'portada_remove' => ['nullable', 'in:0,1'],
            ]);
        } catch (ValidationException $e) {
            if ($evento) {
                throw ValidationException::withMessages($e->errors())
                    ->redirectTo(route('admin.eventos.index', ['edit' => $evento->id_evento]));
            }
            throw $e;
        }

        $inicio = Carbon::parse("{$validated['fecha']} {$validated['hora']}");
        if ($inicio->lt(now())) {
            $messages = [
                'fecha' => 'La fecha y hora no pueden ser anteriores al momento actual.',
                'hora' => 'Selecciona una hora válida para la fecha elegida.',
            ];
            if ($evento) {
                throw ValidationException::withMessages($messages)
                    ->redirectTo(route('admin.eventos.index', ['edit' => $evento->id_evento]));
            }
            throw ValidationException::withMessages($messages);
        }

        $validated['fecha_inicio'] = $inicio;
        unset($validated['fecha'], $validated['hora'], $validated['portada_data'], $validated['portada_remove']);

        return $validated;
    }
}
