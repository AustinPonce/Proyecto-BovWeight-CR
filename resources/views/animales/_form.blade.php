{{--
    Partial reutilizable del formulario de Animal.
    Variables esperadas:
      $animal             → instancia (vacía al crear, con datos al editar)
      $fincas             → fincas a las que el usuario tiene acceso
      $razas, $sexos, $estados → catálogos
      $fincaSeleccionada  → (opcional) id de finca para preseleccionar
      $esEdicion          → bool, si es true ocultamos el campo arete (no se edita)
--}}
@php
    $esEdicion = $esEdicion ?? false;
    $fincaPreseleccionada = old('id_finca', $animal->id_finca ?? $fincaSeleccionada ?? '');
@endphp

@if (! $esEdicion)
    <div>
        <label class="block text-sm font-medium mb-1">Arete (SENASA)</label>
        <input type="text" name="arete" value="{{ old('arete') }}" required maxlength="30"
               placeholder="Ej. CR-2026-00001"
               class="w-full border border-gray-300 rounded px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <p class="text-xs text-gray-500 mt-1">Identificador único del animal. No se podrá modificar después.</p>
    </div>
@else
    {{-- En modo edición mostramos el arete como solo lectura --}}
    <div>
        <label class="block text-sm font-medium mb-1 text-gray-500">Arete</label>
        <p class="font-mono text-gray-700">{{ $animal->arete }}</p>
    </div>
@endif

<div>
    <label class="block text-sm font-medium mb-1">Nombre <span class="text-gray-400 text-xs">(opcional)</span></label>
    <input type="text" name="nombre" value="{{ old('nombre', $animal->nombre ?? '') }}" maxlength="50"
           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
</div>

<div>
    <label class="block text-sm font-medium mb-1">Finca</label>
    <select name="id_finca" required
            class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <option value="">— Seleccioná una finca —</option>
        @foreach ($fincas as $f)
            <option value="{{ $f->id_finca }}" @selected((int) $fincaPreseleccionada === $f->id_finca)>
                {{ $f->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Raza</label>
        <select name="id_raza" required
                class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">—</option>
            @foreach ($razas as $r)
                <option value="{{ $r->id_raza }}" @selected(old('id_raza', $animal->id_raza ?? '') == $r->id_raza)>
                    {{ $r->raza }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Sexo</label>
        <select name="id_sexo" required
                class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">—</option>
            @foreach ($sexos as $s)
                <option value="{{ $s->id_sexo }}" @selected(old('id_sexo', $animal->id_sexo ?? '') == $s->id_sexo)>
                    {{ $s->sexo }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Estado</label>
        <select name="id_estado" required
                class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">—</option>
            @foreach ($estados as $e)
                <option value="{{ $e->id_estado }}" @selected(old('id_estado', $animal->id_estado ?? '') == $e->id_estado)>
                    {{ $e->estado }}
                </option>
            @endforeach
        </select>
    </div>
</div>
