<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Especie
            </h2>
            <a href="{{ route('species.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                ← Volver al Listado
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('species.update', $species) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Información básica --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Información Básica</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="scientific_name" class="block text-sm font-medium text-gray-700">
                                    Nombre Científico <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="scientific_name" id="scientific_name" 
                                    value="{{ old('scientific_name', $species->scientific_name) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 italic"
                                    required>
                                @error('scientific_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="common_name" class="block text-sm font-medium text-gray-700">
                                    Nombre Común
                                </label>
                                <input type="text" name="common_name" id="common_name" 
                                    value="{{ old('common_name', $species->common_name) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('common_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="taxon_group" class="block text-sm font-medium text-gray-700">
                                    Grupo Taxonómico
                                </label>
                                <select name="taxon_group" id="taxon_group" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione...</option>
                                    @foreach($taxonGroups as $group)
                                        <option value="{{ $group }}" {{ old('taxon_group', $species->taxon_group) == $group ? 'selected' : '' }}>
                                            {{ $group }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('taxon_group')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Protección Nacional --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Protección Nacional (BOE)
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="boe_status" class="block text-sm font-medium text-gray-700">
                                    Estado de Protección BOE
                                </label>
                                <select name="boe_status" id="boe_status" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Sin protección</option>
                                    @foreach($boeStatuses as $status)
                                        <option value="{{ $status }}" {{ old('boe_status', $species->boe_status) == $status ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('boe_status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="boe_law_ref" class="block text-sm font-medium text-gray-700">
                                    Referencia Legal
                                </label>
                                <input type="text" name="boe_law_ref" id="boe_law_ref" 
                                    value="{{ old('boe_law_ref', $species->boe_law_ref) }}"
                                    placeholder="Ej: Real Decreto 139/2011"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('boe_law_ref')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Protección Autonómica --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            Protección Autonómica
                        </h3>
                        
                        <div>
                            <label for="ccaa_status" class="block text-sm font-medium text-gray-700">
                                Estado en Comunidades Autónomas
                            </label>
                            <input type="text" name="ccaa_status" id="ccaa_status" 
                                value="{{ old('ccaa_status', is_array($species->ccaa_status) ? implode(', ', $species->ccaa_status) : $species->ccaa_status) }}"
                                placeholder="Ej: En peligro (Andalucía), Vulnerable (Castilla y León)"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Separe múltiples estados con comas</p>
                            @error('ccaa_status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Clasificaciones Internacionales --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Clasificaciones Internacionales</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="iucn_category" class="block text-sm font-medium text-gray-700">
                                    Categoría IUCN
                                </label>
                                <select name="iucn_category" id="iucn_category" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">No evaluada</option>
                                    @foreach($iucnCategories as $code => $label)
                                        <option value="{{ $code }}" {{ old('iucn_category', $species->iucn_category) == $code ? 'selected' : '' }}>
                                            {{ $code }} - {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('iucn_category')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="cites_appendix" class="block text-sm font-medium text-gray-700">
                                    Apéndice CITES
                                </label>
                                <select name="cites_appendix" id="cites_appendix" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">No incluida</option>
                                    @foreach($citesAppendices as $appendix)
                                        <option value="{{ $appendix }}" {{ old('cites_appendix', $species->cites_appendix) == $appendix ? 'selected' : '' }}>
                                            Apéndice {{ $appendix }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cites_appendix')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-4">
                    <a href="{{ route('species.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>