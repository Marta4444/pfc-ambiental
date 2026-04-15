<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $area->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('protected-areas.edit', $area) }}" class="inline-flex items-center px-4 py-2 bg-eco-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-eco-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('protected-areas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                    ← Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Estado --}}
            <div class="flex items-center gap-3">
                @if($area->active)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Activa
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        Inactiva
                    </span>
                @endif
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    {{ $area->protection_type }}
                </span>
                @if($area->iucn_category)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-teal-100 text-teal-800">
                        IUCN {{ $area->iucn_category }}{{ $iucnLabel ? ' — ' . $iucnLabel : '' }}
                    </span>
                @endif
            </div>

            {{-- Información general --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Información general</h3>
                </div>
                <div class="p-6">
                    <dl style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $area->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Designación</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $area->designation ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Región</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $area->region ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Superficie</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $area->area_km2 ? number_format($area->area_km2, 2, ',', '.') . ' km²' : '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Año de establecimiento</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $area->established_year ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tipo de protección</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $area->protection_type }}</dd>
                        </div>
                        @if($area->description)
                        <div style="grid-column: span 2;">
                            <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $area->description }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Coordenadas (bounding box) --}}
            @if($area->lat_min || $area->lat_max || $area->long_min || $area->long_max)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Coordenadas</h3>
                </div>
                <div class="p-6">
                    <dl style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Latitud mínima</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $area->lat_min ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Latitud máxima</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $area->lat_max ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Longitud mínima</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $area->long_min ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Longitud máxima</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $area->long_max ?? '—' }}</dd>
                        </div>
                    </dl>

                    {{-- Mapa con bounding box --}}
                    @if($area->lat_min && $area->lat_max && $area->long_min && $area->long_max)
                    <div class="mt-6">
                        <div id="map" style="height: 350px; border-radius: 8px; border: 1px solid #e5e7eb;"></div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Acciones --}}
            <div class="flex justify-between items-center">
                <form action="{{ route('protected-areas.destroy', $area) }}" method="POST"
                      onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta área protegida?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Eliminar área
                    </button>
                </form>
                <a href="{{ route('protected-areas.edit', $area) }}" class="inline-flex items-center px-4 py-2 bg-eco-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-eco-700">
                    Editar área →
                </a>
            </div>
        </div>
    </div>

    @if($area->lat_min && $area->lat_max && $area->long_min && $area->long_max)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const latMin = {{ $area->lat_min }};
            const latMax = {{ $area->lat_max }};
            const longMin = {{ $area->long_min }};
            const longMax = {{ $area->long_max }};

            const centerLat = (latMin + latMax) / 2;
            const centerLng = (longMin + longMax) / 2;

            const map = L.map('map').setView([centerLat, centerLng], 8);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const bounds = [[latMin, longMin], [latMax, longMax]];
            L.rectangle(bounds, {
                color: '#16a34a',
                weight: 2,
                fillColor: '#16a34a',
                fillOpacity: 0.15
            }).addTo(map);

            map.fitBounds(bounds, { padding: [30, 30] });
        });
    </script>
    @endif
</x-app-layout>
