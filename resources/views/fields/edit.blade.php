<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Campo') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('fields.show', $field) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Ver Detalles
                </a>
                <a href="{{ route('fields.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('fields.update', $field) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- Información Básica --}}
                        <div class="border-b border-gray-200 pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="key_name" class="block text-sm font-medium text-gray-700">Nombre Clave *</label>
                                    <input type="text" name="key_name" id="key_name" value="{{ old('key_name', $field->key_name) }}" required 
                                        pattern="[a-z0-9_]+" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <p class="mt-1 text-xs text-gray-500">Solo letras minúsculas, números y guiones bajos</p>
                                    @error('key_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="label" class="block text-sm font-medium text-gray-700">Etiqueta *</label>
                                    <input type="text" name="label" id="label" value="{{ old('label', $field->label) }}" required 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('label') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">Tipo *</label>
                                    <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($types as $type)
                                            <option value="{{ $type }}" {{ old('type', $field->type) == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                        @endforeach
                                    </select>
                                    @error('type') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="units" class="block text-sm font-medium text-gray-700">Unidades</label>
                                    <input type="text" name="units" id="units" value="{{ old('units', $field->units) }}" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('units') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Configuración Avanzada --}}
                        <div class="border-b border-gray-200 pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración Avanzada</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="placeholder" class="block text-sm font-medium text-gray-700">Placeholder</label>
                                    <input type="text" name="placeholder" id="placeholder" value="{{ old('placeholder', $field->placeholder) }}" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('placeholder') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="help_text" class="block text-sm font-medium text-gray-700">Texto de Ayuda</label>
                                    <textarea name="help_text" id="help_text" rows="2" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('help_text', $field->help_text) }}</textarea>
                                    @error('help_text') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div id="options_container" class="{{ in_array($field->type, ['select', 'multiselect', 'radio', 'checkbox']) ? '' : 'hidden' }}">
                                    <label for="options_json" class="block text-sm font-medium text-gray-700">Opciones (JSON)</label>
                                    <textarea name="options_json" id="options_json" rows="4" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">{{ old('options_json', $field->options_json ? json_encode($field->options_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500">Formato JSON: {"clave": "valor"}</p>
                                    @error('options_json') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="flex items-center space-x-6">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_numeric" id="is_numeric" value="1" {{ old('is_numeric', $field->is_numeric) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Campo numérico (para cálculos)</span>
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" name="active" id="active" value="1" {{ old('active', $field->active) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Activo</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('fields.show', $field) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Actualizar Campo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const typeSelect = document.getElementById('type');
        const optionsContainer = document.getElementById('options_container');
        const optionsRequiringTypes = ['select', 'multiselect', 'radio', 'checkbox'];

        typeSelect.addEventListener('change', function() {
            if (optionsRequiringTypes.includes(this.value)) {
                optionsContainer.classList.remove('hidden');
            } else {
                optionsContainer.classList.add('hidden');
            }
        });
    </script>
</x-app-layout>