<?php

namespace App\Http\Controllers\Organizador;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\TipoEvento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventoController extends Controller
{
    public function index(): View
    {
        $eventos = auth()->user()
            ->eventosOrganizados()
            ->with('tipoEvento')
            ->latest()
            ->paginate(10);

        return view('organizador.eventos.index', compact('eventos'));
    }

    public function create(): View
    {
        return view('organizador.eventos.create', [
            'tipos' => TipoEvento::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'id_tipo_evento' => ['required', 'exists:tipos_evento,id_tipo_evento'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'lugar' => ['required', 'string', 'max:200'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'capacidad_max' => ['required', 'integer', 'min:1'],
            'estado' => ['required', 'in:borrador,publicado'],
        ]);

        auth()->user()->eventosOrganizados()->create([
            ...$validated,
            'id_organizador' => auth()->id(),
        ]);

        return redirect()->route('organizador.eventos.index')->with('success', 'Evento creado.');
    }
}
