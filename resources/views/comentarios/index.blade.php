@extends('layouts.app')

@section('titulo', 'Comentarios del Veterinario — ' . $animal->arete)

@section('contenido')
@php $usuario = auth()->user(); @endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Comentarios veterinarios</h1>
        <p class="text-sm text-gray-600 mt-1">
            Animal:
            <a href="{{ route('animales.show', $animal) }}" class="text-sky-700 hover:underline font-medium">
                {{ $animal->nombre ?? $animal->arete }}
            </a>
            — Finca: {{ $animal->finca->nombre }}
        </p>
    </div>
</div>

@if (session('exito'))
    <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded">
        {{ session('exito') }}
    </div>
@endif

{{-- Formulario para agregar comentario (solo vets) --}}
@if ($usuario->esVeterinario())
    <div class="bg-white shadow rounded p-5 mb-6">
        <h2 class="font-semibold text-gray-700 mb-3">Nuevo comentario</h2>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-3">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('animales.comentarios.store', $animal) }}" class="space-y-3">
            @csrf
            <textarea name="comentario" rows="4" required
                      placeholder="Observaciones clínicas, diagnóstico, tratamiento recomendado..."
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-400">{{ old('comentario') }}</textarea>
            <button type="submit"
                    class="bg-sky-700 hover:bg-sky-800 text-white px-5 py-2 rounded font-medium text-sm">
                Guardar comentario
            </button>
        </form>
    </div>
@endif

{{-- Lista de comentarios --}}
@if ($animal->comentariosVeterinario->isEmpty())
    <div class="bg-white border border-gray-200 rounded p-8 text-center text-gray-500">
        <p class="text-4xl mb-2">💬</p>
        No hay comentarios del veterinario para este animal.
    </div>
@else
    <div class="space-y-4">
        @foreach ($animal->comentariosVeterinario as $com)
            <div class="bg-white shadow rounded p-5 border-l-4 border-sky-400">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="font-semibold text-sky-800">{{ $com->veterinario->nombre ?? 'Veterinario' }}</p>
                        <p class="text-xs text-gray-500">
                            {{ \Illuminate\Support\Carbon::parse($com->fecha)->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    @if ($usuario->esAdmin() || $com->cedula_veterinario === $usuario->cedula)
                        <form method="POST"
                              action="{{ route('animales.comentarios.destroy', [$animal, $com]) }}"
                              onsubmit="return confirm('¿Eliminar este comentario?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-xs">Eliminar</button>
                        </form>
                    @endif
                </div>
                <p class="text-gray-800 text-sm whitespace-pre-line">{{ $com->comentario }}</p>
            </div>
        @endforeach
    </div>
@endif

<div class="mt-6 flex gap-4">
    <a href="{{ route('animales.show', $animal) }}" class="text-sm text-gray-600 hover:underline">
        ← Volver al animal
    </a>
</div>
@endsection
