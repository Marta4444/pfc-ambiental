<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('Subcategorías') }}</h2>
    </x-slot>

    @php
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
    @endphp

    <div class="p-6">
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
                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Activas</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactivas</option>
                    </select>
                </div>

                @if(request('active') !== null)
                    <a href="{{ route('subcategories.index') }}" class="text-sm text-blue-600 hover:underline">Limpiar filtros</a>
                @endif
            </div>
        </form>

        @if($subcategories->isEmpty())
            <div class="text-sm text-gray-600">No hay subcategorías registradas.</div>
        @else
            @php
                $groups = $subcategories->groupBy(function($s) {
                    return $s->category->name ?? 'Sin categoría';
                });
            @endphp

            <div class="space-y-6">
                @foreach($groups as $categoryName => $items)
                    <div class="bg-white shadow rounded-lg border">
                        <div class="px-5 py-3 border-b flex items-center justify-between">
                            <div class="flex items-center">
                                <h3 class="text-lg font-semibold text-gray-800 mr-4">{{ $categoryName }}</h3>

                                @if($items->first()->category)
                                    <a href="{{ route('categories.show', $items->first()->category) }}"
                                       class="ml-2 inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded text-sm hover:bg-blue-100 transition">
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
                                           class="inline-flex items-center px-3 py-1 bg-white text-blue-700 border border-blue-200 rounded text-sm hover:bg-blue-50 transition">
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

                                            <form action="{{ route('subcategories.destroy', $subcategory) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar esta subcategoría?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1 bg-white text-red-700 border border-red-200 rounded text-sm hover:bg-red-50 transition">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>