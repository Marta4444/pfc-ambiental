<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalles del Caso - {{ $report->ip }}
            </h2>
            <div class="flex gap-2">
                @if($canEdit)
                    <a href="{{ route('report-details.create', $report) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Añadir Detalles
                    </a>
                @endif
                <a href="{{ route('reports.show', $report) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                    Volver al Caso
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

            {{-- Aviso si no puede editar --}}
            @if(!$canEdit)
                <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm">
                        <strong>Modo solo lectura.</strong> Solo el agente asignado o un administrador pueden editar los detalles de este caso.
                    </span>
                </div>
            @endif

            {{-- Información del caso --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Caso</p>
                            <p class="text-lg text-gray-900">{{ $report->title }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Subcategoría</p>
                            <p class="text-lg text-gray-900">{{ $report->subcategory->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Grupos de Detalles</p>
                            <p class="text-lg text-gray-900">{{ $groupedDetails->count() }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Asignado a</p>
                            <p class="text-lg text-gray-900">
                                @if($report->assigned && $report->assignedTo)
                                    {{ $report->assignedTo->name }}
                                @else
                                    <span class="text-gray-400">Sin asignar</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lista de grupos de detalles --}}
            @if($groupedDetails->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay detalles registrados</h3>
                        <p class="mt-1 text-sm text-gray-500">Añade detalles como especies afectadas, residuos, etc.</p>
                        @if($canEdit)
                            <div class="mt-6">
                                <a href="{{ route('report-details.create', $report) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Añadir Primer Detalle
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($groupedDetails as $groupKey => $details)
                        @php
                            // Verificar si hay especie protegida en este grupo
                            $speciesDetail = $details->first(fn($d) => $d->species_id !== null);
                            $species = $speciesDetail?->species;
                            
                            // Verificar si hay área protegida
                            $areaDetail = $details->first(fn($d) => $d->protected_area_id !== null);
                            $protectedArea = $areaDetail?->protectedArea;
                        @endphp
                        
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            {{ ucfirst(str_replace('_', ' ', $groupKey)) }}
                                        </h3>
                                        <p class="text-sm text-gray-500">{{ $details->count() }} campos</p>
                                    </div>
                                    @if($canEdit)
                                        <div class="flex gap-2">
                                            <a href="{{ route('report-details.edit', [$report, $groupKey]) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Editar
                                            </a>
                                            <form action="{{ route('report-details.destroy', [$report, $groupKey]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este grupo de detalles?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>

                                {{-- Badge de especie protegida --}}
                                @if($species && $species->is_protected)
                                    <div class="mb-4 bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-lg p-3">
                                        <div class="flex items-center flex-wrap gap-2">
                                            <span class="text-red-700 font-semibold text-sm">⚠️ ESPECIE PROTEGIDA:</span>
                                            <span class="font-medium text-red-800">{{ $species->scientific_name }}</span>
                                            @if($species->common_name)
                                                <span class="text-red-600 text-sm">({{ $species->common_name }})</span>
                                            @endif
                                            @if($species->boe_status)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                    BOE: {{ $species->boe_status }}
                                                </span>
                                            @endif
                                            @if($species->iucn_category)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                                    IUCN: {{ $species->iucn_category }}
                                                </span>
                                            @endif
                                            @if($species->cites_appendix)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                    CITES: {{ $species->cites_appendix }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($species)
                                    <div class="mb-4 bg-gray-50 border border-gray-200 rounded-lg p-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-600 text-sm">Especie:</span>
                                            <span class="font-medium text-gray-800">{{ $species->scientific_name }}</span>
                                            @if($species->common_name)
                                                <span class="text-gray-500 text-sm">({{ $species->common_name }})</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Badge de área protegida --}}
                                @if($protectedArea)
                                    <div class="mb-4 bg-gradient-to-r from-green-50 to-teal-50 border border-green-200 rounded-lg p-3">
                                        <div class="flex items-center flex-wrap gap-2">
                                            <span class="text-green-700 font-semibold text-sm">🌿 ÁREA PROTEGIDA:</span>
                                            <span class="font-medium text-green-800">{{ $protectedArea->name }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                {{ $protectedArea->protection_type }}
                                            </span>
                                            @if($protectedArea->iucn_category)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-800">
                                                    IUCN: {{ $protectedArea->iucn_category }}
                                                </span>
                                            @endif
                                            @if($protectedArea->region)
                                                <span class="text-xs text-green-600">({{ $protectedArea->region }})</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Grid de campos --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($details as $detail)
                                        <div class="bg-gray-50 rounded p-3">
                                            <p class="text-xs font-medium text-gray-500 uppercase">
                                                @if(isset($fields) && isset($fields[$detail->field_key]))
                                                    {{ $fields[$detail->field_key]->label }}
                                                @else
                                                    {{ $detail->field?->label ?? ucfirst(str_replace('_', ' ', $detail->field_key)) }}
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-900 mt-1">
                                                {{ $detail->formatted_value ?: '-' }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>