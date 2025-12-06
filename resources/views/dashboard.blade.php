<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>

            {{-- Botones de acción rápida --}}
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Ver Casos
                </a>
                <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Crear Caso
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(Auth::user()->role === 'admin')
            {{-- ===================== DASHBOARD ADMIN ===================== --}}
            
            @php
                $totalCasos = \App\Models\Report::count();
                $casosNuevos = \App\Models\Report::where('status', 'nuevo')->count();
                $casosSinAsignar = \App\Models\Report::where('assigned', false)->count();
                $totalSpecies = \App\Models\Species::count();
                $protectedSpecies = \App\Models\Species::where('is_protected', true)->count();
                $totalAreas = \App\Models\ProtectedArea::where('active', true)->count();
            @endphp

            {{-- Cabecera con estadísticas --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Panel de Administración</h3>
                        <p class="text-sm text-gray-600">Gestión del sistema ambiental SEPRONA</p>
                    </div>
                    <div class="flex flex-wrap gap-4 md:gap-6">
                        <div class="text-center px-3 md:px-4 md:border-r border-gray-200">
                            <p class="text-2xl font-bold text-blue-600">{{ $totalCasos }}</p>
                            <p class="text-xs text-gray-500">Total Casos</p>
                        </div>
                        <div class="text-center px-3 md:px-4 md:border-r border-gray-200">
                            <p class="text-2xl font-bold text-yellow-600">{{ $casosNuevos }}</p>
                            <p class="text-xs text-gray-500">Nuevos</p>
                        </div>
                        <div class="text-center px-3 md:px-4 md:border-r border-gray-200">
                            <p class="text-2xl font-bold text-purple-600">{{ $casosSinAsignar }}</p>
                            <p class="text-xs text-gray-500">Sin Asignar</p>
                        </div>
                        <div class="text-center px-3 md:px-4">
                            <p class="text-2xl font-bold text-green-600">{{ $totalSpecies }}</p>
                            <p class="text-xs text-gray-500">Especies</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GRID PRINCIPAL: 2 columnas --}}
            <div class="flex flex-col lg:flex-row gap-6">
                
                {{-- COLUMNA IZQUIERDA --}}
                <div class="w-full lg:w-1/2 space-y-6">
                    
                    {{-- Gestión de Casos --}}
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Gestión de Casos
                        </h4>
                        <div class="flex gap-2">
                            <a href="{{ route('reports.index') }}" class="flex-1 flex items-center justify-center p-2 bg-gray-50 rounded hover:bg-blue-50 transition group">
                                <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                <span class="text-sm text-gray-700 group-hover:text-blue-700">Ver todos</span>
                            </a>
                            <a href="{{ route('reports.create') }}" class="flex-1 flex items-center justify-center p-2 bg-gray-50 rounded hover:bg-blue-50 transition group">
                                <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-sm text-gray-700 group-hover:text-blue-700">Crear nuevo</span>
                            </a>
                        </div>
                    </div>

                    {{-- Campos de Formularios --}}
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Campos de Formularios
                        </h4>
                        <div class="flex gap-2">
                            <a href="{{ route('fields.index') }}" class="flex-1 flex items-center justify-center p-2 bg-gray-50 rounded hover:bg-teal-50 transition group">
                                <svg class="w-4 h-4 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                <span class="text-sm text-gray-700 group-hover:text-teal-700">Ver campos</span>
                            </a>
                            <a href="{{ route('fields.create') }}" class="flex-1 flex items-center justify-center p-2 bg-gray-50 rounded hover:bg-teal-50 transition group">
                                <svg class="w-4 h-4 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-sm text-gray-700 group-hover:text-teal-700">Crear campo</span>
                            </a>
                        </div>
                    </div>

                    {{-- Especies --}}
                    <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064" />
                                </svg>
                                Especies
                            </h4>
                            <span class="text-sm"><span class="font-bold text-green-600">{{ $totalSpecies }}</span> <span class="text-gray-500">({{ $protectedSpecies }} prot.)</span></span>
                        </div>
                        <a href="{{ route('species.index') }}" class="flex items-center p-2 bg-green-50 rounded hover:bg-green-100 transition group">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <span class="text-sm text-gray-700 group-hover:text-green-700">Gestionar catálogo</span>
                            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- COLUMNA DERECHA --}}
                <div class="w-full lg:w-1/2 space-y-6">
                    
                    {{-- Clasificación --}}
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Clasificación
                        </h4>
                        <div class="flex gap-2">
                            <a href="{{ route('categories.index') }}" class="flex-1 flex items-center justify-center p-2 bg-gray-50 rounded hover:bg-indigo-50 transition group">
                                <svg class="w-4 h-4 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <span class="text-sm text-gray-700 group-hover:text-indigo-700">Categorías</span>
                            </a>
                            <a href="{{ route('subcategories.index') }}" class="flex-1 flex items-center justify-center p-2 bg-gray-50 rounded hover:bg-indigo-50 transition group">
                                <svg class="w-4 h-4 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                                </svg>
                                <span class="text-sm text-gray-700 group-hover:text-indigo-700">Subcategorías</span>
                            </a>
                        </div>
                    </div>

                    {{-- Peticionarios --}}
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Peticionarios
                        </h4>
                        <div class="flex gap-2">
                            <a href="{{ route('petitioners.index') }}" class="flex-1 flex items-center justify-center p-2 bg-gray-50 rounded hover:bg-orange-50 transition group">
                                <svg class="w-4 h-4 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-sm text-gray-700 group-hover:text-orange-700">Ver todos</span>
                            </a>
                            <a href="{{ route('petitioners.create') }}" class="flex-1 flex items-center justify-center p-2 bg-gray-50 rounded hover:bg-orange-50 transition group">
                                <svg class="w-4 h-4 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-sm text-gray-700 group-hover:text-orange-700">Crear nuevo</span>
                            </a>
                        </div>
                    </div>

                    {{-- Áreas Protegidas --}}
                    <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Áreas Protegidas
                            </h4>
                            <span class="text-sm"><span class="font-bold text-blue-600">{{ $totalAreas }}</span> <span class="text-gray-500">activas</span></span>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('protected-areas.index') }}" class="flex-1 flex items-center justify-center p-2 bg-blue-50 rounded hover:bg-blue-100 transition group">
                                <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                <span class="text-sm text-gray-700 group-hover:text-blue-700">Ver todas</span>
                            </a>
                            <a href="{{ route('protected-areas.create') }}" class="flex-1 flex items-center justify-center p-2 bg-blue-50 rounded hover:bg-blue-100 transition group">
                                <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-sm text-gray-700 group-hover:text-blue-700">Crear nueva</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @else
            {{-- ===================== DASHBOARD USUARIO ===================== --}}
            @php
            $userId = Auth::id();
            $totalCasos = \App\Models\Report::count();
            $casosSinAsignar = \App\Models\Report::where('assigned', false)->count();
            $totalMisAsignados = \App\Models\Report::where('assigned_to', $userId)->count();
            $misEnProceso = \App\Models\Report::where('assigned_to', $userId)->where('status', 'en_proceso')->count();
            $misEnEspera = \App\Models\Report::where('assigned_to', $userId)->where('status', 'en_espera')->count();
            $misCompletados = \App\Models\Report::where('assigned_to', $userId)->where('status', 'completado')->count();
            $misNuevos = \App\Models\Report::where('assigned_to', $userId)->where('status', 'nuevo')->count();
            $misCreadosCount = \App\Models\Report::where('user_id', $userId)->count();

            $misCasosPrioritarios = \App\Models\Report::where('assigned_to', $userId)
                ->whereIn('status', ['nuevo', 'en_proceso', 'en_espera'])
                ->orderByRaw("FIELD(urgency, 'urgente', 'alta', 'normal')")
                ->orderBy('date_petition', 'asc')
                ->with(['category', 'subcategory'])
                ->take(10)
                ->get();

            $casosDisponibles = \App\Models\Report::where('assigned', false)
                ->where('status', 'nuevo')
                ->orderByRaw("FIELD(urgency, 'urgente', 'alta', 'normal')")
                ->orderBy('created_at', 'desc')
                ->with(['category', 'user'])
                ->take(5)
                ->get();

            $globalPorEstado = \App\Models\Report::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $casosPorCategoria = \App\Models\Report::selectRaw('category_id, count(*) as total')
                ->groupBy('category_id')
                ->with('category')
                ->orderByDesc('total')
                ->take(6)
                ->get();

            // ===== ESTADÍSTICAS DE COSTES =====
            // Coste total global
            $costeTotalGlobal = \App\Models\Report::sum('total_cost');
            $vrTotalGlobal = \App\Models\Report::sum('vr_total');
            $veTotalGlobal = \App\Models\Report::sum('ve_total');
            $vsTotalGlobal = \App\Models\Report::sum('vs_total');
            
            // Casos con costes calculados
            $casosConCostes = \App\Models\Report::where('total_cost', '>', 0)->count();
            
            // Costes por categoría
            $costesPorCategoria = \App\Models\Report::selectRaw('category_id, SUM(total_cost) as total_cost, SUM(vr_total) as vr, SUM(ve_total) as ve, SUM(vs_total) as vs, COUNT(*) as casos')
                ->where('total_cost', '>', 0)
                ->groupBy('category_id')
                ->with('category')
                ->orderByDesc('total_cost')
                ->get();

            // Mis costes (casos asignados a mí)
            $misCostesTotal = \App\Models\Report::where('assigned_to', $userId)->sum('total_cost');
            $misCasosConCostes = \App\Models\Report::where('assigned_to', $userId)->where('total_cost', '>', 0)->count();
            
            // Top 5 casos con mayor coste
            $topCasosCoste = \App\Models\Report::where('total_cost', '>', 0)
                ->orderByDesc('total_cost')
                ->with(['category', 'subcategory'])
                ->take(5)
                ->get();

            // Coste promedio por caso
            $costePromedio = $casosConCostes > 0 ? $costeTotalGlobal / $casosConCostes : 0;
            @endphp

            {{-- Mensaje de bienvenida --}}
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-800">¡Hola, {{ Auth::user()->name }}!</h3>
                <p class="text-gray-600">Este es el resumen de tu actividad y casos pendientes.</p>
            </div>

            {{-- Layout de dos columnas --}}
            <div class="flex flex-col lg:flex-row gap-6">
                {{-- Columna izquierda: Mis casos prioritarios --}}
                <div class="w-full lg:w-2/3 space-y-6">
                    {{-- Mis casos prioritarios --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Mis Casos Pendientes
                                </h4>
                                <a href="{{ route('reports.index', ['assigned_to' => $userId]) }}" class="text-sm text-blue-600 hover:text-blue-800">Ver todos →</a>
                            </div>
                        </div>
                        <div class="p-4">
                            @if($misCasosPrioritarios->isEmpty())
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">No tienes casos pendientes asignados</p>
                            </div>
                            @else
                            <div class="space-y-3">
                                @foreach($misCasosPrioritarios as $caso)
                                <a href="{{ route('reports.show', $caso) }}" class="block p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-medium text-gray-900">{{ $caso->ip }}</span>
                                                @if($caso->urgency === 'urgente')
                                                <span class="px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 rounded-full">Urgente</span>
                                                @elseif($caso->urgency === 'alta')
                                                <span class="px-2 py-0.5 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">Alta</span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-600 truncate">{{ $caso->title }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ $caso->category->name ?? '' }} → {{ $caso->subcategory->name ?? '' }}</p>
                                        </div>
                                        <div class="ml-4 flex-shrink-0">
                                            @php
                                            $statusColors = [
                                                'nuevo' => 'bg-blue-100 text-blue-800',
                                                'en_proceso' => 'bg-yellow-100 text-yellow-800',
                                                'en_espera' => 'bg-gray-100 text-gray-800',
                                                'completado' => 'bg-green-100 text-green-800',
                                            ];
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-medium rounded {{ $statusColors[$caso->status] ?? 'bg-gray-100' }}">
                                                {{ \App\Models\Report::STATUS_LABELS[$caso->status] ?? $caso->status }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Casos disponibles --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                    Casos Disponibles
                                </h4>
                                <a href="{{ route('reports.index', ['assigned_to' => 'unassigned']) }}" class="text-sm text-purple-600 hover:text-purple-800">Ver todos →</a>
                            </div>
                        </div>
                        <div class="p-4">
                            @if($casosDisponibles->isEmpty())
                            <div class="text-center py-6">
                                <p class="text-sm text-gray-500">No hay casos sin asignar en este momento</p>
                            </div>
                            @else
                            <div class="space-y-2">
                                @foreach($casosDisponibles as $caso)
                                <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900 text-sm">{{ $caso->ip }}</span>
                                            @if($caso->urgency === 'urgente')
                                            <span class="px-1.5 py-0.5 text-xs font-medium bg-red-100 text-red-800 rounded">!</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 truncate">{{ $caso->title }}</p>
                                    </div>
                                    <form action="{{ route('reports.selfAssign', $caso) }}" method="POST" class="ml-2">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 text-xs font-medium text-white bg-purple-600 rounded hover:bg-purple-700 transition">Asignarme</button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- ===== NUEVA SECCIÓN: ESTADÍSTICAS DE COSTES ===== --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Estadísticas de Costes
                            </h4>
                        </div>
                        <div class="p-4">
                            {{-- Resumen general de costes --}}
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg p-4 text-center">
                                    <p class="text-xs text-emerald-600 font-medium uppercase">Coste Total</p>
                                    <p class="text-xl font-bold text-emerald-700">{{ number_format($costeTotalGlobal, 2, ',', '.') }} €</p>
                                    <p class="text-xs text-emerald-500 mt-1">{{ $casosConCostes }} casos valorados</p>
                                </div>
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 text-center">
                                    <p class="text-xs text-blue-600 font-medium uppercase">VR Total</p>
                                    <p class="text-lg font-bold text-blue-700">{{ number_format($vrTotalGlobal, 2, ',', '.') }} €</p>
                                    <p class="text-xs text-blue-500 mt-1">Valor Reposición</p>
                                </div>
                                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 text-center">
                                    <p class="text-xs text-purple-600 font-medium uppercase">VE Total</p>
                                    <p class="text-lg font-bold text-purple-700">{{ number_format($veTotalGlobal, 2, ',', '.') }} €</p>
                                    <p class="text-xs text-purple-500 mt-1">Valor Ecológico</p>
                                </div>
                                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 text-center">
                                    <p class="text-xs text-orange-600 font-medium uppercase">VS Total</p>
                                    <p class="text-lg font-bold text-orange-700">{{ number_format($vsTotalGlobal, 2, ',', '.') }} €</p>
                                    <p class="text-xs text-orange-500 mt-1">Valor Social</p>
                                </div>
                            </div>

                            {{-- Costes por categoría --}}
                            @if($costesPorCategoria->isNotEmpty())
                            <div class="mb-6">
                                <h5 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    Costes por Categoría
                                </h5>
                                <div class="space-y-3">
                                    @foreach($costesPorCategoria as $item)
                                    @php
                                        $porcentaje = $costeTotalGlobal > 0 ? ($item->total_cost / $costeTotalGlobal) * 100 : 0;
                                    @endphp
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium text-gray-700">{{ $item->category->name ?? 'Sin categoría' }}</span>
                                            <span class="text-sm font-bold text-gray-900">{{ number_format($item->total_cost, 2, ',', '.') }} €</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ min($porcentaje, 100) }}%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span>{{ $item->casos }} casos</span>
                                            <div class="flex gap-3">
                                                <span>VR: {{ number_format($item->vr, 0, ',', '.') }}€</span>
                                                <span>VE: {{ number_format($item->ve, 0, ',', '.') }}€</span>
                                                <span>VS: {{ number_format($item->vs, 0, ',', '.') }}€</span>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            {{-- Top casos con mayor coste --}}
                            @if($topCasosCoste->isNotEmpty())
                            <div>
                                <h5 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    Top 5 Casos con Mayor Coste
                                </h5>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Caso</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Coste Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($topCasosCoste as $index => $caso)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-2">
                                                    <a href="{{ route('reports.show', $caso) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                                        {{ $caso->ip }}
                                                    </a>
                                                    <p class="text-xs text-gray-500 truncate max-w-xs">{{ $caso->title }}</p>
                                                </td>
                                                <td class="px-3 py-2 text-sm text-gray-600">
                                                    {{ $caso->category->name ?? '-' }}
                                                </td>
                                                <td class="px-3 py-2 text-right">
                                                    <span class="text-sm font-bold text-emerald-600">{{ number_format($caso->total_cost, 2, ',', '.') }} €</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @else
                            <div class="text-center py-6">
                                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">No hay casos con costes calculados aún</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Columna derecha: Estadísticas --}}
                <div class="w-full lg:w-1/3 space-y-6">
                    {{-- Mi resumen por estado --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800">Mi Resumen</h4>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Asignados</span>
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">{{ $totalMisAsignados }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Nuevos</span>
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">{{ $misNuevos }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">En Proceso</span>
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">{{ $misEnProceso }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">En Espera</span>
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">{{ $misEnEspera }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Completados</span>
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">{{ $misCompletados }}</span>
                            </div>
                            <div class="pt-3 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Creados por mí</span>
                                    <span class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded">{{ $misCreadosCount }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Mis costes (casos asignados) --}}
                    <div class="bg-white rounded-lg shadow border-l-4 border-emerald-500">
                        <div class="p-4 border-b border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Mis Costes
                            </h4>
                        </div>
                        <div class="p-4">
                            <div class="text-center mb-4">
                                <p class="text-2xl font-bold text-emerald-600">{{ number_format($misCostesTotal, 2, ',', '.') }} €</p>
                                <p class="text-xs text-gray-500">Total en mis casos asignados</p>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Casos valorados:</span>
                                <span class="font-medium text-gray-800">{{ $misCasosConCostes }} de {{ $totalMisAsignados }}</span>
                            </div>
                            @if($totalMisAsignados > 0)
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ ($misCasosConCostes / $totalMisAsignados) * 100 }}%"></div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Estadísticas globales (compactado) --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h4 class="text-lg font-semibold text-gray-800">Estadísticas Globales</h4>
                                <span class="text-sm font-bold text-gray-800">{{ $totalCasos }} casos</span>
                            </div>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Nuevos</span>
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">{{ $globalPorEstado['nuevo'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">En Proceso</span>
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">{{ $globalPorEstado['en_proceso'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">En Espera</span>
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">{{ $globalPorEstado['en_espera'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Completados</span>
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">{{ $globalPorEstado['completado'] ?? 0 }}</span>
                            </div>
                            <div class="pt-3 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Sin asignar</span>
                                    <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded">{{ $casosSinAsignar }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Resumen de costes global (compacto) --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800">Resumen Costes</h4>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Coste Promedio/Caso</span>
                                <span class="px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-800 rounded">{{ number_format($costePromedio, 2, ',', '.') }} €</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Casos Valorados</span>
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">{{ $casosConCostes }} de {{ $totalCasos }}</span>
                            </div>
                            <div class="pt-3 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Coste Total Sistema</span>
                                    <span class="text-sm font-bold text-emerald-600">{{ number_format($costeTotalGlobal, 2, ',', '.') }} €</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Casos por categoría --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800">Casos Por Categoría</h4>
                        </div>
                        <div class="p-4 space-y-3">
                            @forelse($casosPorCategoria as $item)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 truncate flex-1 mr-2">{{ $item->category->name ?? 'Sin categoría' }}</span>
                                <span class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded flex-shrink-0">{{ $item->total }}</span>
                            </div>
                            @empty
                            <p class="text-sm text-gray-500 text-center py-2">Sin datos</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>