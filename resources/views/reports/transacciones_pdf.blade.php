<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        h1 { color: #065f46; font-size: 16px; }
        .meta { color: #6b7280; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; padding: 6px 8px; border-bottom: 2px solid #fde68a; }
        td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
        tr:nth-child(even) { background: #fafafa; }
        .venta  { color: #065f46; font-weight: bold; }
        .compra { color: #b45309; font-weight: bold; }
        .monto  { text-align: right; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <h1>Transacciones de Ganado — BovWeight CR</h1>
    <div class="meta">
        Generado: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp;
        Usuario: {{ $usuario->nombre }} &nbsp;|&nbsp;
        Total: {{ $transacciones->count() }} transacción(es)
    </div>

    @if ($transacciones->isEmpty())
        <p>No hay transacciones para mostrar.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Arete</th>
                    <th>Animal</th>
                    <th>Finca</th>
                    <th>Contraparte</th>
                    <th>Precio/kg</th>
                    <th>Peso (kg)</th>
                    <th>Monto Total</th>
                </tr>
            </thead>
            <tbody>
                @php $totalMonto = 0; @endphp
                @foreach ($transacciones as $t)
                    @php $totalMonto += $t->monto_total; @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y') }}</td>
                        <td class="{{ $t->tipo }}">{{ ucfirst($t->tipo) }}</td>
                        <td style="font-family:monospace">{{ $t->arete }}</td>
                        <td>{{ $t->animal->nombre ?? '—' }}</td>
                        <td>{{ $t->animal->finca->nombre ?? '—' }}</td>
                        <td>{{ $t->nombre_contraparte }}{{ $t->cedula_contraparte ? ' (' . $t->cedula_contraparte . ')' : '' }}</td>
                        <td>₡{{ number_format($t->precio_por_kg, 2) }}</td>
                        <td>{{ number_format($t->peso_negociado, 2) }}</td>
                        <td class="monto">₡{{ number_format($t->monto_total, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="8" style="text-align:right; font-weight:bold; border-top:2px solid #333; padding-top:8px;">
                        Total:
                    </td>
                    <td class="monto" style="border-top:2px solid #333; padding-top:8px;">
                        ₡{{ number_format($totalMonto, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="footer">
        BovWeight CR — IF7100 Ingeniería del Software — Las estimaciones de peso son orientativas.
    </div>
</body>
</html>
