<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Buscar Especies en APIs
            </h2>
            <a href="{{ route('admin.species.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                ← Volver
            </a>
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

            {{-- Formulario de búsqueda --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Buscar Especie
                    </h3>
                    
                    <form action="{{ route('admin.species.search') }}" method="GET" class="mb-6">
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <input type="text" name="q" value="{{ request('q') }}"
                                    placeholder="Nombre científico o común (ej: Lynx pardinus, lince ibérico)"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required minlength="3">
                            </div>
                            <button type="submit" 
                                class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Buscar en GBIF
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">
                            Busca especies en la base de datos global GBIF. Los resultados incluirán información taxonómica completa.
                        </p>
                    </form>

                    {{-- Resultados --}}
                    @if(isset($results) && count($results) > 0)
                        <div class="border-t pt-4">
                            <h4 class="font-medium text-gray-800 mb-4">
                                Resultados de búsqueda para "{{ request('q') }}" ({{ count($results) }} encontrados)
                            </h4>
                            
                            <div class="space-y-4">
                                @foreach($results as $result)
                                    <div class="border rounded-lg p-4 hover:bg-gray-50 {{ $result['exists_locally'] ? 'bg-green-50 border-green-200' : '' }}">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h5 class="font-semibold italic text-lg text-gray-900">
                                                    {{ $result['scientificName'] ?? $result['canonicalName'] ?? 'Sin nombre' }}
                                                </h5>
                                                @if(isset($result['vernacularName']))
                                                    <p class="text-gray-600">{{ $result['vernacularName'] }}</p>
                                                @endif
                                                
                                                <div class="mt-2 flex flex-wrap gap-2 text-sm">
                                                    @if(isset($result['kingdom']))
                                                        <span class="bg-gray-100 px-2 py-0.5 rounded">{{ $result['kingdom'] }}</span>
                                                    @endif
                                                    @if(isset($result['phylum']))
                                                        <span class="bg-gray-100 px-2 py-0.5 rounded">{{ $result['phylum'] }}</span>
                                                    @endif
                                                    @if(isset($result['class']))
                                                        <span class="bg-gray-100 px-2 py-0.5 rounded">{{ $result['class'] }}</span>
                                                    @endif
                                                    @if(isset($result['order']))
                                                        <span class="bg-gray-100 px-2 py-0.5 rounded">{{ $result['order'] }}</span>
                                                    @endif
                                                    @if(isset($result['family']))
                                                        <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-medium">{{ $result['family'] }}</span>
                                                    @endif
                                                </div>

                                                <div class="mt-2 text-xs text-gray-500">
                                                    @if(isset($result['key']))
                                                        <span class="mr-3">GBIF Key: {{ $result['key'] }}</span>
                                                    @endif
                                                    @if(isset($result['rank']))
                                                        <span class="mr-3">Rango: {{ $result['rank'] }}</span>
                                                    @endif
                                                    @if(isset($result['status']))
                                                        <span>Estado: {{ $result['status'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="ml-4">
                                                @if($result['exists_locally'])
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Ya existe
                                                    </span>
                                                @else
                                                    <form action="{{ route('admin.species.search') }}" method="POST" class="inline">
                                                        @csrf
                                                        <input type="hidden" name="import" value="1">
                                                        <input type="hidden" name="gbif_key" value="{{ $result['key'] ?? '' }}">
                                                        <input type="hidden" name="scientific_name" value="{{ $result['scientificName'] ?? $result['canonicalName'] ?? '' }}">
                                                        <input type="hidden" name="common_name" value="{{ $result['vernacularName'] ?? '' }}">
                                                        <input type="hidden" name="kingdom" value="{{ $result['kingdom'] ?? '' }}">
                                                        <input type="hidden" name="phylum" value="{{ $result['phylum'] ?? '' }}">
                                                        <input type="hidden" name="class" value="{{ $result['class'] ?? '' }}">
                                                        <input type="hidden" name="order" value="{{ $result['order'] ?? '' }}">
                                                        <input type="hidden" name="family" value="{{ $result['family'] ?? '' }}">
                                                        <input type="hidden" name="genus" value="{{ $result['genus'] ?? '' }}">
                                                        <button type="submit" 
                                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                            Importar
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif(request('q'))
                        <div class="border-t pt-4">
                            <p class="text-center text-gray-500 py-8">
                                No se encontraron resultados para "{{ request('q') }}"
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info de APIs --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="font-medium text-blue-800 mb-3">Información sobre las APIs</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <h4 class="font-semibold text-blue-900">GBIF</h4>
                        <p class="text-blue-700">Base de datos global de biodiversidad. Proporciona datos taxonómicos, ocurrencias y distribución.</p>
                        <a href="https://www.gbif.org" target="_blank" class="text-blue-600 hover:underline text-xs">www.gbif.org</a>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-900">IUCN Red List</h4>
                        <p class="text-blue-700">Lista Roja de especies amenazadas. Proporciona categorías de conservación (CR, EN, VU, etc.).</p>
                        <a href="https://www.iucnredlist.org" target="_blank" class="text-blue-600 hover:underline text-xs">www.iucnredlist.org</a>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-900">CITES Species+</h4>
                        <p class="text-blue-700">Convención sobre comercio de especies amenazadas. Proporciona apéndices de protección.</p>
                        <a href="https://speciesplus.net" target="_blank" class="text-blue-600 hover:underline text-xs">speciesplus.net</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
