<x-guest-layout>
    <x-auth-card>
        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Token para restablecer la contraseña -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Dirección de correo electrónico -->
            <x-text-input 
                type="email" 
                name="email" 
                :value="old('email', $request->email)" 
                placeholder="{{ __('Correo Electrónico') }}" 
                required 
                readonly
                class="bg-gray-100 cursor-not-allowed"
                icon="fa-regular fa-envelope" 
            />
            <x-input-error :messages="$errors->get('email')" />

            <!-- Contraseña -->
            <x-text-input 
                type="password" 
                name="password" 
                placeholder="{{ __('Nueva Contraseña') }}" 
                required 
                autocomplete="new-password"
                icon="fa-solid fa-lock" 
            />
            <x-input-error :messages="$errors->get('password')" />

            <!-- Confirmación de contraseña -->
            <x-text-input 
                type="password" 
                name="password_confirmation" 
                placeholder="{{ __('Confirmar Nueva Contraseña') }}" 
                required 
                autocomplete="new-password"
                icon="fa-solid fa-lock" 
            />
            <x-input-error :messages="$errors->get('password_confirmation')" />

            <x-primary-button>
                {{ __('Restablecer Contraseña') }}
            </x-primary-button>
        </form>
    </x-auth-card>
</x-guest-layout>
