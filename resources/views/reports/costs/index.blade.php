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
                                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="showCostDetail({{ json_encode($item) }})">
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 pl-10">
                                                    <div class="flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        {{ $item->concept_name }}
                                                    </div>
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
                                <span class="font-medium" style="color: #047857;">Valor Ecológico (VE)</span>
                                <span class="text-gray-600">{{ $vePercent }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="h-4 rounded-full" style="width: {{ $vePercent }}%; background-color: #10b981;"></div>
                            </div>
                            <p class="text-right text-sm font-semibold" style="color: #065f46;">{{ number_format($totals['VE'], 2, ',', '.') }} €</p>
                        </div>

                        {{-- VS --}}
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium" style="color: #b45309;">Valor Social (VS)</span>
                                <span class="text-gray-600">{{ $vsPercent }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="h-4 rounded-full" style="width: {{ $vsPercent }}%; background-color: #f59e0b;"></div>
                            </div>
                            <p class="text-right text-sm font-semibold" style="color: #92400e;">{{ number_format($totals['VS'], 2, ',', '.') }} €</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Modal de Detalle de Cálculo --}}
    <div id="costDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeCostDetail()"></div>

            {{-- Modal --}}
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Detalle del Cálculo
                            </h3>
                            <p class="mt-1 text-sm text-gray-500" id="modal-subtitle"></p>
                        </div>
                        <button type="button" onclick="closeCostDetail()" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mt-4" id="modal-content">
                        {{-- Contenido dinámico --}}
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeCostDetail()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showCostDetail(item) {
            const modal = document.getElementById('costDetailModal');
            const subtitle = document.getElementById('modal-subtitle');
            const content = document.getElementById('modal-content');
            
            const coefInfo = item.coef_info_json || {};
            const costType = item.cost_type;
            
            subtitle.textContent = `${item.concept_name} - ${costType}`;
            
            let html = '';
            
            // Fórmula principal
            if (coefInfo.formula) {
                html += `
                    <div class="mb-4 p-3 bg-gray-100 rounded-lg">
                        <p class="text-sm font-mono font-semibold text-gray-800">${coefInfo.formula}</p>
                    </div>
                `;
            }
            
            // Detalle según tipo de coste
            if (costType === 'VR') {
                html += renderVRDetail(coefInfo, item);
            } else if (costType === 'VE') {
                html += renderVEDetail(coefInfo, item);
            } else if (costType === 'VS') {
                html += renderVSDetail(coefInfo, item);
            }
            
            // Resultado final
            html += `
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span class="text-gray-700">Total ${costType}:</span>
                        <span class="text-green-700">${formatMoney(item.total_cost)} €</span>
                    </div>
                </div>
            `;
            
            content.innerHTML = html;
            modal.classList.remove('hidden');
        }
        
        function renderVRDetail(coef, item) {
            return `
                <div class="space-y-3">
                    <h4 class="font-semibold text-gray-700 mb-2">Componentes del cálculo:</h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs text-blue-600 font-medium">CB (Coste Base)</p>
                            <p class="text-lg font-bold text-blue-800">${formatMoney(coef.CB || 300)} €</p>
                        </div>
                        
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <p class="text-xs text-purple-600 font-medium">L (Situación Legal - IUCN)</p>
                            <p class="text-lg font-bold text-purple-800">×${coef.L || 1}</p>
                            <p class="text-xs text-purple-500">${coef.L_source || ''}</p>
                        </div>
                        
                        <div class="p-3 bg-amber-50 rounded-lg">
                            <p class="text-xs text-amber-600 font-medium">N (CITES)</p>
                            <p class="text-lg font-bold text-amber-800">×${coef.N || 1}</p>
                            <p class="text-xs text-amber-500">${coef.N_source || ''}</p>
                        </div>
                        
                        <div class="p-3 bg-green-50 rounded-lg">
                            <p class="text-xs text-green-600 font-medium">B (Madurez)</p>
                            <p class="text-lg font-bold text-green-800">×${coef.B || 1}</p>
                            <p class="text-xs text-green-500">${coef.B_source || ''}</p>
                        </div>
                        
                        <div class="p-3 bg-indigo-50 rounded-lg">
                            <p class="text-xs text-indigo-600 font-medium">q (Cantidad)</p>
                            <p class="text-lg font-bold text-indigo-800">${coef.q || 1} uds.</p>
                        </div>
                        
                        <div class="p-3 bg-red-50 rounded-lg">
                            <p class="text-xs text-red-600 font-medium">CR (Coste Reposición)</p>
                            <p class="text-lg font-bold text-red-800">+${formatMoney(coef.CR || 0)} €</p>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                        <p class="text-sm text-gray-600">
                            <strong>Cálculo:</strong> (${coef.CB || 300} × ${coef.L || 1} × ${coef.N || 1} × ${coef.B || 1}) × ${coef.q || 1} + ${coef.CR || 0}
                        </p>
                        <p class="text-sm text-gray-600 mt-1">
                            = ${formatMoney(item.base_value)} × ${coef.q || 1} + ${formatMoney(coef.CR || 0)} = <strong>${formatMoney(item.total_cost)} €</strong>
                        </p>
                    </div>
                </div>
            `;
        }
        
        function renderVEDetail(coef, item) {
            return `
                <div class="space-y-3">
                    <div class="p-4 bg-green-50 rounded-lg">
                        <p class="text-sm text-green-600 font-medium">Valor introducido manualmente</p>
                        <p class="text-2xl font-bold text-green-800">${formatMoney(coef.valor_manual || item.total_cost)} €</p>
                    </div>
                    <p class="text-sm text-gray-500 italic">
                        El Valor Ecológico (VE) se introduce manualmente basándose en tasaciones específicas o informes periciales.
                    </p>
                </div>
            `;
        }
        
        function renderVSDetail(coef, item) {
            const igComponents = coef.IG_components || {};
            
            let componentsHtml = '';
            
            if (igComponents.ubicacion) {
                componentsHtml += `
                    <div class="p-3 bg-indigo-50 rounded-lg">
                        <p class="text-xs text-indigo-600 font-medium">Ubicación</p>
                        <p class="text-sm text-indigo-800 font-semibold">${igComponents.ubicacion.valor || '-'}</p>
                        <div class="flex justify-between text-xs text-indigo-500 mt-1">
                            <span>Puntos: ${igComponents.ubicacion.puntuacion || 0}</span>
                            <span>Peso: ${((igComponents.ubicacion.ponderacion || 0) * 100)}%</span>
                        </div>
                    </div>
                `;
            }
            if (igComponents.nivel_trofico) {
                componentsHtml += `
                    <div class="p-3 bg-purple-50 rounded-lg">
                        <p class="text-xs text-purple-600 font-medium">Nivel Trófico</p>
                        <p class="text-sm text-purple-800 font-semibold">${igComponents.nivel_trofico.valor || '-'}</p>
                        <div class="flex justify-between text-xs text-purple-500 mt-1">
                            <span>Puntos: ${igComponents.nivel_trofico.puntuacion || 0}</span>
                            <span>Peso: ${((igComponents.nivel_trofico.ponderacion || 0) * 100)}%</span>
                        </div>
                    </div>
                `;
            }
            if (igComponents.reproduccion_cautiverio) {
                componentsHtml += `
                    <div class="p-3 bg-pink-50 rounded-lg">
                        <p class="text-xs text-pink-600 font-medium">Reproducción en Cautiverio</p>
                        <p class="text-sm text-pink-800 font-semibold">${igComponents.reproduccion_cautiverio.valor || '-'}</p>
                        <div class="flex justify-between text-xs text-pink-500 mt-1">
                            <span>Puntos: ${igComponents.reproduccion_cautiverio.puntuacion || 0}</span>
                            <span>Peso: ${((igComponents.reproduccion_cautiverio.ponderacion || 0) * 100)}%</span>
                        </div>
                    </div>
                `;
            }
            if (igComponents.estado_vital) {
                componentsHtml += `
                    <div class="p-3 bg-red-50 rounded-lg">
                        <p class="text-xs text-red-600 font-medium">Estado Vital</p>
                        <p class="text-sm text-red-800 font-semibold">${igComponents.estado_vital.valor || '-'}</p>
                        <div class="flex justify-between text-xs text-red-500 mt-1">
                            <span>Puntos: ${igComponents.estado_vital.puntuacion || 0}</span>
                            <span>Peso: ${((igComponents.estado_vital.ponderacion || 0) * 100)}%</span>
                        </div>
                    </div>
                `;
            }
            
            return `
                <div class="space-y-3">
                    <h4 class="font-semibold text-gray-700 mb-2">Índice de Gravedad (IG):</h4>
                    
                    <div class="grid grid-cols-2 gap-3">
                        ${componentsHtml}
                    </div>
                    
                    <div class="mt-4 p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-yellow-700">IG Calculado:</span>
                            <span class="text-xl font-bold text-yellow-800">${(coef.IG || item.gi_value || 0).toFixed(4)}</span>
                        </div>
                        <p class="text-xs text-yellow-600 mt-1">Cada dimensión pondera un 25% del total</p>
                    </div>
                    
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg border-l-4 border-yellow-500">
                        <p class="text-sm text-gray-600">
                            <strong>Cálculo:</strong> VR × IG = ${formatMoney(coef.VR || item.base_value)} × ${(coef.IG || item.gi_value || 0).toFixed(4)}
                        </p>
                        <p class="text-sm text-gray-600 mt-1">
                            = <strong>${formatMoney(item.total_cost)} €</strong>
                        </p>
                    </div>
                </div>
            `;
        }
        
        function formatMoney(value) {
            return new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
        }
        
        function closeCostDetail() {
            document.getElementById('costDetailModal').classList.add('hidden');
        }
        
        // Cerrar modal con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCostDetail();
            }
        });
    </script>
</x-app-layout>
