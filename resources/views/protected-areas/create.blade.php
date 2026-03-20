<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nueva Área Protegida
            </h2>
            <a href="{{ route('protected-areas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                ← Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('protected-areas.store') }}" method="POST">
                        @csrf

                        {{-- Información básica --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                Información Básica
                            </h3>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                                <div style="grid-column: span 2;">
                                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre *</label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="protection_type" class="block text-sm font-medium text-gray-700">Tipo de Protección *</label>
                                    <select name="protection_type" id="protection_type" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                        <option value="">Seleccionar...</option>
                                        @foreach($protectionTypes as $type)
                                            <option value="{{ $type }}" {{ old('protection_type') == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('protection_type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="designation" class="block text-sm font-medium text-gray-700">Designación</label>
                                    <input type="text" name="designation" id="designation" value="{{ old('designation') }}"
                                        placeholder="Ej: Red Natura 2000"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    @error('designation')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="region" class="block text-sm font-medium text-gray-700">Región / CCAA</label>
                                    <input type="text" name="region" id="region" value="{{ old('region') }}"
                                        placeholder="Ej: Andalucía"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    @error('region')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="area_km2" class="block text-sm font-medium text-gray-700">Superficie (km²)</label>
                                    <input type="number" name="area_km2" id="area_km2" value="{{ old('area_km2') }}" step="0.01" min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    @error('area_km2')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="established_year" class="block text-sm font-medium text-gray-700">Año de creación</label>
                                    <input type="number" name="established_year" id="established_year" value="{{ old('established_year') }}" 
                                        min="1800" max="{{ date('Y') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    @error('established_year')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div style="grid-column: span 2;">
                                    <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                                    <textarea name="description" id="description" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">{{ old('description') }}</textarea>
                                    @error('description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- Coordenadas (Bounding Box) --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                Límites Geográficos (Bounding Box)
                            </h3>
                            <p class="text-sm text-gray-500 mb-4">
                                Define el rectángulo que contiene el área protegida. Las coordenadas deben estar en formato decimal (ej: 36.9583 para latitud).
                            </p>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                                <div>
                                    <label for="lat_min" class="block text-sm font-medium text-gray-700">Latitud Mínima (Sur)</label>
                                    <input type="number" name="lat_min" id="lat_min" value="{{ old('lat_min') }}" step="0.0000001" min="-90" max="90"
                                        placeholder="36.7833"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    @error('lat_min')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="lat_max" class="block text-sm font-medium text-gray-700">Latitud Máxima (Norte)</label>
                                    <input type="number" name="lat_max" id="lat_max" value="{{ old('lat_max') }}" step="0.0000001" min="-90" max="90"
                                        placeholder="37.1333"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    @error('lat_max')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="long_min" class="block text-sm font-medium text-gray-700">Longitud Mínima (Oeste)</label>
                                    <input type="number" name="long_min" id="long_min" value="{{ old('long_min') }}" step="0.0000001" min="-180" max="180"
                                        placeholder="-6.5667"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    @error('long_min')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="long_max" class="block text-sm font-medium text-gray-700">Longitud Máxima (Este)</label>
                                    <input type="number" name="long_max" id="long_max" value="{{ old('long_max') }}" step="0.0000001" min="-180" max="180"
                                        placeholder="-6.1500"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    @error('long_max')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- Estado --}}
                        <div class="mb-8">
                            <div class="flex items-center">
                                <input type="hidden" name="active" value="0">
                                <input type="checkbox" name="active" id="active" value="1" {{ old('active', true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500">
                                <label for="active" class="ml-2 block text-sm text-gray-700">
                                    Área activa (se usará para verificar coordenadas)
                                </label>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                            <a href="{{ route('protected-areas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Guardar Área Protegida
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
