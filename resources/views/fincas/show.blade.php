{{--
    Detalle de una finca. Muestra info, animales y veterinarios asignados.
    Desde acá luego vamos a saltar al CRUD de animales (sub-bloque 2B).
--}}
@extends('layouts.app')

@section('titulo', $finca->nombre . ' — BovWeight CR')

@section('contenido')
@php
    $usuario = auth()->user();
    $puedeEditar = $usuario->esAdmin()
        || ($usuario->esGanadero() && $finca->cedula === $usuario->cedula);
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $finca->nombre }}</h1>
        <p class="text-sm text-gray-600 mt-1">{{ $finca->ubicacion }}</p>
    </div>
    @if ($puedeEditar)
        <a href="{{ route('fincas.edit', $finca) }}"
           class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded text-sm">
            Editar
        </a>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

    {{-- Datos básicos --}}
    <div class="bg-white shadow rounded p-5">
        <h2 class="font-semibold text-gray-700 mb-3">Datos</h2>
        <dl class="text-sm space-y-2">
            <div class="flex justify-between"><dt class="text-gray-600">Dueño</dt><dd>{{ $finca->usuario->nombre ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">Cédula dueño</dt><dd class="font-mono">{{ $finca->cedula }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">Total animales</dt><dd>{{ $finca->animales->count() }}</dd></div>
        </dl>
    </div>

    {{-- Veterinarios asignados --}}
    <div class="bg-white shadow rounded p-5">
        <h2 class="font-semibold text-gray-700 mb-3">Veterinarios asignados</h2>

        @if ($finca->veterinarios->isEmpty())
            <p class="text-sm text-gray-500 mb-3">Sin veterinarios asignados.</p>
        @else
            <ul class="text-sm space-y-2 mb-3">
                @foreach ($finca->veterinarios as $vet)
                    <li class="flex items-center justify-between">
                        <span>
                            {{ $vet->nombre }}
                            <span class="text-gray-500 font-mono text-xs">({{ $vet->cedula }})</span>
                        </span>
                        @if ($puedeEditar)
                            <form method="POST"
                                  action="{{ route('fincas.veterinarios.destroy', [$finca, $vet->cedula]) }}"
                                  onsubmit="return confirm('¿Desasignar a {{ $vet->nombre }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs">Quitar</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- Formulario para asignar veterinario (solo dueño o admin) --}}
        @if ($puedeEditar)
            <div class="border-t pt-3">
                <p class="text-xs font-medium text-gray-600 mb-2">Asignar veterinario</p>
                <form method="POST" action="{{ route('fincas.veterinarios.store', $finca) }}" class="space-y-2">
                    @csrf
                    <div>
                        <input type="text" id="vet-buscar" placeholder="Nombre o cédula del veterinario"
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               autocomplete="off">
                        <input type="hidden" name="cedula_veterinario" id="vet-cedula">
                        <div id="vet-resultados"
                             class="hidden border border-gray-200 rounded bg-white shadow-lg mt-1 max-h-48 overflow-y-auto z-10 absolute">
                        </div>
                    </div>
                    <div id="vet-seleccionado" class="hidden text-sm text-emerald-700 font-medium"></div>
                    <button type="submit" id="vet-btn" disabled
                            class="w-full bg-emerald-700 hover:bg-emerald-800 text-white text-sm py-2 rounded disabled:opacity-50 disabled:cursor-not-allowed">
                        Asignar
                    </button>
                </form>
            </div>

            <script>
            (function() {
                const input = document.getElementById('vet-buscar');
                const hiddenInput = document.getElementById('vet-cedula');
                const resultados = document.getElementById('vet-resultados');
                const seleccionado = document.getElementById('vet-seleccionado');
                const btn = document.getElementById('vet-btn');
                let timeout;

                input.addEventListener('input', () => {
                    clearTimeout(timeout);
                    hiddenInput.value = '';
                    btn.disabled = true;
                    seleccionado.classList.add('hidden');

                    const q = input.value.trim();
                    if (q.length < 2) { resultados.classList.add('hidden'); return; }

                    timeout = setTimeout(() => {
                        fetch(`{{ route('veterinarios.buscar') }}?q=${encodeURIComponent(q)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(r => r.json())
                        .then(data => {
                            resultados.innerHTML = '';
                            if (!data.length) {
                                resultados.innerHTML = '<p class="px-3 py-2 text-sm text-gray-500">Sin resultados.</p>';
                            } else {
                                data.forEach(v => {
                                    const el = document.createElement('button');
                                    el.type = 'button';
                                    el.className = 'w-full text-left px-3 py-2 text-sm hover:bg-emerald-50';
                                    el.textContent = `${v.nombre} (${v.cedula})`;
                                    el.addEventListener('click', () => {
                                        hiddenInput.value = v.cedula;
                                        input.value = `${v.nombre} (${v.cedula})`;
                                        seleccionado.textContent = `Veterinario seleccionado: ${v.nombre}`;
                                        seleccionado.classList.remove('hidden');
                                        btn.disabled = false;
                                        resultados.classList.add('hidden');
                                    });
                                    resultados.appendChild(el);
                                });
                            }
                            resultados.classList.remove('hidden');
                        });
                    }, 300);
                });

                document.addEventListener('click', e => {
                    if (!resultados.contains(e.target) && e.target !== input) {
                        resultados.classList.add('hidden');
                    }
                });
            })();
            </script>
        @endif
    </div>
</div>

{{-- Animales de la finca --}}
<div class="bg-white shadow rounded p-5">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-gray-700">Animales en esta finca</h2>
        <div class="space-x-3">
            @if (! $usuario->esVeterinario())
                <a href="{{ route('animales.create', ['finca' => $finca->id_finca]) }}"
                   class="text-emerald-700 hover:underline text-sm font-medium">+ Nuevo animal</a>
            @endif
            <a href="{{ route('animales.index', ['finca' => $finca->id_finca]) }}"
               class="text-sky-700 hover:underline text-sm">Ver todos →</a>
        </div>
    </div>

    @if ($finca->animales->isEmpty())
        <p class="text-sm text-gray-500">Todavía no hay animales registrados en esta finca.</p>
    @else
        <ul class="text-sm space-y-1">
            @foreach ($finca->animales->take(10) as $animal)
                <li>
                    • <a href="{{ route('animales.show', $animal) }}" class="hover:underline">
                        Arete <span class="font-mono">{{ $animal->arete }}</span> — {{ $animal->nombre ?? 'Sin nombre' }}
                    </a>
                </li>
            @endforeach
        </ul>
        @if ($finca->animales->count() > 10)
            <p class="text-xs text-gray-500 mt-2">
                Mostrando 10 de {{ $finca->animales->count() }}.
                <a href="{{ route('animales.index', ['finca' => $finca->id_finca]) }}"
                   class="text-sky-700 hover:underline">Ver todos</a>
            </p>
        @endif
    @endif
</div>

<div class="mt-6">
    <a href="{{ route('fincas.index') }}" class="text-sm text-gray-600 hover:underline">← Volver al listado</a>
</div>
@endsection
