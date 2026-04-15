<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Peticionarios') }}
            </h2>
            <a href="{{ route('petitioners.create') }}" class="inline-flex items-center px-4 py-2 bg-eco-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-eco-700 focus:bg-eco-700 active:bg-eco-800 focus:outline-none focus:ring-2 focus:ring-eco-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Crear Peticionario') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ showModal: false, deleteUrl: '' }">
                <div class="p-6 text-gray-900">
                    @forelse($petitioners as $petitioner)
                        @if($loop->first)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Orden
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nombre
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Casos Asociados
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($petitioners as $petitioner)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $petitioner->order }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('petitioners.show', $petitioner) }}" class="text-sm font-medium text-eco-600 hover:text-blue-900 hover:underline">
                                                    {{ $petitioner->name }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <form action="{{ route('petitioners.toggleActive', $petitioner) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $petitioner->active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                                        {{ $petitioner->active ? 'Activo' : 'Inactivo' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($petitioner->reports()->count() > 0)
                                                    <a href="{{ route('petitioners.show', $petitioner) }}" class="text-eco-600 hover:text-blue-900 hover:underline">
                                                        {{ $petitioner->reports()->count() }} casos
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">0 casos</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                <a href="{{ route('petitioners.show', $petitioner) }}" class="text-eco-600 hover:text-eco-800">Ver</a>
                                                <a href="{{ route('petitioners.edit', $petitioner) }}" class="text-eco-600 hover:text-eco-800">Editar</a>

                                                @if($petitioner->reports()->count() == 0)
                                                    <button type="button" @click="deleteUrl = '{{ route('petitioners.destroy', $petitioner) }}'; showModal = true" class="text-red-600 hover:text-red-900">Eliminar</button>
                                                @else
                                                    <span class="text-gray-400 cursor-not-allowed" title="No se puede eliminar porque tiene casos asociados">Eliminar</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $petitioners->links() }}
                        </div>
                        @endif
                    @empty
                        <p class="text-gray-500 text-center py-4">No hay peticionarios registrados.</p>
                    @endforelse

                    {{-- Modal de confirmación de eliminación --}}
                    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="showModal" @click="showModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                            <div x-show="showModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900">Eliminar peticionario</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">¿Estás seguro de que quieres eliminar este peticionario? Esta acción no se puede deshacer.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <form :action="deleteUrl" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">Eliminar</button>
                                    </form>
                                    <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-eco-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>