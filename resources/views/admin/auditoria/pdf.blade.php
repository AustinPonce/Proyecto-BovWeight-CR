<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría del Sistema — BovWeight CR</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        h1   { font-size: 14px; text-align: center; margin-bottom: 4px; }
        p.sub{ font-size: 9px; text-align: center; color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th    { background: #065f46; color: #fff; padding: 5px 6px; text-align: left; font-size: 9px; }
        td    { padding: 4px 6px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) td { background: #f9fafb; }
        .badge { padding: 1px 5px; border-radius: 3px; font-weight: bold; font-size: 8px; }
        .mod-auth          { background: #dbeafe; color: #1e40af; }
        .mod-fincas        { background: #d1fae5; color: #065f46; }
        .mod-animales      { background: #ecfdf5; color: #047857; }
        .mod-pesajes       { background: #fef9c3; color: #854d0e; }
        .mod-transacciones { background: #f3e8ff; color: #6b21a8; }
        .mod-veterinarios  { background: #cffafe; color: #0e7490; }
        .mod-usuarios      { background: #ffe4e6; color: #9f1239; }
        .mod-catalogos     { background: #ffedd5; color: #9a3412; }
    </style>
</head>
<body>
    <h1>Auditoría del Sistema — BovWeight CR</h1>
    <p class="sub">Generado el {{ now()->format('d/m/Y H:i:s') }} — Total: {{ $registros->count() }} registro(s)</p>

    <table>
        <thead>
            <tr>
                <th style="width:15%">Fecha / Hora</th>
                <th style="width:15%">Usuario</th>
                <th style="width:10%">Módulo</th>
                <th style="width:10%">Acción</th>
                <th style="width:42%">Descripción</th>
                <th style="width:8%">IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($registros as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i:s') }}</td>
                    <td>
                        {{ $r->usuario?->nombre ?? 'Sistema' }}
                        @if ($r->cedula_usuario)
                            <br><small style="color:#666">{{ $r->cedula_usuario }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge mod-{{ $r->modulo }}">{{ strtoupper($r->modulo) }}</span>
                    </td>
                    <td style="font-weight:bold">{{ strtoupper($r->accion) }}</td>
                    <td>{{ $r->descripcion }}</td>
                    <td style="font-size:8px;color:#666">{{ $r->ip ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
