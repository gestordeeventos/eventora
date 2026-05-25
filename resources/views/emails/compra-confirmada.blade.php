<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de apartado</title>
</head>
<body style="margin:0;padding:0;background:#f5f2eb;font-family:Segoe UI,Helvetica,Arial,sans-serif;color:#0f1f3d;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f2eb;padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e8e4dc;">
                <tr>
                    <td style="background:#0f1f3d;padding:24px 28px;">
                        <p style="margin:0;font-size:22px;font-weight:700;color:#c9a84c;letter-spacing:0.5px;">Eventora</p>
                        <p style="margin:8px 0 0;font-size:14px;color:#f5f2eb;">Confirmación de tu apartado</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        <p style="margin:0 0 16px;font-size:16px;">Hola, <strong>{{ $nombreCliente }}</strong>,</p>
                        <p style="margin:0 0 20px;font-size:17px;line-height:1.5;">
                            {{ $mensajeApartado }}
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f2eb;border-radius:8px;margin-bottom:24px;">
                            <tr>
                                <td style="padding:16px 18px;font-size:14px;line-height:1.6;">
                                    <p style="margin:0 0 8px;"><strong>Evento:</strong> {{ $tituloEvento }}</p>
                                    <p style="margin:0 0 8px;"><strong>Fecha del evento:</strong> {{ $fechaEvento }}</p>
                                    <p style="margin:0 0 8px;"><strong>Código de ticket:</strong> {{ $codigoTicket }}</p>
                                    <p style="margin:0;"><strong>Total pagado:</strong> ${{ $totalFormateado }} MXN</p>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 20px;font-size:14px;color:#555;">
                            Guarda este correo. También puedes ver tu ticket y el historial de compras en tu cuenta.
                        </p>

                        <a href="{{ $urlHistorial }}"
                           style="display:inline-block;background:#c9a84c;color:#0f1f3d;text-decoration:none;font-weight:600;font-size:14px;padding:12px 22px;border-radius:8px;">
                            Ver mis compras
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 28px 24px;border-top:1px solid #eee;font-size:12px;color:#888;">
                        Este mensaje se envió porque completaste un pago en Eventora. Si no fuiste tú, contacta al soporte.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
