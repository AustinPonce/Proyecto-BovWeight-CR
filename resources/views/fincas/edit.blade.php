@extends('layouts.app')

@section('titulo', 'Editar finca — BovWeight CR')

@section('contenido')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Editar finca</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- @method('PUT') hace que el form mande método PUT aunque el HTML solo soporte POST. --}}
    <form method="POST" action="{{ route('fincas.update', $finca) }}"
          class="bg-white shadow rounded p-6 space-y-4">
        @csrf
        @method('PUT')

        @include('fincas._form')

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('fincas.index') }}" class="text-sm text-gray-600 hover:underline">← Cancelar</a>
            <button type="submit"
                    class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded font-medium">
                Guardar cambios
            </button>
        </div>
    </form>
</div>
@endsection
