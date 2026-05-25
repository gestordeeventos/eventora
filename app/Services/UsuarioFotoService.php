<?php

namespace App\Services;

use App\Models\User;
use App\Support\SslCertificate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UsuarioFotoService
{
    private const DISK = 'public';

    private const DIR = 'usuarios/avatars';

    private const MAX_BYTES = 2 * 1024 * 1024;

    public function guardarDesdeBase64(?string $dataUrl, ?string $rutaAnterior = null): ?string
    {
        if (! $dataUrl || trim($dataUrl) === '') {
            return $rutaAnterior;
        }

        if (! preg_match('/^data:image\/(jpeg|jpg|png);base64,(.+)$/i', $dataUrl, $matches)) {
            throw new \InvalidArgumentException('Formato de imagen no válido. Usa JPG o PNG.');
        }

        $extension = strtolower($matches[1]) === 'png' ? 'png' : 'jpg';
        $binary = base64_decode($matches[2], true);

        if ($binary === false) {
            throw new \InvalidArgumentException('No se pudo procesar la imagen.');
        }

        if (strlen($binary) > self::MAX_BYTES) {
            throw new \InvalidArgumentException('La foto no puede superar 2 MB.');
        }

        if ($rutaAnterior) {
            $this->eliminar($rutaAnterior);
        }

        $nombre = Str::uuid().'.'.$extension;
        $ruta = self::DIR.'/'.$nombre;

        Storage::disk(self::DISK)->put($ruta, $binary);

        return $ruta;
    }

    /**
     * Descarga la foto de OAuth (Google/Facebook) o devuelve null si no hay imagen.
     * Si falla la descarga, guarda la URL externa como respaldo.
     */
    public function sincronizarDesdeOAuth(?string $urlRemota, ?string $rutaAnterior = null): ?string
    {
        if (! $urlRemota) {
            if ($rutaAnterior && ! str_starts_with($rutaAnterior, 'http')) {
                $this->eliminar($rutaAnterior);
            }

            return null;
        }

        if ($rutaAnterior && ! str_starts_with($rutaAnterior, 'http')) {
            $this->eliminar($rutaAnterior);
        }

        try {
            $response = Http::withOptions(SslCertificate::guzzleOptions())
                ->timeout(12)
                ->withHeaders(['User-Agent' => 'Eventora/1.0'])
                ->get($urlRemota);

            if (! $response->successful()) {
                return $urlRemota;
            }

            $binary = $response->body();
            if ($binary === '' || strlen($binary) > self::MAX_BYTES) {
                return $urlRemota;
            }

            $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary);
            $extension = match ($mime) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => 'jpg',
            };

            if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
                return $urlRemota;
            }

            if ($rutaAnterior && ! str_starts_with($rutaAnterior, 'http')) {
                $this->eliminar($rutaAnterior);
            }

            $nombre = Str::uuid().'.'.$extension;
            $ruta = self::DIR.'/'.$nombre;
            Storage::disk(self::DISK)->put($ruta, $binary);

            return $ruta;
        } catch (\Throwable) {
            return $urlRemota;
        }
    }

    public function esFotoSubidaManual(?string $ruta): bool
    {
        return filled($ruta) && ! str_starts_with($ruta, 'http');
    }

    public function eliminar(?string $ruta): void
    {
        if (! $ruta || str_starts_with($ruta, 'http')) {
            return;
        }

        if (Storage::disk(self::DISK)->exists($ruta)) {
            Storage::disk(self::DISK)->delete($ruta);
        }
    }

    public function eliminarDeUsuario(User $user): void
    {
        $this->eliminar($user->foto_perfil_url);
    }
}
