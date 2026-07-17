<x-guest-layout>
    <x-auth-card>
        <!-- Estado de la sesión -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Dirección de correo electrónico -->
            <x-text-input 
                type="email" 
                name="email" 
                :value="old('email')" 
                placeholder="{{ __('Correo Electrónico') }}" 
                required 
                autofocus 
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" />

            <!-- Contraseña -->
            <x-text-input 
                type="password" 
                name="password" 
                placeholder="{{ __('Contraseña') }}" 
                required 
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" />
            
            <div class="forgot-pass">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">
                        {{ __('¿Olvidó su contraseña?') }}
                    </a>
                @endif
            </div>

            <div class="forgot-pass" style="margin-top: 4px;">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> 
                    <span>{{ __('Recuérdame') }}</span>
                </label>
            </div>

            <x-primary-button>
                {{ __('Iniciar Sesión') }}
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
                {{ __('¿No tienes una cuenta?') }} 
                <a href="{{ route('register') }}">{{ __('Regístrate') }}</a>
            </p>
        </form>
    </x-auth-card>
</x-guest-layout>
