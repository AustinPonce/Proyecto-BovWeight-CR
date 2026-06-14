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
    @if ($puedeEditar)
        <a href="{{ route('animales.edit', $animal) }}"
           class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded text-sm">
            Editar
        </a>
    @endif
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
                        <td class="py-2 text-gray-600">#{{ $p->id_tipo_pesaje }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="mt-6">
    <a href="{{ route('animales.index', ['finca' => $animal->id_finca]) }}"
       class="text-sm text-gray-600 hover:underline">
        ← Volver a los animales de {{ $animal->finca->nombre }}
    </a>
</div>
@endsection
