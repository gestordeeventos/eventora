<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evento extends Model
{
    protected $table = 'eventos';

    protected $primaryKey = 'id_evento';

    protected $fillable = [
        'id_organizador',
        'id_tipo_evento',
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'lugar',
        'ciudad',
        'capacidad_max',
        'estado',
        'imagen_url',
        'imagen_updated_at',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'imagen_updated_at' => 'datetime',
    ];

    public function organizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_organizador', 'id_usuario');
    }

    public function tipoEvento(): BelongsTo
    {
        return $this->belongsTo(TipoEvento::class, 'id_tipo_evento');
    }

    public function paquetes(): BelongsToMany
    {
        return $this->belongsToMany(Paquete::class, 'evento_paquete', 'id_evento', 'id_paquete');
    }

    public function boletos(): HasMany
    {
        return $this->hasMany(Boleto::class, 'id_evento');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'id_evento');
    }

    public function getRouteKeyName(): string
    {
        return 'id_evento';
    }

    public function portadaUrl(): ?string
    {
        if (! $this->imagen_url) {
            return null;
        }

        if (str_starts_with($this->imagen_url, 'http://') || str_starts_with($this->imagen_url, 'https://')) {
            return $this->imagen_url;
        }

        return asset('storage/'.$this->imagen_url);
    }

    public function tienePortada(): bool
    {
        return filled($this->imagen_url);
    }

    public function precioDesde(): ?float
    {
        $min = $this->boletos->min('precio');

        return $min !== null ? (float) $min : null;
    }

    public function cupoDisponible(): int
    {
        if ($this->relationLoaded('boletos') && $this->boletos->isNotEmpty()) {
            return $this->boletos->sum(fn ($b) => $b->disponibles());
        }

        return $this->capacidad_max;
    }

    public function scopePublicados($query)
    {
        return $query->where('estado', 'publicado');
    }

    public function scopeBuscar($query, ?string $termino)
    {
        if (! $termino) {
            return $query;
        }

        $termino = '%'.mb_strtolower($termino).'%';

        return $query->where(function ($q) use ($termino) {
            $q->whereRaw('LOWER(titulo) LIKE ?', [$termino])
                ->orWhereHas('tipoEvento', fn ($t) => $t->whereRaw('LOWER(nombre) LIKE ?', [$termino]));
        });
    }
}
