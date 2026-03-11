<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Áreas Protegidas
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('protected-areas.check') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Verificar Coordenadas
                </a>
                <a href="{{ route('protected-areas.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva Área
                </a>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                    ← Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

            {{-- Filtros --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form action="{{ route('protected-areas.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Nombre o designación..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Protección</label>
                            <select name="protection_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Todos</option>
                                @foreach($protectionTypes as $type)
                                    <option value="{{ $type }}" {{ request('protection_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Región</label>
                            <select name="region" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Todas</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region }}" {{ request('region') == $region ? 'selected' : '' }}>
                                        {{ $region }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="active" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Todos</option>
                                <option value="true" {{ request('active') == 'true' ? 'selected' : '' }}>Activas</option>
                                <option value="false" {{ request('active') == 'false' ? 'selected' : '' }}>Inactivas</option>
                            </select>
                        </div>
                        <div style="grid-column: span 4; display: flex; gap: 8px;">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Filtrar
                            </button>
                            <a href="{{ route('protected-areas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Estadísticas rápidas --}}
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-gray-800">{{ \App\Models\ProtectedArea::count() }}</p>
                    <p class="text-xs text-gray-500">Total áreas</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-green-600">{{ \App\Models\ProtectedArea::where('active', true)->count() }}</p>
                    <p class="text-xs text-gray-500">Activas</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-eco-600">{{ \App\Models\ProtectedArea::where('protection_type', 'Parque Nacional')->count() }}</p>
                    <p class="text-xs text-gray-500">Parques Nacionales</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-teal-600">{{ number_format(\App\Models\ProtectedArea::sum('area_km2'), 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500">km² protegidos</p>
                </div>
            </div>

            {{-- Tabla de áreas --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IUCN</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Región</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Área (km²)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($areas as $area)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $area->name }}</div>
                                        @if($area->designation)
                                            <div class="text-xs text-gray-500">{{ $area->designation }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $area->protection_type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        @if($area->iucn_category)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-800" title="{{ \App\Models\ProtectedArea::IUCN_CATEGORIES[$area->iucn_category] ?? '' }}">
                                                {{ $area->iucn_category }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ $area->region ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ $area->area_km2 ? number_format($area->area_km2, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($area->active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Activa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                Inactiva
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('protected-areas.edit', $area) }}" class="text-eco-600 hover:text-blue-900" title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('protected-areas.destroy', $area) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta área protegida?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        No se encontraron áreas protegidas.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    <div class="mt-4">
                        {{ $areas->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
