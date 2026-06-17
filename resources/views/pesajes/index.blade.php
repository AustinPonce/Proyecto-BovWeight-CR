{{--
    Historial de pesajes — paginado, filtrable por animal o finca.
--}}
@extends('layouts.app')

@section('titulo', 'Pesajes — BovWeight CR')

@section('contenido')
@php
    $usuario = auth()->user();
    $puedeRegistrar = ! $usuario->esVeterinario();
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Historial de pesajes</h1>
        <p class="text-sm text-gray-600 mt-1">{{ $pesajes->total() }} registro(s)</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('export.pesajes.pdf', request()->query()) }}"
           class="bg-red-700 hover:bg-red-800 text-white px-3 py-2 rounded text-sm font-medium">
            PDF
        </a>
        <a href="{{ route('export.pesajes.csv', request()->query()) }}"
           class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded text-sm font-medium">
            CSV
        </a>
        <a href="{{ route('export.pesajes.xlsx', request()->query()) }}"
           class="bg-green-700 hover:bg-green-800 text-white px-3 py-2 rounded text-sm font-medium">
            Excel
        </a>
        @if ($puedeRegistrar)
            <a href="{{ route('pesajes.create') }}"
               class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded font-medium">
                + Registrar pesaje
            </a>
        @endif
    </div>
</div>

@if ($pesajes->isEmpty())
    <div class="bg-white border border-gray-200 rounded p-8 text-center text-gray-500">
        Todavía no hay pesajes registrados.
    </div>
@else
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-sm text-gray-600">
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Animal</th>
                    <th class="px-4 py-3">Finca</th>
                    <th class="px-4 py-3">Peso (kg)</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($pesajes as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">
                            {{ \Illuminate\Support\Carbon::parse($p->fecha)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('animales.show', $p->animal) }}" class="hover:underline">
                                <span class="font-mono text-xs">{{ $p->arete }}</span>
                                @if ($p->animal->nombre)
                                    <span class="text-gray-600"> — {{ $p->animal->nombre }}</span>
                                @endif
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $p->animal->finca->nombre }}</td>
                        <td class="px-4 py-3 font-semibold @class([
                            'text-red-700' => (float) $p->peso < 100,
                            'text-emerald-700' => (float) $p->peso >= 100,
                        ])">
                            {{ number_format($p->peso, 2) }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if ((int) $p->id_tipo_pesaje === 1 || str_contains(strtolower(optional(\App\Models\TipoPesaje::find($p->id_tipo_pesaje))->tipo_pesaje ?? ''), 'foto'))
                                <span class="bg-sky-100 text-sky-800 px-2 py-0.5 rounded text-xs">📷 Foto</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs">📐 Manual</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('pesajes.show', $p) }}" class="text-sky-700 hover:underline text-sm">Ver</a>
                            @if ($puedeRegistrar)
                                <form method="POST" action="{{ route('pesajes.destroy', $p) }}" class="inline"
                                      onsubmit="return confirm('¿Eliminar este pesaje?')">
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

    {{-- Paginación de Laravel --}}
    <div class="mt-4">
        {{ $pesajes->links() }}
    </div>
@endif

<div class="mt-6">
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:underline">← Volver al panel</a>
</div>
@endsection
