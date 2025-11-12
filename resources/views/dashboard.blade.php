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
                        Ver informes
                    </a>

                    <a href="{{ route('reports.create') }}" class="bg-red-600 text-black px-4 py-2 rounded text-center">
                        Crear nuevo informe
                    </a>
                </div>

                <!-- Botones de Categories y Subcategories, solo para Admin -->
                @if(auth()->user()->role === 'admin')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ route('categories.index') }}" class="bg-red-600 text-black px-4 py-2 rounded text-center">
                            Gestionar Categorías
                        </a>

                        <a href="{{ route('subcategories.index') }}" class="bg-red-600 text-black px-4 py-2 rounded text-center">
                            Gestionar Subcategorías
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>

