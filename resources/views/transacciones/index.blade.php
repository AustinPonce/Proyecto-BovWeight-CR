@extends('layouts.app')

@section('titulo', 'Transacciones de Ganado — BovWeight CR')

@section('contenido')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Transacciones de ganado</h1>
        <p class="text-sm text-gray-600 mt-1">Registro de compras y ventas en kg en pie</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('transacciones.pdf', request()->query()) }}"
           class="bg-red-700 hover:bg-red-800 text-white px-3 py-2 rounded text-sm font-medium">PDF</a>
        <a href="{{ route('transacciones.csv', request()->query()) }}"
           class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded text-sm font-medium">CSV/Excel</a>
        <a href="{{ route('transacciones.create') }}"
           class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded font-medium">
            + Nueva transacción
        </a>
    </div>
</div>

@if (session('exito'))
    <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded">
        {{ session('exito') }}
    </div>
@endif

{{-- Filtros --}}
<form method="GET" class="bg-white shadow rounded p-4 mb-6 flex flex-wrap gap-4 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
        <select name="tipo" class="border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option value="">Todos</option>
            <option value="compra" @selected(request('tipo') === 'compra')>Compra</option>
            <option value="venta"  @selected(request('tipo') === 'venta')>Venta</option>
        </select>
    </div>
    <div class="flex-1 min-w-40">
        <label class="block text-xs font-medium text-gray-600 mb-1">Animal (arete)</label>
        <select name="animal" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option value="">Todos</option>
            @foreach ($animales as $a)
                <option value="{{ $a->arete }}" @selected(request('animal') === $a->arete)>
                    {{ $a->arete }}{{ $a->nombre ? ' — ' . $a->nombre : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">Filtrar</button>
    <a href="{{ route('transacciones.index') }}" class="text-sm text-gray-500 hover:underline">Limpiar</a>
</form>

@if ($transacciones->isEmpty())
    <div class="bg-white border border-gray-200 rounded p-8 text-center text-gray-500">
        No hay transacciones registradas.
    </div>
@else
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-gray-600">
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Animal / Finca</th>
                    <th class="px-4 py-3">Contraparte</th>
                    <th class="px-4 py-3 text-right">Precio/kg</th>
                    <th class="px-4 py-3 text-right">Peso (kg)</th>
                    <th class="px-4 py-3 text-right">Monto total</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($transacciones as $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">{{ \Illuminate\Support\Carbon::parse($t->fecha)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                @if($t->tipo === 'venta') bg-emerald-100 text-emerald-800
                                @else bg-amber-100 text-amber-800 @endif">
                                {{ ucfirst($t->tipo) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('animales.show', $t->animal) }}" class="font-mono hover:underline text-sky-700">{{ $t->arete }}</a>
                            @if ($t->animal->nombre)<span class="text-gray-500"> — {{ $t->animal->nombre }}</span>@endif
                            <div class="text-xs text-gray-500">{{ $t->animal->finca->nombre ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $t->nombre_contraparte }}</div>
                            @if ($t->cedula_contraparte)
                                <div class="text-xs text-gray-500 font-mono">{{ $t->cedula_contraparte }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">₡{{ number_format($t->precio_por_kg, 2) }}</td>
                        <td class="px-4 py-3 text-right font-medium">{{ number_format($t->peso_negociado, 2) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-700">
                            ₡{{ number_format($t->monto_total, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $transacciones->links() }}</div>
@endif

<div class="mt-6">
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:underline">← Volver al panel</a>
</div>
@endsection
