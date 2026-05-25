<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $reserva->codigo_ticket }}</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 2rem auto; padding: 1rem; color: #0f1f3d; }
        .code { font-size: 1.75rem; letter-spacing: 0.1em; color: #c9a84c; }
        .box { border: 2px solid #c9a84c; padding: 1rem; border-radius: 8px; }
        @media print { .no-print { display: none; } }
    </style>
    @if ($inline ?? false)
    <script>window.onload = function () { window.print(); };</script>
    @endif
</head>
<body>
    <p class="no-print" style="background:#fff3cd;padding:.75rem;border-radius:6px;">
        DomPDF no está instalado. Usa <strong>Ctrl+P</strong> → Guardar como PDF, o ejecuta <code>composer update</code>.
    </p>
    <h1>Eventora</h1>
    <p class="muted" style="color:#666;font-size:12px;">Comprobante de acceso</p>
    <div class="box" style="border:2px solid #c9a84c;padding:1rem;border-radius:8px;margin:1rem 0;">
        <p style="color:#666;font-size:12px;">Código</p>
        <p class="code">{{ $reserva->codigo_ticket }}</p>
    </div>
    <h2>{{ $reserva->evento->titulo }}</h2>
    <p><strong>Cliente:</strong> {{ $reserva->usuario->nombreCompleto() }}</p>
    <p><strong>Total:</strong> ${{ number_format($reserva->total, 2) }} MXN</p>
    <p class="no-print"><button onclick="window.print()">Imprimir / Guardar PDF</button></p>
</body>
</html>
