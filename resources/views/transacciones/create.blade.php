@extends('layouts.app')

@section('titulo', 'Nueva Transacción — BovWeight CR')

@section('contenido')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Registrar transacción de ganado</h1>
    <p class="text-sm text-gray-600 mb-6">
        Registrá una compra o venta de ganado bovino negociada en kilogramos en pie.
        El monto total se calcula automáticamente.
    </p>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('transacciones.store') }}" class="bg-white shadow rounded p-6 space-y-5" id="form-transaccion">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tipo de transacción</label>
                <select name="tipo" required id="tipo-select"
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">— Seleccioná —</option>
                    <option value="venta"  @selected(old('tipo') === 'venta')>Venta (yo vendo)</option>
                    <option value="compra" @selected(old('tipo') === 'compra')>Compra (yo compro)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Animal</label>
                <select name="arete" required id="animal-select"
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">— Seleccioná un animal —</option>
                    @foreach ($animales as $animal)
                        <option value="{{ $animal->arete }}"
                                data-ultimo-peso="{{ optional($animal->pesajes->first())->peso ?? '' }}"
                                @selected(old('arete') === $animal->arete)>
                            {{ $animal->arete }}{{ $animal->nombre ? ' — ' . $animal->nombre : '' }}
                            ({{ $animal->finca->nombre ?? '?' }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="border rounded p-4 bg-gray-50">
            <p id="contraparte-label" class="text-sm font-medium text-gray-700 mb-3">
                Datos del comprador / vendedor
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre completo</label>
                    <input type="text" name="nombre_contraparte" value="{{ old('nombre_contraparte') }}" required
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cédula / ID (opcional)</label>
                    <input type="text" name="cedula_contraparte" value="{{ old('cedula_contraparte') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Precio por kg (₡)</label>
                <input type="number" name="precio_por_kg" id="precio-kg" value="{{ old('precio_por_kg') }}"
                       step="0.01" min="1" required
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">
                    Peso negociado (kg)
                    <button type="button" id="btn-usar-peso" class="ml-1 text-xs text-sky-600 hover:underline hidden">
                        (usar último pesaje)
                    </button>
                </label>
                <input type="number" name="peso_negociado" id="peso-kg" value="{{ old('peso_negociado') }}"
                       step="0.01" min="1" max="3000" required
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Monto total estimado (₡)</label>
                <input type="text" id="monto-preview" readonly placeholder="—"
                       class="w-full border border-gray-200 rounded px-3 py-2 bg-gray-50 text-emerald-700 font-bold text-lg focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Notas / observaciones (opcional)</label>
            <textarea name="notas" rows="3"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">{{ old('notas') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-amber-600 hover:bg-amber-700 text-white font-semibold px-6 py-2 rounded">
                Registrar transacción
            </button>
            <a href="{{ route('transacciones.index') }}" class="text-sm text-gray-600 hover:underline self-center">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
(function() {
    const animalSelect = document.getElementById('animal-select');
    const tipoSelect   = document.getElementById('tipo-select');
    const precioInput  = document.getElementById('precio-kg');
    const pesoInput    = document.getElementById('peso-kg');
    const montoPreview = document.getElementById('monto-preview');
    const btnUsarPeso  = document.getElementById('btn-usar-peso');
    const contrLabel   = document.getElementById('contraparte-label');

    function calcular() {
        const precio = parseFloat(precioInput.value) || 0;
        const peso   = parseFloat(pesoInput.value) || 0;
        if (precio > 0 && peso > 0) {
            montoPreview.value = '₡' + (precio * peso).toLocaleString('es-CR', { minimumFractionDigits: 2 });
        } else {
            montoPreview.value = '';
        }
    }

    precioInput.addEventListener('input', calcular);
    pesoInput.addEventListener('input', calcular);

    animalSelect.addEventListener('change', () => {
        const opt = animalSelect.options[animalSelect.selectedIndex];
        const ultimoPeso = opt.dataset.ultimoPeso;
        if (ultimoPeso) {
            btnUsarPeso.classList.remove('hidden');
            btnUsarPeso.textContent = `(usar último pesaje: ${ultimoPeso} kg)`;
        } else {
            btnUsarPeso.classList.add('hidden');
        }
    });

    btnUsarPeso.addEventListener('click', () => {
        const opt = animalSelect.options[animalSelect.selectedIndex];
        pesoInput.value = opt.dataset.ultimoPeso;
        calcular();
    });

    tipoSelect.addEventListener('change', () => {
        if (tipoSelect.value === 'venta') {
            contrLabel.textContent = 'Datos del comprador';
        } else if (tipoSelect.value === 'compra') {
            contrLabel.textContent = 'Datos del vendedor';
        } else {
            contrLabel.textContent = 'Datos del comprador / vendedor';
        }
    });
})();
</script>
@endsection
