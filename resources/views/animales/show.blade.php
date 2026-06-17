{{--
    Detalle de un animal con su historial de pesajes.
    El historial es solo lectura por ahora — el CRUD de pesajes viene en 2D.
--}}
@extends('layouts.app')

@section('titulo', 'Animal ' . $animal->arete)

@section('contenido')
@php
    $usuario = auth()->user();
    $puedeEditar = ! $usuario->esVeterinario();
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            {{ $animal->nombre ?? 'Sin nombre' }}
            <span class="text-gray-500 font-mono text-lg">({{ $animal->arete }})</span>
        </h1>
        <p class="text-sm text-gray-600 mt-1">
            En finca:
            <a href="{{ route('fincas.show', $animal->finca) }}" class="text-emerald-700 hover:underline">
                {{ $animal->finca->nombre }}
            </a>
        </p>
    </div>
    <div class="flex items-center gap-2">
        @if ($puedeEditar)
            <a href="{{ route('pesajes.create', ['animal' => $animal->arete]) }}"
               class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded text-sm">
                + Pesaje
            </a>
            <a href="{{ route('animales.edit', $animal) }}"
               class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded text-sm">
                Editar
            </a>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white shadow rounded p-5">
        <h2 class="font-semibold text-gray-700 mb-3">Datos del animal</h2>
        <dl class="text-sm space-y-2">
            <div class="flex justify-between"><dt class="text-gray-600">Raza</dt><dd>{{ $animal->raza->raza }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">Sexo</dt><dd>{{ $animal->sexo->sexo }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">Estado</dt><dd>{{ $animal->estado->estado }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">Total pesajes</dt><dd>{{ $animal->pesajes->count() }}</dd></div>
        </dl>
    </div>

    <div class="bg-white shadow rounded p-5">
        <h2 class="font-semibold text-gray-700 mb-3">Último pesaje</h2>
        @php $ultimo = $animal->pesajes->sortByDesc('fecha')->first(); @endphp
        @if ($ultimo)
            <p class="text-3xl font-bold text-emerald-700">{{ number_format($ultimo->peso, 2) }} <span class="text-sm font-normal text-gray-600">kg</span></p>
            <p class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Carbon::parse($ultimo->fecha)->format('d/m/Y H:i') }}</p>
        @else
            <p class="text-sm text-gray-500">Todavía no hay pesajes registrados.</p>
        @endif
    </div>
</div>

<div class="bg-white shadow rounded p-5">
    <h2 class="font-semibold text-gray-700 mb-3">Historial de pesajes</h2>
    @if ($animal->pesajes->isEmpty())
        <p class="text-sm text-gray-500">Sin pesajes registrados todavía.</p>
    @else
        {{-- RF10: Gráfica de progreso de peso --}}
        @php
            $pesajesOrdenados = $animal->pesajes->sortBy('fecha');
            $labels = $pesajesOrdenados->map(fn($p) => \Illuminate\Support\Carbon::parse($p->fecha)->format('d/m/Y'))->values();
            $pesos  = $pesajesOrdenados->map(fn($p) => round((float)$p->peso, 2))->values();
        @endphp
        <div class="mb-6">
            <canvas id="graficaPeso" height="100"></canvas>
        </div>

        <table class="w-full text-sm">
            <thead class="text-gray-600">
                <tr class="text-left border-b">
                    <th class="py-2">Fecha</th>
                    <th class="py-2">Peso (kg)</th>
                    <th class="py-2">Tipo</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($animal->pesajes->sortByDesc('fecha') as $p)
                    <tr>
                        <td class="py-2">{{ \Illuminate\Support\Carbon::parse($p->fecha)->format('d/m/Y H:i') }}</td>
                        <td class="py-2 font-medium">{{ number_format($p->peso, 2) }}</td>
                        <td class="py-2 text-gray-600">{{ $p->id_tipo_pesaje == 1 ? 'Foto IA' : 'Manual' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            new Chart(document.getElementById('graficaPeso'), {
                type: 'line',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        label: 'Peso (kg)',
                        data: @json($pesos),
                        borderColor: '#15803d',
                        backgroundColor: 'rgba(21,128,61,0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#15803d',
                        tension: 0.3,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.parsed.y + ' kg'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: { callback: v => v + ' kg' }
                        }
                    }
                }
            });
        </script>
    @endif
</div>

{{-- Comentarios del veterinario --}}
@php
    $comentarios = $animal->comentariosVeterinario()
        ->with('veterinario')
        ->orderByDesc('fecha')
        ->limit(3)
        ->get();
@endphp

<div class="bg-white shadow rounded p-5 mt-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-gray-700">Comentarios del veterinario</h2>
        <a href="{{ route('animales.comentarios', $animal) }}"
           class="text-sky-700 hover:underline text-sm">Ver todos →</a>
    </div>

    @if ($comentarios->isEmpty())
        <p class="text-sm text-gray-500">Sin comentarios veterinarios todavía.</p>
    @else
        <div class="space-y-3">
            @foreach ($comentarios as $com)
                <div class="border-l-4 border-sky-300 pl-3">
                    <p class="text-xs text-gray-500 mb-1">
                        {{ $com->veterinario->nombre ?? 'Veterinario' }} —
                        {{ \Illuminate\Support\Carbon::parse($com->fecha)->format('d/m/Y') }}
                    </p>
                    <p class="text-sm text-gray-800">{{ Str::limit($com->comentario, 200) }}</p>
                </div>
            @endforeach
        </div>
    @endif

    @if ($usuario->esVeterinario())
        <div class="mt-3">
            <a href="{{ route('animales.comentarios', $animal) }}"
               class="text-sm bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 px-3 py-1.5 rounded">
                + Agregar comentario
            </a>
        </div>
    @endif
</div>

{{-- ------------------------------------------------------------------ --}}
{{-- RF22 — Recordatorios de re-pesaje (solo ganadero y admin)         --}}
{{-- ------------------------------------------------------------------ --}}
@if (! auth()->user()->esVeterinario())
<div class="bg-white shadow rounded p-5 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-700">Recordatorios de re-pesaje</h2>
    </div>

    @if (session('exito'))
        <div class="mb-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2 rounded">
            {{ session('exito') }}
        </div>
    @endif

    {{-- Formulario para crear recordatorio --}}
    <form method="POST"
          action="{{ route('animales.recordatorios.store', $animal) }}"
          class="flex flex-wrap items-end gap-3 mb-5 bg-gray-50 border border-gray-200 rounded p-4">
        @csrf

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Frecuencia</label>
            <select name="frecuencia" required
                    class="border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">Seleccioná…</option>
                <option value="semanal">Semanal (cada 7 días)</option>
                <option value="quincenal">Quincenal (cada 15 días)</option>
                <option value="mensual">Mensual (cada 30 días)</option>
                <option value="trimestral">Trimestral (cada 90 días)</option>
            </select>
            @error('frecuencia')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Fecha de inicio</label>
            <input type="date" name="fecha_inicio" required
                   value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}"
                   class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            @error('fecha_inicio')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded text-sm font-medium">
            + Agregar recordatorio
        </button>
    </form>

    {{-- Lista de recordatorios activos --}}
    @if ($animal->recordatorios->isEmpty())
        <p class="text-sm text-gray-500">No hay recordatorios configurados para este animal.</p>
    @else
        <div class="space-y-3">
            @foreach ($animal->recordatorios as $rec)
                <div class="flex items-start justify-between border border-gray-100 rounded p-3 bg-gray-50">
                    <div>
                        <p class="text-sm font-medium text-gray-800 capitalize">
                            {{ $rec->frecuencia }}
                            <span class="text-gray-500 font-normal text-xs">
                                — iniciado {{ \Carbon\Carbon::parse($rec->fecha_inicio)->format('d/m/Y') }}
                            </span>
                        </p>
                        <p class="text-xs text-emerald-700 mt-0.5">
                            Próximo pesaje: {{ $rec->proximaFecha()->format('d/m/Y') }}
                        </p>
                        @if ($rec->notificaciones->isNotEmpty())
                            <p class="text-xs text-gray-500 mt-0.5">
                                Última notificación: {{ $rec->notificaciones->first()->fecha_envio->format('d/m/Y H:i') }}
                            </p>
                        @endif
                    </div>
                    <form method="POST"
                          action="{{ route('animales.recordatorios.destroy', [$animal, $rec]) }}"
                          onsubmit="return confirm('¿Eliminar este recordatorio?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="text-xs text-red-600 hover:underline">
                            Eliminar
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endif

<div class="mt-6">
    <a href="{{ route('animales.index', ['finca' => $animal->id_finca]) }}"
       class="text-sm text-gray-600 hover:underline">
        ← Volver a los animales de {{ $animal->finca->nombre }}
    </a>
</div>
@endsection
