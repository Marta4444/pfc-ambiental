<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Crear nuevo informe</h2>
    </x-slot>

    <form method="POST" action="{{ route('reports.store') }}" class="py-6 px-4">
        @csrf

        <div class="mb-4">
            <x-input-label for="title" value="Título" />
            <x-text-input id="title" name="title" class="mt-1 block w-full" required />
        </div>

        <div class="mb-4">
            <x-input-label for="description" value="Descripción" />
            <textarea id="description" name="description" class="mt-1 block w-full border rounded p-2"></textarea>
        </div>

        <div class="mb-4">
            <x-input-label for="category_id" value="Categoría" />
            <select id="category_id" name="category_id" class="mt-1 block w-full border rounded p-2" required>
                <option value="">Selecciona una categoría</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <x-input-label for="subcategory_id" value="Subcategoría" />
            <select id="subcategory_id" name="subcategory_id" class="mt-1 block w-full border rounded p-2" required>
                <option value="">Selecciona primero una categoría</option>
            </select>
        </div>

        <div class="mb-4">
            <x-input-label for="location" value="Ubicación" />
            <x-text-input id="location" name="location" class="mt-1 block w-full" />
        </div>

        <div class="mb-4">
            <x-input-label for="date_damage" value="Fecha del daño" />
            <x-text-input type="date" id="date_damage" name="date_damage" class="mt-1 block w-full" required />
        </div>

        <div class="mb-4">
            <x-input-label for="affected_area" value="Área afectada (m²)" />
            <x-text-input type="number" step="0.01" id="affected_area" name="affected_area" class="mt-1 block w-full" />
        </div>

        <div class="mb-4">
            <x-input-label for="criticallity" value="Nivel de criticidad (1-5)" />
            <x-text-input type="number" min="1" max="5" id="criticallity" name="criticallity" class="mt-1 block w-full" />
        </div>

        <div class="mb-4">
            <x-input-label for="status" value="Estado" />
            <select id="status" name="status" class="mt-1 block w-full border rounded p-2">
                <option value="pendiente">Pendiente</option>
                <option value="en_proceso">En proceso</option>
                <option value="cerrado">Cerrado</option>
            </select>
        </div>

        <div class="mb-4">
            <x-input-label for="pdf_report" value="Adjuntar PDF" />
            <input type="file" id="pdf_report" name="pdf_report" class="mt-1 block w-full" accept=".pdf" />
        </div>

        <div>
            <x-primary-button>Guardar informe</x-primary-button>
        </div>
    </form>

    <!-- Script para cargar subcategorías según categoría seleccionada -->
    <script>
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');

        const subcategories = @json($subcategories); //es un array

        categorySelect.addEventListener('change', function() {
            const categoryId = parseInt(this.value);
            subcategorySelect.innerHTML = '<option value="">Selecciona una subcategoría</option>';

            subcategories.filter(s => s.category_id === categoryId)
                .forEach(s => {
                    const option = document.createElement('option');
                    option.value = s.id;
                    option.textContent = s.nombre;
                    subcategorySelect.appendChild(option);
                });
        });
    </script>
</x-app-layout>
