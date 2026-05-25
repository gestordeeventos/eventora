<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Paquete;
use App\Models\TipoEvento;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $eventosDestacados = Evento::publicados()
            ->with(['tipoEvento', 'boletos'])
            ->where('fecha_inicio', '>=', now())
            ->orderBy('fecha_inicio')
            ->take(3)
            ->get();

        return view('cliente.home', [
            'eventosDestacados' => $eventosDestacados,
            'stats' => [
                'eventos' => Evento::publicados()->count(),
                'paquetes' => Paquete::where('activo', true)->count(),
                'tipos' => TipoEvento::where('activo', true)->count(),
            ],
        ]);
    }
}
