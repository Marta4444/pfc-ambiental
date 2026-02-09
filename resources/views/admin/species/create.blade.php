<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nueva Especie
            </h2>
            <a href="{{ route('admin.species.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.species.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Nombre científico --}}
                            <div class="md:col-span-2">
                                <label for="scientific_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre Científico <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="scientific_name" id="scientific_name" 
                                    value="{{ old('scientific_name') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 italic"
                                    placeholder="Ej: Lynx pardinus" required>
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
                                    value="{{ old('common_name') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Ej: Lince ibérico">
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
                                    <option value="Mamíferos" {{ old('taxon_group') == 'Mamíferos' ? 'selected' : '' }}>Mamíferos</option>
                                    <option value="Aves" {{ old('taxon_group') == 'Aves' ? 'selected' : '' }}>Aves</option>
                                    <option value="Reptiles" {{ old('taxon_group') == 'Reptiles' ? 'selected' : '' }}>Reptiles</option>
                                    <option value="Anfibios" {{ old('taxon_group') == 'Anfibios' ? 'selected' : '' }}>Anfibios</option>
                                    <option value="Peces" {{ old('taxon_group') == 'Peces' ? 'selected' : '' }}>Peces</option>
                                    <option value="Invertebrados" {{ old('taxon_group') == 'Invertebrados' ? 'selected' : '' }}>Invertebrados</option>
                                    <option value="Plantas" {{ old('taxon_group') == 'Plantas' ? 'selected' : '' }}>Plantas</option>
                                    <option value="Hongos" {{ old('taxon_group') == 'Hongos' ? 'selected' : '' }}>Hongos</option>
                                </select>
                                @error('taxon_group')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Taxonomía --}}
                            <div>
                                <label for="kingdom" class="block text-sm font-medium text-gray-700 mb-1">Reino</label>
                                <input type="text" name="kingdom" id="kingdom" value="{{ old('kingdom', 'Animalia') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="phylum" class="block text-sm font-medium text-gray-700 mb-1">Filo</label>
                                <input type="text" name="phylum" id="phylum" value="{{ old('phylum') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="class" class="block text-sm font-medium text-gray-700 mb-1">Clase</label>
                                <input type="text" name="class" id="class" value="{{ old('class') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="order" class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                                <input type="text" name="order" id="order" value="{{ old('order') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="family" class="block text-sm font-medium text-gray-700 mb-1">Familia</label>
                                <input type="text" name="family" id="family" value="{{ old('family') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="genus" class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                                <input type="text" name="genus" id="genus" value="{{ old('genus') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            {{-- Protección --}}
                            <div class="md:col-span-2 border-t pt-4 mt-2">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Estado de Protección</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="is_protected" id="is_protected" value="1"
                                            {{ old('is_protected') ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <label for="is_protected" class="ml-2 text-sm text-gray-700">Especie Protegida</label>
                                    </div>
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
                                    <option value="EX" {{ old('iucn_category') == 'EX' ? 'selected' : '' }}>EX - Extinto</option>
                                    <option value="EW" {{ old('iucn_category') == 'EW' ? 'selected' : '' }}>EW - Extinto en Estado Silvestre</option>
                                    <option value="CR" {{ old('iucn_category') == 'CR' ? 'selected' : '' }}>CR - En Peligro Crítico</option>
                                    <option value="EN" {{ old('iucn_category') == 'EN' ? 'selected' : '' }}>EN - En Peligro</option>
                                    <option value="VU" {{ old('iucn_category') == 'VU' ? 'selected' : '' }}>VU - Vulnerable</option>
                                    <option value="NT" {{ old('iucn_category') == 'NT' ? 'selected' : '' }}>NT - Casi Amenazado</option>
                                    <option value="LC" {{ old('iucn_category') == 'LC' ? 'selected' : '' }}>LC - Preocupación Menor</option>
                                    <option value="DD" {{ old('iucn_category') == 'DD' ? 'selected' : '' }}>DD - Datos Insuficientes</option>
                                    <option value="NE" {{ old('iucn_category') == 'NE' ? 'selected' : '' }}>NE - No Evaluado</option>
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
                                    <option value="LESRPE" {{ old('boe_status') == 'LESRPE' ? 'selected' : '' }}>LESRPE - Listado Especies Silvestres</option>
                                    <option value="EN" {{ old('boe_status') == 'EN' ? 'selected' : '' }}>EN - En Peligro de Extinción</option>
                                    <option value="VU" {{ old('boe_status') == 'VU' ? 'selected' : '' }}>VU - Vulnerable</option>
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
                                    <option value="I" {{ old('cites_appendix') == 'I' ? 'selected' : '' }}>Apéndice I</option>
                                    <option value="II" {{ old('cites_appendix') == 'II' ? 'selected' : '' }}>Apéndice II</option>
                                    <option value="III" {{ old('cites_appendix') == 'III' ? 'selected' : '' }}>Apéndice III</option>
                                </select>
                            </div>

                            {{-- Valor base --}}
                            <div>
                                <label for="base_value" class="block text-sm font-medium text-gray-700 mb-1">
                                    Valor Base (€)
                                </label>
                                <input type="number" name="base_value" id="base_value" 
                                    value="{{ old('base_value', '0') }}" step="0.01" min="0"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <a href="{{ route('admin.species.index') }}" 
                                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                                Cancelar
                            </a>
                            <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Crear Especie
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
