<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Casos') }}
            </h2>
            <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Crear Caso') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Panel de Filtros Avanzados --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Filtros de Búsqueda</h3>
                        <button type="button" id="toggleFilters" class="text-sm text-blue-600 hover:text-blue-800">
                            Mostrar/Ocultar filtros avanzados
                        </button>
                    </div>

                    <form method="GET" action="{{ route('reports.index') }}" id="filterForm">
                        {{-- Búsqueda rápida --}}
                        <div class="mb-4">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Búsqueda rápida (IP, título, localidad, antecedentes)
                            </label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Buscar...">
                        </div>

                        {{-- Filtros avanzados (colapsables) --}}
                        <div id="advancedFilters" class="hidden">
                            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                {{-- Filtro por Autor --}}
                                <div>
                                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Autor</label>
                                    <select name="user_id" id="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Todos los autores</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->agent_num }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro por Agente Asignado --}}
                                <div>
                                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Asignado a</label>
                                    <select name="assigned_to" id="assigned_to" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Todos</option>
                                        <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Sin asignar</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro por Estado --}}
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                    <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Todos los estados</option>
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro por Urgencia --}}
                                <div>
                                    <label for="urgency" class="block text-sm font-medium text-gray-700 mb-1">Urgencia</label>
                                    <select name="urgency" id="urgency" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Todas las urgencias</option>
                                        @foreach($urgencies as $value => $label)
                                            <option value="{{ $value }}" {{ request('urgency') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro por Categoría --}}
                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                                    <select name="category_id" id="category_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Todas</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro por Subcategoría --}}
                                <div>
                                    <label for="subcategory_id" class="block text-sm font-medium text-gray-700 mb-1">Subcategoría</label>
                                    <select name="subcategory_id" id="subcategory_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Todas</option>
                                        @foreach($subcategories as $subcategory)
                                            <option value="{{ $subcategory->id }}" {{ request('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                                {{ $subcategory->name }} ({{ $subcategory->category->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro por Peticionario --}}
                                <div>
                                    <label for="petitioner_id" class="block text-sm font-medium text-gray-700 mb-1">Peticionario</label>
                                    <select name="petitioner_id" id="petitioner_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Todos</option>
                                        @foreach($petitioners as $petitioner)
                                            <option value="{{ $petitioner->id }}" {{ request('petitioner_id') == $petitioner->id ? 'selected' : '' }}>
                                                {{ $petitioner->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro por Comunidad --}}
                                <div>
                                    <label for="community" class="block text-sm font-medium text-gray-700 mb-1">Comunidad Autónoma</label>
                                    <select name="community" id="community" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Todas</option>
                                        @foreach($communities as $community)
                                            <option value="{{ $community }}" {{ request('community') === $community ? 'selected' : '' }}>
                                                {{ $community }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro por Provincia --}}
                                <div>
                                    <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                                    <select name="province" id="province" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Todas</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province }}" {{ request('province') === $province ? 'selected' : '' }}>
                                                {{ $province }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro por Fecha Desde --}}
                                <div>
                                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Fecha petición desde</label>
                                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                {{-- Filtro por Fecha Hasta --}}
                                <div>
                                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Fecha petición hasta</label>
                                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="mt-4 flex justify-end space-x-2">
                            <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Limpiar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                        </svg>
                                        Aplicar filtros
                                    </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Información de resultados --}}
            @if(request()->hasAny(['search', 'status', 'category_id', 'urgency', 'user_id', 'assigned_to', 'petitioner_id', 'community', 'province', 'date_from', 'date_to', 'subcategory_id']))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">
                            Mostrando {{ $reports->total() }} resultado(s) de búsqueda
                        </span>
                        <a href="{{ route('reports.index') }}" class="text-sm underline hover:no-underline">
                            Ver todos los casos
                        </a>
                    </div>
                </div>
            @endif

            {{-- Lista de casos --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($reports->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay casos registrados</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(request()->hasAny(['search', 'status', 'category_id', 'urgency', 'user_id', 'assigned_to']))
                                    No se encontraron casos con los filtros aplicados.
                                @else
                                    Comienza creando un nuevo caso.
                                @endif
                            </p>
                            <div class="mt-6">
                                @if(request()->hasAny(['search', 'status', 'category_id', 'urgency', 'user_id', 'assigned_to']))
                                    <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                        Ver todos los casos
                                    </a>
                                @endif
                                <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Crear Primer Caso
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Autor</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urgencia</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignado</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reports as $report)
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('reports.show', $report) }}" class="text-sm font-medium text-blue-600 hover:text-blue-900 hover:underline">
                                                    {{ $report->ip }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 font-medium">{{ Str::limit($report->title, 40) }}</div>
                                                <div class="text-xs text-gray-500">{{ $report->locality }}, {{ $report->province }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $report->user->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $report->user->agent_num }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $report->category->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $report->subcategory->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    bg-{{ $report->getStatusColor() }}-100 text-{{ $report->getStatusColor() }}-800">
                                                    {{ $report->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    bg-{{ $report->getUrgencyColor() }}-100 text-{{ $report->getUrgencyColor() }}-800">
                                                    {{ $report->getUrgencyLabel() }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($report->assigned && $report->assignedTo)
                                                    <div class="flex items-center text-gray-700">
                                                        <svg class="h-4 w-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span class="text-xs">{{ $report->assignedTo->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-xs">Sin asignar</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div>{{ $report->created_at->format('d/m/Y') }}</div>
                                                <div class="text-xs text-gray-400">{{ $report->created_at->format('H:i') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                <a href="{{ route('reports.show', $report) }}" class="text-blue-600 hover:text-blue-900" title="Ver detalles">Ver</a>
                                                
                                                @if(Auth::user()->role === 'admin' || $report->user_id === Auth::id() || $report->assigned_to === Auth::id())
                                                    <a href="{{ route('reports.edit', $report) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar caso">Editar</a>
                                                @endif

                                                @if(Auth::user()->role === 'admin')
                                                    <form action="{{ route('reports.destroy', $report) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este caso? Esta acción no se puede deshacer.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar caso">Eliminar</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $reports->withQueryString()->links() }}
                        </div>

                        {{-- Resumen de estadísticas --}}
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                <div>
                                    <p class="text-2xl font-bold text-gray-800">{{ $reports->total() }}</p>
                                    <p class="text-xs text-gray-500 uppercase">Total casos</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-blue-600">{{ \App\Models\Report::where('status', 'nuevo')->count() }}</p>
                                    <p class="text-xs text-gray-500 uppercase">Nuevos</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-yellow-600">{{ \App\Models\Report::where('status', 'en_proceso')->count() }}</p>
                                    <p class="text-xs text-gray-500 uppercase">En proceso</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-green-600">{{ \App\Models\Report::where('status', 'completado')->count() }}</p>
                                    <p class="text-xs text-gray-500 uppercase">Completados</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('toggleFilters');
            const advancedFilters = document.getElementById('advancedFilters');
            
            // Mostrar filtros si hay alguno activo
            const hasActiveFilters = {{ request()->hasAny(['user_id', 'assigned_to', 'category_id', 'subcategory_id', 'petitioner_id', 'community', 'province', 'date_from', 'date_to']) ? 'true' : 'false' }};
            
            if (hasActiveFilters) {
                advancedFilters.classList.remove('hidden');
            }
            
            toggleButton.addEventListener('click', function() {
                advancedFilters.classList.toggle('hidden');
            });
        });
    </script>
    @endpush
</x-app-layout>