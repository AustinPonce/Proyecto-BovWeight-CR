{{--
    Vista de login. Usuario ingresa cédula + contraseña.
    Si las credenciales fallan, $errors trae los mensajes del ValidationException.
--}}
@extends('layouts.app')

@section('titulo', 'Iniciar sesión — BovWeight CR')

@section('contenido')
<div class="max-w-md mx-auto bg-white shadow rounded-lg p-8">
    <h1 class="text-2xl font-bold text-emerald-700 mb-6">Iniciar sesión</h1>

    {{-- Bloque de errores de validación. --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Cédula</label>
            <input type="text" name="cedula" value="{{ old('cedula') }}" required autofocus
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Contraseña</label>
            <input type="password" name="contrasena" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <label class="flex items-center text-sm gap-2">
            <input type="checkbox" name="recordarme" value="1">
            Recordarme
        </label>

        <button type="submit"
                class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2 rounded">
            Entrar
        </button>
    </form>

    <p class="mt-4 text-center text-sm">
        ¿No tenés cuenta?
        <a href="{{ route('registro.mostrar') }}" class="text-emerald-700 hover:underline">Registrate aquí</a>
    </p>
</div>
@endsection
