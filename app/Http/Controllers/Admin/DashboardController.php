<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Models\Evento;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $eventos = Evento::with(['tipoEvento', 'boletos'])
            ->latest()
            ->get();

        return view('admin.dashboard.index', [
            'stats' => [
                'eventos' => Evento::count(),
                'boletos_vendidos' => (int) Boleto::sum('cantidad_vendida'),
                'clientes' => User::where('rol', 'cliente')->count(),
            ],
            'eventos' => $eventos,
        ]);
    }
}
