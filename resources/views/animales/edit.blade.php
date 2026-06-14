@extends('layouts.app')

@section('titulo', 'Editar animal — BovWeight CR')

@section('contenido')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Editar animal</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('animales.update', $animal) }}"
          class="bg-white shadow rounded p-6 space-y-4">
        @csrf
        @method('PUT')

        @include('animales._form', ['esEdicion' => true])

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('animales.show', $animal) }}" class="text-sm text-gray-600 hover:underline">← Cancelar</a>
            <button type="submit"
                    class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded font-medium">
                Guardar cambios
            </button>
        </div>
    </form>
</div>
@endsection
