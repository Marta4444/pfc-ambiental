<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Usuario: {{ $user->name }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Info del usuario --}}
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-4">
                            <div class="h-16 w-16 rounded-full bg-eco-100 flex items-center justify-center">
                                <span class="text-eco-700 font-bold text-xl">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Registrado el {{ $user->created_at->format('d/m/Y H:i') }}</p>
                                <p class="text-sm">
                                    Estado: 
                                    @if($user->active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nombre')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="agent_num" :value="__('Número de Agente')" />
                            <x-text-input id="agent_num" class="block mt-1 w-full" type="text" name="agent_num" :value="old('agent_num', $user->agent_num)" />
                            <x-input-error :messages="$errors->get('agent_num')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="role" :value="__('Rol')" />
                            <select id="role" name="role" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-eco-500 focus:ring-eco-500" required>
                                <option value="user" @selected(old('role', $user->role) === 'user')>Usuario</option>
                                <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrador</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password" :value="__('Nueva Contraseña (dejar vacío para mantener)')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between">
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="inline"
                                      onsubmit="return confirm('¿{{ $user->active ? 'Desactivar' : 'Activar' }} usuario {{ $user->name }}?')">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 {{ $user->active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150">
                                        {{ $user->active ? 'Desactivar Usuario' : 'Activar Usuario' }}
                                    </button>
                                </form>
                            @else
                                <span></span>
                            @endif
                            
                            <div class="flex gap-3">
                                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                                    Cancelar
                                </a>
                                <x-primary-button>
                                    {{ __('Guardar Cambios') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
