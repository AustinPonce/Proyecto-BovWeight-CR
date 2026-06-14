{{--
    Layout base de la app web (panel admin de BovWeight CR).
    Todas las vistas autenticadas extienden este layout.

    Usa Tailwind 4 vía Vite (ya configurado en vite.config.js).

    Bloques disponibles:
      @section('titulo')   → título de la pestaña del navegador
      @section('contenido') → contenido principal de la página
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'BovWeight CR')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

    {{-- Navbar superior. Solo se muestra si hay usuario autenticado. --}}
    @auth
        <nav class="bg-emerald-700 text-white shadow">
            <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold">
                    BovWeight CR
                </a>
                <div class="flex items-center gap-4 text-sm">
                    <span class="opacity-90">
                        {{ auth()->user()->nombre }}
                        <span class="opacity-70">
                            ({{ auth()->user()->tipoUsuario->nombre_tipo ?? 'Sin rol' }})
                        </span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-emerald-900 hover:bg-emerald-950 px-3 py-1 rounded">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    @endauth

    {{-- Contenido principal. --}}
    <main class="flex-1 max-w-6xl mx-auto px-4 py-8 w-full">
        {{-- Flash messages (ej. "Bienvenido", "Registro exitoso"). --}}
        @if (session('exito'))
            <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded">
                {{ session('exito') }}
            </div>
        @endif

        @yield('contenido')
    </main>

    <footer class="text-center text-xs text-gray-500 py-4">
        BovWeight CR &copy; {{ date('Y') }} — IF7100 Ingeniería del Software
    </footer>
</body>
</html>
