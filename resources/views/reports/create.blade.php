
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Crear nuevo informe</h2>
    </x-slot>

    <form method="POST" action="{{ route('reports.store') }}" class="py-6 px-4" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <x-input-label for="title" value="Título" />
            <x-text-input id="title" name="title" class="mt-1 block w-full" required value="{{ old('title') }}"/>
            @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <x-input-label for="description" value="Descripción" />
            <textarea id="description" name="description" class="mt-1 block w-full border rounded p-2">{{ old('description') }}</textarea>
        </div>

        <div class="mb-4">
            <x-input-label for="category_id" value="Categoría" />
            <select id="category_id" name="category_id" class="mt-1 block w-full border rounded p-2" required>
                <option value="">Selecciona una categoría</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <x-input-label for="subcategory_id" value="Subcategoría" />
            <select id="subcategory_id" name="subcategory_id" class="mt-1 block w-full border rounded p-2" required>
                <option value="">Selecciona primero una categoría</option>
            </select>
            @error('subcategory_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <x-input-label for="location" value="Ubicación" />
            <x-text-input id="location" name="location" class="mt-1 block w-full" value="{{ old('location') }}" />
        </div>

        <div class="mb-4">
            <x-input-label for="coordinates" value="Coordenadas (lat,lon)" />
            <x-text-input id="coordinates" name="coordinates" placeholder="41.12345,-1.23456" class="mt-1 block w-full" value="{{ old('coordinates') }}" />
        </div>

        <div class="mb-4">
            <x-input-label for="date_damage" value="Fecha del daño" />
            <x-text-input type="date" id="date_damage" name="date_damage" class="mt-1 block w-full" required value="{{ old('date_damage') }}" />
            @error('date_damage')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <x-input-label for="affected_area" value="Área afectada (m²)" />
            <x-text-input type="number" step="0.01" id="affected_area" name="affected_area" class="mt-1 block w-full" value="{{ old('affected_area') }}" />
        </div>

        <div class="mb-4">
            <x-input-label for="criticallity" value="Nivel de criticidad (1-5)" />
            <x-text-input type="number" min="1" max="5" id="criticallity" name="criticallity" class="mt-1 block w-full" value="{{ old('criticallity', 1) }}" />
        </div>

        <div class="mb-4">
            <x-input-label for="status" value="Estado" />
            <select id="status" name="status" class="mt-1 block w-full border rounded p-2">
                <option value="pendiente" {{ old('status')=='pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="en_proceso" {{ old('status')=='en_proceso' ? 'selected' : '' }}>En proceso</option>
                <option value="cerrado" {{ old('status')=='cerrado' ? 'selected' : '' }}>Cerrado</option>
            </select>
        </div>

        <div class="mb-4">
            <x-input-label for="pdf_report" value="Adjuntar PDF" />
            <input type="file" id="pdf_report" name="pdf_report" class="mt-1 block w-full" accept=".pdf" />
            @error('pdf_report')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-primary-button>Guardar informe</x-primary-button>
        </div>
    </form>

    <!-- Script para cargar subcategorías según categoría seleccionada -->
    <script>
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');

        // JSON con campos mínimos para JS (asegura 'name' y 'category_id')
        const subcategories = @json($subcategories->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'category_id' => $s->category_id
        ]));

        function populateSubcategories(categoryId, selectedId = null) {
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

        categorySelect.addEventListener('change', function() {
            populateSubcategories(this.value);
        });

        // Si hay valores viejos en el formulario (error de validación), restaurarlos
        document.addEventListener('DOMContentLoaded', function() {
            const oldCategory = "{{ old('category_id') }}";
            const oldSubcategory = "{{ old('subcategory_id') }}";
            if (oldCategory) {
                categorySelect.value = oldCategory;
                populateSubcategories(oldCategory, oldSubcategory ? parseInt(oldSubcategory) : null);
            }
        });
    </script>
</x-app-layout>