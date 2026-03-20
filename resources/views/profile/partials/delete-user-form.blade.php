<section class="space-y-6">
    @if(auth()->user()->role === 'admin')
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Desactivar Cuenta') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Una vez que tu cuenta sea desactivada, no podrás iniciar sesión. Tus datos y casos permanecerán en el sistema. Un administrador puede reactivar tu cuenta en cualquier momento.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Desactivar Cuenta') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('¿Estás segur@ de que deseas desactivar tu cuenta?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Una vez que tu cuenta sea desactivada, no podrás iniciar sesión hasta que un administrador la reactive. Tus datos y casos permanecerán intactos. Por favor, ingresa tu contraseña para confirmar.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancelar') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Desactivar Cuenta') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
    @else
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Desactivar Cuenta') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Si deseas desactivar tu cuenta, contacta con un administrador del sistema.') }}
        </p>
    </header>
    @endif
</section>
