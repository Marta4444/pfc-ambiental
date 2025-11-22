<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalle del Peticionario') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('petitioners.edit', $petitioner) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Editar') }}
                </a>
                <a href="{{ route('petitioners.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Volver') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Nombre</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $petitioner->name }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Estado</h3>
                            <p>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $petitioner->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $petitioner->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Orden de visualización</h3>
                            <p class="text-lg text-gray-900">{{ $petitioner->order }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Casos asociados</h3>
                            <p class="text-lg text-gray-900">{{ $petitioner->reports()->count() }} casos</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Fecha de creación</h3>
                            <p class="text-gray-900">{{ $petitioner->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Última actualización</h3>
                            <p class="text-gray-900">{{ $petitioner->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if($petitioner->reports()->count() > 0)
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Casos Asociados</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($petitioner->reports()->latest()->take(10)->get() as $report)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                                    <a href="{{ route('reports.show', $report) }}" class="hover:underline">{{ $report->ip }}</a>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($report->title, 50) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize
                                                        @if($report->status === 'nuevo') bg-blue-100 text-blue-800
                                                        @elseif($report->status === 'en_proceso') bg-yellow-100 text-yellow-800
                                                        @elseif($report->status === 'en_espera') bg-orange-100 text-orange-800
                                                        @else bg-green-100 text-green-800
                                                        @endif">
                                                        {{ str_replace('_', ' ', $report->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $report->created_at->format('d/m/Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($petitioner->reports()->count() > 10)
                                <p class="text-sm text-gray-500 mt-2">
                                    Mostrando los 10 casos más recientes de {{ $petitioner->reports()->count() }} en total.
                                </p>
                            @endif
                        </div>
                    @else
                        <div class="mt-8 bg-gray-50 p-4 rounded">
                            <p class="text-gray-500 text-center">No hay casos asociados a este peticionario.</p>
                        </div>
                    @endif

                    @if($petitioner->reports()->count() == 0)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <form action="{{ route('petitioners.destroy', $petitioner) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este peticionario?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Eliminar Peticionario') }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>