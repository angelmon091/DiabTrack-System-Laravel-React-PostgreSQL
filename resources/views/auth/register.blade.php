<x-guest-layout>
    <x-auth-card>
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Nombre -->
            <x-text-input 
                type="text" 
                name="name" 
                :value="old('name')" 
                placeholder="{{ __('Nombre Completo') }}" 
                required 
                autofocus 
                autocomplete="name"
            />
            <x-input-error :messages="$errors->get('name')" />
            
            <!-- Dirección de correo electrónico -->
            <x-text-input 
                type="email" 
                name="email" 
                :value="old('email')" 
                placeholder="{{ __('Correo Electrónico') }}" 
                required 
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" />

            <!-- Contraseña -->
            <x-text-input 
                type="password" 
                name="password" 
                placeholder="{{ __('Contraseña') }}" 
                required 
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" />

            <!-- Confirmación de contraseña -->
            <x-text-input 
                type="password" 
                name="password_confirmation" 
                placeholder="{{ __('Confirmar Contraseña') }}" 
                required 
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" />
            
            <x-primary-button>
                {{ __('Registrarse') }}
            </x-primary-button>

            <div class="separator">
                <span>{{ __('O') }}</span>
            </div>

            <div class="social-buttons">
                <!-- Por ahora ocultamos Facebook hasta tener registrada la empresa -->
                <a href="{{ route('socialite.redirect', 'facebook') }}" class="btn-social" style="display: none;">
                    <i class="fa-brands fa-facebook" style="color: #1877F2;"></i> {{ __('Continuar con Facebook') }}
                </a>
                <a href="{{ route('socialite.redirect', 'google') }}" class="btn-social">
                    <img src="{{ asset('img/medios/logos/google.png') }}" alt="Google"> {{ __('Continuar con Google') }}
                </a>
            </div>

            <p class="footer-link">
                {{ __('¿Ya tienes una cuenta?') }} 
                <a href="{{ route('login') }}">{{ __('Inicia Sesión') }}</a>
            </p>
        </form>
    </x-auth-card>
</x-guest-layout>
