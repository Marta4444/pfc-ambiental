<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">

                <!-- Mensaje de bienvenida -->
                <div class="mb-6">
                    {{ __("Bienvenido!") }}
                </div>

                @if(Auth::user()->role === 'admin')
                    {{-- Sección Casos para Admin --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Gestión de Casos</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Ver Casos --}}
                            <div class="bg-white border border-gray-200 overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center mb-3">
                                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
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

                @else
                    {{-- Botones simples para usuarios no admin --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ route('reports.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-center hover:bg-blue-700">
                            Ver Casos
                        </a>

                        <a href="{{ route('reports.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-center hover:bg-blue-700">
                            Crear nuevo caso
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>