<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Especies
            </h2>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                ← Volver al Dashboard
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

            {{-- Filtros --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form action="{{ route('species.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Nombre científico o común..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Grupo Taxonómico</label>
                            <select name="taxon_group" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach($taxonGroups as $group)
                                    <option value="{{ $group }}" {{ request('taxon_group') == $group ? 'selected' : '' }}>
                                        {{ $group }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Protección</label>
                            <select name="is_protected" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todas</option>
                                <option value="true" {{ request('is_protected') == 'true' ? 'selected' : '' }}>Protegidas</option>
                                <option value="false" {{ request('is_protected') == 'false' ? 'selected' : '' }}>No protegidas</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Datos BOE</label>
                            <select name="has_boe" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todas</option>
                                <option value="true" {{ request('has_boe') == 'true' ? 'selected' : '' }}>Con datos BOE</option>
                                <option value="false" {{ request('has_boe') == 'false' ? 'selected' : '' }}>Sin datos BOE</option>
                            </select>
                        </div>
                        <div class="md:col-span-4 flex gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Filtrar
                            </button>
                            <a href="{{ route('species.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Estadísticas rápidas --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-gray-800">{{ \App\Models\Species::count() }}</p>
                    <p class="text-xs text-gray-500">Total especies</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-green-600">{{ \App\Models\Species::where('is_protected', true)->count() }}</p>
                    <p class="text-xs text-gray-500">Protegidas</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-blue-600">{{ \App\Models\Species::whereNotNull('boe_status')->where('boe_status', '!=', '')->count() }}</p>
                    <p class="text-xs text-gray-500">Con datos BOE</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-yellow-600">{{ \App\Models\Species::where(function($q) { $q->whereNull('boe_status')->orWhere('boe_status', ''); })->count() }}</p>
                    <p class="text-xs text-gray-500">Sin datos BOE</p>
                </div>
            </div>

            {{-- Tabla de especies --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Especie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grupo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BOE</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IUCN</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CITES</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($species as $sp)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 italic">{{ $sp->scientific_name }}</p>
                                            @if($sp->common_name)
                                                <p class="text-sm text-gray-500">{{ $sp->common_name }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600">{{ $sp->taxon_group ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($sp->boe_status)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                {{ $sp->boe_status }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">Sin datos</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($sp->iucn_category)
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
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $iucnColors[$sp->iucn_category] ?? 'bg-gray-100' }}">
                                                {{ $sp->iucn_category }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($sp->cites_appendix)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ $sp->cites_appendix }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($sp->is_protected)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Protegida
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                No protegida
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('species.admin.show', $sp) }}" class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('species.edit', $sp) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">No se encontraron especies con los filtros seleccionados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($species->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $species->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>