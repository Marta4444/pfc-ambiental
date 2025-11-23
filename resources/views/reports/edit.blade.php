<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Editar informe</h2>
    </x-slot>

    @php
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        $completedStatuses = ['completado'];
        $isCompleted = in_array($report->status, $completedStatuses, true);
        $canEdit = $isAdmin || (! $isCompleted && $user && ($user->id === $report->user_id || $user->id === $report->assigned_to));
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

        @if(! $canEdit)
            <div class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 rounded">
                @if($isCompleted && ! $isAdmin)
                    Este informe está marcado como completado. Solo un administrador puede editarlo.
                @else
                    No tienes permisos para editar este informe.
                @endif
            </div>
        @endif

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
                        <x-text-input id="community" name="community" class="mt-1 block w-full" required value="{{ old('community', $report->community) }}" />
                        @error('community') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="province" value="Provincia" />
                        <x-text-input id="province" name="province" class="mt-1 block w-full" required value="{{ old('province', $report->province) }}" />
                        @error('province') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="locality" value="Localidad" />
                        <x-text-input id="locality" name="locality" class="mt-1 block w-full" required value="{{ old('locality', $report->locality) }}" />
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
                            <a href="{{ asset('storage/' . $report->pdf_report) }}" target="_blank" class="text-blue-700 hover:underline text-sm">Ver archivo actual</a>
                        </div>
                    @endif
                    <input type="file" id="pdf_report" name="pdf_report" class="mt-1 block w-full" accept=".pdf" />
                    @error('pdf_report') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

            @else
                {{-- USUARIO NORMAL: Puede editar campos limitados (excepto IP, categoría y subcategoría) --}}
                
                <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-400 text-blue-700 rounded">
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
                        <x-input-label for="community" value="Comunidad Autónoma" />
                        <x-text-input id="community" name="community" class="mt-1 block w-full" required value="{{ old('community', $report->community) }}" />
                        @error('community') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="province" value="Provincia" />
                        <x-text-input id="province" name="province" class="mt-1 block w-full" required value="{{ old('province', $report->province) }}" />
                        @error('province') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="locality" value="Localidad" />
                        <x-text-input id="locality" name="locality" class="mt-1 block w-full" required value="{{ old('locality', $report->locality) }}" />
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
                            <a href="{{ asset('storage/' . $report->pdf_report) }}" target="_blank" class="text-blue-700 hover:underline text-sm">Ver archivo actual</a>
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
    </div>

    @if($isAdmin)
    <script>
        // Subcategory population for admin
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');

        const subcategories = @json($subcategories->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'category_id' => $s->category_id
        ]));

        function populateSubcategories(categoryId, selectedId = null) {
            if (!subcategorySelect) return;
            subcategorySelect.innerHTML = '<option value="">Selecciona una subcategoría</option>';
            if (!categoryId) return;
            subcategories.filter(s => s.category_id === parseInt(categoryId))
                .forEach(s => {
                    const option = document.createElement('option');
                    option.value = s.id;
                    option.textContent = s.name;
                    if (selectedId && parseInt(selectedId) === s.id) option.selected = true;
                    subcategorySelect.appendChild(option);
                });
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                populateSubcategories(this.value);
            });

            document.addEventListener('DOMContentLoaded', function() {
                const initialCategory = "{{ old('category_id', $report->category_id) }}";
                const initialSubcategory = "{{ old('subcategory_id', $report->subcategory_id) }}";
                if (initialCategory) {
                    categorySelect.value = initialCategory;
                    populateSubcategories(initialCategory, initialSubcategory ? parseInt(initialSubcategory) : null);
                }
            });
        }
    </script>
    @endif
</x-app-layout>