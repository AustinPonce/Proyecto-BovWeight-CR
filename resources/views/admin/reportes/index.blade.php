@extends('layouts.app')

@section('titulo', 'Reportes Globales — BovWeight CR')

@section('contenido')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Reportes globales</h1>
        <p class="text-sm text-gray-600 mt-1">Estadísticas de toda la plataforma</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.reportes.pdf', request()->query()) }}"
           class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded text-sm font-medium">
            Exportar PDF
        </a>
        <a href="{{ route('admin.reportes.csv', request()->query()) }}"
           class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded text-sm font-medium">
            Exportar CSV
        </a>
    </div>
</div>

{{-- Filtro de periodo --}}
<form method="GET" class="bg-white shadow rounded p-4 mb-6 flex flex-wrap gap-4 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
        <input type="date" name="desde" value="{{ $desde->format('Y-m-d') }}"
               class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
        <input type="date" name="hasta" value="{{ $hasta->format('Y-m-d') }}"
               class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
    </div>
    <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">Filtrar</button>
</form>

{{-- KPIs globales --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    @foreach ([
        ['label' => 'Usuarios',       'value' => $stats['total_usuarios'],    'color' => 'rose'],
        ['label' => 'Ganaderos',      'value' => $stats['total_ganaderos'],   'color' => 'emerald'],
        ['label' => 'Veterinarios',   'value' => $stats['total_vets'],        'color' => 'sky'],
        ['label' => 'Fincas',         'value' => $stats['total_fincas'],      'color' => 'amber'],
        ['label' => 'Bovinos',        'value' => $stats['total_animales'],    'color' => 'violet'],
        ['label' => 'Pesajes totales','value' => $stats['total_pesajes'],     'color' => 'gray'],
    ] as $kpi)
        <div class="bg-white shadow rounded p-4 text-center">
            <p class="text-2xl font-bold text-{{ $kpi['color'] }}-700">{{ $kpi['value'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $kpi['label'] }}</p>
        </div>
    @endforeach
</div>

{{-- KPIs del periodo --}}
<div class="bg-white shadow rounded p-5 mb-6">
    <h2 class="font-semibold text-gray-700 mb-4">
        Periodo: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center">
            <p class="text-3xl font-bold text-rose-700">{{ $stats['pesajes_periodo'] }}</p>
            <p class="text-sm text-gray-500">Pesajes en periodo</p>
        </div>
        <div class="text-center">
            <p class="text-3xl font-bold text-emerald-700">
                {{ $stats['peso_promedio'] ? number_format($stats['peso_promedio'], 1) : '—' }}
                <span class="text-base font-normal text-gray-500">kg</span>
            </p>
            <p class="text-sm text-gray-500">Peso promedio</p>
        </div>
        <div class="text-center">
            <p class="text-3xl font-bold text-sky-700">
                {{ $stats['peso_maximo'] ? number_format($stats['peso_maximo'], 1) : '—' }}
                <span class="text-base font-normal text-gray-500">kg</span>
            </p>
            <p class="text-sm text-gray-500">Peso máximo</p>
        </div>
        <div class="text-center">
            <p class="text-3xl font-bold text-amber-700">
                {{ $stats['peso_minimo'] ? number_format($stats['peso_minimo'], 1) : '—' }}
                <span class="text-base font-normal text-gray-500">kg</span>
            </p>
            <p class="text-sm text-gray-500">Peso mínimo</p>
        </div>
    </div>
</div>

{{-- Pesajes por día --}}
@if ($pesajesPorDia->isNotEmpty())
<div class="bg-white shadow rounded p-5 mb-6">
    <h2 class="font-semibold text-gray-700 mb-4">Pesajes por día</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-gray-600">
                    <th class="px-3 py-2">Fecha</th>
                    <th class="px-3 py-2">Pesajes</th>
                    <th class="px-3 py-2">Peso prom. (kg)</th>
                    <th class="px-3 py-2">Gráfica</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @php $maxTotal = $pesajesPorDia->max('total') ?: 1; @endphp
                @foreach ($pesajesPorDia as $dia)
                    <tr>
                        <td class="px-3 py-2">{{ \Carbon\Carbon::parse($dia->dia)->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 font-medium">{{ $dia->total }}</td>
                        <td class="px-3 py-2">{{ number_format($dia->promedio, 1) }}</td>
                        <td class="px-3 py-2 w-40">
                            <div class="bg-gray-100 rounded h-3">
                                <div class="bg-rose-500 h-3 rounded"
                                     style="width: {{ round($dia->total / $maxTotal * 100) }}%"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="mt-6">
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:underline">← Volver al panel</a>
</div>
@endsection
