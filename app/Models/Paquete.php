<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paquete extends Model
{
    protected $table = 'paquetes';

    protected $primaryKey = 'id_paquete';

    protected $fillable = ['nombre', 'descripcion', 'precio', 'incluye', 'activo'];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function eventos(): BelongsToMany
    {
        return $this->belongsToMany(Evento::class, 'evento_paquete', 'id_paquete', 'id_evento');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'id_paquete');
    }
}
