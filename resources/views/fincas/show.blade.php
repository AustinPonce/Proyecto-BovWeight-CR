{{--
    Detalle de una finca. Muestra info, animales y veterinarios asignados.
    Desde acá luego vamos a saltar al CRUD de animales (sub-bloque 2B).
--}}
@extends('layouts.app')

@section('titulo', $finca->nombre . ' — BovWeight CR')

@section('contenido')
@php
    $usuario = auth()->user();
    $puedeEditar = $usuario->esAdmin()
        || ($usuario->esGanadero() && $finca->cedula === $usuario->cedula);
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $finca->nombre }}</h1>
        <p class="text-sm text-gray-600 mt-1">{{ $finca->ubicacion }}</p>
    </div>
    @if ($puedeEditar)
        <a href="{{ route('fincas.edit', $finca) }}"
           class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded text-sm">
            Editar
        </a>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

    {{-- Datos básicos --}}
    <div class="bg-white shadow rounded p-5">
        <h2 class="font-semibold text-gray-700 mb-3">Datos</h2>
        <dl class="text-sm space-y-2">
            <div class="flex justify-between"><dt class="text-gray-600">Dueño</dt><dd>{{ $finca->usuario->nombre ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">Cédula dueño</dt><dd class="font-mono">{{ $finca->cedula }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">Total animales</dt><dd>{{ $finca->animales->count() }}</dd></div>
        </dl>
    </div>

    {{-- Veterinarios asignados --}}
    <div class="bg-white shadow rounded p-5">
        <h2 class="font-semibold text-gray-700 mb-3">Veterinarios asignados</h2>
        @if ($finca->veterinarios->isEmpty())
            <p class="text-sm text-gray-500">Sin veterinarios asignados.</p>
        @else
            <ul class="text-sm space-y-1">
                @foreach ($finca->veterinarios as $vet)
                    <li>• {{ $vet->nombre }} <span class="text-gray-500 font-mono text-xs">({{ $vet->cedula }})</span></li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

{{-- Animales de la finca --}}
<div class="bg-white shadow rounded p-5">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-gray-700">Animales en esta finca</h2>
        <div class="space-x-3">
            @if (! $usuario->esVeterinario())
                <a href="{{ route('animales.create', ['finca' => $finca->id_finca]) }}"
                   class="text-emerald-700 hover:underline text-sm font-medium">+ Nuevo animal</a>
            @endif
            <a href="{{ route('animales.index', ['finca' => $finca->id_finca]) }}"
               class="text-sky-700 hover:underline text-sm">Ver todos →</a>
        </div>
    </div>

    @if ($finca->animales->isEmpty())
        <p class="text-sm text-gray-500">Todavía no hay animales registrados en esta finca.</p>
    @else
        <ul class="text-sm space-y-1">
            @foreach ($finca->animales->take(10) as $animal)
                <li>
                    • <a href="{{ route('animales.show', $animal) }}" class="hover:underline">
                        Arete <span class="font-mono">{{ $animal->arete }}</span> — {{ $animal->nombre ?? 'Sin nombre' }}
                    </a>
                </li>
            @endforeach
        </ul>
        @if ($finca->animales->count() > 10)
            <p class="text-xs text-gray-500 mt-2">
                Mostrando 10 de {{ $finca->animales->count() }}.
                <a href="{{ route('animales.index', ['finca' => $finca->id_finca]) }}"
                   class="text-sky-700 hover:underline">Ver todos</a>
            </p>
        @endif
    @endif
</div>

<div class="mt-6">
    <a href="{{ route('fincas.index') }}" class="text-sm text-gray-600 hover:underline">← Volver al listado</a>
</div>
@endsection
