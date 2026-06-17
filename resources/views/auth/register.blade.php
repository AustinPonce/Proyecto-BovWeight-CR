{{--
    Vista de registro. El usuario elige su rol (Ganadero / Veterinario / Comprador).
    El rol Administrador no se ofrece aquí por seguridad — se crea por seeder.

    $rolesSeleccionables viene del controller: [id => nombre, ...]
--}}
@extends('layouts.app')

@section('titulo', 'Registro — BovWeight CR')

@section('contenido')
<div class="max-w-md mx-auto bg-white shadow rounded-lg p-8">
    <h1 class="text-2xl font-bold text-emerald-700 mb-6">Crear cuenta</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('registro') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Cédula</label>
            <input type="text" name="cedula" value="{{ old('cedula') }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Nombre completo</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Correo electrónico</label>
            <input type="email" name="correo" value="{{ old('correo') }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Tipo de usuario</label>
            <select name="id_tipo_usuario" required
                    class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">— Seleccioná un rol —</option>
                @foreach ($rolesSeleccionables as $id => $nombre)
                    <option value="{{ $id }}" @selected(old('id_tipo_usuario') == $id)>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Contraseña</label>
            <input type="password" name="contrasena" required minlength="8"
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres.</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Confirmar contraseña</label>
            <input type="password" name="contrasena_confirmation" required minlength="8"
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <button type="submit"
                class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2 rounded">
            Registrarme
        </button>
    </form>

    <p class="mt-4 text-center text-sm">
        ¿Ya tenés cuenta?
        <a href="{{ route('login') }}" class="text-emerald-700 hover:underline">Ingresá</a>
    </p>
</div>
@endsection
