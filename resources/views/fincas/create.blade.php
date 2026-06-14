@extends('layouts.app')

@section('titulo', 'Nueva finca — BovWeight CR')

@section('contenido')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Nueva finca</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('fincas.store') }}" class="bg-white shadow rounded p-6 space-y-4">
        @csrf
        {{-- Pasamos una instancia "vacía" del modelo para que el partial funcione
             en modo creación (todos los inputs arrancan vacíos vía old()). --}}
        @include('fincas._form', ['finca' => new \App\Models\Finca()])

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('fincas.index') }}" class="text-sm text-gray-600 hover:underline">← Cancelar</a>
            <button type="submit"
                    class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded font-medium">
                Crear finca
            </button>
        </div>
    </form>
</div>
@endsection
