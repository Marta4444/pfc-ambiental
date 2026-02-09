<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Administración de Especies - Sincronización API
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.species.logs') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Logs
                </a>
                <a href="{{ route('admin.species.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exportar CSV
                </a>
                <a href="{{ route('admin.species.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva Especie
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

            {{-- Panel de sincronización --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Sincronización con APIs Externas
                    </h3>

                    {{-- Estado de las APIs --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        @foreach($apiStatus as $api => $status)
                        <div class="p-4 rounded-lg {{ $status['available'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ strtoupper($api) }}</p>
                                    <p class="text-sm {{ $status['available'] ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $status['available'] ? 'Disponible' : 'No disponible' }}
                                    </p>
                                </div>
                                <div class="w-3 h-3 rounded-full {{ $status['available'] ? 'bg-green-500' : 'bg-red-500' }}"></div>
                            </div>
                            @if(!$status['available'] && isset($status['error']))
                                <p class="text-xs text-red-500 mt-2">{{ $status['error'] }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    {{-- Acciones de sincronización --}}
                    <div class="flex flex-wrap gap-4">
                        <form action="{{ route('admin.species.syncAll') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="limit" value="50">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Sincronizar Existentes (50)
                            </button>
                        </form>

                        <form action="{{ route('admin.species.importSpanish') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                </svg>
                                Importar Fauna Española (GBIF)
                            </button>
                        </form>

                        <a href="{{ route('admin.species.search') }}" class="inline-flex items-center px-4 py-2 bg-cyan-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-cyan-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Buscar en APIs
                        </a>
                    </div>
                </div>
            </div>

            {{-- Estadísticas --}}
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-500">Total</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-green-600">{{ $stats['protected'] }}</p>
                    <p class="text-xs text-gray-500">Protegidas</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['synced'] }}</p>
                    <p class="text-xs text-gray-500">Sincronizadas</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_sync'] }}</p>
                    <p class="text-xs text-gray-500">Pendientes</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-red-600">{{ $stats['sync_errors'] }}</p>
                    <p class="text-xs text-gray-500">Con errores</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['from_gbif'] }}</p>
                    <p class="text-xs text-gray-500">Desde GBIF</p>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form action="{{ route('admin.species.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Nombre científico o común..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado Sync</label>
                            <select name="sync_status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="synced" {{ request('sync_status') == 'synced' ? 'selected' : '' }}>Sincronizados</option>
                                <option value="pending" {{ request('sync_status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                                <option value="error" {{ request('sync_status') == 'error' ? 'selected' : '' }}>Con error</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fuente</label>
                            <select name="sync_source" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todas</option>
                                <option value="gbif" {{ request('sync_source') == 'gbif' ? 'selected' : '' }}>GBIF</option>
                                <option value="iucn" {{ request('sync_source') == 'iucn' ? 'selected' : '' }}>IUCN</option>
                                <option value="cites" {{ request('sync_source') == 'cites' ? 'selected' : '' }}>CITES</option>
                                <option value="manual" {{ request('sync_source') == 'manual' ? 'selected' : '' }}>Manual</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Protección</label>
                            <select name="is_protected" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todas</option>
                                <option value="1" {{ request('is_protected') == '1' ? 'selected' : '' }}>Protegidas</option>
                                <option value="0" {{ request('is_protected') == '0' ? 'selected' : '' }}>No protegidas</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Filtrar
                            </button>
                            <a href="{{ route('admin.species.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabla de especies --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Especie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taxonomía</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sincronización</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Sync</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($species as $sp)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900 italic">{{ $sp->scientific_name }}</p>
                                        @if($sp->common_name)
                                            <p class="text-sm text-gray-500">{{ $sp->common_name }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($sp->family)
                                        <span class="block">{{ $sp->family }}</span>
                                    @endif
                                    @if($sp->taxon_group)
                                        <span class="text-xs text-gray-400">{{ $sp->taxon_group }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($sp->is_protected)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Protegida
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            No protegida
                                        </span>
                                    @endif
                                    @if($sp->iucn_category)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 ml-1">
                                            IUCN: {{ $sp->iucn_category }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($sp->sync_status === 'synced')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Sincronizado
                                        </span>
                                    @elseif($sp->sync_status === 'error')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" title="{{ $sp->sync_error }}">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Error
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                            </svg>
                                            Pendiente
                                        </span>
                                    @endif
                                    @if($sp->sync_source)
                                        <span class="text-xs text-gray-400 block mt-1">{{ strtoupper($sp->sync_source) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($sp->last_synced_at)
                                        {{ $sp->last_synced_at->diffForHumans() }}
                                    @else
                                        <span class="text-gray-400">Nunca</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('admin.species.sync', $sp) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-indigo-600 hover:text-indigo-900" title="Sincronizar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.species.edit', $sp) }}" class="text-blue-600 hover:text-blue-900" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.species.destroy', $sp) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta especie?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No se encontraron especies
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($species->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $species->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
