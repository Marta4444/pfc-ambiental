<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Panel de Administración') }}
            </h2>
            <a href="{{ route('statistics.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Estadísticas Generales
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- FILTROS --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('statistics.admin') }}">
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; align-items: end;">
                        <div>
                            <label for="date_from" class="block text-xs font-medium text-gray-700 mb-1">Desde</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                        </div>
                        <div>
                            <label for="date_to" class="block text-xs font-medium text-gray-700 mb-1">Hasta</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                        </div>
                        <div style="grid-column: span 3; display: flex; gap: 8px; justify-content: flex-end;">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 rounded-md text-sm text-white font-medium hover:bg-purple-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Filtrar
                            </button>
                            <a href="{{ route('statistics.admin') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-sm text-gray-700 font-medium hover:bg-gray-300">
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- MÉTRICAS PRINCIPALES DE CASOS --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-4 border-t-4 border-blue-500">
                    <p class="text-sm text-gray-500">Total Casos</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($totalReports) }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4 border-t-4 border-green-500">
                    <p class="text-sm text-gray-500">Completados</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($completedReports) }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4 border-t-4 border-yellow-500">
                    <p class="text-sm text-gray-500">Pendientes</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($pendingReports) }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4 border-t-4 border-purple-500">
                    <p class="text-sm text-gray-500">Tasa Completado</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($completionRate, 1) }}%</p>
                </div>
            </div>

            {{-- SECCIÓN DE AUDITORÍA --}}
            <div class="bg-gradient-to-r from-slate-800 to-slate-700 text-white shadow-sm sm:rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Actividad del Sistema (Auditoría)
                    </h3>
                    <a href="{{ route('audit.index') }}" class="text-sm text-slate-300 hover:text-white flex items-center">
                        Ver registro completo
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white/10 rounded-lg p-3">
                        <p class="text-xs text-slate-300">Total Registros</p>
                        <p class="text-2xl font-bold">{{ number_format($totalAuditLogs) }}</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-3">
                        <p class="text-xs text-slate-300">Logins (7 días)</p>
                        <p class="text-2xl font-bold">{{ number_format($recentLogins) }}</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-3">
                        <p class="text-xs text-slate-300">Usuarios Activos</p>
                        <p class="text-2xl font-bold">{{ count($auditByUser) }}</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-3">
                        <p class="text-xs text-slate-300">Tipos de Acción</p>
                        <p class="text-2xl font-bold">{{ count($auditByAction) }}</p>
                    </div>
                </div>
            </div>

            {{-- GRÁFICOS DE AUDITORÍA --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Tendencia de Actividad (30 días) --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Actividad del Sistema (Últimos 30 días)</h3>
                    <div class="relative h-64">
                        <canvas id="auditTrendChart"></canvas>
                    </div>
                </div>

                {{-- Actividad por Tipo de Acción --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Acciones Realizadas</h3>
                    <div class="relative h-64">
                        <canvas id="auditActionsChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- ACTIVIDAD POR USUARIO Y MODELO --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Top Usuarios por Actividad --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Usuarios Más Activos</h3>
                    <div class="relative h-64">
                        <canvas id="auditUsersChart"></canvas>
                    </div>
                </div>

                {{-- Actividad por Tipo de Modelo --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Registros por Tipo de Entidad</h3>
                    <div class="relative h-64">
                        <canvas id="auditModelsChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- ESTADÍSTICAS DE CATEGORÍAS --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Rendimiento por Categoría
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completados</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tasa</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Coste Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($categoryStats as $cat)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900">{{ $cat->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-gray-600">{{ $cat->total_reports }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-green-600">{{ $cat->completed }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="h-2 rounded-full {{ $cat->completion_rate >= 70 ? 'bg-green-500' : ($cat->completion_rate >= 40 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                                 style="width: {{ $cat->completion_rate }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ $cat->completion_rate }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-gray-600">{{ number_format($cat->total_cost ?? 0, 2) }} €</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay datos de categorías</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- GRÁFICOS DE CATEGORÍAS Y SUBCATEGORÍAS --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Top Categorías por Coste --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Categorías con Mayor Coste</h3>
                    <div class="relative h-64">
                        <canvas id="costlyCategoriesChart"></canvas>
                    </div>
                </div>

                {{-- Top Subcategorías --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Subcategorías Más Utilizadas</h3>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @forelse($topSubcategories as $index => $sub)
                        <div class="flex items-center justify-between p-2 {{ $index % 2 === 0 ? 'bg-gray-50' : '' }} rounded">
                            <div>
                                <span class="font-medium text-gray-800">{{ $sub->subcategory_name }}</span>
                                <span class="text-xs text-gray-500 ml-2">({{ $sub->category_name }})</span>
                            </div>
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium">{{ $sub->total }}</span>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 py-4">No hay datos de subcategorías</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- EFICIENCIA DE USUARIOS Y COMUNIDADES --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Eficiencia por Usuario --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Eficiencia por Usuario
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Completados</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Tiempo Medio</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($userEfficiency as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $user->name }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">{{ $user->completed_count }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-center text-gray-600">{{ number_format($user->avg_days ?? 0, 1) }} días</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-4 text-center text-gray-500">No hay datos de eficiencia</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Distribución por Comunidad --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Casos por Comunidad Autónoma
                    </h3>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @forelse($communitiesStats as $community)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                            <span class="font-medium text-gray-800">{{ $community->community }}</span>
                            <div class="flex items-center gap-4">
                                <span class="text-sm text-gray-600">{{ number_format($community->total_cost ?? 0, 2) }} €</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">{{ $community->total }}</span>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 py-4">No hay datos de comunidades</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- COSTES --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Resumen de Costes</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Coste Total</p>
                        <p class="text-xl font-bold text-gray-800">{{ number_format($totalCost, 2) }} €</p>
                    </div>
                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                        <p class="text-xs text-blue-600 uppercase tracking-wide">VR Total</p>
                        <p class="text-xl font-bold text-blue-700">{{ number_format($vrTotal, 2) }} €</p>
                    </div>
                    <div class="text-center p-3 bg-green-50 rounded-lg">
                        <p class="text-xs text-green-600 uppercase tracking-wide">VE Total</p>
                        <p class="text-xl font-bold text-green-700">{{ number_format($veTotal, 2) }} €</p>
                    </div>
                    <div class="text-center p-3 bg-purple-50 rounded-lg">
                        <p class="text-xs text-purple-600 uppercase tracking-wide">VS Total</p>
                        <p class="text-xl font-bold text-purple-700">{{ number_format($vsTotal, 2) }} €</p>
                    </div>
                </div>
            </div>

            {{-- MÉTRICAS SECUNDARIAS --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="bg-indigo-100 p-2 rounded-lg mr-3">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Asignados</p>
                            <p class="text-lg font-semibold text-gray-800">{{ number_format($assignedReports) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="bg-red-100 p-2 rounded-lg mr-3">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Sin Asignar</p>
                            <p class="text-lg font-semibold text-gray-800">{{ number_format($unassignedCount) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="bg-orange-100 p-2 rounded-lg mr-3">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tiempo Promedio</p>
                            <p class="text-lg font-semibold text-gray-800">{{ number_format($avgResolutionTime, 1) }} días</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="bg-red-100 p-2 rounded-lg mr-3">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Urgentes</p>
                            <p class="text-lg font-semibold text-gray-800">{{ number_format($urgentReports) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GRÁFICOS DE USUARIOS --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Casos por Usuario Asignado --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Casos por Usuario Asignado</h3>
                    <div class="relative h-64">
                        <canvas id="userAssignedChart"></canvas>
                    </div>
                </div>

                {{-- Casos Completados por Usuario --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Casos Completados por Usuario</h3>
                    <div class="relative h-64">
                        <canvas id="userCompletedChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- DISTRIBUCIÓN DE URGENCIA --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Gráfico de Urgencia --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribución por Urgencia</h3>
                    <div class="relative h-64">
                        <canvas id="urgencyChart"></canvas>
                    </div>
                </div>

                {{-- Tarjetas de urgencia --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Prioridades</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border-l-4 border-red-500">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="font-medium text-red-800">Urgentes</span>
                            </div>
                            <span class="text-2xl font-bold text-red-600">{{ $urgentReports }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-medium text-yellow-800">Prioridad Alta</span>
                            </div>
                            <span class="text-2xl font-bold text-yellow-600">{{ $highPriorityReports }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border-l-4 border-green-500">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-medium text-green-800">Normal</span>
                            </div>
                            <span class="text-2xl font-bold text-green-600">{{ $normalReports }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTIVIDAD RECIENTE --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Casos Recientes</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignado a</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentReports as $report)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $report->id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('reports.show', $report) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                        {{ Str::limit($report->title, 40) }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $report->category->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $report->assignedTo->name ?? 'Sin asignar' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'nuevo' => 'bg-blue-100 text-blue-800',
                                            'en_proceso' => 'bg-yellow-100 text-yellow-800',
                                            'en_espera' => 'bg-purple-100 text-purple-800',
                                            'completado' => 'bg-green-100 text-green-800',
                                        ];
                                        $statusLabels = [
                                            'nuevo' => 'Nuevo',
                                            'en_proceso' => 'En Proceso',
                                            'en_espera' => 'En Espera',
                                            'completado' => 'Completado',
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$report->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $statusLabels[$report->status] ?? $report->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $report->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    No hay reportes recientes
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const colors = [
                'rgba(59, 130, 246, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(234, 179, 8, 0.8)',
                'rgba(168, 85, 247, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(236, 72, 153, 0.8)',
                'rgba(20, 184, 166, 0.8)',
                'rgba(99, 102, 241, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(6, 182, 212, 0.8)',
            ];

            // Datos desde PHP
            const reportsByAssignedUser = @json($reportsByAssignedUser);
            const completedByUser = @json($completedByUser);
            const auditTrendFull = @json($auditTrendFull);
            const auditByAction = @json($auditByAction);
            const auditByUser = @json($auditByUser);
            const auditByModelType = @json($auditByModelType);
            const topCostlyCategories = @json($topCostlyCategories);
            const actionLabels = @json($actionLabels);

            // Helper para traducir nombres de acciones
            function translateAction(action) {
                return actionLabels[action] || action;
            }

            // Helper para traducir tipos de modelo
            function translateModelType(modelType) {
                const translations = {
                    'App\\Models\\Report': 'Casos',
                    'App\\Models\\User': 'Usuarios',
                    'App\\Models\\Category': 'Categorías',
                    'App\\Models\\Subcategory': 'Subcategorías',
                    'App\\Models\\Field': 'Campos',
                    'App\\Models\\Species': 'Especies',
                    'App\\Models\\Petitioner': 'Solicitantes',
                    'App\\Models\\ProtectedArea': 'Áreas Protegidas',
                    'App\\Models\\ReportDetail': 'Detalles',
                    'App\\Models\\ReportCostItem': 'Costes'
                };
                return translations[modelType] || modelType.split('\\').pop();
            }

            // 1. Tendencia de Auditoría (30 días)
            const auditTrendCtx = document.getElementById('auditTrendChart').getContext('2d');
            new Chart(auditTrendCtx, {
                type: 'line',
                data: {
                    labels: Object.keys(auditTrendFull).map(date => {
                        const d = new Date(date);
                        return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
                    }),
                    datasets: [{
                        label: 'Actividad',
                        data: Object.values(auditTrendFull),
                        borderColor: 'rgba(99, 102, 241, 1)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true },
                        x: { 
                            ticks: { 
                                maxTicksLimit: 10,
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });

            // 2. Acciones de Auditoría
            const auditActionsCtx = document.getElementById('auditActionsChart').getContext('2d');
            new Chart(auditActionsCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(auditByAction).map(translateAction),
                    datasets: [{
                        data: Object.values(auditByAction),
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { font: { size: 11 } }
                        }
                    }
                }
            });

            // 3. Usuarios Más Activos
            const auditUsersCtx = document.getElementById('auditUsersChart').getContext('2d');
            new Chart(auditUsersCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(auditByUser),
                    datasets: [{
                        label: 'Acciones',
                        data: Object.values(auditByUser),
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });

            // 4. Actividad por Tipo de Modelo
            const auditModelsCtx = document.getElementById('auditModelsChart').getContext('2d');
            new Chart(auditModelsCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(auditByModelType).map(translateModelType),
                    datasets: [{
                        label: 'Registros',
                        data: Object.values(auditByModelType),
                        backgroundColor: colors,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // 5. Categorías Más Costosas
            const costlyCategoriesCtx = document.getElementById('costlyCategoriesChart').getContext('2d');
            new Chart(costlyCategoriesCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(topCostlyCategories),
                    datasets: [{
                        label: 'Coste (€)',
                        data: Object.values(topCostlyCategories),
                        backgroundColor: 'rgba(234, 179, 8, 0.8)',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('es-ES') + ' €';
                                }
                            }
                        }
                    }
                }
            });

            // 6. Casos por Usuario Asignado
            const userAssignedCtx = document.getElementById('userAssignedChart').getContext('2d');
            new Chart(userAssignedCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(reportsByAssignedUser),
                    datasets: [{
                        label: 'Casos Asignados',
                        data: Object.values(reportsByAssignedUser),
                        backgroundColor: colors,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });

            // 7. Casos Completados por Usuario
            const userCompletedCtx = document.getElementById('userCompletedChart').getContext('2d');
            new Chart(userCompletedCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(completedByUser),
                    datasets: [{
                        label: 'Casos Completados',
                        data: Object.values(completedByUser),
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });

            // 8. Distribución por Urgencia
            const urgencyCtx = document.getElementById('urgencyChart').getContext('2d');
            new Chart(urgencyCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Urgente', 'Alta', 'Normal'],
                    datasets: [{
                        data: [{{ $urgentReports }}, {{ $highPriorityReports }}, {{ $normalReports }}],
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(234, 179, 8, 0.8)',
                            'rgba(34, 197, 94, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>
</x-app-layout>
