<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Mis informes</h2>
    </x-slot>

    <div class="py-6 px-4">
        <a href="{{ route('reports.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">Nuevo informe</a>

        <table class="min-w-full mt-4 border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 border">Título</th>
                    <th class="px-4 py-2 border">Categoría</th>
                    <th class="px-4 py-2 border">Subcategoría</th>
                    <th class="px-4 py-2 border">Fecha del daño</th>
                    <th class="px-4 py-2 border">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr>
                    <td class="px-4 py-2 border">{{ $report->title }}</td>
                    <td class="px-4 py-2 border">{{ $report->category->nombre }}</td>
                    <td class="px-4 py-2 border">{{ $report->subcategory->nombre }}</td>
                    <td class="px-4 py-2 border">{{ $report->date_damage->format('d/m/Y') }}</td>
                    <td class="px-4 py-2 border">
                        <a href="{{ route('reports.edit', $report) }}" class="text-blue-600">Editar</a>
                        <form action="{{ route('reports.destroy', $report) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 ml-2" onclick="return confirm('¿Seguro que quieres eliminar este informe?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
