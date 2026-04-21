<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Crear Nuevo Caso</h2>
    </x-slot>

    <div class="py-6 px-4">
        <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data" class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6">
            @csrf

            {{-- Sección: Información Básica --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Información Básica</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="ip" value="Número IP (Informe Pericial) *" />
                        <x-text-input 
                            id="ip" 
                            name="ip" 
                            class="mt-1 block w-full" 
                            placeholder="2025-IP312" 
                            value="{{ old('ip') }}"
                            pattern="\d{4}-IP\d+"
                            title="Formato: AAAA-IPNNN (ejemplo: 2025-IP312)"
                            required
                        />
                        <p class="text-xs text-gray-500 mt-1">Formato: AAAA-IPNNN</p>
                        @error('ip')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="title" value="Título *" />
                        <x-text-input id="title" name="title" class="mt-1 block w-full" required value="{{ old('title') }}" />
                        @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <x-input-label for="background" value="Antecedentes *" />
                    <textarea 
                        id="background" 
                        name="background" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                        rows="4" 
                        required
                    >{{ old('background') }}</textarea>
                    @error('background')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Sección: Categorización --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Categorización</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="category_id" value="Categoría *" />
                        <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Selecciona una categoría</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="subcategory_id" value="Subcategoría *" />
                        <select id="subcategory_id" name="subcategory_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Selecciona primero una categoría</option>
                        </select>
                        @error('subcategory_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Sección: Localización --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Localización</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="community" value="Comunidad Autónoma *" />
                        <select id="community" name="community" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Selecciona una comunidad</option>
                        </select>
                        @error('community')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="province" value="Provincia *" />
                        <select id="province" name="province" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required disabled>
                            <option value="">Selecciona primero una comunidad</option>
                        </select>
                        @error('province')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="locality" value="Localidad *" />
                        <input 
                            type="text" 
                            id="locality" 
                            name="locality" 
                            list="locality-suggestions"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                            required 
                            disabled
                            autocomplete="off"
                            value="{{ old('locality') }}"
                            placeholder="Escribe para buscar..."
                        />
                        <datalist id="locality-suggestions"></datalist>
                        <p id="locality-hint" class="text-xs text-gray-500 mt-1 hidden">Escribe al menos 2 caracteres para ver sugerencias</p>
                        @error('locality')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="coordinates" value="Coordenadas (lat,lon)" />
                        <x-text-input id="coordinates" name="coordinates" placeholder="41.12345,-1.23456" class="mt-1 block w-full" value="{{ old('coordinates') }}" />
                        <p id="coordinates-validation" class="text-xs mt-1 hidden"></p>
                        @error('coordinates')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Sección: Peticionario --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Unidad Peticionaria</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="petitioner_id" value="Peticionario *" />
                        <select id="petitioner_id" name="petitioner_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Selecciona una opción</option>
                            @foreach($petitioners as $petitioner)
                                <option value="{{ $petitioner->id }}" {{ old('petitioner_id') == $petitioner->id ? 'selected' : '' }}>
                                    {{ $petitioner->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('petitioner_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div id="petitioner_other_wrapper" class="hidden">
                        <x-input-label for="petitioner_other" value="Especificar Otro" />
                        <x-text-input 
                            id="petitioner_other" 
                            name="petitioner_other" 
                            class="mt-1 block w-full" 
                            value="{{ old('petitioner_other') }}"
                            placeholder="Especifique la unidad"
                        />
                        @error('petitioner_other')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="office" value="Despacho/Oficina" />
                        <x-text-input id="office" name="office" class="mt-1 block w-full" value="{{ old('office') }}" />
                        @error('office')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="diligency" value="Diligencias" />
                        <x-text-input id="diligency" name="diligency" placeholder="D-2025-001" class="mt-1 block w-full" value="{{ old('diligency') }}" />
                        @error('diligency')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Sección: Fechas y Urgencia --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Fechas y Prioridad</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="date_petition" value="Fecha de Petición *" />
                        <x-text-input type="date" id="date_petition" name="date_petition" class="mt-1 block w-full" required value="{{ old('date_petition') }}" />
                        <p class="text-xs text-gray-500 mt-1">Fecha de llegada al SEPRONA</p>
                        @error('date_petition')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="date_damage" value="Fecha del Daño *" />
                        <x-text-input type="date" id="date_damage" name="date_damage" class="mt-1 block w-full" required value="{{ old('date_damage') }}" />
                        <p class="text-xs text-gray-500 mt-1">Fecha del delito/daño</p>
                        @error('date_damage')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="urgency" value="Urgencia *" />
                        <select id="urgency" name="urgency" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach(\App\Models\Report::URGENCY_LABELS as $value => $label)
                                <option value="{{ $value }}" {{ old('urgency', 'normal') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('urgency')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Sección: Asignación --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Asignación (Opcional)</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="assigned_to" value="Asignar a Agente" />
                        <select id="assigned_to" name="assigned_to" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Sin asignar</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ old('assigned_to') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }} ({{ $agent->agent_num }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Puede dejarse sin asignar y asignarse posteriormente</p>
                        @error('assigned_to')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="pdf_report" value="Adjuntar PDF" />
                        <input type="file" id="pdf_report" name="pdf_report" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-eco-700 hover:file:bg-blue-100" accept=".pdf" />
                        @error('pdf_report')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-200">
                    Cancelar
                </a>
                <x-primary-button>Crear Caso</x-primary-button>
            </div>
        </form>
    </div>

    {{-- Scripts --}}
    <script>
        // ============================================
        // DATOS GEOGRÁFICOS DE ESPAÑA (cargados desde JSON)
        // ============================================
        let SPAIN_GEO_DATA = null;

        // Cargar datos geográficos al inicio
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const response = await fetch('{{ asset("data/spain-geo.json") }}');
                if (!response.ok) throw new Error('Error cargando datos geográficos: ' + response.status);
                SPAIN_GEO_DATA = await response.json();
                initGeoSelectors();
            } catch (error) {
                console.error('Error cargando datos geográficos:', error);
                alert('Error cargando datos geográficos. Por favor, recarga la página.');
            }
            
            // Inicializar categorías y peticionario
            initCategories();
            initPetitioner();
        });

        // ============================================
        // FUNCIONES DE VALIDACIÓN GEOGRÁFICA
        // ============================================
        function getComunidades() {
            if (!SPAIN_GEO_DATA) return [];
            return Object.keys(SPAIN_GEO_DATA.comunidades).sort();
        }

        function getProvincias(comunidad) {
            if (!SPAIN_GEO_DATA) return [];
            const data = SPAIN_GEO_DATA.comunidades[comunidad];
            return data ? Object.keys(data.provincias).sort() : [];
        }

        function getMunicipios(comunidad, provincia) {
            if (!SPAIN_GEO_DATA) return [];
            const data = SPAIN_GEO_DATA.comunidades[comunidad];
            if (!data || !data.provincias[provincia]) return [];
            return data.provincias[provincia].municipios.sort();
        }

        function getComunidadBbox(comunidad) {
            if (!SPAIN_GEO_DATA) return null;
            const data = SPAIN_GEO_DATA.comunidades[comunidad];
            return data ? data.bbox : null;
        }

        function validateCoordinates(lat, lng, comunidad) {
            const bbox = getComunidadBbox(comunidad);
            if (!bbox) return { valid: false, message: 'Comunidad no encontrada' };
            
            const inside = lat >= bbox.minLat && lat <= bbox.maxLat &&
                           lng >= bbox.minLng && lng <= bbox.maxLng;
            
            return {
                valid: inside,
                message: inside 
                    ? 'Coordenadas válidas para ' + comunidad
                    : 'Las coordenadas NO corresponden a ' + comunidad
            };
        }

        function parseCoordinates(coordStr) {
            if (!coordStr) return null;
            const cleanStr = coordStr.replace(/\s/g, '');
            const parts = cleanStr.split(',');
            if (parts.length !== 2) return null;
            const lat = parseFloat(parts[0]);
            const lng = parseFloat(parts[1]);
            if (isNaN(lat) || isNaN(lng)) return null;
            if (lat < 27 || lat > 44 || lng < -19 || lng > 5) return null;
            return { lat, lng };
        }

        function searchMunicipios(comunidad, provincia, searchTerm) {
            const municipios = getMunicipios(comunidad, provincia);
            if (!searchTerm || searchTerm.length < 2) return municipios.slice(0, 15);
            
            const term = searchTerm.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            return municipios.filter(m => {
                const normalized = m.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                return normalized.includes(term);
            });
        }

        // ============================================
        // INICIALIZACIÓN DE SELECTORES GEOGRÁFICOS
        // ============================================
        function initGeoSelectors() {
            const communitySelect = document.getElementById('community');
            const provinceSelect = document.getElementById('province');
            const localityInput = document.getElementById('locality');
            const localitySuggestions = document.getElementById('locality-suggestions');
            const localityHint = document.getElementById('locality-hint');
            const coordinatesInput = document.getElementById('coordinates');
            const coordinatesValidation = document.getElementById('coordinates-validation');

            // Poblar comunidades
            getComunidades().forEach(c => {
                const option = document.createElement('option');
                option.value = c;
                option.textContent = c;
                communitySelect.appendChild(option);
            });

            // Evento: cambio de comunidad
            communitySelect.addEventListener('change', function() {
                const comunidad = this.value;
                provinceSelect.innerHTML = '<option value="">Selecciona una provincia</option>';
                localityInput.value = '';
                localitySuggestions.innerHTML = '';
                
                if (comunidad) {
                    getProvincias(comunidad).forEach(p => {
                        const option = document.createElement('option');
                        option.value = p;
                        option.textContent = p;
                        provinceSelect.appendChild(option);
                    });
                    provinceSelect.disabled = false;
                } else {
                    provinceSelect.disabled = true;
                }
                localityInput.disabled = true;
                
                validateCoordinatesField();
            });

            // Evento: cambio de provincia
            provinceSelect.addEventListener('change', function() {
                const provincia = this.value;
                localityInput.value = '';
                localitySuggestions.innerHTML = '';
                
                if (provincia) {
                    localityInput.disabled = false;
                    localityHint.classList.remove('hidden');
                    updateLocalitySuggestions('');
                } else {
                    localityInput.disabled = true;
                    localityHint.classList.add('hidden');
                }
            });

            // Evento: escritura en localidad
            localityInput.addEventListener('input', function() {
                updateLocalitySuggestions(this.value);
            });

            function updateLocalitySuggestions(searchTerm) {
                const comunidad = communitySelect.value;
                const provincia = provinceSelect.value;
                localitySuggestions.innerHTML = '';
                
                if (comunidad && provincia) {
                    const matches = searchMunicipios(comunidad, provincia, searchTerm);
                    matches.forEach(m => {
                        const option = document.createElement('option');
                        option.value = m;
                        localitySuggestions.appendChild(option);
                    });
                }
            }

            // Eventos: validación de coordenadas
            coordinatesInput.addEventListener('input', validateCoordinatesField);
            coordinatesInput.addEventListener('blur', validateCoordinatesField);

            function validateCoordinatesField() {
                const coordStr = coordinatesInput.value.trim();
                const comunidad = communitySelect.value;
                
                if (!coordStr) {
                    coordinatesValidation.classList.add('hidden');
                    coordinatesInput.classList.remove('border-red-500', 'border-green-500');
                    return;
                }

                coordinatesValidation.classList.remove('hidden');
                
                const coords = parseCoordinates(coordStr);
                if (!coords) {
                    coordinatesValidation.textContent = 'Formato inválido. Use: lat,lon (ej: 40.4168,-3.7038)';
                    coordinatesValidation.className = 'text-xs mt-1 text-red-600';
                    coordinatesInput.classList.add('border-red-500');
                    coordinatesInput.classList.remove('border-green-500');
                    return;
                }

                if (!comunidad) {
                    coordinatesValidation.textContent = 'Selecciona una comunidad para validar las coordenadas';
                    coordinatesValidation.className = 'text-xs mt-1 text-yellow-600';
                    coordinatesInput.classList.remove('border-red-500', 'border-green-500');
                    return;
                }

                const result = validateCoordinates(coords.lat, coords.lng, comunidad);
                if (result.valid) {
                    coordinatesValidation.textContent = '✓ ' + result.message;
                    coordinatesValidation.className = 'text-xs mt-1 text-green-600';
                    coordinatesInput.classList.add('border-green-500');
                    coordinatesInput.classList.remove('border-red-500');
                } else {
                    coordinatesValidation.textContent = '✗ ' + result.message;
                    coordinatesValidation.className = 'text-xs mt-1 text-red-600';
                    coordinatesInput.classList.add('border-red-500');
                    coordinatesInput.classList.remove('border-green-500');
                }
            }

            // Restaurar valores old() si existen
            const oldCommunity = "{{ old('community') }}";
            const oldProvince = "{{ old('province') }}";
            const oldLocality = "{{ old('locality') }}";
            
            if (oldCommunity) {
                communitySelect.value = oldCommunity;
                communitySelect.dispatchEvent(new Event('change'));
                
                setTimeout(() => {
                    if (oldProvince) {
                        provinceSelect.value = oldProvince;
                        provinceSelect.dispatchEvent(new Event('change'));
                        
                        setTimeout(() => {
                            if (oldLocality) {
                                localityInput.value = oldLocality;
                            }
                        }, 50);
                    }
                }, 50);
            }

            // Validar coordenadas si existen
            if (coordinatesInput.value) {
                setTimeout(validateCoordinatesField, 200);
            }
        }

        // ============================================
        // MANEJO DE CATEGORÍAS/SUBCATEGORÍAS
        // ============================================
        const subcategories = @json($subcategories->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'category_id' => $s->category_id
        ]));

        function initCategories() {
            const categorySelect = document.getElementById('category_id');
            const subcategorySelect = document.getElementById('subcategory_id');

            function populateSubcategories(categoryId, selectedId = null) {
                subcategorySelect.innerHTML = '<option value="">Selecciona una subcategoría</option>';
                if (!categoryId) return;
                
                subcategories
                    .filter(s => s.category_id === parseInt(categoryId))
                    .forEach(s => {
                        const option = document.createElement('option');
                        option.value = s.id;
                        option.textContent = s.name;
                        if (selectedId && parseInt(selectedId) === s.id) option.selected = true;
                        subcategorySelect.appendChild(option);
                    });
            }

            categorySelect.addEventListener('change', function() {
                populateSubcategories(this.value);
            });

            // Restaurar valores
            const oldCategory = "{{ old('category_id') }}";
            const oldSubcategory = "{{ old('subcategory_id') }}";
            if (oldCategory) {
                categorySelect.value = oldCategory;
                populateSubcategories(oldCategory, oldSubcategory);
            }
        }

        // ============================================
        // MANEJO DEL CAMPO "OTRO" EN PETICIONARIO
        // ============================================
        const petitionerOtherId = @json(optional($petitioners->firstWhere('name', 'Otro'))->id);

        function initPetitioner() {
            const petitionerSelect = document.getElementById('petitioner_id');
            const petitionerOtherWrapper = document.getElementById('petitioner_other_wrapper');
            const petitionerOtherInput = document.getElementById('petitioner_other');

            petitionerSelect.addEventListener('change', function() {
                if (parseInt(this.value) === petitionerOtherId) {
                    petitionerOtherWrapper.classList.remove('hidden');
                    petitionerOtherInput.required = true;
                } else {
                    petitionerOtherWrapper.classList.add('hidden');
                    petitionerOtherInput.required = false;
                    petitionerOtherInput.value = '';
                }
            });

            // Restaurar valores
            const oldPetitionerId = "{{ old('petitioner_id') }}";
            if (oldPetitionerId && parseInt(oldPetitionerId) === petitionerOtherId) {
                petitionerOtherWrapper.classList.remove('hidden');
                petitionerOtherInput.required = true;
            }
        }
    </script>
</x-app-layout>