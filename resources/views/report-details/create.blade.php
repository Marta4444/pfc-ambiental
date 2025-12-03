<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Añadir Detalles - {{ $report->ip }}
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

            {{-- Badge de especie protegida (oculto inicialmente) --}}
            <div id="species-protection-badge" class="mb-4 hidden">
                <div class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-semibold text-red-800">
                                ⚠️ ESPECIE PROTEGIDA
                            </h3>
                            <div id="species-protection-details" class="mt-2 text-sm text-red-700">
                                <!-- Se rellena con JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Badge de especie NO protegida --}}
            <div id="species-not-protected-badge" class="mb-4 hidden">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="ml-2 text-sm text-gray-600">Especie sin protección especial registrada</span>
                    </div>
                </div>
            </div>

            {{-- Badge de área protegida (oculto inicialmente) --}}
            <div id="protected-area-badge" class="mb-4 hidden">
                <div class="bg-gradient-to-r from-green-50 to-teal-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-semibold text-green-800">
                                🌿 UBICACIÓN EN ÁREA PROTEGIDA
                            </h3>
                            <div id="protected-area-details" class="mt-2 text-sm text-green-700">
                                <!-- Se rellena con JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

                    <form action="{{ route('report-details.store', $report) }}" method="POST">
                        @csrf
                        <input type="hidden" name="group_key" value="{{ $nextGroupKey }}">

                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Nuevo registro: {{ ucfirst(str_replace('_', ' ', $nextGroupKey)) }}
                        </h3>

                        <div class="space-y-4">
                            @foreach($fields as $field)
                                <div>
                                    <label for="field_{{ $field->key_name }}" class="block text-sm font-medium text-gray-700">
                                        {{ $field->label }}
                                        @if($field->pivot->is_required)
                                            <span class="text-red-500">*</span>
                                        @endif
                                        @if($field->units)
                                            <span class="text-gray-400 text-xs">({{ $field->units }})</span>
                                        @endif
                                    </label>

                                    @if($field->help_text)
                                        <p class="text-xs text-gray-500 mb-1">{{ $field->help_text }}</p>
                                    @endif

                                    @switch($field->type)
                                        @case('textarea')
                                            <textarea 
                                                name="fields[{{ $field->key_name }}]" 
                                                id="field_{{ $field->key_name }}"
                                                rows="3"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="{{ $field->placeholder }}"
                                                {{ $field->pivot->is_required ? 'required' : '' }}
                                            >{{ old("fields.{$field->key_name}", $field->pivot->default_value) }}</textarea>
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
                                                    <option value="{{ $option }}" {{ old("fields.{$field->key_name}", $field->pivot->default_value) == $option ? 'selected' : '' }}>
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
                                                value="{{ old("fields.{$field->key_name}", $field->pivot->default_value) }}"
                                                step="{{ $field->type === 'decimal' ? '0.0000001' : '1' }}"
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
                                                value="{{ old("fields.{$field->key_name}", $field->pivot->default_value) }}"
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
                                                        {{ old("fields.{$field->key_name}", $field->pivot->default_value) ? 'checked' : '' }}
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
                                                value="{{ old("fields.{$field->key_name}", $field->pivot->default_value) }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="{{ $field->placeholder }}"
                                                {{ $field->pivot->is_required ? 'required' : '' }}
                                                @if($field->key_name === 'especie')
                                                    list="species-list"
                                                    autocomplete="off"
                                                @endif
                                            >
                                    @endswitch

                                    @error("fields.{$field->key_name}")
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        {{-- Botones --}}
                        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-between">
                            <a href="{{ route('report-details.index', $report) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-200">
                                Cancelar
                            </a>
                            <div class="flex gap-2">
                                <button type="submit" name="add_another" value="1" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Guardar y Añadir Otro
                                </button>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    Guardar Detalles
                                </button>
                            </div>
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
        // =============================================
        // AUTOCOMPLETADO Y AUTORELLENADO DE ESPECIES
        // =============================================
        const speciesInput = document.getElementById('field_especie');
        const speciesDatalist = document.getElementById('species-list');
        
        // Cache de especies buscadas
        let speciesCache = {};
        let selectedSpecies = null;

        if (speciesInput) {
            // Debounce para no hacer muchas peticiones
            let searchTimeout;
            
            speciesInput.addEventListener('input', async function() {
                const term = this.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (term.length < 2) {
                    speciesDatalist.innerHTML = '';
                    hideSpeciesBadges();
                    return;
                }

                searchTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(`{{ route('species.search') }}?q=${encodeURIComponent(term)}`);
                        const data = await response.json();
                        
                        speciesDatalist.innerHTML = '';
                        speciesCache = {};
                        
                        data.data.forEach(species => {
                            // Guardar en cache por nombre científico y común
                            speciesCache[species.scientific_name.toLowerCase()] = species;
                            if (species.common_name) {
                                speciesCache[species.common_name.toLowerCase()] = species;
                            }
                            
                            const option = document.createElement('option');
                            option.value = species.scientific_name;
                            let label = species.scientific_name;
                            if (species.common_name) {
                                label += ` (${species.common_name})`;
                            }
                            if (species.is_protected) {
                                label += ' ⚠️ PROTEGIDA';
                            }
                            option.textContent = label;
                            speciesDatalist.appendChild(option);
                        });
                    } catch (error) {
                        console.error('Error buscando especies:', error);
                    }
                }, 300);
            });

            // Cuando se selecciona una especie del datalist
            speciesInput.addEventListener('change', function() {
                handleSpeciesSelection(this.value);
            });

            // También verificar al perder el foco
            speciesInput.addEventListener('blur', function() {
                setTimeout(() => handleSpeciesSelection(this.value), 100);
            });
        }

        function handleSpeciesSelection(value) {
            const trimmedValue = value.trim().toLowerCase();
            const species = speciesCache[trimmedValue];
            
            if (species) {
                selectedSpecies = species;
                autofillSpeciesProtection(species);
                showSpeciesBadge(species);
            } else if (value.trim() === '') {
                hideSpeciesBadges();
            }
        }

        // Autorellenar campos de protección
        function autofillSpeciesProtection(species) {
            // Mapeo de campos: posibles key_name del campo -> propiedad de species
            const fieldMappings = {
                // Protección nacional BOE
                'proteccion_nacional': species.boe_status,
                'nivel_proteccion_nacional': species.boe_status,
                'proteccion_boe': species.boe_status,
                'estado_proteccion': species.boe_status,
                
                // Referencia legal
                'referencia_legal': species.boe_law_ref,
                'ley_referencia': species.boe_law_ref,
                
                // Protección autonómica
                'proteccion_autonomica': Array.isArray(species.ccaa_status) ? species.ccaa_status.join(', ') : species.ccaa_status,
                'proteccion_ccaa': Array.isArray(species.ccaa_status) ? species.ccaa_status.join(', ') : species.ccaa_status,
                
                // IUCN
                'categoria_iucn': species.iucn_category,
                'clasificacion_iucn': species.iucn_category,
                'iucn': species.iucn_category,
                
                // CITES
                'cites': species.cites_appendix,
                'apendice_cites': species.cites_appendix,
                
                // Grupo taxonómico
                'grupo_taxonomico': species.taxon_group,
                'taxon': species.taxon_group,
            };

            Object.entries(fieldMappings).forEach(([fieldKey, value]) => {
                if (!value) return;
                
                const input = document.getElementById(`field_${fieldKey}`);
                if (!input) return;

                // Si es un select, buscar la opción correcta
                if (input.tagName === 'SELECT') {
                    const options = Array.from(input.options);
                    const match = options.find(opt => 
                        opt.value.toLowerCase() === String(value).toLowerCase() ||
                        opt.textContent.toLowerCase().includes(String(value).toLowerCase())
                    );
                    if (match) {
                        input.value = match.value;
                    }
                } else {
                    input.value = value;
                }
                
                // Añadir indicador visual de autorellenado
                input.classList.add('bg-yellow-50', 'border-yellow-300');
                setTimeout(() => {
                    input.classList.remove('bg-yellow-50', 'border-yellow-300');
                }, 2000);
            });
        }

        // Mostrar badge de especie protegida/no protegida
        function showSpeciesBadge(species) {
            const protectedBadge = document.getElementById('species-protection-badge');
            const notProtectedBadge = document.getElementById('species-not-protected-badge');
            const detailsDiv = document.getElementById('species-protection-details');

            if (species.is_protected) {
                // Construir detalles de protección
                let details = [];
                
                if (species.boe_status) {
                    details.push(`<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">BOE: ${species.boe_status}</span>`);
                }
                if (species.iucn_category) {
                    const iucnLabel = species.iucn_label || species.iucn_category;
                    details.push(`<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">IUCN: ${iucnLabel}</span>`);
                }
                if (species.cites_appendix) {
                    details.push(`<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">CITES: ${species.cites_appendix}</span>`);
                }
                if (species.ccaa_status && (Array.isArray(species.ccaa_status) ? species.ccaa_status.length > 0 : species.ccaa_status)) {
                    const ccaaText = Array.isArray(species.ccaa_status) ? species.ccaa_status.join(', ') : species.ccaa_status;
                    details.push(`<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">CCAA: ${ccaaText}</span>`);
                }

                detailsDiv.innerHTML = `
                    <p class="font-medium">${species.scientific_name}${species.common_name ? ` (${species.common_name})` : ''}</p>
                    <div class="flex flex-wrap gap-2 mt-2">${details.join('')}</div>
                `;
                
                protectedBadge.classList.remove('hidden');
                notProtectedBadge.classList.add('hidden');
            } else {
                protectedBadge.classList.add('hidden');
                notProtectedBadge.classList.remove('hidden');
            }
        }

        function hideSpeciesBadges() {
            document.getElementById('species-protection-badge')?.classList.add('hidden');
            document.getElementById('species-not-protected-badge')?.classList.add('hidden');
            selectedSpecies = null;
        }

        // =============================================
        // VERIFICACIÓN DE COORDENADAS EN ÁREA PROTEGIDA
        // =============================================
        
        // Buscar campos de coordenadas con varios posibles nombres
        const latInputNames = ['latitud', 'lat', 'coordenada_lat', 'latitude', 'coord_lat'];
        const longInputNames = ['longitud', 'long', 'coordenada_long', 'longitude', 'coord_long', 'lng'];
        
        let latInput = null;
        let longInput = null;
        
        for (const name of latInputNames) {
            latInput = document.getElementById(`field_${name}`);
            if (latInput) break;
        }
        
        for (const name of longInputNames) {
            longInput = document.getElementById(`field_${name}`);
            if (longInput) break;
        }

        if (latInput && longInput) {
            let coordTimeout;
            
            const checkCoordinates = async () => {
                const lat = parseFloat(latInput.value);
                const long = parseFloat(longInput.value);
                
                if (isNaN(lat) || isNaN(long)) {
                    hideProtectedAreaBadge();
                    return;
                }

                // Validar rango de coordenadas
                if (lat < -90 || lat > 90 || long < -180 || long > 180) {
                    hideProtectedAreaBadge();
                    return;
                }

                try {
                    const response = await fetch(`{{ route('protected-areas.check-coordinates') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ lat, long }),
                    });
                    
                    const data = await response.json();
                    
                    if (data.in_protected_area && data.areas.length > 0) {
                        showProtectedAreaBadge(data.areas);
                        autofillProtectedArea(data.areas[0]);
                    } else {
                        hideProtectedAreaBadge();
                    }
                } catch (error) {
                    console.error('Error verificando coordenadas:', error);
                }
            };

            // Verificar cuando cambian las coordenadas
            [latInput, longInput].forEach(input => {
                input.addEventListener('change', () => {
                    clearTimeout(coordTimeout);
                    coordTimeout = setTimeout(checkCoordinates, 500);
                });
                input.addEventListener('blur', () => {
                    clearTimeout(coordTimeout);
                    coordTimeout = setTimeout(checkCoordinates, 200);
                });
            });
        }

        function showProtectedAreaBadge(areas) {
            const badge = document.getElementById('protected-area-badge');
            const detailsDiv = document.getElementById('protected-area-details');
            
            if (!badge || !detailsDiv) return;

            let html = '<div class="space-y-2">';
            areas.forEach(area => {
                html += `
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="font-medium">${area.name}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">${area.protection_type}</span>
                        ${area.iucn_category ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-800">IUCN: ${area.iucn_category}</span>` : ''}
                        ${area.region ? `<span class="text-xs text-gray-500">(${area.region})</span>` : ''}
                    </div>
                `;
            });
            html += '</div>';
            
            detailsDiv.innerHTML = html;
            badge.classList.remove('hidden');
        }

        function hideProtectedAreaBadge() {
            document.getElementById('protected-area-badge')?.classList.add('hidden');
        }

        function autofillProtectedArea(area) {
            // Mapeo de campos de área protegida
            const areaMappings = {
                'area_protegida': area.name,
                'nombre_area_protegida': area.name,
                'espacio_protegido': area.name,
                'tipo_proteccion': area.protection_type,
                'tipo_espacio': area.protection_type,
                'en_area_protegida': '1',
                'dentro_espacio_protegido': '1',
            };

            Object.entries(areaMappings).forEach(([fieldKey, value]) => {
                if (!value) return;
                
                const input = document.getElementById(`field_${fieldKey}`);
                if (!input) return;

                if (input.type === 'checkbox') {
                    input.checked = value === '1' || value === true;
                } else if (input.tagName === 'SELECT') {
                    const options = Array.from(input.options);
                    const match = options.find(opt => 
                        opt.value.toLowerCase() === String(value).toLowerCase() ||
                        opt.textContent.toLowerCase().includes(String(value).toLowerCase())
                    );
                    if (match) input.value = match.value;
                } else {
                    input.value = value;
                }
                
                // Indicador visual
                input.classList.add('bg-green-50', 'border-green-300');
                setTimeout(() => {
                    input.classList.remove('bg-green-50', 'border-green-300');
                }, 2000);
            });
        }
    </script>
    @endpush
</x-app-layout>