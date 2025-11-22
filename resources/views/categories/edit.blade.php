<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('Editar Categoría') }}</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto py-6 px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('categories.update', $category) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <x-input-label for="name" value="Nombre" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" required value="{{ old('name', $category->name) }}" />
                    @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <x-input-label for="description" value="Descripción" />
                    <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows="4">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <x-input-label for="active" value="Estado" />
                    <select id="active" name="active" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="1" {{ old('active', $category->active) == '1' ? 'selected' : '' }}>Activa</option>
                        <option value="0" {{ old('active', $category->active) == '0' ? 'selected' : '' }}>Inactiva</option>
                    </select>
                    @error('active')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center space-x-3">
                    <x-primary-button>Guardar Cambios</x-primary-button>
                    <a href="{{ route('categories.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-200 rounded text-sm text-gray-700 hover:bg-gray-50">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>