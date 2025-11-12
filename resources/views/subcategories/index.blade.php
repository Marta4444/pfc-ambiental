
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('Subcategorías') }}</h2>
    </x-slot>

    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <div class="text-lg font-medium">Listado de subcategorías</div>
            <a href="{{ route('subcategories.create') }}" class="bg-green-600 text-white px-3 py-1 rounded text-sm">Crear subcategoría</a>
        </div>

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
                                        <h4 class="text-md font-semibold text-gray-800">{{ $subcategory->name }}</h4>
                                        <p class="text-sm text-gray-600 mt-2">
                                            {{ $subcategory->description ?? '—' }}
                                        </p>
                                    </div>

                                    <div class="mt-4 flex items-center space-x-2">
                                        <a href="{{ route('subcategories.show', $subcategory) }}"
                                           class="inline-flex items-center px-3 py-1 bg-white text-blue-700 border border-blue-200 rounded text-sm hover:bg-blue-50 transition">
                                            Ver
                                        </a>

                                        <a href="{{ route('subcategories.edit', $subcategory) }}"
                                           class="inline-flex items-center px-3 py-1 bg-white text-yellow-800 border border-yellow-200 rounded text-sm hover:bg-yellow-50 transition">
                                            Editar
                                        </a>

                                        <form action="{{ route('subcategories.destroy', $subcategory) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar esta subcategoría?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1 bg-white text-red-700 border border-red-200 rounded text-sm hover:bg-red-50 transition">
                                                Eliminar
                                            </button>
                                        </form>
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