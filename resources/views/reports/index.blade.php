
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Informes</h2>
    </x-slot>

    @php
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
    @endphp

    <div class="py-6 px-4">
        <a href="{{ route('reports.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">Nuevo informe</a>

        <table class="min-w-full mt-4 border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 border">Título</th>
                    @if($isAdmin)
                        <th class="px-4 py-2 border">Autor</th>
                    @endif
                    <th class="px-4 py-2 border">Categoría</th>
                    <th class="px-4 py-2 border">Subcategoría</th>
                    <th class="px-4 py-2 border">Fecha del daño</th>
                    <th class="px-4 py-2 border">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr>
                    <td class="px-4 py-2 border">{{ $report->title ?? '—' }}</td>
                    @if($isAdmin)
                        <td class="px-4 py-2 border">{{ $report->user->name ?? '—' }}</td>
                    @endif
                    <td class="px-4 py-2 border">{{ $report->category->name ?? '—' }}</td>
                    <td class="px-4 py-2 border">{{ $report->subcategory->name ?? '—' }}</td>
                    <td class="px-4 py-2 border">
                        {{ $report->date_damage ? \Illuminate\Support\Carbon::parse($report->date_damage)->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-4 py-2 border">
                        <a href="{{ route('reports.show', $report) }}" class="text-blue-600 mr-2">Ver</a>
                        <a href="{{ route('reports.edit', $report) }}" class="text-blue-600 mr-2">Editar</a>
                        <form action="{{ route('reports.destroy', $report) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600" onclick="return confirm('¿Seguro que quieres eliminar este informe?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Paginación: enlaces + flechas prev/next --}}
        <div class="mt-4 flex items-center justify-between">
            <div>
                @if ($reports->onFirstPage())
                    <button disabled class="px-3 py-1 bg-gray-200 text-gray-500 rounded">&larr; Anterior</button>
                @else
                    <a href="{{ $reports->previousPageUrl() }}" class="px-3 py-1 bg-white border rounded hover:bg-gray-50">&larr; Anterior</a>
                @endif
            </div>

            <div class="text-sm text-gray-600">
                Página {{ $reports->currentPage() }} de {{ $reports->lastPage() }} — {{ $reports->total() }} informe(s)
            </div>

            <div>
                @if ($reports->hasMorePages())
                    <a href="{{ $reports->nextPageUrl() }}" class="px-3 py-1 bg-white border rounded hover:bg-gray-50">Siguiente &rarr;</a>
                @else
                    <button disabled class="px-3 py-1 bg-gray-200 text-gray-500 rounded">Siguiente &rarr;</button>
                @endif
            </div>
        </div>

        {{-- Enlaces de paginación estándar (opcional, Tailwind) --}}
        <div class="mt-3">
            {{ $reports->links() }}
        </div>
    </div>
</x-app-layout>
