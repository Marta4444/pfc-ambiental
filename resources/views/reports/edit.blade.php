<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Editar informe</h2>
    </x-slot>

    @php
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        $isFinalizado = $report->isFinalizado();
        $completedStatuses = ['completado'];
        $isCompleted = in_array($report->status, $completedStatuses, true);
        $canEdit = !$isFinalizado && ($isAdmin || (! $isCompleted && $user && ($user->id === $report->user_id || $user->id === $report->assigned_to)));
        $categories = $categories ?? \App\Models\Category::where('active', true)->get();
        $subcategories = $subcategories ?? \App\Models\Subcategory::where('active', true)->get();
    @endphp

    <div class="py-6 px-4 max-w-4xl mx-auto">
        <div class="mb-4 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $report->title }}</h1>
                <p class="text-sm text-gray-500 mt-1">Creado por: <span class="text-gray-700">{{ $report->user->name ?? '—' }}</span></p>
            </div>

            <a href="{{ route('reports.show', $report) }}" class="inline-flex items-center px-3 py-1 bg-gray-100 border border-gray-200 rounded text-sm text-gray-700 hover:bg-gray-50">Volver</a>
        </div>

        @if($isFinalizado)
            <div class="mb-4 p-4 bg-gray-100 border-l-4 border-gray-500 text-gray-700 rounded">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold">Caso Finalizado</p>
                        <p class="text-sm">Este caso está cerrado y no se puede editar. Si necesita reabrirlo, contacte con un administrador.</p>
                    </div>
                </div>
            </div>
        @elseif(! $canEdit)
            <div class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 rounded">
                @if($isCompleted && ! $isAdmin)
                    Este informe está marcado como completado. Solo un administrador puede editarlo.
                @else
                    No tienes permisos para editar este informe.
                @endif
            </div>
        @endif

        @if($canEdit)

        <form method="POST" action="{{ route('reports.update', $report) }}" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6">
            @csrf
            @method('PUT')

            @if($isAdmin)
                {{-- ADMIN: Puede editar TODO --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <x-input-label for="title" value="Título" />
                        <x-text-input id="title" name="title" class="mt-1 block w-full" required value="{{ old('title', $report->title) }}" />
                        @error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="ip" value="Número IP (Informe Pericial) *" />
                        <x-text-input 
                            id="ip" 
                            name="ip" 
                            class="mt-1 block w-full" 
                            placeholder="2025-IP312" 
                            value="{{ old('ip', $report->ip) }}"
                            pattern="\d{4}-IP\d+"
                            title="Formato: AAAA-IPNNN (ejemplo: 2025-IP312)"
                            required
                        />
                        <p class="text-sm text-gray-500 mt-1">Formato obligatorio: AAAA-IPNNN (ejemplo: 2025-IP312)</p>
                        @error('ip') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <x-input-label for="date_petition" value="Fecha de petición" />
                        <x-text-input type="date" id="date_petition" name="date_petition" class="mt-1 block w-full" required value="{{ old('date_petition', optional($report->date_petition)->format('Y-m-d')) }}" />
                        @error('date_petition') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="date_damage" value="Fecha del daño" />
                        <x-text-input type="date" id="date_damage" name="date_damage" class="mt-1 block w-full" required value="{{ old('date_damage', optional($report->date_damage)->format('Y-m-d')) }}" />
                        @error('date_damage') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <x-input-label for="category_id" value="Categoría" />
                        <select id="category_id" name="category_id" class="mt-1 block w-full border rounded p-2" required>
                            <option value="">Selecciona una categoría</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (int) old('category_id', $report->category_id) === $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="subcategory_id" value="Subcategoría" />
                        <select id="subcategory_id" name="subcategory_id" class="mt-1 block w-full border rounded p-2" required>
                            <option value="">Selecciona una subcategoría</option>
                        </select>
                        @error('subcategory_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <x-input-label for="community" value="Comunidad Autónoma" />
                        <select id="community" name="community" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Selecciona una comunidad</option>
                        </select>
                        @error('community') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="province" value="Provincia" />
                        <select id="province" name="province" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required disabled>
                            <option value="">Selecciona primero una comunidad</option>
                        </select>
                        @error('province') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="locality" value="Localidad" />
                        <input 
                            type="text" 
                            id="locality" 
                            name="locality" 
                            list="locality-suggestions"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                            required 
                            disabled
                            autocomplete="off"
                            value="{{ old('locality', $report->locality) }}"
                            placeholder="Escribe para buscar..."
                        />
                        <datalist id="locality-suggestions"></datalist>
                        @error('locality') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <x-input-label for="location" value="Ubicación específica" />
                        <x-text-input id="location" name="location" class="mt-1 block w-full" value="{{ old('location', $report->location) }}" />
                        @error('location') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="coordinates" value="Coordenadas (lat,lon)" />
                        <x-text-input id="coordinates" name="coordinates" placeholder="41.12345,-1.23456" class="mt-1 block w-full" value="{{ old('coordinates', $report->coordinates) }}" />
                        <p id="coordinates-validation" class="text-xs mt-1 hidden"></p>
                        @error('coordinates') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <x-input-label for="background" value="Antecedentes" />
                    <textarea id="background" name="background" rows="4" class="mt-1 block w-full border rounded p-2" required>{{ old('background', $report->background) }}</textarea>
                    @error('background') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <x-input-label for="petitioner_id" value="Unidad peticionaria" />
                        <select id="petitioner_id" name="petitioner_id" class="mt-1 block w-full border rounded p-2" required>
                            @foreach($petitioners as $petitioner)
                                <option value="{{ $petitioner->id }}" {{ (int) old('petitioner_id', $report->petitioner_id) === $petitioner->id ? 'selected' : '' }}>
                                    {{ $petitioner->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('petitioner_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="petitioner_other" value="Otra unidad (especificar)" />
                        <x-text-input id="petitioner_other" name="petitioner_other" class="mt-1 block w-full" value="{{ old('petitioner_other', $report->petitioner_other) }}" />
                        @error('petitioner_other') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="office" value="Despacho/Oficina" />
                            <x-text-input id="office" name="office" class="mt-1 block w-full" value="{{ old('office', $report->office) }}" />
                            @error('office') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-input-label for="diligency" value="Diligencias" />
                            <x-text-input id="diligency" name="diligency" placeholder="D-2025-001" class="mt-1 block w-full" value="{{ old('diligency', $report->diligency) }}" />
                            @error('diligency') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    
                    <div>
                        <x-input-label for="urgency" value="Urgencia" />
                        <select id="urgency" name="urgency" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach(\App\Models\Report::URGENCY_LABELS as $value => $label)
                                <option value="{{ $value }}" {{ old('urgency', $report->urgency ?? 'normal') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('urgency') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="status" value="Estado" />
                        <select id="status" name="status" class="mt-1 block w-full border rounded p-2" required>
                            @foreach(\App\Models\Report::STATUS_LABELS as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $report->status) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <x-input-label for="assigned_to" value="Asignar a" />
                    <select id="assigned_to" name="assigned_to" class="mt-1 block w-full border rounded p-2">
                        <option value="">Sin asignar</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('assigned_to', $report->assigned_to) == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <x-input-label for="pdf_report" value="Adjuntar PDF (reemplaza el existente)" />
                    @if($report->pdf_report)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $report->pdf_report) }}" target="_blank" class="text-eco-700 hover:underline text-sm">Ver archivo actual</a>
                        </div>
                    @endif
                    <input type="file" id="pdf_report" name="pdf_report" class="mt-1 block w-full" accept=".pdf" />
                    @error('pdf_report') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

            @else
                {{-- USUARIO NORMAL: Puede editar campos limitados (excepto IP, categoría y subcategoría) --}}
                
                <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-400 text-eco-700 rounded">
                    <p class="font-semibold">Camposos para usuarios:</p>
                    <ul class="list-disc list-inside text-sm mt-2">
                        <li>Número IP (Informe Pericial)</li>
                        <li>Categoría y Subcategoría</li>
                    </ul>
                    <p class="text-sm mt-2">Si necesitas modificar estos campos, contacta con un administrador.</p>
                </div>

                {{-- Campos bloqueados (solo lectura) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <x-input-label value="Número IP" />
                        <div class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded text-gray-700">
                            {{ $report->ip }}
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Categoría / Subcategoría" />
                        <div class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded text-gray-700">
                            {{ $report->category->name ?? '—' }} / {{ $report->subcategory->name ?? '—' }}
                        </div>
                    </div>
                </div>

                {{-- Campos editables --}}
                <div class="mb-4">
                    <x-input-label for="title" value="Título" />
                    <x-text-input id="title" name="title" class="mt-1 block w-full" required value="{{ old('title', $report->title) }}" />
                    @error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <x-input-label for="date_petition" value="Fecha de petición" />
                        <x-text-input type="date" id="date_petition" name="date_petition" class="mt-1 block w-full" required value="{{ old('date_petition', optional($report->date_petition)->format('Y-m-d')) }}" />
                        @error('date_petition') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="date_damage" value="Fecha del daño" />
                        <x-text-input type="date" id="date_damage" name="date_damage" class="mt-1 block w-full" required value="{{ old('date_damage', optional($report->date_damage)->format('Y-m-d')) }}" />
                        @error('date_damage') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <x-input-label for="community_user" value="Comunidad Autónoma" />
                        <select id="community_user" name="community" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Selecciona una comunidad</option>
                        </select>
                        @error('community') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="province_user" value="Provincia" />
                        <select id="province_user" name="province" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required disabled>
                            <option value="">Selecciona primero una comunidad</option>
                        </select>
                        @error('province') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="locality_user" value="Localidad" />
                        <input 
                            type="text" 
                            id="locality_user" 
                            name="locality" 
                            list="locality-suggestions-user"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                            required 
                            disabled
                            autocomplete="off"
                            value="{{ old('locality', $report->locality) }}"
                            placeholder="Escribe para buscar..."
                        />
                        <datalist id="locality-suggestions-user"></datalist>
                        @error('locality') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                
                <div class="mb-4">
                    <x-input-label for="background" value="Antecedentes" />
                    <textarea id="background" name="background" rows="4" class="mt-1 block w-full border rounded p-2" required>{{ old('background', $report->background) }}</textarea>
                    @error('background') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <x-input-label for="petitioner_other" value="Otra unidad peticionaria (especificar)" />
                    <x-text-input id="petitioner_other" name="petitioner_other" class="mt-1 block w-full" value="{{ old('petitioner_other', $report->petitioner_other) }}" />
                    @error('petitioner_other') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    <p class="text-sm text-gray-500 mt-1">Solo si la unidad peticionaria es "Otro"</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <x-input-label for="affected_area" value="Área afectada (m²)" />
                        <x-text-input type="number" step="0.01" id="affected_area" name="affected_area" class="mt-1 block w-full" value="{{ old('affected_area', $report->affected_area) }}" />
                        @error('affected_area') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="urgency" value="Urgencia" />
                        <select id="urgency" name="urgency" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" {{ $isCompleted ? 'disabled' : '' }}>
                            @foreach(\App\Models\Report::URGENCY_LABELS as $value => $label)
                                <option value="{{ $value }}" {{ old('urgency', $report->urgency ?? 'normal') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('urgency') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="status" value="Estado" />
                        <select id="status" name="status" class="mt-1 block w-full border rounded p-2" required {{ $isCompleted ? 'disabled' : '' }}>
                            @foreach(\App\Models\Report::STATUS_LABELS as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $report->status) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        @if($isCompleted)
                            <p class="text-sm text-gray-500 mt-2">Informe completado. Solo un administrador puede modificarlo.</p>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <x-input-label for="pdf_report" value="Adjuntar PDF (reemplaza el existente)" />
                    @if($report->pdf_report)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $report->pdf_report) }}" target="_blank" class="text-eco-700 hover:underline text-sm">Ver archivo actual</a>
                        </div>
                    @endif
                    <input type="file" id="pdf_report" name="pdf_report" class="mt-1 block w-full" accept=".pdf" {{ $isCompleted ? 'disabled' : '' }} />
                    @error('pdf_report') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="flex items-center space-x-3 mt-4">
                @if($canEdit)
                    <x-primary-button>Guardar cambios</x-primary-button>
                @endif

                <a href="{{ route('reports.show', $report) }}" class="inline-flex items-center px-3 py-1 bg-gray-100 border border-gray-200 rounded text-sm text-gray-700 hover:bg-gray-50">Cancelar</a>
            </div>
        </form>
        @endif {{-- fin de if canEdit --}}
    </div>

    {{-- Script unificado que carga datos geográficos desde JSON --}}
    <script>
        // ============================================
        // DATOS GEOGRÁFICOS DE ESPAÑA (cargados desde JSON)
        // ============================================
        let SPAIN_GEO_DATA = null;

        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const response = await fetch('{{ asset("data/spain-geo.json") }}');
                if (!response.ok) throw new Error('Error cargando datos geográficos: ' + response.status);
                SPAIN_GEO_DATA = await response.json();
                console.log('Datos geográficos cargados:', Object.keys(SPAIN_GEO_DATA.comunidades).length + ' comunidades');
                initGeoSelectors();
            } catch (error) {
                console.error('Error cargando datos geográficos:', error);
                alert('Error cargando datos geográficos. Por favor, recarga la página.');
            }
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
            // Intentar inicializar selectores de admin
            const adminSelectors = initGeoSelectorSet('community', 'province', 'locality', 'coordinates');
            
            // Intentar inicializar selectores de usuario
            const userSelectors = initGeoSelectorSet('community_user', 'province_user', 'locality_user', 'coordinates_user');
        }

        function initGeoSelectorSet(communityId, provinceId, localityId, coordinatesId) {
            const communitySelect = document.getElementById(communityId);
            const provinceSelect = document.getElementById(provinceId);
            const localityInput = document.getElementById(localityId);
            const localitySuggestions = document.getElementById(localityId + '-suggestions');
            const localityHint = document.getElementById(localityId + '-hint');
            const coordinatesInput = document.getElementById(coordinatesId);
            const coordinatesValidation = document.getElementById(coordinatesId + '-validation');

            if (!communitySelect || !provinceSelect) return false;

            // Guardar valor actual
            const currentCommunity = communitySelect.value;
            const currentProvince = provinceSelect.value;
            const currentLocality = localityInput ? localityInput.value : '';

            // Poblar comunidades
            const existingOptions = communitySelect.querySelectorAll('option:not([value=""])');
            if (existingOptions.length === 0) {
                getComunidades().forEach(c => {
                    const option = document.createElement('option');
                    option.value = c;
                    option.textContent = c;
                    communitySelect.appendChild(option);
                });
            }

            // Evento: cambio de comunidad
            communitySelect.addEventListener('change', function() {
                const comunidad = this.value;
                provinceSelect.innerHTML = '<option value="">Selecciona una provincia</option>';
                if (localityInput) localityInput.value = '';
                if (localitySuggestions) localitySuggestions.innerHTML = '';
                
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
                if (localityInput) localityInput.disabled = true;
                
                validateCoordinatesField();
            });

            // Evento: cambio de provincia
            provinceSelect.addEventListener('change', function() {
                const provincia = this.value;
                if (localityInput) localityInput.value = '';
                if (localitySuggestions) localitySuggestions.innerHTML = '';
                
                if (provincia && localityInput) {
                    localityInput.disabled = false;
                    if (localityHint) localityHint.classList.remove('hidden');
                    updateLocalitySuggestions('');
                } else if (localityInput) {
                    localityInput.disabled = true;
                    if (localityHint) localityHint.classList.add('hidden');
                }
            });

            // Evento: escritura en localidad
            if (localityInput) {
                localityInput.addEventListener('input', function() {
                    updateLocalitySuggestions(this.value);
                });
            }

            function updateLocalitySuggestions(searchTerm) {
                if (!localitySuggestions) return;
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
            if (coordinatesInput) {
                coordinatesInput.addEventListener('input', validateCoordinatesField);
                coordinatesInput.addEventListener('blur', validateCoordinatesField);
            }

            function validateCoordinatesField() {
                if (!coordinatesInput || !coordinatesValidation) return;
                
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
                    coordinatesValidation.textContent = 'Selecciona una comunidad para validar';
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

            // Restaurar valores existentes
            const oldCommunity = currentCommunity || "{{ old('community', $report->community) }}";
            const oldProvince = currentProvince || "{{ old('province', $report->province) }}";
            const oldLocality = currentLocality || "{{ old('locality', $report->locality) }}";

            if (oldCommunity) {
                communitySelect.value = oldCommunity;
                communitySelect.dispatchEvent(new Event('change'));
                
                setTimeout(() => {
                    if (oldProvince) {
                        provinceSelect.value = oldProvince;
                        provinceSelect.dispatchEvent(new Event('change'));
                        
                        setTimeout(() => {
                            if (oldLocality && localityInput) {
                                localityInput.value = oldLocality;
                            }
                        }, 50);
                    }
                }, 50);
            }

            // Validar coordenadas si existen
            if (coordinatesInput && coordinatesInput.value) {
                setTimeout(validateCoordinatesField, 200);
            }

            return true;
        }
    </script>
</x-app-layout>