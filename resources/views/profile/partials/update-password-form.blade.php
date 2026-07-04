<section>
    <header class="mb-4">
        <h3 class="fw-bold text-dark fs-5">
            {{ __('Actualizar Contraseña') }}
        </h3>

        <p class="mt-1 small text-muted">
            {{ __('Asegúrate de que tu cuenta esté utilizando una contraseña larga y aleatoria para mantener la seguridad.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6">
        @csrf
        @method('put')

        <div class="mb-4">
            <label class="form-label small fw-bold text-muted text-uppercase" for="update_password_current_password">{{ __('Contraseña Actual') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control diab-input" autocomplete="current-password" />
            @if($errors->updatePassword->has('current_password'))
                <span class="text-danger extra-small">{{ $errors->updatePassword->first('current_password') }}</span>
            @endif
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold text-muted text-uppercase" for="update_password_password">{{ __('Nueva Contraseña') }}</label>
            <input id="update_password_password" name="password" type="password" class="form-control diab-input" autocomplete="new-password" />
            @if($errors->updatePassword->has('password'))
                <span class="text-danger extra-small">{{ $errors->updatePassword->first('password') }}</span>
            @endif
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold text-muted text-uppercase" for="update_password_password_confirmation">{{ __('Confirmar Contraseña') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control diab-input" autocomplete="new-password" />
            @if($errors->updatePassword->has('password_confirmation'))
                <span class="text-danger extra-small">{{ $errors->updatePassword->first('password_confirmation') }}</span>
            @endif
        </div>

        <div class="d-flex align-items-center gap-4 mt-4">
            <button type="submit" class="btn-diab-primary shadow-sm">{{ __('Guardar Contraseña') }}</button>

            @if (session('status') === 'password-updated')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="small text-success fw-semibold animate-fade-in">
                    <i class="fa-solid fa-circle-check me-1"></i> {{ __('Contraseña actualizada.') }}
                </div>
            @endif
        </div>
    </form>
</section>

