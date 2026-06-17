@extends('layouts.app')

@section('titulo', 'Pesaje #' . $pesaje->id_pesaje)

@section('contenido')
@php
    $usuario = auth()->user();
    $puedeEliminar = ! $usuario->esVeterinario();
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
        <div class="bg-white shadow rounded p-5">
            <h2 class="text-xs uppercase text-gray-500 mb-2">Peso registrado</h2>
            <p class="text-4xl font-bold @class([
                'text-red-700' => (float) $pesaje->peso < 100,
                'text-emerald-700' => (float) $pesaje->peso >= 100,
            ])">
                {{ number_format($pesaje->peso, 2) }}
                <span class="text-base font-normal text-gray-600">kg</span>
            </p>
            @if ((float) $pesaje->peso < 100)
                <p class="text-xs text-red-700 mt-2">⚠️ Peso por debajo del umbral crítico (100 kg).</p>
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
