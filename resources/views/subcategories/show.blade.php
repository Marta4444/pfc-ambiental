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

                @if($isAdmin)
                {{-- Sección de Gestión de Campos --}}
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800">Campos Dinámicos</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                Esta subcategoría tiene {{ $subcategory->fields_count }} campo(s) asignado(s)
                            </p>
                        </div>
                        <a href="{{ route('fields.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md text-sm font-medium hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Gestionar Campos
                        </a>
                    </div>

                    {{-- Lista rápida de campos asignados --}}
                    @if($subcategory->hasFields())
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="text-sm font-medium text-gray-700 mb-3">Campos actuales:</h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach($subcategory->orderedFields as $field)
                            <div class="flex items-center text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $field->pivot->is_required ? 'bg-red-100 text-red-800' : 'bg-gray-200 text-gray-700' }}">
                                    @if($field->pivot->is_required)
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    @endif
                                    {{ $field->label }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-sm text-yellow-800 font-medium">No hay campos asignados</p>
                                <p class="text-xs text-yellow-700 mt-1">Esta subcategoría no tiene campos dinámicos configurados. Haz clic en "Gestionar Campos" para asignar campos.</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                @if($subcategory->updated_at)
                <div class="mt-4 text-sm text-gray-500">
                    Última modificación: {{ $subcategory->updated_at->format('d/m/Y H:i') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>