<x-app-layout>
    @php $isAdmin = Auth::user()->role === 'admin'; @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Estadísticas') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- FILTROS --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('statistics.index') }}">
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label for="date_from" class="block text-xs font-medium text-gray-700 mb-1">Desde</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-eco-500 text-sm">
                        </div>
                        <div>
                            <label for="date_to" class="block text-xs font-medium text-gray-700 mb-1">Hasta</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-eco-500 text-sm">
                        </div>
                        <div>
                            <label for="category_id" class="block text-xs font-medium text-gray-700 mb-1">Categoría</label>
                            <select name="category_id" id="category_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-eco-500 text-sm">
                                <option value="">Todas</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Estado</label>
                            <select name="status" id="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-eco-500 text-sm">
                                <option value="">Todos</option>
                                @foreach($statuses as $s)
                                    <option value="{{ $s }}" {{ $status == $s ? 'selected' : '' }}>{{ $statusLabels[$s] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="urgency" class="block text-xs font-medium text-gray-700 mb-1">Urgencia</label>
                            <select name="urgency" id="urgency"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-eco-500 text-sm">
                                <option value="">Todas</option>
                                @foreach($urgencies as $u)
                                    <option value="{{ $u }}" {{ $urgency == $u ? 'selected' : '' }}>{{ $urgencyLabels[$u] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; align-items: end;">
                        <div>
                            <label for="community" class="block text-xs font-medium text-gray-700 mb-1">Comunidad</label>
                            <select name="community" id="community"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-eco-500 text-sm">
                                <option value="">Todas</option>
                                @foreach($communities as $comm)
                                    <option value="{{ $comm }}" {{ $community == $comm ? 'selected' : '' }}>{{ $comm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="assigned_to" class="block text-xs font-medium text-gray-700 mb-1">Asignado a</label>
                            <select name="assigned_to" id="assigned_to"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-eco-500 text-sm">
                                <option value="">Todos</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $assignedTo == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="assignment_status" class="block text-xs font-medium text-gray-700 mb-1">Asignación</label>
                            <select name="assignment_status" id="assignment_status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-eco-500 text-sm">
                                <option value="">Todos</option>
                                <option value="assigned" {{ $assignmentStatus == 'assigned' ? 'selected' : '' }}>Asignados</option>
                                <option value="unassigned" {{ $assignmentStatus == 'unassigned' ? 'selected' : '' }}>Sin asignar</option>
                            </select>
                        </div>
                        <div style="grid-column: span 2; display: flex; gap: 8px; justify-content: flex-end;">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-eco-600 rounded-md text-sm text-white font-medium hover:bg-eco-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Filtrar
                            </button>
                            <a href="{{ route('statistics.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-sm text-gray-700 font-medium hover:bg-gray-300">
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TARJETAS RESUMEN --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Casos</p>
                            <p class="text-3xl font-bold text-eco-600">{{ number_format($totalReports) }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <svg class="w-8 h-8 text-eco-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Coste Total</p>
                            <p class="text-3xl font-bold text-green-600">{{ number_format($totalCost, 2) }} €</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Coste Promedio</p>
                            <p class="text-3xl font-bold text-purple-600">{{ number_format($avgCost, 2) }} €</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GRÁFICOS FILA 1 --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                {{-- Casos por Estado --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Casos por Estado</h3>
                    <div class="relative h-48">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                {{-- Casos por Urgencia --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Casos por Urgencia</h3>
                    <div class="relative h-48">
                        <canvas id="urgencyChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- GRÁFICOS FILA 2 --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                {{-- Tendencia Mensual --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Tendencia Mensual (12 meses)</h3>
                    <div class="relative h-48">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                {{-- Casos por Categoría --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Casos por Categoría (Top 10)</h3>
                    <div class="relative h-48">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- GRÁFICOS FILA 3 --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                {{-- Casos por Comunidad --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Casos por Comunidad (Top 10)</h3>
                    <div class="relative h-48">
                        <canvas id="communityChart"></canvas>
                    </div>
                </div>

                {{-- Costes por Categoría --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Costes por Categoría (Top 10)</h3>
                    <div class="relative h-48">
                        <canvas id="costsChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- SECCIÓN EXCLUSIVA PARA ADMINISTRADORES --}}
            {{-- ============================================== --}}
            @if($isAdmin && !empty($adminData))
            
            <div class="mt-8 mb-6">
                <div class="flex items-center">
                    <div class="flex-grow border-t border-purple-300"></div>
                    <span class="flex-shrink mx-4 text-purple-600 font-semibold text-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Estadísticas de Administración
                    </span>
                    <div class="flex-grow border-t border-purple-300"></div>
                </div>
            </div>

            {{-- MÉTRICAS DEL SISTEMA --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                    <p class="text-xs text-purple-600 font-medium">Usuarios</p>
                    <p class="text-2xl font-bold text-purple-700">{{ $adminData['totalUsers'] }}</p>
                </div>
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 shadow-sm sm:rounded-lg p-4 border-l-4 border-indigo-500">
                    <p class="text-xs text-indigo-600 font-medium">Categorías</p>
                    <p class="text-2xl font-bold text-indigo-700">{{ $adminData['totalCategories'] }}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="text-xs text-eco-600 font-medium">Subcategorías</p>
                    <p class="text-2xl font-bold text-eco-700">{{ $adminData['totalSubcategories'] }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                    <p class="text-xs text-green-600 font-medium">Especies</p>
                    <p class="text-2xl font-bold text-green-700">{{ $adminData['totalSpecies'] }}</p>
                </div>
                <div class="bg-gradient-to-br from-teal-50 to-teal-100 shadow-sm sm:rounded-lg p-4 border-l-4 border-teal-500">
                    <p class="text-xs text-teal-600 font-medium">Áreas Protegidas</p>
                    <p class="text-2xl font-bold text-teal-700">{{ $adminData['totalProtectedAreas'] }}</p>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 shadow-sm sm:rounded-lg p-4 border-l-4 border-orange-500">
                    <p class="text-xs text-orange-600 font-medium">Solicitantes</p>
                    <p class="text-2xl font-bold text-orange-700">{{ $adminData['totalPetitioners'] }}</p>
                </div>
            </div>

            {{-- SECCIÓN DE AUDITORÍA --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-4 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Actividad del Sistema (Auditoría)
                    </h3>
                    <a href="{{ route('audit.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                        Ver registro completo
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-indigo-50 rounded-lg p-3">
                        <p class="text-xs text-indigo-600">Total Registros</p>
                        <p class="text-2xl font-bold text-indigo-800">{{ number_format($adminData['totalAuditLogs']) }}</p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-3">
                        <p class="text-xs text-indigo-600">Logins (7 días)</p>
                        <p class="text-2xl font-bold text-indigo-800">{{ number_format($adminData['recentLogins']) }}</p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-3">
                        <p class="text-xs text-indigo-600">Usuarios Activos</p>
                        <p class="text-2xl font-bold text-indigo-800">{{ count($adminData['auditByUser']) }}</p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-3">
                        <p class="text-xs text-indigo-600">Tipos de Acción</p>
                        <p class="text-2xl font-bold text-indigo-800">{{ count($adminData['auditByAction']) }}</p>
                    </div>
                </div>
            </div>

            {{-- GRÁFICOS DE AUDITORÍA --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                {{-- Tendencia de Actividad (30 días) --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Actividad del Sistema (Últimos 30 días)</h3>
                    <div class="relative h-48">
                        <canvas id="auditTrendChart"></canvas>
                    </div>
                </div>

                {{-- Actividad por Tipo de Acción --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Acciones Realizadas</h3>
                    <div class="relative h-48">
                        <canvas id="auditActionsChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- ACTIVIDAD POR USUARIO Y MODELO --}}
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
                {{-- Top Usuarios por Actividad --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Usuarios Más Activos</h3>
                    <div class="relative h-48">
                        <canvas id="auditUsersChart"></canvas>
                    </div>
                </div>

                {{-- Actividad por Tipo de Modelo --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Registros por Tipo de Entidad</h3>
                    <div class="relative h-48">
                        <canvas id="auditModelsChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- ESTADÍSTICAS DE CATEGORÍAS --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-3 mb-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-2 flex items-center">
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
                            @forelse($adminData['categoryStats'] as $cat)
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

            {{-- SUBCATEGORÍAS Y EFICIENCIA --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                {{-- Top Subcategorías --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-eco-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Subcategorías Más Utilizadas
                    </h3>
                    <div class="space-y-1 max-h-48 overflow-y-auto">
                        @forelse($adminData['topSubcategories'] as $index => $sub)
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

                {{-- Eficiencia por Usuario --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Eficiencia por Usuario
                    </h3>
                    <div class="overflow-x-auto max-h-48">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Completados</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Tiempo Medio</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($adminData['userEfficiency'] as $user)
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
            </div>

            @endif
            {{-- FIN SECCIÓN ADMIN --}}

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Colores para los gráficos
            const colors = {
                blue: 'rgba(59, 130, 246, 0.8)',
                green: 'rgba(34, 197, 94, 0.8)',
                yellow: 'rgba(234, 179, 8, 0.8)',
                red: 'rgba(239, 68, 68, 0.8)',
                purple: 'rgba(168, 85, 247, 0.8)',
                indigo: 'rgba(99, 102, 241, 0.8)',
                pink: 'rgba(236, 72, 153, 0.8)',
                teal: 'rgba(20, 184, 166, 0.8)',
                orange: 'rgba(249, 115, 22, 0.8)',
                cyan: 'rgba(6, 182, 212, 0.8)',
            };

            const statusColors = {
                'nuevo': colors.blue,
                'en_proceso': colors.yellow,
                'en_espera': colors.purple,
                'completado': colors.green
            };

            const statusLabels = @json($statusLabels);

            const urgencyColors = {
                'normal': colors.green,
                'alta': colors.yellow,
                'urgente': colors.red
            };

            const urgencyLabels = @json($urgencyLabels);

            // Datos desde PHP
            const reportsByStatus = @json($reportsByStatus);
            const reportsByUrgency = @json($reportsByUrgency);
            const reportsByCategory = @json($reportsByCategory);
            const reportsByCommunity = @json($reportsByCommunity);
            const monthlyTrend = @json($allMonths);
            const costsByCategory = @json($costsByCategory);

            // 1. Gráfico de Estado (Doughnut)
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(reportsByStatus).map(k => statusLabels[k] || k),
                    datasets: [{
                        data: Object.values(reportsByStatus),
                        backgroundColor: Object.keys(reportsByStatus).map(k => statusColors[k] || colors.blue),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });

            // 2. Gráfico de Urgencia (Pie)
            const urgencyCtx = document.getElementById('urgencyChart').getContext('2d');
            new Chart(urgencyCtx, {
                type: 'pie',
                data: {
                    labels: Object.keys(reportsByUrgency).map(k => urgencyLabels[k] || k),
                    datasets: [{
                        data: Object.values(reportsByUrgency),
                        backgroundColor: Object.keys(reportsByUrgency).map(k => urgencyColors[k] || colors.blue),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });

            // 3. Tendencia Mensual (Line)
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: Object.keys(monthlyTrend),
                    datasets: [
                        {
                            label: 'Total Casos',
                            data: Object.values(monthlyTrend).map(v => v.total),
                            borderColor: colors.blue,
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Completados',
                            data: Object.values(monthlyTrend).map(v => v.completed),
                            borderColor: colors.green,
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // 4. Casos por Categoría (Bar horizontal)
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(reportsByCategory),
                    datasets: [{
                        label: 'Casos',
                        data: Object.values(reportsByCategory),
                        backgroundColor: colors.blue,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // 5. Casos por Comunidad (Bar)
            const communityCtx = document.getElementById('communityChart').getContext('2d');
            new Chart(communityCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(reportsByCommunity),
                    datasets: [{
                        label: 'Casos',
                        data: Object.values(reportsByCommunity),
                        backgroundColor: colors.teal,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // 6. Costes por Categoría (Bar horizontal)
            const costsCtx = document.getElementById('costsChart').getContext('2d');
            new Chart(costsCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(costsByCategory),
                    datasets: [{
                        label: 'Coste (€)',
                        data: Object.values(costsByCategory),
                        backgroundColor: colors.green,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + new Intl.NumberFormat('es-ES', {
                                        style: 'currency',
                                        currency: 'EUR'
                                    }).format(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('es-ES', {
                                        style: 'currency',
                                        currency: 'EUR',
                                        minimumFractionDigits: 0
                                    }).format(value);
                                }
                            }
                        }
                    }
                }
            });

            // ========================================
            // GRÁFICOS EXCLUSIVOS PARA ADMINISTRADORES
            // ========================================
            @if($isAdmin && !empty($adminData))
            
            const adminColors = Object.values(colors);

            const auditTrendFull = @json($adminData['auditTrendFull']);
            const auditByAction = @json($adminData['auditByAction']);
            const auditByUser = @json($adminData['auditByUser']);
            const auditByModelType = @json($adminData['auditByModelType']);
            const actionLabels = @json($adminData['actionLabels']);

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

            // Admin 1: Tendencia de Auditoría (30 días)
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

            // Admin 2: Acciones de Auditoría (Doughnut)
            const auditActionsCtx = document.getElementById('auditActionsChart').getContext('2d');
            new Chart(auditActionsCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(auditByAction).map(translateAction),
                    datasets: [{
                        data: Object.values(auditByAction),
                        backgroundColor: adminColors,
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

            // Admin 3: Usuarios Más Activos
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

            // Admin 4: Actividad por Tipo de Modelo
            const auditModelsCtx = document.getElementById('auditModelsChart').getContext('2d');
            new Chart(auditModelsCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(auditByModelType).map(translateModelType),
                    datasets: [{
                        label: 'Registros',
                        data: Object.values(auditByModelType),
                        backgroundColor: adminColors,
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

            @endif
        });
    </script>
    @endpush
</x-app-layout>
