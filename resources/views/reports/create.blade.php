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
                        <x-text-input id="community" name="community" class="mt-1 block w-full" required value="{{ old('community') }}" />
                        @error('community')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="province" value="Provincia *" />
                        <x-text-input id="province" name="province" class="mt-1 block w-full" required value="{{ old('province') }}" />
                        @error('province')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="locality" value="Localidad *" />
                        <x-text-input id="locality" name="locality" class="mt-1 block w-full" required value="{{ old('locality') }}" />
                        @error('locality')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-input-label for="coordinates" value="Coordenadas (lat,lon)" />
                        <x-text-input id="coordinates" name="coordinates" placeholder="41.12345,-1.23456" class="mt-1 block w-full" value="{{ old('coordinates') }}" />
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

                    <div id="petitioner_other_wrapper" style="display: none;">
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
                        <input type="file" id="pdf_report" name="pdf_report" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept=".pdf" />
                        @error('pdf_report')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-gray-200 rounded text-sm text-blue-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <div class="flex gap-2">
                    {{-- Guardar y añadir detalles --}}
                    <button type="submit" name="add_details" value="1" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Guardar y Añadir Detalles
                    </button>
                    {{-- Solo guardar --}}
                    <x-primary-button>Crear Caso</x-primary-button>
                </div>
            </div>
        </form>
    </div>

    {{-- Scripts --}}
    <script>
        // Manejo de subcategorías dinámicas
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');
        const subcategories = @json($subcategories->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'category_id' => $s->category_id
        ]));

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

        // Restaurar valores en caso de error de validación
        document.addEventListener('DOMContentLoaded', function() {
            const oldCategory = "{{ old('category_id') }}";
            const oldSubcategory = "{{ old('subcategory_id') }}";
            if (oldCategory) {
                categorySelect.value = oldCategory;
                populateSubcategories(oldCategory, oldSubcategory);
            }
        });

        // Manejo del campo "Otro" en peticionario - CAMBIO
        const petitionerSelect = document.getElementById('petitioner_id');
        const petitionerOtherWrapper = document.getElementById('petitioner_other_wrapper');
        const petitionerOtherInput = document.getElementById('petitioner_other');

        // Obtener los datos de petitioners con sus IDs
        const petitioners = @json($petitioners->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name
        ]));

        petitionerSelect.addEventListener('change', function() {
            const selectedPetitioner = petitioners.find(p => p.id === parseInt(this.value));
            
            if (selectedPetitioner && selectedPetitioner.name === 'Otro') {
                petitionerOtherWrapper.style.display = 'block';
                petitionerOtherInput.required = true;
            } else {
                petitionerOtherWrapper.style.display = 'none';
                petitionerOtherInput.required = false;
                petitionerOtherInput.value = '';
            }
        });

        // Verificar al cargar la página (por si hay old values)
        document.addEventListener('DOMContentLoaded', function() {
            const oldPetitionerId = "{{ old('petitioner_id') }}";
            if (oldPetitionerId) {
                const selectedPetitioner = petitioners.find(p => p.id === parseInt(oldPetitionerId));
                if (selectedPetitioner && selectedPetitioner.name === 'Otro') {
                    petitionerOtherWrapper.style.display = 'block';
                    petitionerOtherInput.required = true;
                }
            }
        });
    </script>
</x-app-layout>