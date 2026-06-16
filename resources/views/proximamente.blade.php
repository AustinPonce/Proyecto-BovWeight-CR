{{--
    Vista placeholder para features que están planeadas pero todavía no implementadas.
    Se elimina cuando cada feature se complete.

    Espera variables: $titulo, $descripcion, $fase
--}}
@extends('layouts.app')

@section('titulo', $titulo . ' — BovWeight CR')

@section('contenido')
<div class="max-w-xl mx-auto bg-white shadow rounded p-8 text-center mt-12">
    <div class="text-6xl mb-4">🚧</div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $titulo }}</h1>
    <p class="text-gray-600 mb-4">{{ $descripcion }}</p>
    <p class="text-xs text-gray-500 mb-6">
        Esta vista se implementa en <span class="font-mono bg-gray-100 px-2 py-0.5 rounded">{{ $fase }}</span>.
    </p>
    <a href="{{ route('dashboard') }}"
       class="inline-block bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded text-sm">
        ← Volver al panel
    </a>
</div>
@endsection
