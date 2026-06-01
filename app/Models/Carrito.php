<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrito extends Model
{
    protected $table = 'carritos';

    protected $primaryKey = 'id_carrito';

    protected $fillable = [
        'id_usuario',
        'estado',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CarritoItem::class, 'id_carrito', 'id_carrito');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'id_carrito', 'id_carrito');
    }

    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    public function getRouteKeyName(): string
    {
        return 'id_carrito';
    }
}
