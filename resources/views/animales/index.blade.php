{{--
    Listado de animales. Se puede filtrar por finca con ?finca=ID.
    Las columnas y acciones se adaptan al rol del usuario.
--}}
@extends('layouts.app')

@section('titulo', 'Animales — BovWeight CR')

@section('contenido')
@php
    $usuario = auth()->user();
    $puedeCrear = ! $usuario->esVeterinario();
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            @if ($fincaSeleccionada)
                Animales de {{ $fincaSeleccionada->nombre }}
            @else
                @if ($usuario->esVeterinario())
                    Animales en fincas asignadas
                @else
                    Mis animales
                @endif
            @endif
        </h1>
        <p class="text-sm text-gray-600 mt-1">{{ $animales->count() }} animal(es)</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('export.animales.pdf', request()->query()) }}"
           class="bg-red-700 hover:bg-red-800 text-white px-3 py-2 rounded text-sm font-medium">
            PDF
        </a>
        <a href="{{ route('export.animales.csv', request()->query()) }}"
           class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded text-sm font-medium">
            CSV/Excel
        </a>
        @if ($puedeCrear)
            <a href="{{ route('animales.create', $fincaSeleccionada ? ['finca' => $fincaSeleccionada->id_finca] : []) }}"
               class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded font-medium">
                + Nuevo animal
            </a>
        @endif
    </div>
</div>

{{-- RF08 + RF12: Búsqueda y filtro --}}
<form method="GET" action="{{ route('animales.index') }}" class="bg-white shadow rounded p-4 mb-6 flex flex-wrap gap-3 items-end">
    @if(request('finca'))
        <input type="hidden" name="finca" value="{{ request('finca') }}">
    @endif
    <div class="flex-1 min-w-40">
        <label class="block text-xs font-medium text-gray-600 mb-1">Buscar por arete o nombre</label>
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Ej: 1001 o Torito"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
        <select name="estado" class="border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">Todos</option>
            @foreach ($estados as $est)
                <option value="{{ $est->estado }}" @selected(request('estado') === $est->estado)>
                    {{ $est->estado }}
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded text-sm">Filtrar</button>
    <a href="{{ route('animales.index', request('finca') ? ['finca' => request('finca')] : []) }}"
       class="text-sm text-gray-500 hover:underline">Limpiar</a>
</form>

@if ($fincaSeleccionada)
    <div class="mb-4">
        <a href="{{ route('animales.index') }}" class="text-sm text-emerald-700 hover:underline">
            ← Ver todos los animales
        </a>
    </div>
@endif

@if ($animales->isEmpty())
    <div class="bg-white border border-gray-200 rounded p-8 text-center text-gray-500">
        Todavía no hay animales registrados.
        @if ($puedeCrear)
            <br><a href="{{ route('animales.create') }}" class="text-emerald-700 hover:underline">Registrá el primero</a>.
        @endif
    </div>
@else
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-sm text-gray-600">
                    <th class="px-4 py-3">Arete</th>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Finca</th>
                    <th class="px-4 py-3">Raza</th>
                    <th class="px-4 py-3">Sexo</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($animales as $animal)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-sm">{{ $animal->arete }}</td>
                        <td class="px-4 py-3">{{ $animal->nombre ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('fincas.show', $animal->finca) }}"
                               class="text-sky-700 hover:underline">
                                {{ $animal->finca->nombre }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $animal->raza->raza }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $animal->sexo->sexo }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-xs
                                @class([
                                    'bg-green-100 text-green-800'   => $animal->estado->estado === 'Activo',
                                    'bg-amber-100 text-amber-800'   => $animal->estado->estado === 'Vendido',
                                    'bg-gray-200  text-gray-700'    => $animal->estado->estado === 'Fallecido',
                                ])">
                                {{ $animal->estado->estado }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('animales.show', $animal) }}"
                               class="text-sky-700 hover:underline text-sm">Ver</a>

                            @if ($puedeCrear)
                                <a href="{{ route('animales.edit', $animal) }}"
                                   class="text-emerald-700 hover:underline text-sm">Editar</a>

                                <form method="POST" action="{{ route('animales.destroy', $animal) }}" class="inline"
                                      onsubmit="return confirm('¿Eliminar este animal? Se borrarán también sus pesajes.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-700 hover:underline text-sm">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="mt-6">
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:underline">← Volver al panel</a>
</div>
@endsection
