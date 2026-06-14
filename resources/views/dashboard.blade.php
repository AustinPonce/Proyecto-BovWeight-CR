{{--
    Dashboard principal. Muestra bloques distintos según el rol del usuario.

    Esto demuestra el control de acceso por VISTA (además del de RUTA).
    El middleware 'rol' bloquea el acceso a rutas; los @if (auth()->user()->esXxx())
    deciden qué se muestra dentro de una página común.

    Roles:
      - Admin       → ve todo
      - Ganadero    → sus fincas, animales, pesajes
      - Veterinario → fincas asignadas, dosificación
--}}
@extends('layouts.app')

@section('titulo', 'Panel — BovWeight CR')

@section('contenido')
@php
    $usuario = auth()->user();
@endphp

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Hola, {{ $usuario->nombre }} 👋</h1>
    <p class="text-gray-600 mt-1">
        Estás conectada como
        <span class="font-semibold text-emerald-700">{{ $usuario->tipoUsuario->nombre_tipo }}</span>.
    </p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

    {{-- ============================================================ --}}
    {{-- BLOQUE ADMIN: visible solo para administradores             --}}
    {{-- ============================================================ --}}
    @if ($usuario->esAdmin())
        <a href="#" class="block bg-white border-l-4 border-rose-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-rose-700">Gestionar usuarios</h3>
            <p class="text-sm text-gray-600 mt-1">Crear, editar y desactivar cuentas.</p>
        </a>
        <a href="#" class="block bg-white border-l-4 border-rose-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-rose-700">Catálogos del sistema</h3>
            <p class="text-sm text-gray-600 mt-1">Razas, estados, tipos de pesaje.</p>
        </a>
        <a href="#" class="block bg-white border-l-4 border-rose-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-rose-700">Reportes globales</h3>
            <p class="text-sm text-gray-600 mt-1">Estadísticas de todas las fincas.</p>
        </a>
    @endif

    {{-- ============================================================ --}}
    {{-- BLOQUE GANADERO: dueño de fincas                            --}}
    {{-- ============================================================ --}}
    @if ($usuario->esGanadero() || $usuario->esAdmin())
        <a href="{{ route('fincas.index') }}" class="block bg-white border-l-4 border-emerald-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-emerald-700">Mis fincas</h3>
            <p class="text-sm text-gray-600 mt-1">Gestionar fincas registradas a tu nombre.</p>
        </a>
        <a href="{{ route('animales.index') }}" class="block bg-white border-l-4 border-emerald-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-emerald-700">Mis animales</h3>
            <p class="text-sm text-gray-600 mt-1">Registrar y consultar ganado por finca.</p>
        </a>
        <a href="#" class="block bg-white border-l-4 border-emerald-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-emerald-700">Estimar peso por foto</h3>
            <p class="text-sm text-gray-600 mt-1">Subí una foto y obtené el peso estimado. <span class="text-xs">(próximo bloque)</span></p>
        </a>
    @endif

    {{-- ============================================================ --}}
    {{-- BLOQUE VETERINARIO: fincas asignadas, dosificación          --}}
    {{-- ============================================================ --}}
    @if ($usuario->esVeterinario() || $usuario->esAdmin())
        <a href="{{ route('fincas.index') }}" class="block bg-white border-l-4 border-sky-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-sky-700">Fincas asignadas</h3>
            <p class="text-sm text-gray-600 mt-1">Fincas en las que sos veterinario.</p>
        </a>
        <a href="#" class="block bg-white border-l-4 border-sky-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-sky-700">Calculadora de dosis</h3>
            <p class="text-sm text-gray-600 mt-1">Basada en peso estimado del animal. <span class="text-xs">(próximo bloque)</span></p>
        </a>
    @endif

</div>

{{-- Aviso legal del PDF: el sistema NO sustituye báscula oficial. --}}
<div class="mt-8 bg-yellow-50 border border-yellow-200 text-yellow-900 rounded p-4 text-sm">
    <strong>Aviso:</strong> Las estimaciones de peso son orientativas. No sustituyen
    la báscula oficial para transacciones comerciales legales.
</div>
@endsection
