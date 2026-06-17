{{--
    Registro de pesaje. La pantalla tiene 2 tabs:
      - Manual : pide medidas corporales (largo, altura, perímetro torácico).
                 El backend calcula el peso con la fórmula clásica.
      - Foto   : pide subir una foto del animal.
                 El backend (por ahora) devuelve un peso aleatorio (mock).
                 En 2E se conecta al microservicio Python con YOLOv8.

    JS muy chiquito al final maneja el switch entre tabs y el preview de imagen.
--}}
@extends('layouts.app')

@section('titulo', 'Registrar pesaje — BovWeight CR')

@section('contenido')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Registrar pesaje</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if ($animales->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-900 rounded p-4 text-sm">
            No tenés animales registrados. Primero
            <a href="{{ route('animales.create') }}" class="text-emerald-700 hover:underline">registrá un animal</a>.
        </div>
    @else
        <form method="POST" action="{{ route('pesajes.store') }}" enctype="multipart/form-data"
              class="bg-white shadow rounded p-6 space-y-5">
            @csrf

            {{-- Selector de animal --}}
            <div>
                <label class="block text-sm font-medium mb-1">Animal</label>
                <select name="arete" required
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">— Seleccioná un animal —</option>
                    @foreach ($animales as $a)
                        <option value="{{ $a->arete }}"
                            @selected(old('arete', $animalPreseleccionado) === $a->arete)>
                            {{ $a->arete }}
                            @if ($a->nombre) — {{ $a->nombre }} @endif
                            ({{ $a->finca->nombre }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tabs Manual / Foto --}}
            <div>
                <label class="block text-sm font-medium mb-2">Modo de pesaje</label>
                <div class="grid grid-cols-2 border border-gray-300 rounded overflow-hidden">
                    <label class="cursor-pointer">
                        <input type="radio" name="tipo" value="manual" class="hidden tipo-radio"
                               @checked(old('tipo', 'manual') === 'manual')>
                        <div class="tab-pill text-center py-3 text-sm transition select-none">
                            📐 Por medidas
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tipo" value="foto" class="hidden tipo-radio"
                               @checked(old('tipo') === 'foto')>
                        <div class="tab-pill text-center py-3 text-sm transition select-none">
                            📷 Por foto
                        </div>
                    </label>
                </div>
            </div>

            {{-- Panel: medidas manuales --}}
            <div id="panel-manual" class="space-y-4">
                <p class="text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded p-3">
                    Fórmula:
                    <span class="font-mono">peso = (torax² × largo) / 10840</span>.
                    Medí en centímetros.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Largo del cuerpo (cm)</label>
                        <input type="number" name="largo_cuerpo" step="0.1" min="30" max="300"
                               value="{{ old('largo_cuerpo') }}"
                               class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Altura (cm)</label>
                        <input type="number" name="altura" step="0.1" min="30" max="200"
                               value="{{ old('altura') }}"
                               class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Perímetro torácico (cm)</label>
                        <input type="number" name="perimetro_toracico" step="0.1" min="30" max="300"
                               value="{{ old('perimetro_toracico') }}"
                               class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                </div>
            </div>

            {{-- Panel: subir foto --}}
            <div id="panel-foto" class="space-y-4 hidden">
                <p class="text-sm text-gray-600 bg-sky-50 border border-sky-200 rounded p-3">
                    Subí una foto de cuerpo completo del animal de costado.
                    El sistema usa IA para estimar el peso.
                    <strong>Por ahora el peso es simulado</strong> (se conecta al modelo real en el próximo bloque).
                </p>

                <div>
                    <label class="block text-sm font-medium mb-1">Foto del animal</label>
                    <input type="file" name="imagen" accept="image/*" id="input-imagen"
                           class="w-full border border-gray-300 rounded px-3 py-2 bg-white">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG o WEBP, máx 5 MB.</p>

                    <img id="preview-imagen" src="" alt="Preview"
                         class="hidden mt-3 max-h-64 rounded border border-gray-200">
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('pesajes.index') }}" class="text-sm text-gray-600 hover:underline">← Cancelar</a>
                <button type="submit"
                        class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded font-medium">
                    Registrar pesaje
                </button>
            </div>
        </form>

        {{-- Script chiquito para alternar tabs + preview de imagen --}}
        <script>
            (function () {
                const radios = document.querySelectorAll('.tipo-radio');
                const panelManual = document.getElementById('panel-manual');
                const panelFoto   = document.getElementById('panel-foto');
                const pills       = document.querySelectorAll('.tab-pill');

                function actualizar() {
                    const tipo = document.querySelector('.tipo-radio:checked')?.value || 'manual';
                    panelManual.classList.toggle('hidden', tipo !== 'manual');
                    panelFoto.classList.toggle('hidden',   tipo !== 'foto');

                    // Estilo visual de los tabs.
                    pills.forEach((pill, i) => {
                        const valor = radios[i].value;
                        if (valor === tipo) {
                            pill.classList.add('bg-emerald-700', 'text-white', 'font-medium');
                            pill.classList.remove('bg-gray-50', 'text-gray-700');
                        } else {
                            pill.classList.add('bg-gray-50', 'text-gray-700');
                            pill.classList.remove('bg-emerald-700', 'text-white', 'font-medium');
                        }
                    });
                }
                radios.forEach(r => r.addEventListener('change', actualizar));
                actualizar();

                // Preview de imagen.
                const input = document.getElementById('input-imagen');
                const preview = document.getElementById('preview-imagen');
                input?.addEventListener('change', e => {
                    const file = e.target.files[0];
                    if (!file) { preview.classList.add('hidden'); return; }
                    preview.src = URL.createObjectURL(file);
                    preview.classList.remove('hidden');
                });
            })();
        </script>
    @endif
</div>
@endsection
