<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventoController extends Controller
{
    public function index(Request $request): View
    {
        $eventos = Evento::publicados()
            ->with(['tipoEvento', 'boletos'])
            ->buscar($request->input('q'))
            ->where('fecha_inicio', '>=', now()->subDay())
            ->orderBy('fecha_inicio')
            ->paginate(9)
            ->withQueryString();

        return view('cliente.eventos.index', compact('eventos'));
    }

    public function show(Evento $evento): View
    {
        abort_unless(
            $evento->estado === 'publicado'
            || auth()->id() === $evento->id_organizador
            || auth()->user()?->isAdmin(),
            404
        );

        $evento->load(['tipoEvento', 'organizador', 'boletos', 'paquetes']);

        return view('cliente.eventos.show', compact('evento'));
    }
}
