<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Evento;
use App\Services\CarritoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarritoController extends Controller
{
    public function __construct(private CarritoService $carrito) {}

    public function index(): View
    {
        $carrito = $this->carrito->obtenerActivo(auth()->user());
        $carrito->load(['items.evento.tipoEvento', 'items.boleto']);
        $total = $this->carrito->total($carrito);

        return view('cliente.carrito.index', compact('carrito', 'total'));
    }

    public function agregar(Request $request, Evento $evento): RedirectResponse
    {
        abort_unless($evento->estado === 'publicado', 404);

        $validated = $request->validate([
            'boletos' => ['required', 'array', 'min:1'],
            'boletos.*.id' => ['required', 'exists:boletos,id_boleto'],
            'boletos.*.cantidad' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $this->carrito->agregarDesdeEvento(auth()->user(), $evento, $validated['boletos']);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('cliente.carrito.index')
            ->with('success', 'Boletos agregados al carrito.');
    }

    public function actualizar(Request $request, CarritoItem $item): RedirectResponse
    {
        $this->autorizarItem($item);

        $validated = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->carrito->actualizarCantidad($item, (int) $validated['cantidad']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cantidad actualizada.');
    }

    public function eliminar(CarritoItem $item): RedirectResponse
    {
        $this->autorizarItem($item);
        $this->carrito->eliminarItem($item);

        return back()->with('success', 'Artículo eliminado del carrito.');
    }

    public function vaciar(): RedirectResponse
    {
        $carrito = $this->carrito->obtenerActivo(auth()->user());
        $this->carrito->vaciar($carrito);

        return back()->with('success', 'Carrito vaciado.');
    }

    public function checkout(): RedirectResponse
    {
        $carrito = $this->carrito->obtenerActivo(auth()->user());

        try {
            $this->carrito->checkout(auth()->user(), $carrito);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $carritoCheckout = $carrito->fresh();

        return redirect()
            ->route('cliente.pago.carrito', $carritoCheckout)
            ->with('success', 'Revisa tu orden y completa el pago.');
    }

    private function autorizarItem(CarritoItem $item): void
    {
        abort_unless(auth()->user()->isCliente(), 403);
        $item->load('carrito');
        abort_unless($item->carrito->id_usuario === auth()->user()->id_usuario, 403);
        abort_unless($item->carrito->estaActivo(), 403);
    }
}
