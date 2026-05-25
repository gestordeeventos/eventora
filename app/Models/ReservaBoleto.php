<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservaBoleto extends Model
{
    protected $table = 'reserva_boletos';

    public $timestamps = false;

    protected $fillable = [
        'id_reserva',
        'id_boleto',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'id_reserva');
    }

    public function boleto(): BelongsTo
    {
        return $this->belongsTo(Boleto::class, 'id_boleto');
    }
}
