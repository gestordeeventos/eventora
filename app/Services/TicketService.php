<?php

namespace App\Services;

use App\Models\Reserva;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class TicketService
{
    public function generarCodigo(): string
    {
        return 'EVT-'.strtoupper(Str::random(8));
    }

    public function respuestaPdf(Reserva $reserva, bool $inline = false): Response
    {
        $reserva->load(['evento.tipoEvento', 'usuario', 'reservaBoletos.boleto']);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('cliente.tickets.pdf', compact('reserva'))
                ->setPaper('a4', 'portrait');

            $nombre = 'ticket-'.$reserva->codigo_ticket.'.pdf';

            return $inline
                ? $pdf->stream($nombre)
                : $pdf->download($nombre);
        }

        $html = view('cliente.tickets.print', compact('reserva', 'inline'))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
