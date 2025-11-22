<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Peticionario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('petitioners.update', $petitioner) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nombre del Peticionario')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $petitioner->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="order" :value="__('Orden de visualización')" />
                            <x-text-input id="order" class="block mt-1 w-full" type="number" name="order" :value="old('order', $petitioner->order)" min="0" />
                            <p class="text-xs text-gray-500 mt-1">Número que determina el orden en los desplegables (menor = primero)</p>
                            <x-input-error :messages="$errors->get('order')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="active" value="1" {{ old('active', $petitioner->active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">{{ __('Activo') }}</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Solo los peticionarios activos aparecerán en los formularios</p>
                        </div>

                        <div class="mb-6 p-4 bg-gray-50 rounded">
                            <p class="text-sm text-gray-700">
                                <strong>Casos asociados:</strong> {{ $petitioner->reports()->count() }}
                            </p>
                            @if($petitioner->reports()->count() > 0)
                                <p class="text-xs text-gray-500 mt-1">
                                    No se puede eliminar este peticionario mientras tenga casos asociados.
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('petitioners.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Cancelar') }}
                            </a>

                            <x-primary-button>
                                {{ __('Actualizar Peticionario') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>