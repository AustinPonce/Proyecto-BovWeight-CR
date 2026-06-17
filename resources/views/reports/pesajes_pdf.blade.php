<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        h1 { color: #065f46; font-size: 16px; margin-bottom: 4px; }
        .meta { color: #6b7280; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; padding: 6px 8px; border-bottom: 2px solid #d1fae5; }
        td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tr:nth-child(even) { background: #f9fafb; }
        .peso-alto { color: #065f46; font-weight: bold; }
        .peso-bajo  { color: #b91c1c; font-weight: bold; }
        .foto-td { width: 70px; text-align: center; }
        .foto-td img { width: 60px; height: 45px; object-fit: cover; border-radius: 3px; }
        .sin-foto { color: #9ca3af; font-size: 9px; }
        .footer { margin-top: 20px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <h1>Historial de Pesajes — BovWeight CR</h1>
    <div class="meta">
        Generado: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp;
        Usuario: {{ $usuario->nombre }} ({{ $usuario->tipoUsuario->nombre_tipo ?? '' }}) &nbsp;|&nbsp;
        Total: {{ $pesajes->count() }} registros
    </div>

    @if ($pesajes->isEmpty())
        <p>No hay pesajes para mostrar con los filtros aplicados.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th class="foto-td">Foto</th>
                    <th>Fecha</th>
                    <th>Arete</th>
                    <th>Animal</th>
                    <th>Finca</th>
                    <th>Raza</th>
                    <th>Peso (kg)</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pesajes as $p)
                    @php
                        $fotoBase64 = null;
                        if ($p->imagen) {
                            $absPath = storage_path('app/public/' . $p->imagen);
                            if (is_file($absPath)) {
                                $mime = mime_content_type($absPath) ?: 'image/jpeg';
                                $fotoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($absPath));
                            }
                        }
                    @endphp
                    <tr>
                        <td class="foto-td">
                            @if ($fotoBase64)
                                <img src="{{ $fotoBase64 }}" alt="Foto">
                            @else
                                <span class="sin-foto">Sin foto</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($p->fecha)->format('d/m/Y H:i') }}</td>
                        <td style="font-family:monospace">{{ $p->arete }}</td>
                        <td>{{ $p->animal->nombre ?? '—' }}</td>
                        <td>{{ $p->animal->finca->nombre ?? '—' }}</td>
                        <td>{{ $p->animal->raza->raza ?? '—' }}</td>
                        <td class="{{ (float)$p->peso >= 100 ? 'peso-alto' : 'peso-bajo' }}">
                            {{ number_format($p->peso, 2) }}
                        </td>
                        <td>{{ $p->id_tipo_pesaje === 1 ? 'Foto IA' : 'Manual' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        BovWeight CR — IF7100 Ingeniería del Software — Las estimaciones son orientativas y no sustituyen báscula oficial.
    </div>
</body>
</html>
