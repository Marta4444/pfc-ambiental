<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Información del perfil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Actualiza la información de tu perfil y la dirección de correo electrónico.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- Nombre --}}
        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- Número de Agente --}}
        <div>
            <x-input-label for="agent_num" :value="__('Número de Agente')" />
            <x-text-input 
                id="agent_num" 
                name="agent_num" 
                type="text" 
                class="mt-1 block w-full bg-gray-100 cursor-not-allowed" 
                :value="$user->agent_num" 
                readonly 
                disabled 
            />
            <p class="mt-1 text-xs text-gray-500">
                Este campo solo puede ser modificado por un administrador.
            </p>
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Tu dirección de correo no está verificada.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Haz clic aquí para reenviar el correo de verificación.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Rol - Condicional según tipo de usuario --}}
        <div>
            <x-input-label for="role" :value="__('Rol')" />
            
            @if($user->role === 'admin')
                {{-- Admin puede editar su rol --}}
                <select id="role" name="role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador</option>
                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Usuario</option>
                </select>
                
                @if(!$canBecomeAdmin && $user->role !== 'admin')
                    <p class="mt-1 text-sm text-orange-600">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Límite de administradores alcanzado ({{ $adminCount }}/5). No puedes cambiar a administrador.
                    </p>
                @endif
                
                <p class="mt-1 text-sm text-gray-500">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Como administrador, puedes modificar tu propio rol. 
                    @php
                        $currentAdminCount = \App\Models\User::where('role', 'admin')->count();
                    @endphp
                    @if($currentAdminCount <= 1)
                        <strong class="text-red-600">Advertencia:</strong> Eres el único administrador. No puedes cambiar a usuario.
                    @endif
                </p>
            @else
                {{-- Usuario normal ve su rol como solo lectura --}}
                <div class="mt-1">
                    <span class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium bg-green-100 text-green-800 border-green-300">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        Usuario
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    Tu rol no puede ser modificado desde el perfil. Contacta con un administrador si necesitas cambiar tu rol.
                </p>
            @endif
            
            <x-input-error class="mt-2" :messages="$errors->get('role')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Guardado.') }}</p>
            @endif
        </div>
    </form>
</section>