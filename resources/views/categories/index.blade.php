<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('Categorías') }}</h2>
    </x-slot>

    <div class="p-6 bg-white rounded shadow">
        <div class="flex justify-between items-center mb-4">
            <div class="text-lg font-medium">Listado de categorías</div>
            <a href="{{ route('categories.create') }}" class="bg-green-600 text-white px-3 py-1 rounded text-sm">Crear categoría</a>
        </div>

        @if($categories->isEmpty())
            <div class="text-sm text-gray-600">No hay categorías registradas.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border">Nombre</th>
                            <th class="px-4 py-2 border">Descripción</th>
                            <th class="px-4 py-2 border">Coeficiente base</th>
                            <th class="px-4 py-2 border">Activa</th>
                            <th class="px-4 py-2 border">Creada</th>
                            <th class="px-4 py-2 border">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border">{{ $category->name }}</td>
                                <td class="px-4 py-2 border">{{ $category->description }}</td>
                                <td class="px-4 py-2 border">{{ number_format($category->base_coeficient, 2) }}</td>
                                <td class="px-4 py-2 border">{{ $category->active ? 'Sí' : 'No' }}</td>
                                <td class="px-4 py-2 border">{{ optional($category->created_at)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 border">
                                    <a href="{{ route('categories.show', $category) }}" class="text-blue-600 mr-2 text-sm">Ver</a>
                                    <a href="{{ route('categories.edit', $category) }}" class="text-yellow-600 mr-2 text-sm">Editar</a>

                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que quieres eliminar esta categoría?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 text-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if(method_exists($categories, 'links'))
                    <div class="mt-4">
                        {{ $categories->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
