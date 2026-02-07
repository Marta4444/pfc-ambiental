<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalles del Caso') }} - {{ $report->ip }}
            </h2>
            <div class="flex gap-2">
                {{-- Botón Editar: visible para admin, creador o asignado --}}
                @if(Auth::user()->role === 'admin' || $report->user_id === Auth::id() || $report->assigned_to === Auth::id())
                    <a href="{{ route('reports.edit', $report) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar Caso
                    </a>
                @endif
                
                {{-- Botón Volver a la lista --}}
                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Ver Lista de Casos
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

            @if (session('info'))
                <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('info') }}</span>
                </div>
            @endif

            {{-- Panel de Asignación --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        Estado de Asignación
                    </h3>

                    <div class="flex items-center justify-between">
                        <div>
                            @if($report->assigned && $report->assignedTo)
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Asignado a: {{ $report->assignedTo->name }} ({{ $report->assignedTo->agent_num }})
                                    </span>
                                </div>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Sin asignar
                                </span>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            @if(!$report->assigned || ($report->assigned && $report->assigned_to !== Auth::id()))
                                {{-- Botón de autoasignación (solo si no está asignado o está asignado a otro) --}}
                                @if(!$report->assigned)
                                    <form action="{{ route('reports.selfAssign', $report) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Autoasignar
                                        </button>
                                    </form>
                                @endif

                                {{-- Botón para asignar a otro (siempre visible si tienes permisos) --}}
                                @if(Auth::user()->role === 'admin' || !$report->assigned || $report->user_id === Auth::id())
                                    <button type="button" onclick="document.getElementById('assignModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Asignar
                                    </button>
                                @endif
                            @else
                                {{-- Botón de desasignación (solo si está asignado a ti mismo) --}}
                                <form action="{{ route('reports.unassign', $report) }}" method="POST" onsubmit="return confirm('¿Estás seguro de desasignar este caso?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Desasignarme
                                    </button>
                                </form>

                                {{-- Admin o creador pueden reasignar --}}
                                @if(Auth::user()->role === 'admin' || $report->user_id === Auth::id())
                                    <button type="button" onclick="document.getElementById('assignModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                        Reasignar
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información General --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Información General</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Número IP</label>
                            <p class="mt-1 text-sm text-gray-900 font-semibold">{{ $report->ip }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    bg-{{ $report->getStatusColor() }}-100 text-{{ $report->getStatusColor() }}-800">
                                    {{ $report->getStatusLabel() }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Urgencia</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    bg-{{ $report->getUrgencyColor() }}-100 text-{{ $report->getUrgencyColor() }}-800">
                                    {{ $report->getUrgencyLabel() }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Creado por</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $report->user->name }} ({{ $report->user->agent_num }})</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Título</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $report->title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Categoría</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $report->category->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subcategoría</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $report->subcategory->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unidad Peticionaria</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $report->petitioner->name }}
                                @if($report->petitioner->name === 'Otro' && $report->petitioner_other)
                                    - {{ $report->petitioner_other }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ubicación --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Ubicación
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Comunidad Autónoma</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $report->community }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Provincia</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $report->province }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Localidad</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $report->locality }}</p>
                        </div>

                        @if($report->coordinates)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Coordenadas GPS</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $report->coordinates }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Fechas --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Fechas
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha de Petición</label>
                            <p class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($report->date_petition)->format('d/m/Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha del Daño</label>
                            <p class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($report->date_damage)->format('d/m/Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha de Creación</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $report->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Antecedentes --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Antecedentes
                    </h3>
                    
                    <div class="prose max-w-none">
                        <p class="text-sm text-gray-900 whitespace-pre-line">{{ $report->background }}</p>
                    </div>
                </div>
            </div>

            {{-- Información Administrativa --}}
            @if($report->office || $report->diligency)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Información Administrativa
                    </h3>
        
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($report->office)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Despacho/Oficina</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $report->office }}</p>
                        </div>
                        @endif

                        @if($report->diligency)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Diligencias</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $report->diligency }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Valoraciones y Costes --}}
            @if($report->vr_total || $report->ve_total || $report->vs_total || $report->total_cost)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Valoraciones y Costes Económicos
                    </h3>
        
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @if($report->vr_total)
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <label class="block text-xs font-medium text-blue-700 uppercase tracking-wider">Valor de Reposición (VR)</label>
                            <p class="mt-2 text-2xl font-bold text-blue-900">{{ number_format($report->vr_total, 2, ',', '.') }} €</p>
                        </div>
                        @endif

                        @if($report->ve_total)
                        <div class="bg-green-50 p-4 rounded-lg">
                            <label class="block text-xs font-medium text-green-700 uppercase tracking-wider">Valor Ecológico (VE)</label>
                            <p class="mt-2 text-2xl font-bold text-green-900">{{ number_format($report->ve_total, 2, ',', '.') }} €</p>
                        </div>
                        @endif

                        @if($report->vs_total)
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <label class="block text-xs font-medium text-yellow-700 uppercase tracking-wider">Valor de Servicios (VS)</label>
                            <p class="mt-2 text-2xl font-bold text-yellow-900">{{ number_format($report->vs_total, 2, ',', '.') }} €</p>
                        </div>
                        @endif

                        @if($report->total_cost)
                        <div class="bg-red-50 p-4 rounded-lg border-2 border-red-200">
                            <label class="block text-xs font-medium text-red-700 uppercase tracking-wider">Coste Total</label>
                            <p class="mt-2 text-2xl font-bold text-red-900">{{ number_format($report->total_cost, 2, ',', '.') }} €</p>
                        </div>
                        @endif
                    </div>

                    @if($report->vr_total && $report->ve_total && $report->vs_total)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500">
                            <strong>Nota:</strong> El coste total es la suma de VR + VE + VS
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Documento PDF --}}
            @if($report->pdf_report)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Documento Adjunto
                        </h3>
                        
                        <a href="{{ Storage::url($report->pdf_report) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Descargar PDF
                        </a>
                    </div>
                </div>
            @endif

            {{-- Detalles del Caso --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Detalles del Caso
                        </h3>

                        <div class="flex gap-2">
                            @if($report->hasDetails())
                                {{-- Botón Ver Detalles --}}
                                <a href="{{ route('report-details.index', $report) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Ver Detalles
                                </a>
                                
                                {{-- Botón Añadir Más (visible para admin, creador o asignado) --}}
                                @if(Auth::user()->role === 'admin' || $report->user_id === Auth::id() || $report->assigned_to === Auth::id())
                                    <a href="{{ route('report-details.create', $report) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Añadir Más Detalles
                                    </a>
                                @endif
                            @else
                                {{-- Botón Añadir Detalles (visible para admin, creador o asignado) --}}
                                @if(Auth::user()->role === 'admin' || $report->user_id === Auth::id() || $report->assigned_to === Auth::id())
                                    <a href="{{ route('report-details.create', $report) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Añadir Detalles
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    @if($report->hasDetails())
                        <p class="text-sm text-gray-600">
                            Este caso tiene <strong>{{ $report->details_groups_count }}</strong> grupo(s) de detalles registrados.
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Haz clic en "Ver Detalles" para ver, editar o eliminar los detalles del caso.
                        </p>
                    @else
                        <p class="text-sm text-gray-500">
                            Aún no se han añadido detalles como especies afectadas, residuos, emisiones, etc.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Sección de Costes --}}
            @if($report->hasDetails())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Cálculo de Costes
                        </h3>

                        <div class="flex gap-2">
                            {{-- Botón Ver Costes (solo si hay costes calculados) --}}
                            @if($report->hasCosts())
                                <a href="{{ route('report-costs.index', $report) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Ver Costes
                                </a>
                            @endif

                            {{-- Botón Calcular Costes (visible para admin, creador o asignado) --}}
                            @if(Auth::user()->role === 'admin' || $report->user_id === Auth::id() || $report->assigned_to === Auth::id())
                                <form action="{{ route('report-costs.calculate', $report) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 transition ease-in-out duration-150" onclick="return confirm('{{ $report->hasCosts() ? '¿Recalcular costes? Esto reemplazará los costes actuales.' : '¿Calcular costes para este caso?' }}')">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $report->hasCosts() ? 'Recalcular Costes' : 'Calcular Costes' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if($report->hasCosts())
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-green-800">
                                    <strong>Costes calculados.</strong> 
                                    Coste total: <strong>{{ number_format($report->total_cost, 2, ',', '.') }} €</strong>
                                </span>
                            </div>
                            <p class="text-xs text-green-600 mt-2">
                                VR: {{ number_format($report->vr_total ?? 0, 2, ',', '.') }} € | 
                                VE: {{ number_format($report->ve_total ?? 0, 2, ',', '.') }} € | 
                                VS: {{ number_format($report->vs_total ?? 0, 2, ',', '.') }} €
                            </p>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-yellow-800">
                                    <strong>Costes pendientes de calcular.</strong> 
                                    Pulsa "Calcular Costes" para generar la valoración económica.
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Modal de Asignación --}}
    <div id="assignModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('assignModal').classList.add('hidden')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('reports.assign', $report) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Asignar caso
                                </h3>
                                <div class="mt-4">
                                    <label for="assigned_to" class="block text-sm font-medium text-gray-700">Seleccionar agente</label>
                                    <select name="assigned_to" id="assigned_to" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}" {{ $report->assigned_to == $agent->id ? 'selected' : '' }}>
                                                {{ $agent->name }} ({{ $agent->agent_num }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Asignar
                        </button>
                        <button type="button" onclick="document.getElementById('assignModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>