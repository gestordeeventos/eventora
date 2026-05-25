<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket {{ $reserva->codigo_ticket }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f1f3d; margin: 32px; }
        h1 { color: #0f1f3d; font-size: 22px; margin-bottom: 4px; }
        .gold { color: #c9a84c; }
        .box { border: 2px solid #c9a84c; padding: 16px; margin: 20px 0; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; font-size: 12px; }
        .code { font-size: 28px; letter-spacing: 2px; font-weight: bold; }
        .muted { color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Eventora</h1>
    <p class="muted">Comprobante de acceso · Simulación de pago</p>

    <div class="box">
        <p class="muted">Código de ticket</p>
        <p class="code gold">{{ $reserva->codigo_ticket }}</p>
    </div>

    <h2>{{ $reserva->evento->titulo }}</h2>
    <p><strong>Fecha:</strong> {{ $reserva->evento->fecha_inicio?->format('d/m/Y H:i') ?? 'Por confirmar' }}</p>
    <p><strong>Cliente:</strong> {{ $reserva->usuario->nombreCompleto() }}</p>
    <p><strong>Correo:</strong> {{ $reserva->usuario->email }}</p>
    <p><strong>Pagado:</strong> {{ $reserva->pagado_at?->format('d/m/Y H:i') }}</p>
    @if ($reserva->ultimos4_tarjeta)
        <p><strong>Tarjeta:</strong> **** {{ $reserva->ultimos4_tarjeta }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Boleto</th>
                <th>Cant.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reserva->reservaBoletos as $linea)
                <tr>
                    <td>{{ $linea->boleto->nombre_tipo }}</td>
                    <td>{{ $linea->cantidad }}</td>
                    <td>${{ number_format($linea->subtotal, 2) }} MXN</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top:16px;"><strong>Total:</strong> ${{ number_format($reserva->total, 2) }} MXN</p>
    <p class="muted">Presenta este ticket (impreso o digital) en el acceso al evento.</p>
</body>
</html>
