@extends('layouts.app')

@section('titulo', 'Olvidé mi contraseña — BovWeight CR')

@section('contenido')
<div class="max-w-md mx-auto bg-white shadow rounded-lg p-8">
    <h1 class="text-2xl font-bold text-emerald-700 mb-2">¿Olvidaste tu contraseña?</h1>
    <p class="text-sm text-gray-600 mb-6">
        Ingresá tu correo electrónico y te enviaremos un enlace para restablecerla.
    </p>

    @if (session('exito'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-4 text-sm">
            {{ session('exito') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Correo electrónico</label>
            <input type="email" name="correo" value="{{ old('correo') }}" required autofocus
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <button type="submit"
                class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2 rounded">
            Enviar enlace de restablecimiento
        </button>
    </form>

    <p class="mt-4 text-center text-sm">
        <a href="{{ route('login') }}" class="text-emerald-700 hover:underline">← Volver al inicio de sesión</a>
    </p>
</div>
@endsection
