<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const PROTECTED_ADMIN_EMAIL = 'admin@gestoreventos.com';

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password_hash',
        'rol',
        'telefono',
        'activo',
        'foto_perfil_url',
        'foto_perfil_updated_at',
        'google_id',
        'facebook_id',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'email_verificado_at' => 'datetime',
        'foto_perfil_updated_at' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function nombreCompleto(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }

    public function fotoPerfilUrl(): ?string
    {
        if (! $this->foto_perfil_url) {
            return null;
        }

        if (str_starts_with($this->foto_perfil_url, 'file://')) {
            return null;
        }

        if (str_starts_with($this->foto_perfil_url, 'http://') || str_starts_with($this->foto_perfil_url, 'https://')) {
            return $this->foto_perfil_url;
        }

        if (str_contains($this->foto_perfil_url, '://')) {
            return null;
        }

        return asset('storage/'.$this->foto_perfil_url);
    }

    public function inicialesAvatar(): string
    {
        $letra = strtoupper(substr(trim($this->nombre), 0, 1));

        return $letra !== '' ? $letra : '?';
    }

    public function colorAvatar(): string
    {
        $colores = ['#4285F4', '#DB4437', '#F4B400', '#0F9D58', '#AB47BC', '#00ACC1', '#FF7043', '#5C6BC0'];
        $indice = crc32(strtolower(trim($this->email))) % count($colores);

        return $colores[$indice];
    }

    public function tieneFotoPerfil(): bool
    {
        return $this->fotoPerfilUrl() !== null;
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function isProtectedAdmin(): bool
    {
        return strcasecmp($this->email, self::PROTECTED_ADMIN_EMAIL) === 0;
    }

    public function isOrganizador(): bool
    {
        return $this->rol === 'organizador';
    }

    public function isCliente(): bool
    {
        return $this->rol === 'cliente';
    }

    public function eventosOrganizados(): HasMany
    {
        return $this->hasMany(Evento::class, 'id_organizador', 'id_usuario');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'id_usuario', 'id_usuario');
    }

    public function getRouteKeyName(): string
    {
        return 'id_usuario';
    }
}
