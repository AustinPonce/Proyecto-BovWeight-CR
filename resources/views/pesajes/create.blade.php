{{--
    Registro de pesaje — flujo de dos pasos (RF04):
      Paso 1: el usuario llena el formulario y presiona "Calcular peso".
              JS envía los datos vía fetch a /pesajes/calcular.
              El backend calcula el peso (y guarda la imagen si aplica).
      Paso 2: se muestra el peso estimado con opción de ingresarlo manual.
              Al confirmar, se hace submit normal al store de pesajes.

    Tabs:
      - Manual : pide medidas corporales (largo, altura, perímetro torácico).
      - Foto   : pide subir una foto del animal (microservicio YOLOv8).
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
        {{-- ============================================================
             PASO 1: Formulario de medidas / foto
             ============================================================ --}}
        <form id="form-pesaje" method="POST" action="{{ route('pesajes.store') }}" enctype="multipart/form-data"
              class="bg-white shadow rounded p-6 space-y-5">
            @csrf

            {{-- Selector de animal --}}
            <div>
                <label class="block text-sm font-medium mb-1">Animal</label>
                <select name="arete" id="select-arete" required
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

                {{-- RF03: Disclaimer prominente --}}
                <div class="bg-amber-50 border-l-4 border-amber-400 rounded p-4">
                    <div class="flex items-start gap-2">
                        <span class="text-amber-500 text-lg mt-0.5">⚠️</span>
                        <div>
                            <p class="font-semibold text-amber-800 text-sm">Estimación orientativa — no es un valor oficial</p>
                            <p class="text-amber-700 text-xs mt-1">
                                El peso calculado por IA es una <strong>aproximación</strong> basada en el análisis de la fotografía.
                                No sustituye una medición en báscula certificada. Para pesajes oficiales usá el método por medidas.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-sky-50 border border-sky-200 rounded p-3 text-sm text-sky-800">
                    📷 Tomá la foto <strong>de perfil lateral</strong>, con el cuerpo completo visible y buena iluminación.
                    Evitá fotos de frente, desde atrás o con el animal parcialmente cortado.
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Foto del animal</label>
                    <input type="file" name="imagen" accept="image/*" id="input-imagen"
                           class="w-full border border-gray-300 rounded px-3 py-2 bg-white">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG o WEBP, máx 5 MB.</p>

                    <img id="preview-imagen" src="" alt="Preview"
                         class="hidden mt-3 max-h-64 rounded border border-gray-200">
                </div>
            </div>

            {{-- Botón Calcular (paso 1) --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('pesajes.index') }}" class="text-sm text-gray-600 hover:underline">← Cancelar</a>
                <button type="button" id="btn-calcular"
                        class="bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2 rounded font-medium flex items-center gap-2">
                    <span id="txt-calcular">Calcular peso</span>
                    <svg id="spinner-calcular" class="hidden w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                </button>
            </div>

            {{-- ============================================================
                 PASO 2: Resultado del cálculo + confirmación (oculto al inicio)
                 ============================================================ --}}
            <div id="panel-resultado" class="hidden border-t border-gray-200 pt-5 space-y-4">

                {{-- Resultado --}}
                <div class="bg-emerald-50 border border-emerald-300 rounded-lg p-5">
                    <p class="text-sm text-emerald-700 font-medium mb-1">Peso estimado:</p>
                    <p class="text-4xl font-bold text-emerald-800">
                        <span id="txt-peso-estimado">—</span>
                        <span class="text-2xl font-normal ml-1">kg</span>
                    </p>
                    <p id="txt-factor-raza" class="text-xs text-emerald-600 mt-1 hidden">
                        Factor de raza aplicado
                    </p>
                </div>

                {{-- Opción de ingreso manual --}}
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-3">
                    <p class="text-sm font-medium text-gray-700">¿Querés ingresar el peso manualmente?</p>
                    <p class="text-xs text-gray-500">Si conocés el peso exacto por báscula, podés reemplazar la estimación antes de guardar.</p>

                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <input type="number" name="peso_manual" id="input-peso-manual"
                                   step="0.01" min="1" max="2000"
                                   placeholder="Ej: 420.50"
                                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <span class="text-gray-600 text-sm">kg</span>
                    </div>
                </div>

                {{-- Campos ocultos con los valores del paso 1 --}}
                <input type="hidden" name="imagen_guardada"    id="h-imagen-guardada">
                <input type="hidden" name="peso_calculado"     id="h-peso-calculado">
                <input type="hidden" name="peso_original_calc" id="h-peso-original">
                <input type="hidden" name="factor_raza_calc"   id="h-factor-raza">

                {{-- Botón final de guardar --}}
                <div class="flex items-center justify-between pt-2">
                    <button type="button" id="btn-recalcular"
                            class="text-sm text-gray-500 hover:text-gray-700 hover:underline">
                        ← Volver a calcular
                    </button>
                    <button type="submit"
                            class="bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2 rounded font-medium">
                        Guardar pesaje
                    </button>
                </div>
            </div>

            {{-- Error del cálculo --}}
            <div id="panel-error" class="hidden bg-red-50 border border-red-300 text-red-800 rounded p-4 text-sm mt-2">
                <span id="txt-error-calculo"></span>
            </div>

        </form>

        <script>
            (function () {
                /* --------------------------------------------------------
                   Tab switch: Manual ↔ Foto
                -------------------------------------------------------- */
                const radios     = document.querySelectorAll('.tipo-radio');
                const panelManual = document.getElementById('panel-manual');
                const panelFoto   = document.getElementById('panel-foto');
                const pills       = document.querySelectorAll('.tab-pill');

                function actualizarTabs() {
                    const tipo = document.querySelector('.tipo-radio:checked')?.value || 'manual';
                    panelManual.classList.toggle('hidden', tipo !== 'manual');
                    panelFoto.classList.toggle('hidden',   tipo !== 'foto');

                    pills.forEach((pill, i) => {
                        const activo = radios[i].value === tipo;
                        pill.classList.toggle('bg-emerald-700', activo);
                        pill.classList.toggle('text-white',     activo);
                        pill.classList.toggle('font-medium',    activo);
                        pill.classList.toggle('bg-gray-50',     !activo);
                        pill.classList.toggle('text-gray-700',  !activo);
                    });
                }
                // Al cambiar tab después de un cálculo, reiniciar también el resultado.
                radios.forEach(r => r.addEventListener('change', () => {
                    actualizarTabs();
                    resetResultado();
                }));
                actualizarTabs(); // Llamada inicial: sin resetResultado (aún no están las const del paso 2)

                /* --------------------------------------------------------
                   Preview de imagen
                -------------------------------------------------------- */
                const inputImg  = document.getElementById('input-imagen');
                const previewImg = document.getElementById('preview-imagen');
                inputImg?.addEventListener('change', e => {
                    const file = e.target.files[0];
                    if (!file) { previewImg.classList.add('hidden'); return; }
                    previewImg.src = URL.createObjectURL(file);
                    previewImg.classList.remove('hidden');
                });

                /* --------------------------------------------------------
                   Elementos del paso 2
                -------------------------------------------------------- */
                const btnCalcular   = document.getElementById('btn-calcular');
                const txtCalcular   = document.getElementById('txt-calcular');
                const spinnerCalc   = document.getElementById('spinner-calcular');
                const panelResultado = document.getElementById('panel-resultado');
                const panelError    = document.getElementById('panel-error');
                const txtError      = document.getElementById('txt-error-calculo');
                const btnRecalcular = document.getElementById('btn-recalcular');
                const panelForm     = document.getElementById('panel-manual'); // alias

                const txtPesoEst   = document.getElementById('txt-peso-estimado');
                const txtFactorRaza = document.getElementById('txt-factor-raza');
                const hImagenGuardada = document.getElementById('h-imagen-guardada');
                const hPesoCalc     = document.getElementById('h-peso-calculado');
                const hPesoOrig     = document.getElementById('h-peso-original');
                const hFactorRaza   = document.getElementById('h-factor-raza');

                /* Ocultar resultado y mostrar formulario de entrada */
                function resetResultado() {
                    panelResultado.classList.add('hidden');
                    panelError.classList.add('hidden');
                    btnCalcular.classList.remove('hidden');
                    document.getElementById('select-arete').disabled  = false;
                    radios.forEach(r => r.disabled = false);
                    document.getElementById('input-peso-manual').value = '';
                }

                btnRecalcular?.addEventListener('click', resetResultado);

                // Los campos disabled no se incluyen en el submit.
                // Los reactivamos antes de que el form recolecte los datos.
                document.getElementById('form-pesaje').addEventListener('submit', () => {
                    document.getElementById('select-arete').disabled = false;
                    radios.forEach(r => r.disabled = false);
                });

                /* --------------------------------------------------------
                   Paso 1: Calcular
                -------------------------------------------------------- */
                btnCalcular?.addEventListener('click', async () => {
                    panelError.classList.add('hidden');

                    // Recopilar datos del form.
                    const form     = document.getElementById('form-pesaje');
                    const formData = new FormData(form);
                    // Quitar campos del paso 2 para no enviarlos al endpoint calcular.
                    formData.delete('imagen_guardada');
                    formData.delete('peso_calculado');
                    formData.delete('peso_original_calc');
                    formData.delete('factor_raza_calc');
                    formData.delete('peso_manual');

                    // Estado de carga.
                    txtCalcular.textContent  = 'Calculando…';
                    spinnerCalc.classList.remove('hidden');
                    btnCalcular.disabled      = true;

                    try {
                        const resp = await fetch('{{ route('pesajes.calcular') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        const json = await resp.json();

                        if (!resp.ok) {
                            // 422 u otro error del servidor.
                            const msg = json.message || json.error || 'Error al calcular el peso.';
                            txtError.textContent = msg;
                            panelError.classList.remove('hidden');
                            return;
                        }

                        // Mostrar resultado.
                        txtPesoEst.textContent  = json.peso_calculado.toLocaleString('es-CR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        if (json.factor_raza_calc && json.factor_raza_calc !== 1) {
                            txtFactorRaza.textContent = `Factor de raza: ${json.factor_raza_calc} (peso bruto: ${json.peso_original_calc} kg)`;
                            txtFactorRaza.classList.remove('hidden');
                        } else {
                            txtFactorRaza.classList.add('hidden');
                        }

                        // Guardar valores en hidden inputs.
                        hImagenGuardada.value = json.imagen_guardada ?? '';
                        hPesoCalc.value       = json.peso_calculado;
                        hPesoOrig.value       = json.peso_original_calc;
                        hFactorRaza.value     = json.factor_raza_calc;

                        // Bloquear cambios en paso 1 mientras se confirma.
                        document.getElementById('select-arete').disabled = true;
                        radios.forEach(r => r.disabled = true);

                        // Mostrar panel resultado.
                        panelResultado.classList.remove('hidden');
                        btnCalcular.classList.add('hidden');
                        panelResultado.scrollIntoView({ behavior: 'smooth', block: 'start' });

                    } catch (err) {
                        txtError.textContent = 'No se pudo conectar con el servidor. Intentá de nuevo.';
                        panelError.classList.remove('hidden');
                    } finally {
                        txtCalcular.textContent = 'Calcular peso';
                        spinnerCalc.classList.add('hidden');
                        btnCalcular.disabled     = false;
                    }
                });

            })();
        </script>
    @endif
</div>
@endsection
