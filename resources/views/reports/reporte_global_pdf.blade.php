<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { color: #065f46; font-size: 18px; }
        h2 { color: #374151; font-size: 14px; border-bottom: 1px solid #d1fae5; padding-bottom: 4px; }
        .kpi-grid { display: flex; gap: 20px; margin: 16px 0; }
        .kpi { text-align: center; background: #f9fafb; border: 1px solid #e5e7eb; padding: 12px; border-radius: 4px; flex: 1; }
        .kpi .val { font-size: 22px; font-weight: bold; color: #065f46; }
        .kpi .lbl { font-size: 10px; color: #6b7280; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #f3f4f6; text-align: left; padding: 6px 10px; font-size: 11px; }
        td { padding: 5px 10px; border-bottom: 1px solid #f3f4f6; }
        .footer { margin-top: 24px; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <h1>Reporte Global — BovWeight CR</h1>
    <p>Periodo: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</p>
    <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>

    <h2>Totales del sistema</h2>
    <div class="kpi-grid">
        <div class="kpi"><div class="val">{{ $stats['total_usuarios'] }}</div><div class="lbl">Usuarios</div></div>
        <div class="kpi"><div class="val">{{ $stats['total_fincas'] }}</div><div class="lbl">Fincas</div></div>
        <div class="kpi"><div class="val">{{ $stats['total_animales'] }}</div><div class="lbl">Bovinos</div></div>
        <div class="kpi"><div class="val">{{ $stats['total_pesajes'] }}</div><div class="lbl">Pesajes</div></div>
    </div>

    <h2>Periodo analizado</h2>
    <table>
        <tr><th>Indicador</th><th>Valor</th></tr>
        <tr><td>Pesajes en el periodo</td><td>{{ $stats['pesajes_periodo'] }}</td></tr>
        <tr><td>Peso promedio</td><td>{{ $stats['peso_promedio'] ? number_format($stats['peso_promedio'], 2) . ' kg' : '—' }}</td></tr>
    </table>

    @if ($pesajesPorDia->isNotEmpty())
    <h2 style="margin-top:20px">Pesajes por día</h2>
    <table>
        <tr><th>Fecha</th><th>Cantidad</th><th>Peso prom. (kg)</th></tr>
        @foreach ($pesajesPorDia as $dia)
            <tr>
                <td>{{ \Carbon\Carbon::parse($dia->dia)->format('d/m/Y') }}</td>
                <td>{{ $dia->total }}</td>
                <td>{{ number_format($dia->promedio, 2) }}</td>
            </tr>
        @endforeach
    </table>
    @endif

    <div class="footer">BovWeight CR — IF7100 Ingeniería del Software — Las estimaciones son orientativas.</div>
</body>
</html>
