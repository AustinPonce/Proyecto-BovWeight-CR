@extends('layouts.app')

@section('titulo', 'Pesaje #' . $pesaje->id_pesaje)

@section('contenido')
@php
    $usuario     = auth()->user();
    $puedeEliminar = ! $usuario->esVeterinario();
    $tieneCorreccion = $pesaje->peso_corregido !== null && $pesaje->factor_raza !== null;
@endphp

<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Pesaje #{{ $pesaje->id_pesaje }}</h1>
            <p class="text-sm text-gray-600 mt-1">
                Animal:
                <a href="{{ route('animales.show', $pesaje->animal) }}" class="text-emerald-700 hover:underline">
                    <span class="font-mono">{{ $pesaje->arete }}</span>
                    @if ($pesaje->animal->nombre) — {{ $pesaje->animal->nombre }} @endif
                </a>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        {{-- Peso final --}}
        <div class="bg-white shadow rounded p-5">
            <h2 class="text-xs uppercase text-gray-500 mb-2">
                Peso {{ $tieneCorreccion ? 'corregido por raza' : 'registrado' }}
            </h2>
            <p class="text-4xl font-bold @class([
                'text-red-700'     => (float) $pesaje->peso < 100,
                'text-emerald-700' => (float) $pesaje->peso >= 100,
            ])">
                {{ number_format($pesaje->peso, 2) }}
                <span class="text-base font-normal text-gray-600">kg</span>
            </p>
            @if ((float) $pesaje->peso < 100)
                <p class="text-xs text-red-700 mt-2">⚠️ Peso por debajo del umbral crítico (100 kg).</p>
            @endif

            {{-- RF13 — Desglose de corrección por raza --}}
            @if ($tieneCorreccion)
                <div class="mt-3 border-t pt-3">
                    <p class="text-xs font-semibold text-gray-600 mb-2">Desglose corrección (RF13):</p>
                    <dl class="text-xs space-y-1">
                        <div class="flex justify-between text-gray-600">
                            <dt>Peso estimado (bruto)</dt>
                            <dd class="font-mono">{{ number_format($pesaje->peso_original, 2) }} kg</dd>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <dt>Factor raza ({{ $pesaje->animal->raza->raza ?? '—' }})</dt>
                            <dd class="font-mono font-semibold">× {{ number_format($pesaje->factor_raza, 4) }}</dd>
                        </div>
                        <div class="flex justify-between text-emerald-700 font-semibold border-t pt-1">
                            <dt>Peso corregido</dt>
                            <dd class="font-mono">{{ number_format($pesaje->peso_corregido, 2) }} kg</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </div>

        <div class="bg-white shadow rounded p-5">
            <h2 class="text-xs uppercase text-gray-500 mb-2">Detalles</h2>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-gray-600">Fecha</dt>
                    <dd>{{ \Illuminate\Support\Carbon::parse($pesaje->fecha)->format('d/m/Y H:i') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-600">Finca</dt>
                    <dd>{{ $pesaje->animal->finca->nombre }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-600">Raza</dt>
                    <dd>{{ $pesaje->animal->raza->raza ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-600">Sincronizado</dt>
                    <dd>{{ $pesaje->sincronizado ? 'Sí' : 'Pendiente' }}</dd></div>
            </dl>
        </div>
    </div>

    @if ($pesaje->imagen)
        <div class="bg-white shadow rounded p-5 mb-4">
            <h2 class="text-xs uppercase text-gray-500 mb-2">Foto usada para la estimación</h2>
            <img src="{{ \Illuminate\Support\Facades\Storage::url($pesaje->imagen) }}"
                 alt="Foto del pesaje"
                 class="max-w-full rounded border border-gray-200">
            <p class="text-xs text-gray-500 mt-2">
                Si la imagen no se ve, corré una vez:
                <code class="bg-gray-100 px-1 py-0.5 rounded">php artisan storage:link</code>
            </p>
        </div>
    @endif

    {{-- Disclaimer RF13 --}}
    @if ($tieneCorreccion && $pesaje->factor_raza != 1.0)
        <div class="bg-amber-50 border border-amber-200 rounded p-4 mb-4 text-xs text-amber-800">
            <p class="font-semibold mb-1">⚠️ Estimación con corrección por raza</p>
            <p>El peso mostrado ({{ number_format($pesaje->peso, 2) }} kg) incluye un factor de corrección
               de <strong>{{ number_format($pesaje->factor_raza, 4) }}</strong>
               aplicado por la raza <strong>{{ $pesaje->animal->raza->raza ?? '—' }}</strong>.
               Este factor es una aproximación académica. Para mayor precisión,
               complementar con medición directa en báscula.</p>
        </div>
    @endif

    <div class="flex items-center justify-between">
        <a href="{{ route('pesajes.index') }}" class="text-sm text-gray-600 hover:underline">← Volver al historial</a>
        @if ($puedeEliminar)
            <form method="POST" action="{{ route('pesajes.destroy', $pesaje) }}"
                  onsubmit="return confirm('¿Eliminar este pesaje?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-700 hover:underline text-sm">Eliminar pesaje</button>
            </form>
        @endif
    </div>
</div>
@endsection

