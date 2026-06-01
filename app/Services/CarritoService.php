<?php

namespace App\Services;

use App\Models\Boleto;
use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Evento;
use App\Models\Reserva;
use App\Models\ReservaBoleto;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CarritoService
{
    public function obtenerActivo(User $usuario): Carrito
    {
        return Carrito::firstOrCreate(
            ['id_usuario' => $usuario->id_usuario, 'estado' => 'activo'],
            ['estado' => 'activo']
        );
    }

    public function contarUnidades(User $usuario): int
    {
        $carrito = Carrito::activo()
            ->where('id_usuario', $usuario->id_usuario)
            ->with('items')
            ->first();

        if (! $carrito) {
            return 0;
        }

        return (int) $carrito->items->sum('cantidad');
    }

    /**
     * @param  array<int, array{id: int|string, cantidad: int|string}>  $boletos
     */
    public function agregarDesdeEvento(User $usuario, Evento $evento, array $boletos): Carrito
    {
        $carrito = $this->obtenerActivo($usuario);

        DB::transaction(function () use ($carrito, $evento, $boletos) {
            foreach ($boletos as $item) {
                $cantidad = (int) ($item['cantidad'] ?? 0);
                if ($cantidad < 1) {
                    continue;
                }

                $boleto = Boleto::where('id_evento', $evento->id_evento)
                    ->lockForUpdate()
                    ->findOrFail($item['id']);

                $enCarrito = $this->cantidadEnCarrito($carrito, $boleto->id_boleto);
                $disponible = $boleto->disponibles() - $enCarrito;

                if ($cantidad > $disponible) {
                    throw new \RuntimeException(
                        "No hay suficientes boletos «{$boleto->nombre_tipo}» (máx. {$disponible} más en el carrito)."
                    );
                }

                $existente = $carrito->items()
                    ->where('id_boleto', $boleto->id_boleto)
                    ->first();

                if ($existente) {
                    $existente->update([
                        'cantidad' => $existente->cantidad + $cantidad,
                        'precio_unitario' => $boleto->precio,
                    ]);
                } else {
                    CarritoItem::create([
                        'id_carrito' => $carrito->id_carrito,
                        'id_evento' => $evento->id_evento,
                        'id_boleto' => $boleto->id_boleto,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $boleto->precio,
                    ]);
                }
            }

            if ($carrito->items()->count() === 0) {
                throw new \RuntimeException('Selecciona al menos un boleto.');
            }
        });

        return $carrito->fresh(['items.evento', 'items.boleto']);
    }

    public function actualizarCantidad(CarritoItem $item, int $cantidad): void
    {
        if ($cantidad < 1) {
            throw new \InvalidArgumentException('La cantidad debe ser al menos 1.');
        }

        DB::transaction(function () use ($item, $cantidad) {
            $item->load('carrito', 'boleto');
            $this->asegurarCarritoActivoDelUsuario($item->carrito);

            $boleto = Boleto::where('id_evento', $item->id_evento)
                ->lockForUpdate()
                ->findOrFail($item->id_boleto);

            $otrosEnCarrito = $this->cantidadEnCarrito($item->carrito, $boleto->id_boleto, $item->id_item);
            $disponible = $boleto->disponibles() - $otrosEnCarrito;

            if ($cantidad > $disponible) {
                throw new \RuntimeException("Solo quedan {$disponible} boletos disponibles para «{$boleto->nombre_tipo}».");
            }

            $item->update([
                'cantidad' => $cantidad,
                'precio_unitario' => $boleto->precio,
            ]);
        });
    }

    public function eliminarItem(CarritoItem $item): void
    {
        $this->asegurarCarritoActivoDelUsuario($item->carrito);
        $item->delete();
    }

    public function vaciar(Carrito $carrito): void
    {
        $this->asegurarCarritoActivoDelUsuario($carrito);
        $carrito->items()->delete();
    }

    public function total(Carrito $carrito): float
    {
        return (float) $carrito->items->sum(fn (CarritoItem $item) => $item->subtotal());
    }

    /**
     * @return Collection<int, Reserva>
     */
    public function checkout(User $usuario, Carrito $carrito): Collection
    {
        $this->asegurarCarritoActivoDelUsuario($carrito);

        if ($carrito->items()->count() === 0) {
            throw new \RuntimeException('Tu carrito está vacío.');
        }

        $carrito->load(['items.boleto', 'items.evento']);

        $reservas = collect();

        DB::transaction(function () use ($usuario, $carrito, &$reservas) {
            foreach ($carrito->items->groupBy('id_evento') as $idEvento => $items) {
                $total = 0;
                $lineas = [];

                foreach ($items as $item) {
                    $boleto = Boleto::where('id_evento', $idEvento)
                        ->lockForUpdate()
                        ->findOrFail($item->id_boleto);

                    if ($item->cantidad > $boleto->disponibles()) {
                        throw new \RuntimeException(
                            "Ya no hay stock suficiente para «{$boleto->nombre_tipo}» en {$item->evento->titulo}."
                        );
                    }

                    $subtotal = (float) $boleto->precio * $item->cantidad;
                    $total += $subtotal;
                    $lineas[] = [
                        'boleto' => $boleto,
                        'cantidad' => $item->cantidad,
                        'subtotal' => $subtotal,
                    ];
                }

                $reserva = Reserva::create([
                    'id_usuario' => $usuario->id_usuario,
                    'id_evento' => $idEvento,
                    'id_carrito' => $carrito->id_carrito,
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

                $reservas->push($reserva);
            }

            $carrito->update(['estado' => 'convertido']);
            $carrito->items()->delete();

            Carrito::create([
                'id_usuario' => $usuario->id_usuario,
                'estado' => 'activo',
            ]);
        });

        return $reservas;
    }

    public function totalCarritoCheckout(Carrito $carrito): float
    {
        return (float) Reserva::where('id_carrito', $carrito->id_carrito)
            ->where('estado', 'pendiente')
            ->sum('total');
    }

    private function cantidadEnCarrito(Carrito $carrito, int $idBoleto, ?int $excluirItemId = null): int
    {
        $query = $carrito->items()->where('id_boleto', $idBoleto);

        if ($excluirItemId) {
            $query->where('id_item', '!=', $excluirItemId);
        }

        return (int) $query->sum('cantidad');
    }

    private function asegurarCarritoActivoDelUsuario(Carrito $carrito): void
    {
        if ($carrito->estado !== 'activo') {
            throw new \RuntimeException('Este carrito ya no está activo.');
        }

        if ($carrito->id_usuario !== auth()->user()->id_usuario) {
            abort(403);
        }
    }
}
