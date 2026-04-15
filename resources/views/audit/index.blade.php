<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registro de Auditoría
            </h2>
            <span class="text-sm text-gray-500">
                {{ $audits->total() }} registros
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filtros --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('audit.index') }}">
                        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 12px;">
                            {{-- Filtro por usuario --}}
                            <div>
                                <label for="user_id" class="block text-xs font-medium text-gray-700 mb-1">Usuario</label>
                                <select name="user_id" id="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500 text-sm">
                                    <option value="">Todos</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtro por acción --}}
                            <div>
                                <label for="action" class="block text-xs font-medium text-gray-700 mb-1">Acción</label>
                                <select name="action" id="action" class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500 text-sm">
                                    <option value="">Todas</option>
                                    @foreach($actions as $key => $label)
                                        <option value="{{ $key }}" @selected(request('action') == $key)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtro por modelo --}}
                            <div>
                                <label for="model_type" class="block text-xs font-medium text-gray-700 mb-1">Tipo de registro</label>
                                <select name="model_type" id="model_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500 text-sm">
                                    <option value="">Todos</option>
                                    @foreach($modelTypes as $type => $label)
                                        <option value="{{ $type }}" @selected(request('model_type') == $type)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Fecha desde --}}
                            <div>
                                <label for="date_from" class="block text-xs font-medium text-gray-700 mb-1">Desde</label>
                                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500 text-sm">
                            </div>

                            {{-- Fecha hasta --}}
                            <div>
                                <label for="date_to" class="block text-xs font-medium text-gray-700 mb-1">Hasta</label>
                                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500 text-sm">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; align-items: end;">
                            {{-- Búsqueda --}}
                            <div style="grid-column: span 3;">
                                <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Buscar</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Buscar en descripción..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500 text-sm">
                            </div>

                            {{-- Botones --}}
                            <div style="grid-column: span 2; display: flex; gap: 8px; justify-content: flex-end;">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-eco-600 rounded-md text-sm text-white font-medium hover:bg-eco-700">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    Filtrar
                                </button>
                                <a href="{{ route('audit.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-sm text-gray-700 font-medium hover:bg-gray-300">
                                    Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabla de registros --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha/Hora
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Usuario
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acción
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    IP
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($audits as $audit)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $audit->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-400">{{ $audit->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $audit->user_name ?? 'Sistema' }}
                                        </div>
                                        @if($audit->user)
                                            <div class="text-xs text-gray-500">
                                                {{ $audit->user->email }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $colorClasses = [
                                                'green' => 'bg-green-100 text-green-800',
                                                'yellow' => 'bg-yellow-100 text-yellow-800',
                                                'red' => 'bg-red-100 text-red-800',
                                                'blue' => 'bg-blue-100 text-blue-800',
                                                'gray' => 'bg-gray-100 text-gray-800',
                                                'purple' => 'bg-purple-100 text-purple-800',
                                                'orange' => 'bg-orange-100 text-orange-800',
                                                'indigo' => 'bg-indigo-100 text-indigo-800',
                                                'teal' => 'bg-teal-100 text-teal-800',
                                                'pink' => 'bg-pink-100 text-pink-800',
                                                'cyan' => 'bg-cyan-100 text-cyan-800',
                                                'slate' => 'bg-slate-100 text-slate-800',
                                            ];
                                            $colorClass = $colorClasses[$audit->action_color] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                            {{ $audit->action_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                        {{ $audit->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $audit->model_name }}
                                        @if($audit->model_id)
                                            <span class="text-xs text-gray-400">#{{ $audit->model_id }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400">
                                        {{ $audit->ip_address }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('audit.show', $audit) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="mt-2">No se encontraron registros de auditoría</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($audits->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $audits->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>