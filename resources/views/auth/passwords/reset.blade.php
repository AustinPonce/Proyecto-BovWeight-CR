@extends('layouts.app')

@section('titulo', 'Nueva contraseña — BovWeight CR')

@section('contenido')
<div class="max-w-md mx-auto bg-white shadow rounded-lg p-8">
    <h1 class="text-2xl font-bold text-emerald-700 mb-6">Establecer nueva contraseña</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="block text-sm font-medium mb-1">Correo electrónico</label>
            <input type="email" name="correo" value="{{ old('correo', $correo) }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Nueva contraseña</label>
            <div class="relative">
                <input type="password" name="contrasena" required id="new-password"
                       class="w-full border border-gray-300 rounded px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button type="button" data-toggle-password="new-password"
                        class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
                    <span class="icono-mostrar">👁️</span>
                    <span class="icono-ocultar hidden">🙈</span>
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres, con mayúscula, minúscula y símbolo.</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Confirmar nueva contraseña</label>
            <div class="relative">
                <input type="password" name="contrasena_confirmation" required id="new-password-confirm"
                       class="w-full border border-gray-300 rounded px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button type="button" data-toggle-password="new-password-confirm"
                        class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
                    <span class="icono-mostrar">👁️</span>
                    <span class="icono-ocultar hidden">🙈</span>
                </button>
            </div>
        </div>

        <button type="submit"
                class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2 rounded">
            Restablecer contraseña
        </button>
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
