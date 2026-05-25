<?php

namespace App\Services;

use App\Models\Evento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventoPortadaService
{
    private const DISK = 'public';

    private const DIR = 'eventos/portadas';

    private const MAX_BYTES = 3 * 1024 * 1024;

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
            throw new \InvalidArgumentException('La imagen no puede superar 3 MB.');
        }

        if ($rutaAnterior) {
            $this->eliminar($rutaAnterior);
        }

        $nombre = Str::uuid().'.'.$extension;
        $ruta = self::DIR.'/'.$nombre;

        Storage::disk(self::DISK)->put($ruta, $binary);

        return $ruta;
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

    public function eliminarDeEvento(Evento $evento): void
    {
        $this->eliminar($evento->imagen_url);
    }
}
