<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>


            {{-- Botones de acción rápida para usuarios --}}
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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(Auth::user()->role === 'admin')
            {{-- ===================== DASHBOARD ADMIN ===================== --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                <div class="mb-6">
                    {{ __("Bienvenido, Administrador!") }}
                </div>

                {{-- Sección Casos para Admin --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Gestión de Casos</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Ver Casos --}}
                        <div class="bg-white border border-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center mb-3">
                                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-800">Ver Casos</h3>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">Consulta y gestiona todos los casos registrados</p>
                                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                    Ver Casos
                                </a>
                            </div>
                        </div>

                        {{-- Crear Nuevo Caso --}}
                        <div class="bg-white border border-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center mb-3">
                                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-800">Crear Caso</h3>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">Registra un nuevo caso en el sistema</p>
                                <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                    Crear Nuevo Caso
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel de Administración --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Panel de Administración</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {{-- Gestión de Categorías --}}
                        <div class="bg-white border border-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center mb-3">
                                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-800">Categorías</h3>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">Gestiona las categorías de casos</p>
                                <a href="{{ route('categories.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                    Ver Categorías
                                </a>
                            </div>
                        </div>

                        {{-- Gestión de Subcategorías --}}
                        <div class="bg-white border border-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center mb-3">
                                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-800">Subcategorías</h3>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">Gestiona las subcategorías</p>
                                <a href="{{ route('subcategories.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                    Ver Subcategorías
                                </a>
                            </div>
                        </div>

                        {{-- Gestión de Peticionarios --}}
                        <div class="bg-white border border-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center mb-3">
                                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-800">Peticionarios</h3>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">Gestiona unidades peticionarias</p>
                                <a href="{{ route('petitioners.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                    Ver Peticionarios
                                </a>
                            </div>
                        </div>

                        {{-- Gestión de Campos Dinámicos --}}
                        <div class="bg-white border border-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center mb-3">
                                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-800">Campos</h3>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">Gestiona campos dinámicos</p>
                                <a href="{{ route('fields.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                    Ver Campos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @else
            {{-- ===================== DASHBOARD USUARIO ===================== --}}
            @php
            $userId = Auth::id();

            // Estadísticas generales
            $totalCasos = \App\Models\Report::count();
            $casosNuevos = \App\Models\Report::where('status', 'nuevo')->count();
            $casosSinAsignar = \App\Models\Report::where('assigned', false)->count();

            // Mis casos asignados
            $misAsignados = \App\Models\Report::where('assigned_to', $userId);
            $totalMisAsignados = $misAsignados->count();

            // Mis casos por estado
            $misEnProceso = \App\Models\Report::where('assigned_to', $userId)->where('status', 'en_proceso')->count();
            $misEnEspera = \App\Models\Report::where('assigned_to', $userId)->where('status', 'en_espera')->count();
            $misCompletados = \App\Models\Report::where('assigned_to', $userId)->where('status', 'completado')->count();
            $misNuevos = \App\Models\Report::where('assigned_to', $userId)->where('status', 'nuevo')->count();

            // Casos que he creado
            $misCreadosCount = \App\Models\Report::where('user_id', $userId)->count();

            // Casos urgentes asignados a mí
            $misUrgentes = \App\Models\Report::where('assigned_to', $userId)
            ->where('urgency', 'urgente')
            ->whereIn('status', ['nuevo', 'en_proceso', 'en_espera'])
            ->orderBy('date_petition', 'asc')
            ->take(5)
            ->get();

            // Mis casos ordenados por urgencia (para la lista principal)
            $misCasosPrioritarios = \App\Models\Report::where('assigned_to', $userId)
            ->whereIn('status', ['nuevo', 'en_proceso', 'en_espera'])
            ->orderByRaw("FIELD(urgency, 'urgente', 'alta', 'normal')")
            ->orderBy('date_petition', 'asc')
            ->with(['category', 'subcategory'])
            ->take(10)
            ->get();

            // Casos nuevos sin asignar (disponibles)
            $casosDisponibles = \App\Models\Report::where('assigned', false)
            ->where('status', 'nuevo')
            ->orderByRaw("FIELD(urgency, 'urgente', 'alta', 'normal')")
            ->orderBy('created_at', 'desc')
            ->with(['category', 'user'])
            ->take(5)
            ->get();

            // Estadísticas por categoría (mis casos)
            $misCasosPorCategoria = \App\Models\Report::where('assigned_to', $userId)
            ->whereIn('status', ['nuevo', 'en_proceso', 'en_espera'])
            ->selectRaw('category_id, count(*) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();

            // Estadísticas globales por estado
            $globalPorEstado = \App\Models\Report::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
            @endphp

            {{-- Mensaje de bienvenida --}}
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-800">¡Hola, {{ Auth::user()->name }}!</h3>
                <p class="text-gray-600">Este es el resumen de tu actividad y casos pendientes.</p>
            </div>

            {{-- Tarjetas de resumen rápido --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                {{-- Mis casos asignados --}}
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500 uppercase">Mis Asignados</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $totalMisAsignados }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- En proceso --}}
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500 uppercase">En Proceso</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $misEnProceso }}</p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Completados --}}
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500 uppercase">Completados</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $misCompletados }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Casos sin asignar --}}
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500 uppercase">Sin Asignar</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $casosSinAsignar }}</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Columna izquierda: Mis casos prioritarios --}}
                <div class="lg:col-span-2 space-y-6">
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
                                <a href="{{ route('reports.index', ['assigned_to' => $userId]) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                    Ver todos →
                                </a>
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
                                            <p class="text-xs text-gray-400 mt-1">{{ $caso->category->name }} → {{ $caso->subcategory->name }}</p>
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

                    {{-- Casos disponibles para asignar --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                    Casos Disponibles
                                </h4>
                                <a href="{{ route('reports.index', ['assigned_to' => 'unassigned']) }}" class="text-sm text-purple-600 hover:text-purple-800">
                                    Ver todos →
                                </a>
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
                                        <button type="submit" class="px-3 py-1 text-xs font-medium text-white bg-purple-600 rounded hover:bg-purple-700 transition">
                                            Asignarme
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Columna derecha: Estadísticas --}}
                <div class="space-y-6">
                    {{-- Mi resumen por estado --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800">Mi Resumen</h4>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
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
                                        <span class="text-sm font-medium text-gray-700">Casos creados por mí</span>
                                        <span class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded">{{ $misCreadosCount }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Mis casos por categoría --}}
                    @if($misCasosPorCategoria->isNotEmpty())
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800">Por Categoría</h4>
                        </div>
                        <div class="p-4">
                            <div class="space-y-2">
                                @foreach($misCasosPorCategoria as $item)
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600 truncate" title="{{ $item->category->name ?? 'Sin categoría' }}">
                                        {{ Str::limit($item->category->name ?? 'Sin categoría', 25) }}
                                    </span>
                                    <span class="font-medium text-gray-800">{{ $item->total }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Estadísticas globales --}}
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800">Estadísticas Globales</h4>
                        </div>
                        <div class="p-4">
                            <div class="text-center mb-4">
                                <p class="text-3xl font-bold text-gray-800">{{ $totalCasos }}</p>
                                <p class="text-xs text-gray-500 uppercase">Total de Casos</p>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="p-2 bg-blue-50 rounded">
                                    <p class="text-lg font-bold text-blue-600">{{ $globalPorEstado['nuevo'] ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Nuevos</p>
                                </div>
                                <div class="p-2 bg-yellow-50 rounded">
                                    <p class="text-lg font-bold text-yellow-600">{{ $globalPorEstado['en_proceso'] ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">En Proceso</p>
                                </div>
                                <div class="p-2 bg-gray-50 rounded">
                                    <p class="text-lg font-bold text-gray-600">{{ $globalPorEstado['en_espera'] ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">En Espera</p>
                                </div>
                                <div class="p-2 bg-green-50 rounded">
                                    <p class="text-lg font-bold text-green-600">{{ $globalPorEstado['completado'] ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Completados</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>