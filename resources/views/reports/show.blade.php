
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('Informe') }}</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-6 px-4">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $report->title }}</h1>
                <p class="text-sm text-gray-500 mt-1">Creado por: <span class="text-gray-700">{{ $report->user->name ?? '—' }}</span></p>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-3 py-1 bg-gray-100 border border-gray-200 rounded text-sm text-gray-700 hover:bg-gray-50">Volver</a>
                <a href="{{ route('reports.edit', $report) }}" class="inline-flex items-center px-3 py-1 bg-yellow-100 border border-yellow-200 rounded text-sm text-yellow-800 hover:bg-yellow-50">Editar</a>

                @if($report->pdf_report)
                    <a href="{{ asset('storage/' . $report->pdf_report) }}" target="_blank" class="inline-flex items-center px-3 py-1 bg-blue-100 border border-blue-200 rounded text-sm text-blue-700 hover:bg-blue-50">
                        Ver PDF
                    </a>
                @endif

                <form action="{{ route('reports.destroy', $report) }}" method="POST" onsubmit="return confirm('¿Eliminar informe?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-100 border border-red-200 rounded text-sm text-red-700 hover:bg-red-50">Eliminar</button>
                </form>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                <div class="md:col-span-2">
                    <h3 class="text-sm font-medium text-gray-600">Descripción</h3>
                    <p class="mt-2 text-gray-800 whitespace-pre-line">{{ $report->description ?? '—' }}</p>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-xs text-gray-500">Categoría</h4>
                            <p class="text-sm text-gray-800 mt-1">{{ $report->category->name ?? '—' }}</p>
                        </div>

                        <div>
                            <h4 class="text-xs text-gray-500">Subcategoría</h4>
                            <p class="text-sm text-gray-800 mt-1">{{ $report->subcategory->name ?? '—' }}</p>
                        </div>

                        <div>
                            <h4 class="text-xs text-gray-500">Fecha del daño</h4>
                            <p class="text-sm text-gray-800 mt-1">
                                {{ $report->date_damage ? \Illuminate\Support\Carbon::parse($report->date_damage)->format('d/m/Y') : '—' }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-xs text-gray-500">Estado</h4>
                            <p class="text-sm text-gray-800 mt-1 capitalize">{{ $report->status ?? '—' }}</p>
                        </div>

                        <div>
                            <h4 class="text-xs text-gray-500">Área afectada (m²)</h4>
                            <p class="text-sm text-gray-800 mt-1">{{ $report->affected_area !== null ? number_format($report->affected_area, 2) : '—' }}</p>
                        </div>

                        <div>
                            <h4 class="text-xs text-gray-500">Criticidad</h4>
                            <p class="text-sm text-gray-800 mt-1">{{ $report->criticallity ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-xs text-gray-500">Ubicación</h4>
                        <p class="text-sm text-gray-800 mt-1">{{ $report->location ?? '—' }}</p>

                        <h4 class="text-xs text-gray-500 mt-3">Coordenadas</h4>
                        <p class="text-sm text-gray-800 mt-1">
                            @if($report->coordinates)
                                {{ $report->coordinates }}
                                @php
                                    [$lat, $lon] = array_pad(explode(',', $report->coordinates), 2, null);
                                @endphp
                                @if($lat && $lon)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ trim($lat) }},{{ trim($lon) }}" target="_blank" class="text-blue-600 hover:underline ml-2 text-sm">Ver en mapa</a>
                                @endif
                            @else
                                —
                            @endif
                        </p>
                    </div>
                </div>

                <aside class="space-y-4">
                    <div class="bg-gray-50 border border-gray-100 rounded p-4">
                        <h4 class="text-xs text-gray-500">Metadatos</h4>
                        <div class="mt-2 text-sm text-gray-700">
                            <div><strong>Autor:</strong> {{ $report->user->name ?? '—' }}</div>
                            <div class="mt-1"><strong>Creado:</strong> {{ optional($report->created_at)->format('d/m/Y H:i') ?? '—' }}</div>
                            <div class="mt-1"><strong>Última modificación:</strong> {{ optional($report->updated_at)->format('d/m/Y H:i') ?? '—' }}</div>
                        </div>
                    </div>

                    @if($report->pdf_report)
                        <div class="bg-white border border-gray-100 rounded p-4">
                            <h4 class="text-xs text-gray-500">Archivo adjunto</h4>
                            <a href="{{ asset('storage/' . $report->pdf_report) }}" target="_blank" class="mt-2 inline-block text-sm text-blue-700 hover:underline">
                                Descargar / Ver PDF
                            </a>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
