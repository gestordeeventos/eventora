<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Models\Evento;
use App\Models\Reserva;
use App\Services\CompraNotificacionService;
use App\Services\TicketService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PagoController extends Controller
{
    public function __construct(
        private TicketService $tickets,
        private CompraNotificacionService $notificaciones
    ) {}

    public function show(Reserva $reserva): View|RedirectResponse
    {
        $this->autorizarReserva($reserva);

        if ($reserva->estado === 'pagada') {
            return redirect()->route('cliente.compras.exito', $reserva);
        }

        $reserva->load(['evento.tipoEvento', 'reservaBoletos.boleto']);

        return view('cliente.pago.index', compact('reserva'));
    }

    public function procesar(Request $request, Reserva $reserva): RedirectResponse
    {
        $this->autorizarReserva($reserva);

        if ($reserva->estado === 'pagada') {
            return redirect()->route('cliente.compras.exito', $reserva);
        }

        $validated = $request->validate([
            'titular' => ['required', 'string', 'max:120'],
            'numero_tarjeta' => ['required', 'string'],
            'vencimiento' => ['required', 'regex:/^\d{2}\/\d{2}$/'],
            'cvv' => ['required', 'digits_between:3,4'],
        ]);

        $numero = preg_replace('/\D/', '', $validated['numero_tarjeta']);
        if (strlen($numero) < 15 || strlen($numero) > 16) {
            return back()->withInput()->withErrors(['numero_tarjeta' => 'Número de tarjeta inválido.']);
        }

        if (! preg_match('/^(4|5)/', $numero)) {
            return back()->withInput()->withErrors(['numero_tarjeta' => 'Solo se simulan tarjetas Visa o Mastercard.']);
        }

        [$mes, $anio] = explode('/', $validated['vencimiento']);
        $expira = Carbon::createFromDate(2000 + (int) $anio, (int) $mes, 1)->endOfMonth();
        if ($expira->isPast()) {
            return back()->withInput()->withErrors(['vencimiento' => 'La tarjeta está vencida.']);
        }

        try {
            DB::transaction(function () use ($reserva, $numero) {
                $reserva->load('reservaBoletos.boleto');

                foreach ($reserva->reservaBoletos as $linea) {
                    $boleto = Boleto::where('id_evento', $reserva->id_evento)
                        ->lockForUpdate()
                        ->findOrFail($linea->id_boleto);

                    if ($linea->cantidad > $boleto->disponibles()) {
                        throw new \RuntimeException('Ya no hay boletos disponibles para completar el pago.');
                    }

                    $boleto->increment('cantidad_vendida', $linea->cantidad);
                }

                $reserva->update([
                    'estado' => 'pagada',
                    'codigo_ticket' => $this->tickets->generarCodigo(),
                    'pagado_at' => now(),
                    'metodo_pago' => 'tarjeta_simulada',
                    'ultimos4_tarjeta' => substr($numero, -4),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $reserva->refresh();
        $this->notificaciones->enviarConfirmacion($reserva);

        return redirect()
            ->route('cliente.compras.exito', $reserva)
            ->with('success', '¡Pago procesado correctamente! Revisa tu correo para la confirmación.');
    }

    public function exito(Reserva $reserva): View|RedirectResponse
    {
        $this->autorizarReserva($reserva);
        abort_unless($reserva->estado === 'pagada', 404);

        $reserva->load(['evento.tipoEvento', 'reservaBoletos.boleto']);

        return view('cliente.pago.exito', compact('reserva'));
    }

    public function ticket(Reserva $reserva): Response
    {
        $this->autorizarReserva($reserva);
        abort_unless($reserva->estado === 'pagada' && $reserva->codigo_ticket, 404);

        return $this->tickets->respuestaPdf($reserva, true);
    }

    private function autorizarReserva(Reserva $reserva): void
    {
        abort_unless(auth()->user()->isCliente(), 403);
        abort_unless($reserva->id_usuario === auth()->user()->id_usuario, 403);
    }
}
