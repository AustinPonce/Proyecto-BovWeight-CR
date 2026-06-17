@extends('layouts.app')

@section('titulo', 'Editar Usuario — BovWeight CR')

@section('contenido')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Editar usuario: {{ $usuario->nombre }}</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.usuarios.update', $usuario->cedula) }}" class="bg-white shadow rounded p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium mb-1">Cédula</label>
            <input type="text" value="{{ $usuario->cedula }}" disabled
                   class="w-full border border-gray-200 rounded px-3 py-2 bg-gray-50 text-gray-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Nombre completo</label>
            <input type="text" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-400">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Correo electrónico</label>
            <input type="email" name="correo" value="{{ old('correo', $usuario->correo) }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-400">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Rol</label>
            <select name="id_tipo_usuario" required
                    class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-rose-400">
                @foreach ($roles as $rol)
                    <option value="{{ $rol->id_tipo_usuario }}"
                            @selected(old('id_tipo_usuario', $usuario->id_tipo_usuario) == $rol->id_tipo_usuario)>
                        {{ $rol->nombre_tipo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="border-t pt-4">
            <p class="text-xs text-gray-500 mb-3">Dejá en blanco si no querés cambiar la contraseña.</p>

            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Nueva contraseña</label>
                    <div class="relative">
                        <input type="password" name="contrasena" id="edit-password"
                               class="w-full border border-gray-300 rounded px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-rose-400">
                        <button type="button" data-toggle-password="edit-password"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
                            <span class="icono-mostrar">👁️</span>
                            <span class="icono-ocultar hidden">🙈</span>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Confirmar nueva contraseña</label>
                    <div class="relative">
                        <input type="password" name="contrasena_confirmation" id="edit-password-confirm"
                               class="w-full border border-gray-300 rounded px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-rose-400">
                        <button type="button" data-toggle-password="edit-password-confirm"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
                            <span class="icono-mostrar">👁️</span>
                            <span class="icono-ocultar hidden">🙈</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-rose-600 hover:bg-rose-700 text-white font-semibold px-5 py-2 rounded">
                Guardar cambios
            </button>
            <a href="{{ route('admin.usuarios.index') }}" class="text-sm text-gray-600 hover:underline self-center">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.togglePassword);
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            btn.querySelector('.icono-mostrar').classList.toggle('hidden', !visible);
            btn.querySelector('.icono-ocultar').classList.toggle('hidden', visible);
        });
    });
</script>
@endsection
