<?php

namespace App\Http\Controllers;

use App\Models\Boleto;
use App\Models\Evento;
use App\Models\Reserva;
use App\Models\ReservaBoleto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function store(Request $request, Evento $evento): RedirectResponse
    {
        abort_unless($evento->estado === 'publicado', 404);

        $validated = $request->validate([
            'boletos' => ['required', 'array', 'min:1'],
            'boletos.*.id' => ['required', 'exists:boletos,id_boleto'],
            'boletos.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $reserva = null;

        try {
            DB::transaction(function () use ($validated, $evento, &$reserva) {
                $total = 0;
                $lineas = [];

                foreach ($validated['boletos'] as $item) {
                    if (empty($item['cantidad']) || (int) $item['cantidad'] < 1) {
                        continue;
                    }
                    $boleto = Boleto::where('id_evento', $evento->id_evento)
                        ->lockForUpdate()
                        ->findOrFail($item['id']);

                    $cantidad = (int) $item['cantidad'];
                    if ($cantidad > $boleto->disponibles()) {
                        throw new \RuntimeException("No hay suficientes boletos {$boleto->nombre_tipo}.");
                    }

                    $subtotal = $boleto->precio * $cantidad;
                    $total += $subtotal;
                    $lineas[] = ['boleto' => $boleto, 'cantidad' => $cantidad, 'subtotal' => $subtotal];
                }

                if (empty($lineas)) {
                    throw new \RuntimeException('Selecciona al menos un boleto.');
                }

                $reserva = Reserva::create([
                    'id_usuario' => auth()->user()->id_usuario,
                    'id_evento' => $evento->id_evento,
                    'estado' => 'pendiente',
                    'total' => $total,
                ]);

                foreach ($lineas as $linea) {
                    ReservaBoleto::create([
                        'id_reserva' => $reserva->id_reserva,
                        'id_boleto' => $linea['boleto']->id_boleto,
                        'cantidad' => $linea['cantidad'],
                        'precio_unitario' => $linea['boleto']->precio,
                        'subtotal' => $linea['subtotal'],
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('cliente.pago.show', $reserva)
            ->with('success', 'Revisa tu orden y completa el pago simulado.');
    }
}
