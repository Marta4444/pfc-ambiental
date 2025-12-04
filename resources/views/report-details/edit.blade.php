<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Detalles - {{ $report->ip }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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
                    {{-- Info del caso --}}
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Caso</p>
                                <p class="text-gray-900">{{ $report->title }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Subcategoría</p>
                                <p class="text-gray-900">{{ $subcategory->name }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('report-details.update', [$report, $groupKey]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Editando: {{ ucfirst(str_replace('_', ' ', $groupKey)) }}
                        </h3>

                        {{-- Aviso de campos de protección restringidos --}}
                        @if(!auth()->user()->role !== 'admin')
                            @php
                                $protectionFields = ['boe_status', 'ccaa_status', 'iucn_category'];
                                $hasProtectionFields = $fields->whereIn('key_name', $protectionFields)->isNotEmpty();
                            @endphp
                            @if($hasProtectionFields)
                                <div class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <div>
                                            <h4 class="text-sm font-semibold text-amber-800">Campos de protección restringidos</h4>
                                            <p class="text-sm text-amber-700 mt-1">
                                                Los campos de nivel de protección (LESPRE/BOE, Catálogo CCAA e IUCN) solo pueden ser editados por un administrador.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="space-y-4">
                            @php
                                // Campos de protección que solo admin puede editar
                                $protectionFields = ['boe_status', 'ccaa_status', 'iucn_category'];
                                $isAdmin = auth()->user()->role === 'admin';
                            @endphp

                            @foreach($fields as $field)
                                @php
                                    $currentValue = $existingValues[$field->key_name] ?? $field->pivot->default_value ?? '';
                                    $isProtectionField = in_array($field->key_name, $protectionFields);
                                    $isReadOnly = $isProtectionField && !$isAdmin;
                                @endphp
                                
                                <div class="{{ $isReadOnly ? 'opacity-75' : '' }}">
                                    <label for="field_{{ $field->key_name }}" class="block text-sm font-medium text-gray-700">
                                        {{ $field->label }}
                                        @if($field->pivot->is_required && !$isReadOnly)
                                            <span class="text-red-500">*</span>
                                        @endif
                                        @if($field->units)
                                            <span class="text-gray-400 text-xs">({{ $field->units }})</span>
                                        @endif
                                        @if($isReadOnly)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                                Solo admin
                                            </span>
                                        @endif
                                    </label>

                                    @if($field->help_text)
                                        <p class="text-xs text-gray-500 mb-1">{{ $field->help_text }}</p>
                                    @endif

                                    @if($isReadOnly)
                                        {{-- Campo de solo lectura para no-admins --}}
                                        <div class="mt-1 relative">
                                            @switch($field->type)
                                                @case('select')
                                                    <div class="block w-full rounded-md border-gray-200 bg-gray-100 px-3 py-2 text-gray-600 cursor-not-allowed">
                                                        {{ $currentValue ?: 'No especificado' }}
                                                    </div>
                                                    {{-- Campo oculto para mantener el valor --}}
                                                    <input type="hidden" name="fields[{{ $field->key_name }}]" value="{{ $currentValue }}">
                                                    @break
                                                @default
                                                    <input 
                                                        type="text" 
                                                        value="{{ $currentValue ?: 'No especificado' }}"
                                                        class="mt-1 block w-full rounded-md border-gray-200 bg-gray-100 text-gray-600 cursor-not-allowed"
                                                        disabled
                                                    >
                                                    {{-- Campo oculto para mantener el valor --}}
                                                    <input type="hidden" name="fields[{{ $field->key_name }}]" value="{{ $currentValue }}">
                                            @endswitch
                                        </div>
                                    @else
                                        {{-- Campo editable --}}
                                        @switch($field->type)
                                            @case('textarea')
                                                <textarea 
                                                    name="fields[{{ $field->key_name }}]" 
                                                    id="field_{{ $field->key_name }}"
                                                    rows="3"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="{{ $field->placeholder }}"
                                                    {{ $field->pivot->is_required ? 'required' : '' }}
                                                >{{ old("fields.{$field->key_name}", $currentValue) }}</textarea>
                                                @break

                                            @case('select')
                                                <select 
                                                    name="fields[{{ $field->key_name }}]" 
                                                    id="field_{{ $field->key_name }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    {{ $field->pivot->is_required ? 'required' : '' }}
                                                >
                                                    <option value="">{{ $field->placeholder ?: 'Seleccione...' }}</option>
                                                    @foreach($field->options ?? [] as $option)
                                                        <option value="{{ $option }}" {{ old("fields.{$field->key_name}", $currentValue) == $option ? 'selected' : '' }}>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @break

                                            @case('number')
                                            @case('decimal')
                                                <input 
                                                    type="number" 
                                                    name="fields[{{ $field->key_name }}]" 
                                                    id="field_{{ $field->key_name }}"
                                                    value="{{ old("fields.{$field->key_name}", $currentValue) }}"
                                                    step="{{ $field->type === 'decimal' ? '0.01' : '1' }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="{{ $field->placeholder }}"
                                                    {{ $field->pivot->is_required ? 'required' : '' }}
                                                >
                                                @break

                                            @case('date')
                                                <input 
                                                    type="date" 
                                                    name="fields[{{ $field->key_name }}]" 
                                                    id="field_{{ $field->key_name }}"
                                                    value="{{ old("fields.{$field->key_name}", $currentValue) }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    {{ $field->pivot->is_required ? 'required' : '' }}
                                                >
                                                @break

                                            @case('boolean')
                                                <div class="mt-1">
                                                    <label class="inline-flex items-center">
                                                        <input 
                                                            type="checkbox" 
                                                            name="fields[{{ $field->key_name }}]" 
                                                            value="1"
                                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            {{ old("fields.{$field->key_name}", $currentValue) ? 'checked' : '' }}
                                                        >
                                                        <span class="ml-2 text-sm text-gray-600">Sí</span>
                                                    </label>
                                                </div>
                                                @break

                                            @default
                                                <input 
                                                    type="text" 
                                                    name="fields[{{ $field->key_name }}]" 
                                                    id="field_{{ $field->key_name }}"
                                                    value="{{ old("fields.{$field->key_name}", $currentValue) }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="{{ $field->placeholder }}"
                                                    {{ $field->pivot->is_required ? 'required' : '' }}
                                                    @if($field->key_name === 'especie')
                                                        list="species-list"
                                                        autocomplete="off"
                                                    @endif
                                                >
                                        @endswitch
                                    @endif

                                    @error("fields.{$field->key_name}")
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        {{-- Botones --}}
                        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-between">
                            <a href="{{ route('report-details.index', $report) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Datalist para autocompletado de especies --}}
    <datalist id="species-list"></datalist>

    @push('scripts')
    <script>
        // Autocompletado de especies
        const speciesInput = document.getElementById('field_especie');
        if (speciesInput) {
            speciesInput.addEventListener('input', async function() {
                const term = this.value;
                if (term.length < 2) return;

                try {
                    const response = await fetch(`{{ route('species.search') }}?q=${encodeURIComponent(term)}`);
                    const data = await response.json();
                    
                    const datalist = document.getElementById('species-list');
                    datalist.innerHTML = '';
                    
                    data.data.forEach(species => {
                        const option = document.createElement('option');
                        option.value = species.scientific_name;
                        option.textContent = species.common_name 
                            ? `${species.scientific_name} (${species.common_name})` 
                            : species.scientific_name;
                        datalist.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error buscando especies:', error);
                }
            });
        }
    </script>
    @endpush
</x-app-layout>