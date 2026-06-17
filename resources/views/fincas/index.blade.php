{{--
    Listado de fincas. Lo que ve cada rol:
      - Admin       → todas las fincas, con columna "Dueño".
      - Ganadero    → solo sus fincas.
      - Veterinario → solo fincas asignadas.

    Las acciones (Editar/Eliminar) se ocultan al Veterinario porque es solo lectura.
--}}
@extends('layouts.app')

@section('titulo', 'Fincas — BovWeight CR')

@section('contenido')
@php
    $usuario = auth()->user();
    $puedeCrear = $usuario->esAdmin() || $usuario->esGanadero();
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            @if ($usuario->esAdmin())
                Todas las fincas
            @elseif ($usuario->esVeterinario())
                Fincas asignadas
            @else
                Mis fincas
            @endif
        </h1>
        <p class="text-sm text-gray-600 mt-1">{{ $fincas->count() }} finca(s)</p>
    </div>

    @if ($puedeCrear)
        <a href="{{ route('fincas.create') }}"
           class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded font-medium">
            + Nueva finca
        </a>
    @endif
</div>

@if ($fincas->isEmpty())
    <div class="bg-white border border-gray-200 rounded p-8 text-center text-gray-500">
        Todavía no hay fincas registradas.
        @if ($puedeCrear)
            <br><a href="{{ route('fincas.create') }}" class="text-emerald-700 hover:underline">Creá la primera</a>.
        @endif
    </div>
@else
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-sm text-gray-600">
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Ubicación</th>
                    @if ($usuario->esAdmin())
                        <th class="px-4 py-3">Dueño</th>
                    @endif
                    <th class="px-4 py-3"># Animales</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($fincas as $finca)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $finca->nombre }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $finca->ubicacion }}</td>
                        @if ($usuario->esAdmin())
                            <td class="px-4 py-3 text-gray-700">
                                {{ $finca->usuario->nombre ?? '—' }}
                            </td>
                        @endif
                        <td class="px-4 py-3 text-gray-700">{{ $finca->animales->count() }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('fincas.show', $finca) }}"
                               class="text-sky-700 hover:underline text-sm">Ver</a>

                            @if ($usuario->esAdmin() || ($usuario->esGanadero() && $finca->cedula === $usuario->cedula))
                                <a href="{{ route('fincas.edit', $finca) }}"
                                   class="text-emerald-700 hover:underline text-sm">Editar</a>

                                <form method="POST" action="{{ route('fincas.destroy', $finca) }}" class="inline"
                                      onsubmit="return confirm('¿Eliminar esta finca? Se borrarán también sus animales.')">
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
