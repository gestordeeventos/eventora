<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarritoItem extends Model
{
    protected $table = 'carrito_items';

    protected $primaryKey = 'id_item';

    protected $fillable = [
        'id_carrito',
        'id_evento',
        'id_boleto',
        'cantidad',
        'precio_unitario',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    public function carrito(): BelongsTo
    {
        return $this->belongsTo(Carrito::class, 'id_carrito', 'id_carrito');
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'id_evento');
    }

    public function boleto(): BelongsTo
    {
        return $this->belongsTo(Boleto::class, 'id_boleto');
    }

    public function subtotal(): float
    {
        return (float) $this->precio_unitario * $this->cantidad;
    }

    public function getRouteKeyName(): string
    {
        return 'id_item';
    }
}
