@extends('layouts.app')

@section('titulo', 'Auditoría del Sistema — BovWeight CR')

@section('contenido')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Auditoría del Sistema</h1>
        <p class="text-sm text-gray-600 mt-1">Registro de todas las acciones realizadas en el sistema.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.auditoria.csv', request()->query()) }}"
           class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-3 py-2 rounded">
            ↓ CSV
        </a>
        <a href="{{ route('admin.auditoria.pdf', request()->query()) }}"
           class="inline-flex items-center gap-1 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-3 py-2 rounded">
            ↓ PDF
        </a>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('admin.auditoria.index') }}"
      class="bg-white shadow rounded p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

    {{-- Usuario --}}
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Usuario</label>
        <select name="cedula" id="filtro-cedula"
                class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <option value="">Todos</option>
            @foreach ($usuarios as $u)
                <option value="{{ $u->cedula }}" @selected(request('cedula') === $u->cedula)>
                    {{ $u->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Módulo --}}
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Módulo</label>
        <select name="modulo" id="filtro-modulo"
                class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <option value="">Todos</option>
            @foreach ($modulos as $key => $label)
                <option value="{{ $key }}" @selected(request('modulo') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Desde --}}
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
        <input type="date" name="desde" id="filtro-desde" value="{{ request('desde') }}"
               class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
    </div>

    {{-- Hasta --}}
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
        <input type="date" name="hasta" id="filtro-hasta" value="{{ request('hasta') }}"
               class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
    </div>

    {{-- Buscar --}}
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Búsqueda</label>
        <input type="text" name="buscar" id="filtro-buscar" value="{{ request('buscar') }}"
               placeholder="Texto en descripción…"
               class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
    </div>

    {{-- Botones --}}
    <div class="sm:col-span-2 lg:col-span-5 flex gap-2">
        <button type="submit"
                class="bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded">
            Filtrar
        </button>
        <a href="{{ route('admin.auditoria.index') }}"
           class="text-sm text-gray-600 hover:underline self-center ml-2">
            Limpiar filtros
        </a>
        <span class="self-center ml-auto text-xs text-gray-500">
            {{ $registros->total() }} registro(s) encontrado(s)
        </span>
    </div>
</form>

{{-- Tabla --}}
@if ($registros->isEmpty())
    <div class="bg-white border border-gray-200 rounded p-10 text-center text-gray-500">
        No hay registros de auditoría que coincidan con los filtros.
    </div>
@else
    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead class="bg-gray-50 border-b text-gray-600">
                <tr class="text-left">
                    <th class="px-4 py-3 w-40">Fecha / Hora</th>
                    <th class="px-4 py-3">Usuario</th>
                    <th class="px-4 py-3 w-28">Módulo</th>
                    <th class="px-4 py-3 w-28">Acción</th>
                    <th class="px-4 py-3">Descripción</th>
                    <th class="px-4 py-3 w-32">IP</th>
                    <th class="px-4 py-3 w-10 text-center">+</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($registros as $r)
                    <tr class="hover:bg-gray-50" id="auditoria-row-{{ $r->id_auditoria }}">
                        <td class="px-4 py-3 text-xs text-gray-700 whitespace-nowrap font-mono">
                            {{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($r->usuario)
                                <span class="font-medium">{{ $r->usuario->nombre }}</span>
                                <br><span class="text-xs text-gray-500 font-mono">{{ $r->cedula_usuario }}</span>
                            @else
                                <span class="text-gray-400 italic text-xs">Sistema / desconocido</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $colores = [
                                    'auth'          => 'bg-blue-100 text-blue-800',
                                    'fincas'        => 'bg-green-100 text-green-800',
                                    'animales'      => 'bg-emerald-100 text-emerald-800',
                                    'pesajes'       => 'bg-yellow-100 text-yellow-800',
                                    'transacciones' => 'bg-purple-100 text-purple-800',
                                    'veterinarios'  => 'bg-cyan-100 text-cyan-800',
                                    'usuarios'      => 'bg-rose-100 text-rose-800',
                                    'catalogos'     => 'bg-orange-100 text-orange-800',
                                ];
                                $color = $colores[$r->modulo] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $color }}">
                                {{ $r->modulo }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $accionColor = match(true) {
                                    in_array($r->accion, ['crear','registro','asignar'])   => 'text-emerald-700',
                                    in_array($r->accion, ['eliminar','desasignar'])        => 'text-red-700',
                                    in_array($r->accion, ['actualizar','activar'])         => 'text-blue-700',
                                    in_array($r->accion, ['login','logout'])               => 'text-purple-700',
                                    default                                                => 'text-gray-600',
                                };
                            @endphp
                            <span class="font-semibold text-xs {{ $accionColor }}">{{ strtoupper($r->accion) }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-700 max-w-xs truncate" title="{{ $r->descripcion }}">
                            {{ $r->descripcion }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 font-mono">
                            {{ $r->ip ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($r->datos_antes || $r->datos_despues)
                                <button type="button"
                                        onclick="toggleDetalle({{ $r->id_auditoria }})"
                                        class="text-xs text-emerald-700 hover:underline font-semibold"
                                        id="btn-detalle-{{ $r->id_auditoria }}">
                                    Ver
                                </button>
                            @endif
                        </td>
                    </tr>
                    {{-- Fila oculta con detalle JSON --}}
                    @if ($r->datos_antes || $r->datos_despues)
                        <tr id="detalle-{{ $r->id_auditoria }}" class="hidden bg-gray-50">
                            <td colspan="7" class="px-6 py-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                                    @if ($r->datos_antes)
                                        <div>
                                            <p class="font-semibold text-gray-600 mb-1">Antes:</p>
                                            <pre class="bg-white border rounded p-2 overflow-x-auto text-gray-700">{{ json_encode($r->datos_antes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    @endif
                                    @if ($r->datos_despues)
                                        <div>
                                            <p class="font-semibold text-gray-600 mb-1">Después:</p>
                                            <pre class="bg-white border rounded p-2 overflow-x-auto text-gray-700">{{ json_encode($r->datos_despues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $registros->links() }}
    </div>
@endif

<div class="mt-8">
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:underline">← Volver al panel</a>
</div>

<script>
function toggleDetalle(id) {
    const fila = document.getElementById('detalle-' + id);
    const btn  = document.getElementById('btn-detalle-' + id);
    if (fila.classList.contains('hidden')) {
        fila.classList.remove('hidden');
        btn.textContent = 'Ocultar';
    } else {
        fila.classList.add('hidden');
        btn.textContent = 'Ver';
    }
}
</script>
@endsection
