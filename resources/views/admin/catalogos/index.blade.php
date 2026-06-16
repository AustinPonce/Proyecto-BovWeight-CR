@extends('layouts.app')

@section('titulo', 'Catálogos del Sistema — BovWeight CR')

@section('contenido')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Catálogos del sistema</h1>
    <p class="text-sm text-gray-600 mt-1">Razas, estados y medicamentos registrados.</p>
</div>

@if (session('exito'))
    <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded">
        {{ session('exito') }}
    </div>
@endif

@php $tab = request('tab', 'medicamentos'); @endphp

{{-- Tabs --}}
<div class="border-b border-gray-200 mb-6">
    <nav class="-mb-px flex gap-6">
        @foreach ([
            'medicamentos' => 'Medicamentos',
            'razas'        => 'Razas',
            'estados'      => 'Estados',
        ] as $key => $label)
            <a href="{{ route('admin.catalogos.index', ['tab' => $key]) }}"
               class="pb-3 text-sm font-medium border-b-2 @if($tab === $key) border-rose-500 text-rose-700 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif">
                {{ $label }}
                @if($key === 'medicamentos')
                    <span class="ml-1 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded">{{ $medicamentos->count() }}</span>
                @elseif($key === 'razas')
                    <span class="ml-1 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded">{{ $razas->count() }}</span>
                @else
                    <span class="ml-1 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded">{{ $estados->count() }}</span>
                @endif
            </a>
        @endforeach
    </nav>
</div>

{{-- ===== MEDICAMENTOS ===== --}}
@if ($tab === 'medicamentos')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Formulario agregar --}}
    <div class="bg-white shadow rounded p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Agregar medicamento</h2>
        <form method="POST" action="{{ route('admin.catalogos.medicamentos.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Unidad</label>
                <select name="unidad" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-rose-400">
                    <option value="ml" @selected(old('unidad') === 'ml')>ml</option>
                    <option value="mg" @selected(old('unidad') === 'mg')>mg</option>
                    <option value="g"  @selected(old('unidad') === 'g')>g</option>
                    <option value="UI" @selected(old('unidad') === 'UI')>UI</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dosis por kg de peso</label>
                <input type="number" name="dosis_por_kg" value="{{ old('dosis_por_kg') }}" step="0.0001" min="0" required
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
                <p class="text-xs text-gray-500 mt-0.5">Ej: 0.05 ml por kg</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Descripción (opcional)</label>
                <textarea name="descripcion" rows="2"
                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">{{ old('descripcion') }}</textarea>
            </div>
            <button type="submit"
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold py-2 rounded">
                Agregar
            </button>
        </form>
    </div>

    {{-- Lista --}}
    <div class="lg:col-span-2">
        @if ($medicamentos->isEmpty())
            <div class="bg-white border border-gray-200 rounded p-8 text-center text-gray-500">
                No hay medicamentos registrados todavía.
            </div>
        @else
            <div class="bg-white shadow rounded overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr class="text-left text-gray-600">
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Dosis/kg</th>
                            <th class="px-4 py-3">Descripción</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($medicamentos as $med)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ $med->nombre }}</td>
                                <td class="px-4 py-3">{{ $med->dosis_por_kg }} {{ $med->unidad }}/kg</td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ $med->descripcion ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.catalogos.medicamentos.destroy', $med) }}" class="inline"
                                          onsubmit="return confirm('¿Eliminar {{ $med->nombre }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-700 hover:underline text-xs">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endif

{{-- ===== RAZAS ===== --}}
@if ($tab === 'razas')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white shadow rounded p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Agregar raza</h2>
        <form method="POST" action="{{ route('admin.catalogos.razas.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre de la raza</label>
                <input type="text" name="raza" value="{{ old('raza') }}" required
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
            </div>
            <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold py-2 rounded">
                Agregar
            </button>
        </form>
    </div>
    <div class="lg:col-span-2 bg-white shadow rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-gray-600">
                    <th class="px-4 py-3">Raza</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($razas as $raza)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $raza->raza }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.catalogos.razas.destroy', $raza) }}" class="inline"
                                  onsubmit="return confirm('¿Eliminar la raza {{ $raza->raza }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline text-xs">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ===== ESTADOS ===== --}}
@if ($tab === 'estados')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white shadow rounded p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Agregar estado</h2>
        <form method="POST" action="{{ route('admin.catalogos.estados.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del estado</label>
                <input type="text" name="estado" value="{{ old('estado') }}" required
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
            </div>
            <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold py-2 rounded">
                Agregar
            </button>
        </form>
    </div>
    <div class="lg:col-span-2 bg-white shadow rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-gray-600">
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($estados as $estado)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $estado->estado }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.catalogos.estados.destroy', $estado) }}" class="inline"
                                  onsubmit="return confirm('¿Eliminar el estado {{ $estado->estado }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline text-xs">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="mt-8">
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:underline">← Volver al panel</a>
</div>
@endsection
