{{--
    Partial reutilizable: campos del formulario de Finca.
    Se incluye en create.blade.php y edit.blade.php para no duplicar HTML.

    Variables esperadas:
      $finca      → instancia (vacía para crear, con datos para editar)
      $ganaderos  → colección de usuarios ganaderos (solo si el admin lo está usando)
--}}
@php
    $usuario = auth()->user();
@endphp

<div>
    <label class="block text-sm font-medium mb-1">Nombre de la finca</label>
    <input type="text" name="nombre" value="{{ old('nombre', $finca->nombre ?? '') }}" required maxlength="100"
           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
</div>

<div>
    <label class="block text-sm font-medium mb-1">Ubicación</label>
    <input type="text" name="ubicacion" value="{{ old('ubicacion', $finca->ubicacion ?? '') }}" required maxlength="255"
           placeholder="Ej. Liberia, Guanacaste"
           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
</div>

{{-- Selector de dueño: SOLO se muestra al Admin. Para Ganadero, su cédula se
     asigna automáticamente en el controller. --}}
@if ($usuario->esAdmin())
    <div>
        <label class="block text-sm font-medium mb-1">Ganadero dueño</label>
        <select name="cedula" required
                class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">— Seleccioná un ganadero —</option>
            @foreach ($ganaderos as $g)
                <option value="{{ $g->cedula }}"
                    @selected(old('cedula', $finca->cedula ?? '') === $g->cedula)>
                    {{ $g->nombre }} ({{ $g->cedula }})
                </option>
            @endforeach
        </select>
    </div>
@endif
