<section>
    <header class="mb-4">
        <h3 class="fw-bold text-dark fs-5">
            {{ __('Eliminar Cuenta') }}
        </h3>

        <p class="mt-1 small text-muted">
            {{ __('Una vez que tu cuenta sea eliminada, todos sus recursos y datos serán eliminados permanentemente. Antes de eliminar tu cuenta, por favor descarga cualquier información que desees conservar.') }}
        </p>
    </header>

    <button 
        class="btn btn-outline-danger rounded-pill px-4"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ __('Eliminar mi Cuenta') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-4 p-md-5">
            @csrf
            @method('delete')

            <h2 class="fw-bold text-dark fs-4 mb-3">
                {{ __('¿Estás seguro de que deseas eliminar tu cuenta?') }}
            </h2>

            <p class="text-muted mb-4">
                {{ __('Esta acción no se puede deshacer. Por favor, introduce tu contraseña para confirmar que deseas eliminar permanentemente tu cuenta y todos tus datos de salud asociados.') }}
            </p>

            <div class="mb-4">
                <label for="password" class="form-label small fw-bold text-muted text-uppercase">{{ __('Contraseña de Confirmación') }}</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="form-control diab-input"
                    placeholder="{{ __('Introduce tu contraseña') }}"
                />

                @if($errors->userDeletion->has('password'))
                    <span class="text-danger extra-small">{{ $errors->userDeletion->first('password') }}</span>
                @endif
            </div>

            <div class="d-flex justify-content-end gap-3">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" x-on:click="$dispatch('close')">
                    {{ __('Cancelar') }}
                </button>

                <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm fw-semibold">
                    {{ __('Eliminar de Forma Permanente') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>

