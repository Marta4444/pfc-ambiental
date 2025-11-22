<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('Subcategoría') }}</h2>
    </x-slot>

    @php
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
    @endphp

    <div class="max-w-3xl mx-auto py-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-800">{{ $subcategory->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $subcategory->category ? __('Categoría:') . ' ' : '' }}
                            @if($subcategory->category)
                                <a href="{{ route('categories.show', $subcategory->category) }}" class="text-blue-600 hover:underline">
                                    {{ $subcategory->category->name }}
                                </a>
                            @endif
                        </p>
                        <span class="mt-2 inline-block px-2 py-1 rounded text-xs {{ $subcategory->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $subcategory->active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        @if($isAdmin)
                            <a href="{{ route('subcategories.edit', $subcategory) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded text-sm hover:bg-yellow-200">Editar</a>

                            <form action="{{ route('subcategories.toggleActive', $subcategory) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded text-sm {{ $subcategory->active ? 'bg-orange-100 text-orange-800 hover:bg-orange-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">
                                    {{ $subcategory->active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>

                            <form action="{{ route('subcategories.destroy', $subcategory) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar esta subcategoría?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded text-sm hover:bg-red-200">Eliminar</button>
                            </form>
                        @endif

                        <a href="{{ route('subcategories.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded text-sm hover:bg-gray-200">Volver</a>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <h4 class="text-sm font-medium text-gray-600">Descripción</h4>
                        <p class="mt-1 text-gray-800">{{ $subcategory->description ?? '—' }}</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-600">Categoría</h4>
                        <p class="mt-1 text-gray-800">
                            @if($subcategory->category)
                                <a href="{{ route('categories.show', $subcategory->category) }}" class="text-blue-600 hover:underline">
                                    {{ $subcategory->category->name }}
                                </a>
                            @else
                                —
                            @endif
                        </p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-600">Estado</h4>
                        <p class="mt-1 text-gray-800">{{ $subcategory->active ? 'Activa' : 'Inactiva' }}</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-600">Creada</h4>
                        <p class="mt-1 text-gray-800">{{ optional($subcategory->created_at)->format('d/m/Y H:i') ?? '—' }}</p>
                    </div>
                </div>

                @if($subcategory->updated_at)
                    <div class="mt-4 text-sm text-gray-500">
                        Última modificación: {{ $subcategory->updated_at->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>