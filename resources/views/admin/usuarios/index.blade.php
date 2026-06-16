@extends('layouts.app')

@section('titulo', 'Gestión de Usuarios — BovWeight CR')

@section('contenido')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h1>
        <p class="text-sm text-gray-600 mt-1">{{ $usuarios->total() }} usuario(s) registrados</p>
    </div>
    <a href="{{ route('admin.usuarios.create') }}"
       class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded font-medium">
        + Nuevo usuario
    </a>
</div>

@if (session('exito'))
    <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded">
        {{ session('exito') }}
    </div>
@endif
@if (session('error'))
    <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded">
        {{ session('error') }}
    </div>
@endif

{{-- Filtros --}}
<form method="GET" class="bg-white shadow rounded p-4 mb-6 flex flex-wrap gap-4 items-end">
    <div class="flex-1 min-w-48">
        <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, cédula o correo"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Rol</label>
        <select name="rol" class="border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-rose-400">
            <option value="">Todos</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol->id_tipo_usuario }}" @selected(request('rol') == $rol->id_tipo_usuario)>
                    {{ $rol->nombre_tipo }}
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">Filtrar</button>
    <a href="{{ route('admin.usuarios.index') }}" class="text-sm text-gray-500 hover:underline">Limpiar</a>
</form>

@if ($usuarios->isEmpty())
    <div class="bg-white border border-gray-200 rounded p-8 text-center text-gray-500">
        No se encontraron usuarios con esos criterios.
    </div>
@else
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-sm text-gray-600">
                    <th class="px-4 py-3">Cédula</th>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Correo</th>
                    <th class="px-4 py-3">Rol</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($usuarios as $u)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-sm">{{ $u->cedula }}</td>
                        <td class="px-4 py-3 font-medium">{{ $u->nombre }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $u->correo }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs @class([
                                'bg-rose-100 text-rose-800'       => $u->esAdmin(),
                                'bg-emerald-100 text-emerald-800' => $u->esGanadero(),
                                'bg-sky-100 text-sky-800'         => $u->esVeterinario(),
                            ])">
                                {{ $u->tipoUsuario->nombre_tipo }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium @class([
                                'bg-green-100 text-green-800' => $u->activo,
                                'bg-gray-200 text-gray-600'   => ! $u->activo,
                            ])">
                                {{ $u->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.usuarios.edit', $u->cedula) }}"
                               class="text-emerald-700 hover:underline text-sm">Editar</a>
                            @if ($u->cedula !== auth()->user()->cedula)
                                <form method="POST" action="{{ route('admin.usuarios.toggle-activo', $u->cedula) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="text-sm hover:underline {{ $u->activo ? 'text-amber-600' : 'text-green-700' }}">
                                        {{ $u->activo ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.usuarios.destroy', $u->cedula) }}" class="inline"
                                      onsubmit="return confirm('¿Eliminar al usuario {{ $u->nombre }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-700 hover:underline text-sm">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $usuarios->links() }}</div>
@endif

<div class="mt-6">
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:underline">← Volver al panel</a>
</div>
@endsection
