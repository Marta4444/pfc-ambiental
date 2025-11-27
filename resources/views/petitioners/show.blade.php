<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalle del Peticionario') }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('petitioners.edit', $petitioner) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition ease-in-out duration-150">
                    Editar
                </a>
                <a href="{{ route('petitioners.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Información del Peticionario --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Información General</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Nombre</p>
                                <p class="text-lg text-gray-900">{{ $petitioner->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Estado</p>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $petitioner->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $petitioner->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Orden</p>
                                <p class="text-lg text-gray-900">{{ $petitioner->order }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Estadísticas --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Estadísticas</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <p class="text-sm font-medium text-blue-600">Total de Casos</p>
                                <p class="text-2xl font-bold text-blue-800">{{ $petitioner->reports()->count() }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-600">Fecha de Creación</p>
                                <p class="text-lg text-gray-800">{{ $petitioner->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Últimos Casos --}}
                    @if($petitioner->reports->count() > 0)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Últimos Casos Asociados</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nº Caso</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subcategoría</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($petitioner->reports as $report)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $report->case_number }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $report->subcategory->name ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $report->created_at->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($report->status === 'pending') bg-yellow-100 text-yellow-800
                                                        @elseif($report->status === 'in_progress') bg-blue-100 text-blue-800
                                                        @elseif($report->status === 'completed') bg-green-100 text-green-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst($report->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                    <a href="{{ route('reports.show', $report) }}" class="text-blue-600 hover:text-blue-900">Ver</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-gray-500">
                            Este peticionario no tiene casos asociados.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>