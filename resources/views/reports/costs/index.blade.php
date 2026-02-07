<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Costes del Caso - {{ $report->ip }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.show', $report) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver al Caso
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Panel de Resumen de Totales --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Resumen de Costes - {{ $report->title }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        {{-- VR Total --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-blue-700 uppercase tracking-wider">Valor de Reposición (VR)</p>
                                    <p class="mt-2 text-2xl font-bold text-blue-900">{{ number_format($totals['VR'], 2, ',', '.') }} €</p>
                                </div>
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- VE Total --}}
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-green-700 uppercase tracking-wider">Valor Ecológico (VE)</p>
                                    <p class="mt-2 text-2xl font-bold text-green-900">{{ number_format($totals['VE'], 2, ',', '.') }} €</p>
                                </div>
                                <div class="p-3 bg-green-100 rounded-full">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- VS Total --}}
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-yellow-700 uppercase tracking-wider">Valor Social (VS)</p>
                                    <p class="mt-2 text-2xl font-bold text-yellow-900">{{ number_format($totals['VS'], 2, ',', '.') }} €</p>
                                </div>
                                <div class="p-3 bg-yellow-100 rounded-full">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Total General --}}
                        <div class="bg-red-50 border-2 border-red-300 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-red-700 uppercase tracking-wider">Coste Total</p>
                                    <p class="mt-2 text-2xl font-bold text-red-900">{{ number_format($totals['total'], 2, ',', '.') }} €</p>
                                </div>
                                <div class="p-3 bg-red-100 rounded-full">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla de Costes por Grupo --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                            Desglose por Concepto
                        </h3>

                        <div class="flex gap-2">
                            {{-- Botón Recalcular --}}
                            @if(Auth::user()->role === 'admin' || $report->user_id === Auth::id() || $report->assigned_to === Auth::id())
                                <form action="{{ route('report-costs.calculate', $report) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 transition ease-in-out duration-150" onclick="return confirm('¿Recalcular todos los costes?')">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Recalcular
                                    </button>
                                </form>

                                {{-- Botón Eliminar Costes --}}
                                <form action="{{ route('report-costs.destroy', $report) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition ease-in-out duration-150" onclick="return confirm('¿Eliminar todos los costes calculados?')">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if($groupedCosts->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200" id="costs-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Grupo / Concepto
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tipo
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Valor Base
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            CR
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            GI
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Coste Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($groupedCosts as $groupKey => $items)
                                        @php
                                            $groupTotal = $items->sum('total_cost');
                                            $groupLabel = ucfirst(str_replace('_', ' ', $groupKey));
                                        @endphp
                                        
                                        {{-- Fila de encabezado del grupo --}}
                                        <tr class="bg-gray-100">
                                            <td colspan="6" class="px-6 py-2 text-sm font-semibold text-gray-800">
                                                {{ $groupLabel }}
                                                <span class="text-gray-500 font-normal">(Total: {{ number_format($groupTotal, 2, ',', '.') }} €)</span>
                                            </td>
                                        </tr>

                                        @foreach($items as $item)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 pl-10">
                                                    {{ $item->concept_name }}
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap">
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $item->cost_type === 'VR' ? 'bg-blue-100 text-blue-800' : '' }}
                                                        {{ $item->cost_type === 'VE' ? 'bg-green-100 text-green-800' : '' }}
                                                        {{ $item->cost_type === 'VS' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                                        {{ $item->cost_type }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 text-right">
                                                    {{ number_format($item->base_value, 2, ',', '.') }} €
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 text-center">
                                                    {{ $item->cr_value ? number_format($item->cr_value, 2) : '-' }}
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 text-center">
                                                    {{ $item->gi_value ? number_format($item->gi_value, 2) : '-' }}
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                    {{ number_format($item->total_cost, 2, ',', '.') }} €
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-800 text-white">
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-sm font-bold text-right uppercase">
                                            Total General
                                        </td>
                                        <td class="px-6 py-4 text-lg font-bold text-right">
                                            {{ number_format($totals['total'], 2, ',', '.') }} €
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Información adicional --}}
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Leyenda de Coeficientes</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-600">
                                <div>
                                    <strong>CR (Coeficiente de Recuperabilidad):</strong> 
                                    Factor que ajusta el valor según la posibilidad de recuperación del recurso dañado.
                                </div>
                                <div>
                                    <strong>GI (Índice de Gravedad):</strong> 
                                    Multiplicador basado en la urgencia del caso y el estado del recurso afectado.
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay costes calculados</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Utiliza el botón "Calcular Costes" en la vista del caso para generar la valoración económica.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Resumen por Tipo de Coste --}}
            @if($groupedCosts->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                        Distribución por Tipo de Valoración
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @php
                            $total = $totals['total'] > 0 ? $totals['total'] : 1;
                            $vrPercent = round(($totals['VR'] / $total) * 100, 1);
                            $vePercent = round(($totals['VE'] / $total) * 100, 1);
                            $vsPercent = round(($totals['VS'] / $total) * 100, 1);
                        @endphp

                        {{-- VR --}}
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-blue-700 font-medium">Valor de Reposición (VR)</span>
                                <span class="text-gray-600">{{ $vrPercent }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $vrPercent }}%"></div>
                            </div>
                            <p class="text-right text-sm font-semibold text-blue-800">{{ number_format($totals['VR'], 2, ',', '.') }} €</p>
                        </div>

                        {{-- VE --}}
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-green-700 font-medium">Valor Ecológico (VE)</span>
                                <span class="text-gray-600">{{ $vePercent }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="bg-green-600 h-4 rounded-full" style="width: {{ $vePercent }}%"></div>
                            </div>
                            <p class="text-right text-sm font-semibold text-green-800">{{ number_format($totals['VE'], 2, ',', '.') }} €</p>
                        </div>

                        {{-- VS --}}
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-700 font-medium">Valor Social (VS)</span>
                                <span class="text-gray-600">{{ $vsPercent }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="bg-yellow-500 h-4 rounded-full" style="width: {{ $vsPercent }}%"></div>
                            </div>
                            <p class="text-right text-sm font-semibold text-yellow-800">{{ number_format($totals['VS'], 2, ',', '.') }} €</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
