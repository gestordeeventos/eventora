<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $primaryKey = 'id_reserva';

    protected $fillable = [
        'id_usuario',
        'id_evento',
        'id_paquete',
        'estado',
        'total',
        'notas',
        'codigo_ticket',
        'pagado_at',
        'metodo_pago',
        'ultimos4_tarjeta',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'pagado_at' => 'datetime',
    ];

    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagada');
    }

    public function estaPagada(): bool
    {
        return $this->estado === 'pagada';
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'id_evento');
    }

    public function paquete(): BelongsTo
    {
        return $this->belongsTo(Paquete::class, 'id_paquete');
    }

    public function reservaBoletos(): HasMany
    {
        return $this->hasMany(ReservaBoleto::class, 'id_reserva');
    }

    public function getRouteKeyName(): string
    {
        return 'id_reserva';
    }
}
