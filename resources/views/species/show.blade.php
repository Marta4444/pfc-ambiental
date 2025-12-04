<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalle de Especie
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('species.edit', $species) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('species.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                    ← Volver al Listado
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Información principal --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 italic">{{ $species->scientific_name }}</h3>
                            @if($species->common_name)
                                <p class="text-lg text-gray-600 mt-1">{{ $species->common_name }}</p>
                            @endif
                            @if($species->taxon_group)
                                <p class="text-sm text-gray-500 mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $species->taxon_group }}
                                    </span>
                                </p>
                            @endif
                        </div>
                        <div>
                            @if($species->is_protected)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Especie Protegida
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                                    No protegida
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datos de protección --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Protección BOE --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Protección Nacional (BOE)
                        </h4>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estado de protección</dt>
                                <dd class="mt-1">
                                    @if($species->boe_status)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-sm font-medium bg-red-100 text-red-800">
                                            {{ $species->boe_status }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400 italic">Sin datos</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Referencia legal</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $species->boe_law_ref ?? 'No especificada' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Protección Autonómica --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Protección Autonómica (CCAA)
                        </h4>
                        <dl>
                            <dt class="text-sm font-medium text-gray-500">Estado en comunidades autónomas</dt>
                            <dd class="mt-1">
                                @if($species->ccaa_status)
                                    @if(is_array($species->ccaa_status))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($species->ccaa_status as $status)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $status }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-sm font-medium bg-blue-100 text-blue-800">
                                            {{ $species->ccaa_status }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-sm text-gray-400 italic">Sin datos</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                {{-- IUCN --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Clasificación IUCN
                        </h4>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                                <dd class="mt-1">
                                    @if($species->iucn_category)
                                        @php
                                            $iucnColors = [
                                                'CR' => 'bg-red-600 text-white',
                                                'EN' => 'bg-orange-500 text-white',
                                                'VU' => 'bg-yellow-500 text-black',
                                                'NT' => 'bg-blue-400 text-white',
                                                'LC' => 'bg-green-500 text-white',
                                                'DD' => 'bg-gray-400 text-white',
                                                'NE' => 'bg-gray-300 text-gray-700',
                                            ];
                                            $iucnLabels = \App\Models\Species::IUCN_CATEGORIES;
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded text-sm font-medium {{ $iucnColors[$species->iucn_category] ?? 'bg-gray-100' }}">
                                            {{ $species->iucn_category }} - {{ $iucnLabels[$species->iucn_category] ?? '' }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400 italic">No evaluada</span>
                                    @endif
                                </dd>
                            </div>
                            @if($species->iucn_assessment_year)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Año de evaluación</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $species->iucn_assessment_year }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- CITES --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            CITES
                        </h4>
                        <dl>
                            <dt class="text-sm font-medium text-gray-500">Apéndice</dt>
                            <dd class="mt-1">
                                @if($species->cites_appendix)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-sm font-medium bg-purple-100 text-purple-800">
                                        {{ $species->cites_appendix }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400 italic">No incluida en CITES</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Metadatos --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Información adicional</h4>
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Añadida manualmente</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $species->manually_added ? 'Sí' : 'No' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Última sincronización</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $species->synced_at ? $species->synced_at->format('d/m/Y H:i') : 'Nunca' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Actualizado</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $species->updated_at->format('d/m/Y H:i') }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>