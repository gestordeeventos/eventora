<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompraConfirmadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $nombreCliente;

    public string $mensajeApartado;

    public string $tituloEvento;

    public string $fechaEvento;

    public string $codigoTicket;

    public string $totalFormateado;

    public string $urlHistorial;

    public function __construct(public Reserva $reserva)
    {
        $reserva->loadMissing(['evento', 'usuario', 'reservaBoletos.boleto']);

        $fecha = $reserva->evento->fecha_inicio ?? now();
        $mes = ucfirst($fecha->locale('es')->translatedFormat('F'));

        $this->nombreCliente = $reserva->usuario->nombreCompleto();
        $this->tituloEvento = $reserva->evento->titulo;
        $this->mensajeApartado = sprintf(
            'Apartaste un evento %s del día %d de %s del año %d.',
            $this->tituloEvento,
            $fecha->day,
            $mes,
            $fecha->year
        );
        $this->fechaEvento = $fecha->locale('es')->translatedFormat('l j \d\e F \d\e Y, H:i');
        $this->codigoTicket = $reserva->codigo_ticket ?? '—';
        $this->totalFormateado = number_format((float) $reserva->total, 2);
        $this->urlHistorial = route('cliente.compras.index');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de apartado — '.$this->tituloEvento,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.compra-confirmada',
        );
    }
}
