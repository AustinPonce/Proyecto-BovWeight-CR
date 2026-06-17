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
        .activo   { color: #065f46; }
        .vendido  { color: #b45309; }
        .fallecido{ color: #6b7280; }
        .foto-celda { width: 70px; text-align: center; }
        .foto-celda img { width: 60px; height: 45px; object-fit: cover; border-radius: 3px; }
        .sin-foto { color: #9ca3af; font-size: 9px; }
        .footer { margin-top: 20px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <h1>Mis Animales — BovWeight CR</h1>
    <div class="meta">
        Generado: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp;
        Usuario: {{ $usuario->nombre }} &nbsp;|&nbsp;
        Total: {{ $animales->count() }} bovinos
    </div>

    @if ($animales->isEmpty())
        <p>No hay animales para mostrar.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th class="foto-celda">Foto</th>
                    <th>Arete</th>
                    <th>Nombre</th>
                    <th>Finca</th>
                    <th>Raza</th>
                    <th>Sexo</th>
                    <th>Estado</th>
                    <th>Pesajes</th>
                    <th>Último Peso (kg)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($animales as $a)
                    @php
                        $ultimoPeso = $a->pesajes->sortByDesc('fecha')->first();
                        // RF24: foto del último pesaje embebida en base64 para DomPDF
                        $fotoBase64 = null;
                        if ($ultimoPeso && $ultimoPeso->imagen) {
                            $absPath = storage_path('app/public/' . $ultimoPeso->imagen);
                            if (is_file($absPath)) {
                                $mime = mime_content_type($absPath) ?: 'image/jpeg';
                                $fotoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($absPath));
                            }
                        }
                    @endphp
                    <tr>
                        <td class="foto-celda">
                            @if ($fotoBase64)
                                <img src="{{ $fotoBase64 }}" alt="Foto">
                            @else
                                <span class="sin-foto">Sin foto</span>
                            @endif
                        </td>
                        <td style="font-family:monospace">{{ $a->arete }}</td>
                        <td>{{ $a->nombre ?? '—' }}</td>
                        <td>{{ $a->finca->nombre ?? '—' }}</td>
                        <td>{{ $a->raza->raza ?? '—' }}</td>
                        <td>{{ $a->sexo->sexo ?? '—' }}</td>
                        <td class="{{ strtolower($a->estado->estado ?? '') }}">{{ $a->estado->estado ?? '—' }}</td>
                        <td>{{ $a->pesajes->count() }}</td>
                        <td>{{ $ultimoPeso ? number_format($ultimoPeso->peso, 2) : '—' }}</td>
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
