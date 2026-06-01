<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReservaController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('cliente.perfil');
    }

    public function create(Evento $evento): View
    {
        abort_unless($evento->estado === 'publicado', 404);
        $evento->load('boletos');

        return view('cliente.reservas.create', compact('evento'));
    }

}
