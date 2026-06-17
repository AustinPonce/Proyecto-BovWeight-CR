@extends('layouts.app')

@section('titulo', 'Calculadora de Dosis — BovWeight CR')

@section('contenido')
@php $usuario = auth()->user(); @endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Calculadora de dosis</h1>
    <p class="text-sm text-gray-600 mt-1">
        Calculá la dosis de medicamento según el peso del animal.
    </p>
</div>

@if ($errors->any())
    <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Formulario de cálculo --}}
    <div class="bg-white shadow rounded p-6">
        <h2 class="font-semibold text-gray-700 mb-4">Datos del cálculo</h2>

        <form method="POST" action="{{ route('dosis.calcular') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Animal</label>
                <select name="arete" required id="animal-select"
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <option value="">— Seleccioná un animal —</option>
                    @foreach ($animales as $animal)
                        <option value="{{ $animal->arete }}"
                                data-ultimo-peso="{{ optional($animal->pesajes->sortByDesc('fecha')->first())->peso ?? '' }}"
                                @selected(old('arete', $resultado['animal']->arete ?? '') === $animal->arete)>
                            {{ $animal->arete }}{{ $animal->nombre ? ' — ' . $animal->nombre : '' }}
                            ({{ $animal->finca->nombre ?? '?' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Peso del animal (kg)</label>
                <div class="flex gap-2">
                    <input type="number" name="peso_referencia" id="peso-input"
                           value="{{ old('peso_referencia', $resultado['peso'] ?? '') }}"
                           step="0.1" min="10" max="3000" placeholder="Automático desde último pesaje"
                           class="flex-1 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <button type="button" id="btn-usar-ultimo"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm hidden">
                        Usar último
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Si dejás vacío, se usa el peso del último pesaje registrado.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Medicamento</label>
                <select name="id_medicamento" required
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <option value="">— Seleccioná un medicamento —</option>
                    @foreach ($medicamentos as $med)
                        <option value="{{ $med->id_medicamento }}"
                                data-dosis="{{ $med->dosis_por_kg }}"
                                data-unidad="{{ $med->unidad }}"
                                @selected(old('id_medicamento', $resultado['medicamento']->id_medicamento ?? '') == $med->id_medicamento)>
                            {{ $med->nombre }} ({{ $med->dosis_por_kg }} {{ $med->unidad }}/kg)
                        </option>
                    @endforeach
                </select>
                @if ($medicamentos->isEmpty())
                    <p class="text-xs text-amber-600 mt-1">
                        No hay medicamentos registrados.
                        @if (auth()->user()->esAdmin())
                            <a href="{{ route('admin.catalogos.index', ['tab' => 'medicamentos']) }}" class="underline">Agregar medicamentos</a>.
                        @else
                            Pedile al administrador que los agregue.
                        @endif
                    </p>
                @endif
            </div>

            {{-- Comentario veterinario --}}
            @if ($usuario->esVeterinario())
                <div>
                    <label class="block text-sm font-medium mb-1">Comentario (opcional)</label>
                    <textarea name="comentario" rows="3" placeholder="Observaciones, indicaciones de administración..."
                              class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-400 text-sm">{{ old('comentario') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">El comentario quedará asociado al animal y visible para el ganadero.</p>
                </div>
            @endif

            <button type="submit"
                    class="w-full bg-sky-700 hover:bg-sky-800 text-white font-semibold py-2 rounded">
                Calcular dosis
            </button>
        </form>
    </div>

    {{-- Resultado --}}
    <div>
        @if ($resultado)
            <div class="bg-sky-50 border border-sky-200 rounded p-6 mb-4">
                <h2 class="font-semibold text-sky-800 mb-4 text-lg">Resultado del cálculo</h2>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Animal</dt>
                        <dd class="font-medium">
                            {{ $resultado['animal']->nombre ?? $resultado['animal']->arete }}
                            <span class="font-mono text-xs text-gray-500">({{ $resultado['animal']->arete }})</span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Peso base</dt>
                        <dd class="font-medium">{{ number_format($resultado['peso'], 2) }} kg</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Medicamento</dt>
                        <dd class="font-medium">{{ $resultado['medicamento']->nombre }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Dosis por kg</dt>
                        <dd>{{ $resultado['medicamento']->dosis_por_kg }} {{ $resultado['medicamento']->unidad }}/kg</dd>
                    </div>
                    <div class="flex justify-between border-t pt-3">
                        <dt class="text-gray-700 font-semibold">Dosis total</dt>
                        <dd class="text-2xl font-bold text-sky-700">
                            {{ number_format($resultado['dosis_total'], 4) }}
                            <span class="text-base font-normal text-gray-600">{{ $resultado['medicamento']->unidad }}</span>
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 bg-white border border-sky-100 rounded p-3">
                    <p class="text-xs text-gray-500 font-medium mb-1">Fórmula aplicada:</p>
                    <p class="font-mono text-sm text-gray-800">{{ $resultado['formula'] }}</p>
                </div>

                @if ($resultado['medicamento']->descripcion)
                    <div class="mt-3 text-xs text-gray-600">
                        <strong>Indicaciones:</strong> {{ $resultado['medicamento']->descripcion }}
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white border border-gray-200 rounded p-8 text-center text-gray-400">
                <p class="text-4xl mb-2">💉</p>
                <p>Completá el formulario y hacé clic en <strong>Calcular dosis</strong>.</p>
            </div>
        @endif

        {{-- Tabla de medicamentos disponibles --}}
        @if ($medicamentos->isNotEmpty())
            <div class="bg-white shadow rounded p-4 mt-4">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">Medicamentos disponibles</h3>
                <table class="w-full text-xs">
                    <thead class="bg-gray-50">
                        <tr class="text-gray-600 text-left">
                            <th class="px-2 py-1">Nombre</th>
                            <th class="px-2 py-1">Dosis/kg</th>
                            <th class="px-2 py-1">Descripción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($medicamentos as $med)
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 py-1.5 font-medium">{{ $med->nombre }}</td>
                                <td class="px-2 py-1.5">{{ $med->dosis_por_kg }} {{ $med->unidad }}</td>
                                <td class="px-2 py-1.5 text-gray-500">{{ $med->descripcion ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="mt-6">
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:underline">← Volver al panel</a>
</div>

<script>
(function() {
    const animalSelect = document.getElementById('animal-select');
    const pesoInput = document.getElementById('peso-input');
    const btnUsarUltimo = document.getElementById('btn-usar-ultimo');

    animalSelect.addEventListener('change', () => {
        const option = animalSelect.options[animalSelect.selectedIndex];
        const ultimoPeso = option.dataset.ultimoPeso;
        if (ultimoPeso) {
            btnUsarUltimo.classList.remove('hidden');
            btnUsarUltimo.title = `Último pesaje: ${ultimoPeso} kg`;
        } else {
            btnUsarUltimo.classList.add('hidden');
        }
    });

    btnUsarUltimo.addEventListener('click', () => {
        const option = animalSelect.options[animalSelect.selectedIndex];
        pesoInput.value = option.dataset.ultimoPeso;
    });
})();
</script>
@endsection
