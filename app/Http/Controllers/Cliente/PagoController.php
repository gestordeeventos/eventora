<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Models\Carrito;
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

    public function showCarrito(Carrito $carrito): View|RedirectResponse
    {
        $this->autorizarCarrito($carrito);
        abort_unless($carrito->estado === 'convertido', 404);

        $reservas = $this->reservasPendientesCarrito($carrito);

        if ($reservas->isEmpty()) {
            $pagadas = $this->reservasPagadasCarrito($carrito);
            if ($pagadas->isNotEmpty()) {
                return redirect()->route('cliente.compras.exito-carrito', $carrito);
            }

            return redirect()->route('cliente.carrito.index')->with('error', 'No hay una orden pendiente para este carrito.');
        }

        $total = $reservas->sum('total');

        return view('cliente.pago.carrito', compact('carrito', 'reservas', 'total'));
    }

    public function procesarCarrito(Request $request, Carrito $carrito): RedirectResponse
    {
        $this->autorizarCarrito($carrito);
        abort_unless($carrito->estado === 'convertido', 404);

        $reservas = $this->reservasPendientesCarrito($carrito);

        if ($reservas->isEmpty()) {
            return redirect()->route('cliente.compras.exito-carrito', $carrito);
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
            DB::transaction(function () use ($reservas, $numero) {
                foreach ($reservas as $reserva) {
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
                }
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        foreach ($reservas as $reserva) {
            $this->notificaciones->enviarConfirmacion($reserva->fresh());
        }

        return redirect()
            ->route('cliente.compras.exito-carrito', $carrito)
            ->with('success', '¡Pago procesado correctamente! Revisa tu correo para las confirmaciones.');
    }

    public function exitoCarrito(Carrito $carrito): View|RedirectResponse
    {
        $this->autorizarCarrito($carrito);

        $reservas = $this->reservasPagadasCarrito($carrito);
        abort_unless($reservas->isNotEmpty(), 404);

        return view('cliente.pago.exito-carrito', compact('carrito', 'reservas'));
    }

    private function reservasPendientesCarrito(Carrito $carrito)
    {
        return Reserva::where('id_carrito', $carrito->id_carrito)
            ->where('id_usuario', auth()->user()->id_usuario)
            ->where('estado', 'pendiente')
            ->with(['evento.tipoEvento', 'reservaBoletos.boleto'])
            ->orderBy('id_reserva')
            ->get();
    }

    private function reservasPagadasCarrito(Carrito $carrito)
    {
        return Reserva::where('id_carrito', $carrito->id_carrito)
            ->where('id_usuario', auth()->user()->id_usuario)
            ->where('estado', 'pagada')
            ->with(['evento', 'reservaBoletos.boleto'])
            ->orderBy('id_reserva')
            ->get();
    }

    private function autorizarCarrito(Carrito $carrito): void
    {
        abort_unless(auth()->user()->isCliente(), 403);
        abort_unless($carrito->id_usuario === auth()->user()->id_usuario, 403);
    }

    private function autorizarReserva(Reserva $reserva): void
    {
        abort_unless(auth()->user()->isCliente(), 403);
        abort_unless($reserva->id_usuario === auth()->user()->id_usuario, 403);
    }
}
