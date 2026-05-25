<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Boleto extends Model
{
    protected $table = 'boletos';

    protected $primaryKey = 'id_boleto';

    public $timestamps = false;

    protected $fillable = [
        'id_evento',
        'nombre_tipo',
        'precio',
        'cantidad_total',
        'cantidad_vendida',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'id_evento');
    }

    public function reservaBoletos(): HasMany
    {
        return $this->hasMany(ReservaBoleto::class, 'id_boleto');
    }

    public function disponibles(): int
    {
        return $this->cantidad_total - $this->cantidad_vendida;
    }
}
