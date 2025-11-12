<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Editar informe</h2>
    </x-slot>

    @php
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        $completedStatuses = ['resuelto'];
        $isCompleted = in_array($report->status, $completedStatuses, true);
        $canEdit = $isAdmin || (! $isCompleted && $user && $user->role === 'user');
        // fallback collections if controller didn't pass them
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
                    Este informe está marcado como completado ({{ $report->status }}). Solo un administrador puede editarlo.
                @else
                    No tienes permisos para editar este informe.
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('reports.update', $report) }}" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6" >
            @csrf
            @method('PUT')

            @if($isAdmin)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <x-input-label for="title" value="Título" />
                        <x-text-input id="title" name="title" class="mt-1 block w-full" required value="{{ old('title', $report->title) }}" />
                        @error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="date_damage" value="Fecha del daño" />
                        <x-text-input type="date" id="date_damage" name="date_damage" class="mt-1 block w-full" required value="{{ old('date_damage', optional($report->date_damage)->format('Y-m-d')) }}" />
                        @error('date_damage') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <x-input-label for="description" value="Descripción" />
                    <textarea id="description" name="description" class="mt-1 block w-full border rounded p-2" rows="4">{{ old('description', $report->description) }}</textarea>
                    @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <x-input-label for="location" value="Ubicación" />
                        <x-text-input id="location" name="location" class="mt-1 block w-full" value="{{ old('location', $report->location) }}" />
                    </div>

                    <div>
                        <x-input-label for="coordinates" value="Coordenadas (lat,lon)" />
                        <x-text-input id="coordinates" name="coordinates" placeholder="41.12345,-1.23456" class="mt-1 block w-full" value="{{ old('coordinates', $report->coordinates) }}" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <x-input-label for="affected_area" value="Área afectada (m²)" />
                        <x-text-input type="number" step="0.01" id="affected_area" name="affected_area" class="mt-1 block w-full" value="{{ old('affected_area', $report->affected_area) }}" />
                    </div>

                    <div>
                        <x-input-label for="criticallity" value="Nivel de criticidad (1-5)" />
                        <x-text-input type="number" min="1" max="5" id="criticallity" name="criticallity" class="mt-1 block w-full" value="{{ old('criticallity', $report->criticallity ?? 1) }}" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Estado" />
                        <select id="status_admin" name="status" class="mt-1 block w-full border rounded p-2">
                            <option value="pendiente" {{ old('status', $report->status) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en_proceso" {{ old('status', $report->status) == 'en_proceso' ? 'selected' : '' }}>En proceso</option>
                            <option value="procesando" {{ old('status', $report->status) == 'procesando' ? 'selected' : '' }}>Procesando</option>
                            <option value="resuelto" {{ old('status', $report->status) == 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                            <option value="cerrado" {{ old('status', $report->status) == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <x-input-label for="pdf_report" value="Adjuntar PDF (reemplaza el existente)" />
                    @if($report->pdf_report)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $report->pdf_report) }}" target="_blank" class="text-blue-700 hover:underline text-sm">Archivo actual: ver / descargar</a>
                        </div>
                    @endif
                    <input type="file" id="pdf_report" name="pdf_report" class="mt-1 block w-full" accept=".pdf" />
                    @error('pdf_report') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            @else
                {{-- Non-admin users: only allow status changes unless report is completed --}}
                <div class="mb-4">
                    <x-input-label value="Título" />
                    <p class="mt-1 text-gray-800">{{ $report->title }}</p>
                </div>

                <div class="mb-4">
                    <x-input-label value="Categoría / Subcategoría" />
                    <p class="mt-1 text-gray-800">{{ $report->category->name ?? '—' }} / {{ $report->subcategory->name ?? '—' }}</p>
                </div>

                <div class="mb-4">
                    <x-input-label for="status" value="Estado" />
                    <select id="status_user" name="status" class="mt-1 block w-full border rounded p-2" {{ $isCompleted ? 'disabled' : '' }}>
                        <option value="pendiente" {{ old('status', $report->status) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="en_proceso" {{ old('status', $report->status) == 'en_proceso' ? 'selected' : '' }}>En proceso</option>
                        <option value="procesando" {{ old('status', $report->status) == 'procesando' ? 'selected' : '' }}>Procesando</option>
                        <option value="resuelto" {{ old('status', $report->status) == 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                    </select>
                    @if($isCompleted)
                        <p class="text-sm text-gray-500 mt-2">Informe completado. Solo un administrador puede modificarlo.</p>
                    @endif
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

    <script>
        // Subcategory population - used only for admin
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
</x-app-layout>