<?php

namespace App\Services;

use App\Mail\CompraConfirmadaMail;
use App\Models\Reserva;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CompraNotificacionService
{
    public function enviarConfirmacion(Reserva $reserva): void
    {
        $reserva->loadMissing(['evento', 'usuario']);

        $email = $reserva->usuario?->email;
        if (! $email) {
            return;
        }

        $reservaId = $reserva->id_reserva;

        dispatch(function () use ($reservaId, $email) {
            $reserva = Reserva::with(['evento', 'usuario'])->find($reservaId);
            if (! $reserva) {
                return;
            }

            try {
                Mail::to($email)->send(new CompraConfirmadaMail($reserva));
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar el correo de confirmación de compra (SMTP suele estar bloqueado en Render).', [
                    'id_reserva' => $reservaId,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }
}
