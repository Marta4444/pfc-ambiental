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

                <!-- Botones de Reports, visibles para todos los usuarios autenticados -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <a href="{{ route('reports.index') }}" class="bg-blue-600 text-black px-4 py-2 rounded text-center">
                        Ver Casos
                    </a>

                    <a href="{{ route('reports.create') }}" class="bg-red-600 text-black px-4 py-2 rounded text-center">
                        Crear nuevo caso
                    </a>
                </div>

                <!-- Botones de Categories y Subcategories, y Petiocionarios solo para Admin -->
                @if(Auth::user()->role === 'admin')
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Gestión de Categorías --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Categorías</h3>
                            <p class="text-sm text-gray-600 mb-4">Gestiona las categorías de casos</p>
                            <a href="{{ route('categories.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Ver Categorías
                            </a>
                        </div>
                    </div>

                    {{-- Gestión de Subcategorías --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Subcategorías</h3>
                            <p class="text-sm text-gray-600 mb-4">Gestiona las subcategorías de casos</p>
                            <a href="{{ route('subcategories.index') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Ver Subcategorías
                            </a>
                        </div>
                    </div>

                    {{-- Gestión de Peticionarios --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Peticionarios</h3>
                            <p class="text-sm text-gray-600 mb-4">Gestiona las unidades peticionarias</p>
                            <a href="{{ route('petitioners.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Ver Peticionarios
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            </div>
        </div>
    </div>
</x-app-layout>

