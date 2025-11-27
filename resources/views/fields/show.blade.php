<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalles del Campo') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('fields.edit', $field) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    Editar
                </a>
                <a href="{{ route('fields.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Información del Campo --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Información del Campo</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Etiqueta</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $field->label }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre Clave</label>
                            <p class="mt-1"><code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $field->key_name }}</code></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $field->type }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unidades</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $field->units ?? '—' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Numérico</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $field->is_numeric ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $field->is_numeric ? 'Sí' : 'No' }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $field->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $field->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Placeholder</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $field->placeholder ?? '—' }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Texto de Ayuda</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $field->help_text ?? '—' }}</p>
                        </div>

                        @if($field->options_json)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Opciones (JSON)</label>
                                <div class="bg-gray-50 border border-gray-200 rounded p-3">
                                    <pre class="text-xs text-gray-800 whitespace-pre-wrap">{{ json_encode($field->options_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Subcategorías Asignadas --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Subcategorías Asignadas ({{ $field->subcategories->count() }})</h3>
                    
                    @if($field->subcategories->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subcategoría</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Obligatorio</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor por Defecto</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($field->subcategories as $subcategory)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <a href="{{ route('subcategories.show', $subcategory) }}" class="text-sm font-medium text-blue-600 hover:underline">
                                                    {{ $subcategory->name }}
                                                </a>
                                                <p class="text-xs text-gray-500">{{ $subcategory->category->name }}</p>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subcategory->pivot->is_required ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $subcategory->pivot->is_required ? 'Obligatorio' : 'Opcional' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $subcategory->pivot->order_index }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $subcategory->pivot->default_value ?? '—' }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm font-medium">
                                                <button onclick="openEditModal({{ $subcategory->id }}, {{ $subcategory->pivot->is_required ? 'true' : 'false' }}, {{ $subcategory->pivot->order_index }}, '{{ $subcategory->pivot->default_value }}')" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                    Editar
                                                </button>
                                                <form action="{{ route('fields.unassignFromSubcategory', [$field, $subcategory]) }}" method="POST" class="inline" onsubmit="return confirm('¿Desasignar de esta subcategoría?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Desasignar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Este campo no está asignado a ninguna subcategoría.</p>
                    @endif
                </div>
            </div>

            {{-- Asignar a Nueva Subcategoría --}}
            @if($availableSubcategories->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Asignar a Subcategoría</h3>
                        
                        <form action="{{ route('fields.assignToSubcategory', $field) }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label for="subcategory_id" class="block text-sm font-medium text-gray-700">Subcategoría *</label>
                                    <select name="subcategory_id" id="subcategory_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seleccionar subcategoría...</option>
                                        @foreach($availableSubcategories as $subcategory)
                                            <option value="{{ $subcategory->id }}">
                                                {{ $subcategory->name }} ({{ $subcategory->category->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subcategory_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="is_required" class="flex items-center">
                                        <input type="checkbox" name="is_required" id="is_required" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Campo obligatorio</span>
                                    </label>
                                </div>

                                <div>
                                    <label for="order_index" class="block text-sm font-medium text-gray-700">Orden</label>
                                    <input type="number" name="order_index" id="order_index" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Auto">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="default_value" class="block text-sm font-medium text-gray-700">Valor por Defecto</label>
                                    <input type="text" name="default_value" id="default_value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Opcional">
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Asignar a Subcategoría
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal para editar configuración --}}
    <div id="editModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="closeEditModal()"></div>
            
            <div class="relative bg-white rounded-lg max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold mb-4">Editar Configuración</h3>
                
                <form id="editForm" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_required" id="edit_is_required" value="1" class="rounded border-gray-300">
                            <span class="ml-2 text-sm">Campo obligatorio</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Orden</label>
                        <input type="number" name="order_index" id="edit_order_index" min="0" required class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Valor por Defecto</label>
                        <input type="text" name="default_value" id="edit_default_value" class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(subcategoryId, isRequired, orderIndex, defaultValue) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editForm');
            
            form.action = `/fields/{{ $field->id }}/subcategories/${subcategoryId}`;
            document.getElementById('edit_is_required').checked = isRequired;
            document.getElementById('edit_order_index').value = orderIndex;
            document.getElementById('edit_default_value').value = defaultValue || '';
            
            modal.classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</x-app-layout>