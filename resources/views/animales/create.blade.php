@extends('layouts.app')

@section('titulo', 'Nuevo animal — BovWeight CR')

@section('contenido')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Registrar animal</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if ($fincas->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-900 rounded p-4 text-sm">
            No tenés fincas registradas.
            <a href="{{ route('fincas.create') }}" class="text-emerald-700 hover:underline">
                Creá primero una finca
            </a>
            antes de registrar animales.
        </div>
    @else
        <form method="POST" action="{{ route('animales.store') }}"
              class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            @include('animales._form', [
                'animal' => new \App\Models\Animal(),
                'esEdicion' => false,
            ])

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('animales.index') }}" class="text-sm text-gray-600 hover:underline">← Cancelar</a>
                <button type="submit"
                        class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded font-medium">
                    Registrar animal
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
