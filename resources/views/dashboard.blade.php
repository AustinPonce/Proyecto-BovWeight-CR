{{--
    Dashboard principal. Muestra bloques distintos según el rol del usuario.

    Reglas (requerimiento #10 + #11):
      - Admin       → bloques ADMINISTRATIVOS solamente:
                      gestionar usuarios, lista de usuarios/fincas/animales,
                      catálogos, reportes globales.
                      NO ve "Mis fincas", "Mis animales", "Registrar pesaje",
                      "Historial de pesajes", "Fincas asignadas" ni
                      "Calculadora de dosis" porque el admin no es operativo.
      - Ganadero    → sus fincas, animales, pesajes.
      - Veterinario → fincas asignadas, dosificación.

    El acceso a las RUTAS sigue protegido por middleware, esto solo decide
    qué BOTONES se muestran en el panel.
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
    {{-- BLOQUES DE ADMINISTRADOR (solo admin)                        --}}
    {{-- ============================================================ --}}
    @if ($usuario->esAdmin())
        <a href="{{ route('admin.usuarios.index') }}"
           class="block bg-white border-l-4 border-rose-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-rose-700">Gestionar usuarios</h3>
            <p class="text-sm text-gray-600 mt-1">Crear, editar y desactivar cuentas del sistema.</p>
        </a>

        <a href="{{ route('fincas.index') }}"
           class="block bg-white border-l-4 border-rose-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-rose-700">Lista de fincas</h3>
            <p class="text-sm text-gray-600 mt-1">Ver, editar y eliminar todas las fincas del sistema.</p>
        </a>

        <a href="{{ route('animales.index') }}"
           class="block bg-white border-l-4 border-rose-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-rose-700">Lista de bovinos</h3>
            <p class="text-sm text-gray-600 mt-1">Ver, editar y eliminar todos los animales del sistema.</p>
        </a>

        <a href="{{ route('admin.catalogos.index') }}"
           class="block bg-white border-l-4 border-rose-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-rose-700">Catálogos del sistema</h3>
            <p class="text-sm text-gray-600 mt-1">Razas, estados, tipos de pesaje y medicamentos.</p>
        </a>

        <a href="{{ route('admin.reportes.index') }}"
           class="block bg-white border-l-4 border-rose-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-rose-700">Reportes globales</h3>
            <p class="text-sm text-gray-600 mt-1">Estadísticas de todas las fincas y pesajes.</p>
        </a>
    @endif

    {{-- ============================================================ --}}
    {{-- BLOQUES DE GANADERO (NO se muestran al Admin)                --}}
    {{-- ============================================================ --}}
    @if ($usuario->esGanadero())
        <a href="{{ route('fincas.index') }}"
           class="block bg-white border-l-4 border-emerald-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-emerald-700">Mis fincas</h3>
            <p class="text-sm text-gray-600 mt-1">Gestionar fincas registradas a tu nombre.</p>
        </a>

        <a href="{{ route('animales.index') }}"
           class="block bg-white border-l-4 border-emerald-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-emerald-700">Mis animales</h3>
            <p class="text-sm text-gray-600 mt-1">Registrar y consultar ganado por finca.</p>
        </a>

        <a href="{{ route('pesajes.create') }}"
           class="block bg-white border-l-4 border-emerald-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-emerald-700">Registrar pesaje</h3>
            <p class="text-sm text-gray-600 mt-1">Por medidas corporales o por foto del animal.</p>
        </a>

        <a href="{{ route('pesajes.index') }}"
           class="block bg-white border-l-4 border-emerald-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-emerald-700">Historial de pesajes</h3>
            <p class="text-sm text-gray-600 mt-1">Consultar y exportar pesajes registrados.</p>
        </a>
    @endif

    {{-- ============================================================ --}}
    {{-- BLOQUES DE VETERINARIO (NO se muestran al Admin)             --}}
    {{-- ============================================================ --}}
    @if ($usuario->esVeterinario())
        <a href="{{ route('fincas.index') }}"
           class="block bg-white border-l-4 border-sky-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-sky-700">Fincas asignadas</h3>
            <p class="text-sm text-gray-600 mt-1">Fincas en las que sos veterinario.</p>
        </a>

        <a href="{{ route('animales.index') }}"
           class="block bg-white border-l-4 border-sky-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-sky-700">Bovinos asignados</h3>
            <p class="text-sm text-gray-600 mt-1">Consultar historial y exportar.</p>
        </a>

        <a href="{{ route('dosis.calcular') }}"
           class="block bg-white border-l-4 border-sky-500 shadow rounded p-5 hover:shadow-md transition">
            <h3 class="font-semibold text-sky-700">Calculadora de dosis</h3>
            <p class="text-sm text-gray-600 mt-1">Calcular dosis de medicamentos según peso del animal.</p>
        </a>
    @endif

</div>

{{-- Aviso legal del PDF: el sistema NO sustituye báscula oficial. --}}
<div class="mt-8 bg-yellow-50 border border-yellow-200 text-yellow-900 rounded p-4 text-sm">
    <strong>Aviso:</strong> Las estimaciones de peso son orientativas. No sustituyen
    la báscula oficial para transacciones comerciales legales (compra/venta de
    ganado en kilogramos en pie).
</div>
@endsection
