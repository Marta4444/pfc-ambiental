<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Verificar Coordenadas en Áreas Protegidas
            </h2>
            <a href="{{ route('protected-areas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                ← Volver al listado
            </a>
        </div>
    </x-slot>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Panel de búsqueda --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Verificar Coordenadas</h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; align-items: end;">
                        <div>
                            <label for="check_lat" class="block text-sm font-medium text-gray-700 mb-1">Latitud</label>
                            <input type="number" id="check_lat" step="0.0000001" min="-90" max="90" 
                                placeholder="Ej: 36.95"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label for="check_long" class="block text-sm font-medium text-gray-700 mb-1">Longitud</label>
                            <input type="number" id="check_long" step="0.0000001" min="-180" max="180" 
                                placeholder="Ej: -6.425"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <button type="button" id="btn_check" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest" style="background-color: #2563eb;">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Verificar
                            </button>
                        </div>
                    </div>

                    {{-- Resultado --}}
                    <div id="check_result" class="mt-4 hidden">
                        {{-- Se llenará con JavaScript --}}
                    </div>
                </div>
            </div>

            {{-- Mapa --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Mapa de Áreas Protegidas</h3>
                        <div class="text-sm text-gray-500">
                            <span class="inline-flex items-center">
                                <span class="w-4 h-4 bg-green-500 opacity-30 border border-green-600 mr-2"></span>
                                Áreas protegidas
                            </span>
                            <span class="ml-4 inline-flex items-center">
                                <svg class="w-4 h-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                Punto verificado
                            </span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">
                        Haz clic en el mapa para verificar si un punto está en área protegida. Las áreas protegidas se muestran en verde.
                    </p>
                    <div id="map" style="height: 500px; border-radius: 8px; border: 1px solid #e5e7eb;"></div>
                </div>
            </div>

            {{-- Lista de áreas --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Áreas Protegidas Activas ({{ $areas->count() }})</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($areas as $area)
                        <button type="button" 
                            class="area-btn text-left p-3 rounded-lg border border-gray-200 hover:bg-green-50 hover:border-green-300 transition-colors"
                            data-lat="{{ ($area->lat_min + $area->lat_max) / 2 }}"
                            data-long="{{ ($area->long_min + $area->long_max) / 2 }}"
                            data-name="{{ $area->name }}">
                            <div class="font-medium text-gray-900 text-sm">{{ $area->name }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $area->protection_type }}
                                @if($area->region) · {{ $area->region }} @endif
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        // Inicializar mapa centrado en España
        const map = L.map('map').setView([40.4168, -3.7038], 6);
        
        // Capa de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Marcador del punto verificado
        let marker = null;

        // Datos de áreas protegidas
        const areas = @json($areas);

        // Dibujar áreas protegidas como rectángulos
        areas.forEach(area => {
            if (area.lat_min && area.lat_max && area.long_min && area.long_max) {
                const bounds = [
                    [area.lat_min, area.long_min],
                    [area.lat_max, area.long_max]
                ];
                
                const rect = L.rectangle(bounds, {
                    color: '#059669',
                    weight: 2,
                    fillColor: '#10b981',
                    fillOpacity: 0.2
                }).addTo(map);
                
                rect.bindPopup(`
                    <div class="text-center">
                        <strong>${area.name}</strong><br>
                        <span class="text-xs">${area.protection_type}</span>
                        ${area.region ? `<br><span class="text-xs">${area.region}</span>` : ''}
                    </div>
                `);
            }
        });

        // Función para verificar coordenadas
        async function checkCoordinates(lat, long, addMarker = true) {
            if (isNaN(lat) || isNaN(long)) {
                showResult('error', 'Introduce coordenadas válidas');
                return;
            }

            if (lat < -90 || lat > 90 || long < -180 || long > 180) {
                showResult('error', 'Coordenadas fuera de rango');
                return;
            }

            // Añadir o mover marcador
            if (addMarker) {
                if (marker) {
                    marker.setLatLng([lat, long]);
                } else {
                    marker = L.marker([lat, long], {
                        icon: L.divIcon({
                            className: 'custom-marker',
                            html: `<svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>`,
                            iconSize: [32, 32],
                            iconAnchor: [16, 32]
                        })
                    }).addTo(map);
                }
                map.setView([lat, long], 10);
            }

            // Llamar a la API
            try {
                const response = await fetch('{{ route("protected-areas.check-coordinates") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ lat, long })
                });

                const data = await response.json();

                if (data.in_protected_area) {
                    let html = `<div class="font-medium text-green-800 mb-2">✓ Las coordenadas están en ${data.areas.length} área(s) protegida(s):</div>`;
                    html += '<div class="space-y-2">';
                    data.areas.forEach(area => {
                        html += `
                            <div class="flex flex-wrap gap-2 items-center">
                                <span class="font-medium">${area.name}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">${area.protection_type}</span>
                                ${area.region ? `<span class="text-xs text-gray-500">(${area.region})</span>` : ''}
                            </div>
                        `;
                    });
                    html += '</div>';
                    showResult('success', html);

                    // Actualizar popup del marcador
                    if (marker) {
                        marker.bindPopup(`
                            <strong class="text-green-700">🌿 Área Protegida</strong><br>
                            ${data.areas.map(a => a.name).join('<br>')}
                        `).openPopup();
                    }
                } else {
                    showResult('warning', `Las coordenadas (${lat.toFixed(5)}, ${long.toFixed(5)}) <strong>no están</strong> dentro de ningún área protegida registrada.`);
                    
                    if (marker) {
                        marker.bindPopup(`
                            <strong class="text-yellow-700">⚠ Sin protección</strong><br>
                            Este punto no está en área protegida.
                        `).openPopup();
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showResult('error', 'Error al verificar las coordenadas');
            }
        }

        // Mostrar resultado
        function showResult(type, message) {
            const resultDiv = document.getElementById('check_result');
            resultDiv.classList.remove('hidden');
            
            const colors = {
                success: 'bg-green-50 border-green-200 text-green-800',
                warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
                error: 'bg-red-50 border-red-200 text-red-800'
            };

            resultDiv.innerHTML = `
                <div class="p-4 rounded-lg border ${colors[type]}">
                    ${message}
                </div>
            `;
        }

        // Event: Click en botón verificar
        document.getElementById('btn_check').addEventListener('click', () => {
            const lat = parseFloat(document.getElementById('check_lat').value);
            const long = parseFloat(document.getElementById('check_long').value);
            checkCoordinates(lat, long);
        });

        // Event: Enter en inputs
        ['check_lat', 'check_long'].forEach(id => {
            document.getElementById(id).addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    document.getElementById('btn_check').click();
                }
            });
        });

        // Event: Click en mapa
        map.on('click', (e) => {
            const lat = e.latlng.lat;
            const long = e.latlng.lng;
            
            // Actualizar inputs
            document.getElementById('check_lat').value = lat.toFixed(6);
            document.getElementById('check_long').value = long.toFixed(6);
            
            checkCoordinates(lat, long);
        });

        // Event: Click en botones de área
        document.querySelectorAll('.area-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const lat = parseFloat(btn.dataset.lat);
                const long = parseFloat(btn.dataset.long);
                
                document.getElementById('check_lat').value = lat.toFixed(6);
                document.getElementById('check_long').value = long.toFixed(6);
                
                checkCoordinates(lat, long);
            });
        });
    </script>

    <style>
        .custom-marker {
            background: transparent;
            border: none;
        }
    </style>
</x-app-layout>
