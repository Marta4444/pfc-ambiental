<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Campos Dinámicos') }}
            </h2>
            <a href="{{ route('fields.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Crear Campo
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($fields->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay campos creados</h3>
                            <p class="mt-1 text-sm text-gray-500">Comienza creando un nuevo campo dinámico.</p>
                            <div class="mt-6">
                                <a href="{{ route('fields.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Crear Primer Campo
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidades</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subcategorías</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($fields as $field)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <div class="flex items-start">
                                                    <div>
                                                        <a href="{{ route('fields.show', $field) }}" class="text-sm font-medium text-blue-600 hover:text-blue-900 hover:underline">
                                                            {{ $field->label }}
                                                        </a>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            <code class="bg-gray-100 px-1 rounded">{{ $field->key_name }}</code>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {{ $field->type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $field->units ?? '—' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($field->subcategories->isNotEmpty())
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($field->subcategories->take(3) as $subcategory)
                                                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                                                {{ $subcategory->name }}
                                                            </span>
                                                        @endforeach
                                                        @if($field->subcategories->count() > 3)
                                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                                                +{{ $field->subcategories->count() - 3 }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-xs text-gray-400">Sin asignar</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $field->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $field->active ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                <a href="{{ route('fields.show', $field) }}" class="text-blue-600 hover:text-blue-900">Ver</a>
                                                <a href="{{ route('fields.edit', $field) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                                
                                                <form action="{{ route('fields.toggleActive', $field) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-{{ $field->active ? 'orange' : 'green' }}-600 hover:text-{{ $field->active ? 'orange' : 'green' }}-900">
                                                        {{ $field->active ? 'Desactivar' : 'Activar' }}
                                                    </button>
                                                </form>

                                                @if($field->subcategories->isEmpty())
                                                    <form action="{{ route('fields.destroy', $field) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este campo?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $fields->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>