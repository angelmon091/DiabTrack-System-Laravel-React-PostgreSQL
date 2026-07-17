<x-guest-layout>
    <x-auth-card>
        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="helper-text">
                <p>{{ __('Esta es un área segura. Por favor confirma tu contraseña antes de continuar.') }}</p>
            </div>

            <!-- Contraseña -->
            <x-text-input 
                type="password" 
                name="password" 
                placeholder="{{ __('Contraseña') }}" 
                required 
                autocomplete="current-password"
                icon="fa-solid fa-lock" 
            />
            <x-input-error :messages="$errors->get('password')" />

            <x-primary-button>
                {{ __('Confirmar') }}
            </x-primary-button>
        </form>
    </x-auth-card>
</x-guest-layout>
