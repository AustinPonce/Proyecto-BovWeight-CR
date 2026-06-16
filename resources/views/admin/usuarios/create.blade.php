@extends('layouts.app')

@section('titulo', 'Crear Usuario — BovWeight CR')

@section('contenido')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Crear nuevo usuario</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="bg-white shadow rounded p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Cédula</label>
            <input type="text" name="cedula" value="{{ old('cedula') }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-400">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Nombre completo</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-400">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Correo electrónico</label>
            <input type="email" name="correo" value="{{ old('correo') }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-400">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Rol</label>
            <select name="id_tipo_usuario" required
                    class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-rose-400">
                <option value="">— Seleccioná un rol —</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol->id_tipo_usuario }}" @selected(old('id_tipo_usuario') == $rol->id_tipo_usuario)>
                        {{ $rol->nombre_tipo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Contraseña</label>
            <div class="relative">
                <input type="password" name="contrasena" required id="create-password"
                       class="w-full border border-gray-300 rounded px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-rose-400">
                <button type="button" data-toggle-password="create-password"
                        class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
                    <span class="icono-mostrar">👁️</span>
                    <span class="icono-ocultar hidden">🙈</span>
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres, con mayúscula, minúscula y símbolo.</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Confirmar contraseña</label>
            <div class="relative">
                <input type="password" name="contrasena_confirmation" required id="create-password-confirm"
                       class="w-full border border-gray-300 rounded px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-rose-400">
                <button type="button" data-toggle-password="create-password-confirm"
                        class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
                    <span class="icono-mostrar">👁️</span>
                    <span class="icono-ocultar hidden">🙈</span>
                </button>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-rose-600 hover:bg-rose-700 text-white font-semibold px-5 py-2 rounded">
                Crear usuario
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
