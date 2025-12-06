<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalle de Auditoría #{{ $auditLog->id }}
            </h2>
            <a href="{{ route('audit.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    {{-- Información general --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Fecha y hora</h3>
                            <p class="mt-1 text-lg text-gray-900">
                                {{ $auditLog->created_at->format('d/m/Y H:i:s') }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Usuario</h3>
                            <p class="mt-1 text-lg text-gray-900">
                                {{ $auditLog->user_name ?? 'Sistema' }}
                                @if($auditLog->user)
                                    <span class="text-sm text-gray-500">({{ $auditLog->user->email }})</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Acción</h3>
                            <p class="mt-1">
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
                                    ];
                                    $colorClass = $colorClasses[$auditLog->action_color] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $colorClass }}">
                                    {{ $auditLog->action_label }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Tipo de registro</h3>
                            <p class="mt-1 text-lg text-gray-900">
                                {{ $auditLog->model_name }}
                                @if($auditLog->model_id)
                                    <span class="text-sm text-gray-500">#{{ $auditLog->model_id }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-medium text-gray-500">Descripción</h3>
                        <p class="mt-1 text-gray-900">{{ $auditLog->description }}</p>
                    </div>

                    {{-- Valores anteriores --}}
                    @if($auditLog->old_values)
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-3">Valores anteriores</h3>
                            <div class="bg-red-50 rounded-lg p-4 overflow-x-auto">
                                <pre class="text-sm text-red-800">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif

                    {{-- Valores nuevos --}}
                    @if($auditLog->new_values)
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-3">Valores nuevos</h3>
                            <div class="bg-green-50 rounded-lg p-4 overflow-x-auto">
                                <pre class="text-sm text-green-800">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif

                    {{-- Metadata --}}
                    @if($auditLog->metadata)
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-3">Metadatos</h3>
                            <div class="bg-gray-50 rounded-lg p-4 overflow-x-auto">
                                <pre class="text-sm text-gray-800">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif

                    {{-- Información técnica --}}
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-3">Información técnica</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <dt class="text-xs text-gray-500">Dirección IP</dt>
                                <dd class="text-sm text-gray-900">{{ $auditLog->ip_address ?? '-' }}</dd>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <dt class="text-xs text-gray-500">User Agent</dt>
                                <dd class="text-sm text-gray-900 truncate" title="{{ $auditLog->user_agent }}">
                                    {{ $auditLog->user_agent ?? '-' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>