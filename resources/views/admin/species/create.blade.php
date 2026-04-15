<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nueva Especie
        </h2>
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
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500 italic"
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
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500"
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
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500">
                                    <option value="">Seleccionar...</option>
                                    <option value="Mamíferos" @selected(old('taxon_group') == 'Mamíferos')>Mamíferos</option>
                                    <option value="Aves" @selected(old('taxon_group') == 'Aves')>Aves</option>
                                    <option value="Reptiles" @selected(old('taxon_group') == 'Reptiles')>Reptiles</option>
                                    <option value="Anfibios" @selected(old('taxon_group') == 'Anfibios')>Anfibios</option>
                                    <option value="Peces" @selected(old('taxon_group') == 'Peces')>Peces</option>
                                    <option value="Invertebrados" @selected(old('taxon_group') == 'Invertebrados')>Invertebrados</option>
                                    <option value="Plantas" @selected(old('taxon_group') == 'Plantas')>Plantas</option>
                                    <option value="Hongos" @selected(old('taxon_group') == 'Hongos')>Hongos</option>
                                </select>
                                @error('taxon_group')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Taxonomía --}}
                            <div>
                                <label for="kingdom" class="block text-sm font-medium text-gray-700 mb-1">Reino</label>
                                <input type="text" name="kingdom" id="kingdom" value="{{ old('kingdom', 'Animalia') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500">
                                @error('kingdom')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="phylum" class="block text-sm font-medium text-gray-700 mb-1">Filo</label>
                                <input type="text" name="phylum" id="phylum" value="{{ old('phylum') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500">
                                @error('phylum')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="class" class="block text-sm font-medium text-gray-700 mb-1">Clase</label>
                                <input type="text" name="class" id="class" value="{{ old('class') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500">
                                @error('class')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="order" class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                                <input type="text" name="order" id="order" value="{{ old('order') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500">
                                @error('order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="family" class="block text-sm font-medium text-gray-700 mb-1">Familia</label>
                                <input type="text" name="family" id="family" value="{{ old('family') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500">
                                @error('family')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="genus" class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                                <input type="text" name="genus" id="genus" value="{{ old('genus') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500">
                                @error('genus')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            {{-- Categoría IUCN --}}
                            <div>
                                <label for="iucn_category" class="block text-sm font-medium text-gray-700 mb-1">
                                    Categoría IUCN
                                </label>
                                <select name="iucn_category" id="iucn_category"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500">
                                    <option value="">Sin categoría</option>
                                    <option value="EX" @selected(old('iucn_category') == 'EX')>EX - Extinto</option>
                                    <option value="EW" @selected(old('iucn_category') == 'EW')>EW - Extinto en Estado Silvestre</option>
                                    <option value="CR" @selected(old('iucn_category') == 'CR')>CR - En Peligro Crítico</option>
                                    <option value="EN" @selected(old('iucn_category') == 'EN')>EN - En Peligro</option>
                                    <option value="VU" @selected(old('iucn_category') == 'VU')>VU - Vulnerable</option>
                                    <option value="NT" @selected(old('iucn_category') == 'NT')>NT - Casi Amenazado</option>
                                    <option value="LC" @selected(old('iucn_category') == 'LC')>LC - Preocupación Menor</option>
                                    <option value="DD" @selected(old('iucn_category') == 'DD')>DD - Datos Insuficientes</option>
                                    <option value="NE" @selected(old('iucn_category') == 'NE')>NE - No Evaluado</option>
                                </select>
                                @error('iucn_category')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            {{-- Estado BOE --}}
                            <div>
                                <label for="boe_status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Estado BOE/LESPRE
                                </label>
                                <select name="boe_status" id="boe_status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500">
                                    <option value="">Sin categoría BOE</option>
                                    <option value="LESRPE" @selected(old('boe_status') == 'LESRPE')>LESRPE - Listado Especies Silvestres</option>
                                    <option value="EN" @selected(old('boe_status') == 'EN')>EN - En Peligro de Extinción</option>
                                    <option value="VU" @selected(old('boe_status') == 'VU')>VU - Vulnerable</option>
                                </select>
                                @error('boe_status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            {{-- Apéndice CITES --}}
                            <div>
                                <label for="cites_appendix" class="block text-sm font-medium text-gray-700 mb-1">
                                    Apéndice CITES
                                </label>
                                <select name="cites_appendix" id="cites_appendix"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500">
                                    <option value="">No incluida</option>
                                    <option value="I" @selected(old('cites_appendix') == 'I')>Apéndice I</option>
                                    <option value="II" @selected(old('cites_appendix') == 'II')>Apéndice II</option>
                                    <option value="III" @selected(old('cites_appendix') == 'III')>Apéndice III</option>
                                </select>
                                @error('cites_appendix')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            {{-- Valor base --}}
                            <div>
                                <label for="base_value" class="block text-sm font-medium text-gray-700 mb-1">
                                    Valor Base (€)
                                </label>
                                <input type="number" name="base_value" id="base_value" 
                                    value="{{ old('base_value', '0') }}" step="0.01" min="0"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500">
                                @error('base_value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <a href="{{ route('admin.species.index') }}" 
                                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                                Cancelar
                            </a>
                            <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-eco-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-eco-700">
                                Crear Especie
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
