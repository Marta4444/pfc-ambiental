<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Especie: <span class="italic">{{ $species->scientific_name }}</span>
            </h2>
            <a href="{{ route('admin.species.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Info de sincronización --}}
            @if($species->sync_source || $species->last_synced_at)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-medium text-blue-800 mb-2">Información de Sincronización</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    @if($species->sync_source)
                        <div>
                            <span class="text-blue-600">Fuente:</span>
                            <span class="font-medium">{{ strtoupper($species->sync_source) }}</span>
                        </div>
                    @endif
                    @if($species->gbif_key)
                        <div>
                            <span class="text-blue-600">GBIF Key:</span>
                            <a href="https://www.gbif.org/species/{{ $species->gbif_key }}" target="_blank" class="font-medium hover:underline">{{ $species->gbif_key }}</a>
                        </div>
                    @endif
                    @if($species->iucn_taxon_id)
                        <div>
                            <span class="text-blue-600">IUCN ID:</span>
                            <span class="font-medium">{{ $species->iucn_taxon_id }}</span>
                        </div>
                    @endif
                    @if($species->last_synced_at)
                        <div>
                            <span class="text-blue-600">Última sync:</span>
                            <span class="font-medium">{{ $species->last_synced_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
                @if($species->sync_error)
                    <div class="mt-2 text-red-600 text-sm">
                        <strong>Error:</strong> {{ $species->sync_error }}
                    </div>
                @endif
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.species.update', $species) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Nombre científico --}}
                            <div class="md:col-span-2">
                                <label for="scientific_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre Científico <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="scientific_name" id="scientific_name" 
                                    value="{{ old('scientific_name', $species->scientific_name) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 italic"
                                    required>
                                @error('scientific_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Nombre común --}}
                            <div>
                                <label for="common_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre Común
                                </label>
                                <input type="text" name="common_name" id="common_name" 
                                    value="{{ old('common_name', $species->common_name) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('common_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Grupo taxonómico --}}
                            <div>
                                <label for="taxon_group" class="block text-sm font-medium text-gray-700 mb-1">
                                    Grupo Taxonómico
                                </label>
                                <select name="taxon_group" id="taxon_group" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccionar...</option>
                                    @foreach($taxonGroups as $group)
                                        <option value="{{ $group }}" {{ old('taxon_group', $species->taxon_group) == $group ? 'selected' : '' }}>{{ $group }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Taxonomía --}}
                            <div>
                                <label for="kingdom" class="block text-sm font-medium text-gray-700 mb-1">Reino</label>
                                <input type="text" name="kingdom" id="kingdom" value="{{ old('kingdom', $species->kingdom) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="phylum" class="block text-sm font-medium text-gray-700 mb-1">Filo</label>
                                <input type="text" name="phylum" id="phylum" value="{{ old('phylum', $species->phylum) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="class" class="block text-sm font-medium text-gray-700 mb-1">Clase</label>
                                <input type="text" name="class" id="class" value="{{ old('class', $species->class) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="order" class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                                <input type="text" name="order" id="order" value="{{ old('order', $species->order) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="family" class="block text-sm font-medium text-gray-700 mb-1">Familia</label>
                                <input type="text" name="family" id="family" value="{{ old('family', $species->family) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="genus" class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                                <input type="text" name="genus" id="genus" value="{{ old('genus', $species->genus) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            {{-- Protección --}}
                            <div class="md:col-span-2 border-t pt-4 mt-2">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Estado de Protección</h4>
                                <div class="flex items-center">
                                    <input type="checkbox" name="is_protected" id="is_protected" value="1"
                                        {{ old('is_protected', $species->is_protected) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <label for="is_protected" class="ml-2 text-sm text-gray-700">Especie Protegida</label>
                                </div>
                            </div>

                            {{-- Categoría IUCN --}}
                            <div>
                                <label for="iucn_category" class="block text-sm font-medium text-gray-700 mb-1">
                                    Categoría IUCN
                                </label>
                                <select name="iucn_category" id="iucn_category"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Sin categoría</option>
                                    @foreach($iucnCategories as $code => $label)
                                        <option value="{{ $code }}" {{ old('iucn_category', $species->iucn_category) == $code ? 'selected' : '' }}>{{ $code }} - {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Estado BOE --}}
                            <div>
                                <label for="boe_status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Estado BOE/LESPRE
                                </label>
                                <select name="boe_status" id="boe_status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Sin categoría BOE</option>
                                    @foreach($boeStatuses as $key => $label)
                                        <option value="{{ $key }}" {{ old('boe_status', $species->boe_status) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Estado CCAA --}}
                            <div>
                                <label for="ccaa_status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Estado CCAA
                                </label>
                                <select name="ccaa_status" id="ccaa_status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Sin protección autonómica</option>
                                    @foreach($ccaaStatuses as $key => $label)
                                        <option value="{{ $key }}" {{ old('ccaa_status', $species->ccaa_status) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Apéndice CITES --}}
                            <div>
                                <label for="cites_appendix" class="block text-sm font-medium text-gray-700 mb-1">
                                    Apéndice CITES
                                </label>
                                <select name="cites_appendix" id="cites_appendix"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">No incluida</option>
                                    @foreach($citesAppendices as $code => $label)
                                        <option value="{{ $code }}" {{ old('cites_appendix', $species->cites_appendix) == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Valor base --}}
                            <div>
                                <label for="base_value" class="block text-sm font-medium text-gray-700 mb-1">
                                    Valor Base (€)
                                </label>
                                <input type="number" name="base_value" id="base_value" 
                                    value="{{ old('base_value', $species->base_value) }}" step="0.01" min="0"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <form action="{{ route('admin.species.sync', $species) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Sincronizar desde APIs
                                </button>
                            </form>

                            <div class="flex gap-3">
                                <a href="{{ route('admin.species.index') }}" 
                                    class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                                    Cancelar
                                </a>
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
