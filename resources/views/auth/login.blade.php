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
            {{-- Wrapper relativo para posicionar el botón "ojito" dentro del input. --}}
            <div class="relative">
                <input type="password" name="contrasena" required id="login-password"
                       class="w-full border border-gray-300 rounded px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button type="button" data-toggle-password="login-password"
                        class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
                        aria-label="Mostrar/ocultar contraseña">
                    {{-- Ojo cerrado por default (contraseña oculta) --}}
                    <span class="icono-mostrar">👁️</span>
                    <span class="icono-ocultar hidden">🙈</span>
                </button>
            </div>
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

    <div class="mt-4 flex items-center justify-between text-sm">
        <p>
            ¿No tenés cuenta?
            <a href="{{ route('registro.mostrar') }}" class="text-emerald-700 hover:underline">Registrate aquí</a>
        </p>
        <a href="{{ route('password.request') }}" class="text-gray-500 hover:underline">
            ¿Olvidaste tu contraseña?
        </a>
    </div>
</div>

{{-- Script genérico para los botones "ojito" del show/hide password.
     Funciona con cualquier botón que tenga `data-toggle-password="ID_DEL_INPUT"`. --}}
<script>
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.togglePassword);
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            btn.querySelector('.icono-mostrar').classList.toggle('hidden', !visible);
            btn.querySelector('.icono-ocultar').classList.toggle('hidden',  visible);
        });
    });
</script>
@endsection
