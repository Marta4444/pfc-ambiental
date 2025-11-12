
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('Subcategoría') }}</h2>
    </x-slot>

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
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="{{ route('subcategories.edit', $subcategory) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded text-sm">Editar</a>
                        <a href="{{ route('subcategories.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded text-sm">Volver</a>

                        <form action="{{ route('subcategories.destroy', $subcategory) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar esta subcategoría?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded text-sm">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-600">Descripción</h4>
                        <p class="mt-1 text-gray-800">{{ $subcategory->description ?? '—' }}</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-600">Coeficiente</h4>
                        <p class="mt-1 text-gray-800">{{ number_format($subcategory->coeficient, 2) }}</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-600">Activa</h4>
                        <p class="mt-1 text-gray-800">{{ $subcategory->active ? 'Sí' : 'No' }}</p>
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

            @if($subcategory->notes ?? false)
                <div class="px-6 py-4 border-t bg-gray-50">
                    <h4 class="text-sm font-medium text-gray-600">Notas</h4>
                    <p class="mt-2 text-gray-700">{{ $subcategory->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>