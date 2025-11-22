<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('Categorías') }}</h2>
    </x-slot>

    @php
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
    @endphp

    <div class="p-6 bg-white rounded shadow">
        <div class="flex justify-between items-center mb-4">
            <div class="text-lg font-medium">Listado de categorías</div>
            @if($isAdmin)
                <a href="{{ route('categories.create') }}" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">Crear categoría</a>
            @endif
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('categories.index') }}" class="mb-4">
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
                    <a href="{{ route('categories.index') }}" class="text-sm text-blue-600 hover:underline">Limpiar filtros</a>
                @endif
            </div>
        </form>

        @if($categories->isEmpty())
            <div class="text-sm text-gray-600">No hay categorías registradas.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2 border">Nombre</th>
                            <th class="px-4 py-2 border">Descripción</th>
                            <th class="px-4 py-2 border">Estado</th>
                            <th class="px-4 py-2 border">Creada</th>
                            <th class="px-4 py-2 border">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border">{{ $category->name }}</td>
                                <td class="px-4 py-2 border">{{ $category->description }}</td>
                                <td class="px-4 py-2 border">
                                    <span class="px-2 py-1 rounded text-xs {{ $category->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $category->active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 border">{{ optional($category->created_at)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 border">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('categories.show', $category) }}" class="text-blue-600 hover:underline text-sm">Ver</a>

                                        @if($isAdmin)
                                            <a href="{{ route('categories.edit', $category) }}" class="text-yellow-600 hover:underline text-sm">Editar</a>

                                            <form action="{{ route('categories.toggleActive', $category) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="text-sm {{ $category->active ? 'text-orange-600' : 'text-green-600' }} hover:underline">
                                                    {{ $category->active ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que quieres eliminar esta categoría permanentemente?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline text-sm">Eliminar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>