<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('Subcategorías') }}</h2>
    </x-slot>

    @php
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
    @endphp

    <div class="p-6" x-data="{ showModal: false, deleteUrl: '' }">
        <div class="flex justify-between items-center mb-4">
            <div class="text-lg font-medium">Listado de subcategorías</div>
            @if($isAdmin)
                <a href="{{ route('subcategories.create') }}" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">Crear subcategoría</a>
            @endif
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('subcategories.index') }}" class="mb-4">
            <div class="flex items-center space-x-3">
                <div>
                    <label for="active" class="text-sm text-gray-700">Filtrar por estado:</label>
                    <select id="active" name="active" class="ml-2 border-gray-300 rounded text-sm" onchange="this.form.submit()">
                        <option value="">Todas</option>
                        <option value="1" @selected(request('active') === '1')>Activas</option>
                        <option value="0" @selected(request('active') === '0')>Inactivas</option>
                    </select>
                </div>

                @if(request('active') !== null)
                    <a href="{{ route('subcategories.index') }}" class="text-sm text-eco-600 hover:underline">Limpiar filtros</a>
                @endif
            </div>
        </form>

        @if($subcategories->isEmpty())
            <div class="text-sm text-gray-600">No hay subcategorías registradas.</div>
        @else
            <div class="space-y-6">
                @foreach($groups as $categoryName => $items)
                    <div class="bg-white shadow rounded-lg border">
                        <div class="px-5 py-3 border-b flex items-center justify-between">
                            <div class="flex items-center">
                                <h3 class="text-lg font-semibold text-gray-800 mr-4">{{ $categoryName }}</h3>

                                @if($items->first()->category)
                                    <a href="{{ route('categories.show', $items->first()->category) }}"
                                       class="ml-2 inline-flex items-center px-3 py-1 bg-blue-50 text-eco-700 border border-blue-200 rounded text-sm hover:bg-blue-100 transition">
                                        Ver categoría
                                    </a>
                                @endif
                            </div>

                            <div class="text-sm text-gray-500">{{ $items->count() }} subcategoría(s)</div>
                        </div>

                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($items as $subcategory)
                                <div class="border rounded-lg p-4 bg-gray-50 hover:shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="text-md font-semibold text-gray-800">{{ $subcategory->name }}</h4>
                                            <span class="px-2 py-1 rounded text-xs {{ $subcategory->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $subcategory->active ? 'Activa' : 'Inactiva' }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-2">
                                            {{ $subcategory->description ?? '—' }}
                                        </p>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                        <a href="{{ route('subcategories.show', $subcategory) }}"
                                           class="inline-flex items-center px-3 py-1 bg-white text-eco-700 border border-blue-200 rounded text-sm hover:bg-eco-50 transition">
                                            Ver
                                        </a>

                                        @if($isAdmin)
                                            <a href="{{ route('subcategories.edit', $subcategory) }}"
                                               class="inline-flex items-center px-3 py-1 bg-white text-yellow-800 border border-yellow-200 rounded text-sm hover:bg-yellow-50 transition">
                                                Editar
                                            </a>

                                            <form action="{{ route('subcategories.toggleActive', $subcategory) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1 bg-white border rounded text-sm transition {{ $subcategory->active ? 'text-orange-600 border-orange-200 hover:bg-orange-50' : 'text-green-600 border-green-200 hover:bg-green-50' }}">
                                                    {{ $subcategory->active ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>

                                            <button type="button"
                                    @click="deleteUrl = '{{ route('subcategories.destroy', $subcategory) }}'; showModal = true"
                                    class="inline-flex items-center px-3 py-1 bg-white text-red-700 border border-red-200 rounded text-sm hover:bg-red-50 transition">
                                Eliminar
                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Modal de confirmación eliminar --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar eliminación</h3>
                </div>
                <p class="text-gray-600 mb-6">¿Estás seguro de que quieres eliminar esta subcategoría? Esta acción no se puede deshacer.</p>
                <div class="flex justify-end space-x-3">
                    <button @click="showModal = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancelar</button>
                    <form :action="deleteUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>