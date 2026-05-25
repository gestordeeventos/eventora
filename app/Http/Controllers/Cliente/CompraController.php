<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CompraController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user->isCliente(), 403);

        $compras = $user->reservas()
            ->pagadas()
            ->with(['evento.tipoEvento', 'reservaBoletos.boleto'])
            ->orderByDesc('pagado_at')
            ->orderByDesc('id_reserva')
            ->paginate(10);

        $pendientes = $user->reservas()
            ->where('estado', 'pendiente')
            ->with(['evento', 'reservaBoletos.boleto'])
            ->orderByDesc('created_at')
            ->get();

        return view('cliente.compras.index', compact('compras', 'pendientes'));
    }
}
