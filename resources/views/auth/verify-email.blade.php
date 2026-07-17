<x-guest-layout>
    <x-auth-card>
        <div class="verify-email-text">
            {{ __('Se envió un código de seis dígitos a :email. Ingrésalo para verificar tu correo y continuar con el registro.', ['email' => auth()->user()->email]) }}
        </div>

        @if (session('status') === 'verification-code-sent')
            <div class="auth-status auth-status--success">
                {{ __('Se envió un código nuevo. Revisa también tu carpeta de correo no deseado.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.code') }}" class="mt-4">
            @csrf

            <div>
                <x-input-label for="code" :value="__('Código de verificación')" />
                <x-text-input
                    id="code"
                    name="code"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    autocomplete="one-time-code"
                    class="block mt-1 w-full"
                    required
                    autofocus
                />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div class="verify-email-actions">
                <x-primary-button>
                    {{ __('Verificar correo') }}
                </x-primary-button>
            </div>
        </form>

        <div class="verify-email-actions">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-link">
                    {{ __('Reenviar código') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-link">
                    {{ __('Cerrar sesión') }}
                </button>
            </form>
        </div>
    </x-auth-card>
</x-guest-layout>
