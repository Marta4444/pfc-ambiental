<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Logs de Sincronización
            </h2>
            <a href="{{ route('admin.species.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Resumen --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-green-600">{{ $species->where('sync_status', 'synced')->count() }}</p>
                    <p class="text-xs text-gray-500">Sincronizados OK</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-red-600">{{ $species->where('sync_status', 'error')->count() }}</p>
                    <p class="text-xs text-gray-500">Con Errores</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-yellow-600">{{ $species->whereNull('sync_status')->count() }}</p>
                    <p class="text-xs text-gray-500">Sin Sincronizar</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-blue-600">{{ $species->whereNotNull('last_synced_at')->count() }}</p>
                    <p class="text-xs text-gray-500">Con Historial</p>
                </div>
            </div>

            {{-- Errores recientes --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-red-800 mb-4">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Errores de Sincronización
                    </h3>

                    @php
                        $errored = $species->where('sync_status', 'error')->take(20);
                    @endphp

                    @if($errored->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-red-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Especie</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fuente</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Último Intento</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($errored as $sp)
                                    <tr class="hover:bg-red-50">
                                        <td class="px-4 py-3">
                                            <p class="font-medium italic text-gray-900">{{ $sp->scientific_name }}</p>
                                            @if($sp->common_name)
                                                <p class="text-sm text-gray-500">{{ $sp->common_name }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            {{ $sp->sync_source ? strtoupper($sp->sync_source) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-red-600 max-w-md truncate" title="{{ $sp->sync_error }}">
                                            {{ Str::limit($sp->sync_error, 100) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $sp->last_sync_attempt ? $sp->last_sync_attempt->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <form action="{{ route('admin.species.sync', $sp) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                    Reintentar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No hay errores de sincronización</p>
                    @endif
                </div>
            </div>

            {{-- Historial reciente --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Sincronizaciones Recientes
                    </h3>

                    @php
                        $recent = $species->whereNotNull('last_synced_at')->sortByDesc('last_synced_at')->take(30);
                    @endphp

                    @if($recent->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Especie</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fuente</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IDs Externos</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sincronizado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($recent as $sp)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.species.edit', $sp) }}" class="font-medium italic text-blue-600 hover:underline">
                                                {{ $sp->scientific_name }}
                                            </a>
                                            @if($sp->common_name)
                                                <p class="text-sm text-gray-500">{{ $sp->common_name }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($sp->sync_status === 'synced')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Sincronizado
                                                </span>
                                            @elseif($sp->sync_status === 'error')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Error
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $sp->sync_source ? strtoupper($sp->sync_source) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            <div class="flex flex-wrap gap-1">
                                                @if($sp->gbif_key)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700">
                                                        GBIF: {{ Str::limit($sp->gbif_key, 10) }}
                                                    </span>
                                                @endif
                                                @if($sp->iucn_taxon_id)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-green-100 text-green-700">
                                                        IUCN: {{ $sp->iucn_taxon_id }}
                                                    </span>
                                                @endif
                                                @if($sp->cites_id)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-purple-100 text-purple-700">
                                                        CITES: {{ $sp->cites_id }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $sp->last_synced_at->diffForHumans() }}
                                            <span class="text-xs text-gray-400 block">{{ $sp->last_synced_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No hay sincronizaciones registradas</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
