<x-guest-layout>
    <x-auth-card>
        <form method="POST" action="{{ route('onboarding.patient.store') }}">
            @csrf
            
            <div class="text-center mb-5 animate-fade-in">
                <div class="admin-card-icon-wrapper mx-auto bg-diab-primary-light">
                    <i class="fa-solid fa-user-gear fs-2 text-diab-primary"></i>
                </div>
                <h3 class="fw-extrabold text-diab-primary">Datos Personales</h3>
                <p class="text-diab-secondary small">Completa tu perfil para una mejor experiencia.</p>
            </div>

            <div class="mb-4 animate-fade-in" style="animation-delay: 0.1s;">
                <x-input-label value="{{ __('Fecha de Nacimiento') }}" class="text-uppercase extra-small fw-bold mb-2" />
                <div class="date-row">
                    <div class="select-wrapper">
                        <label>{{ __('Día') }}</label>
                        <select name="birth_day" class="full-select">
                            @for ($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}" {{ old('birth_day') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="select-wrapper">
                        <label>{{ __('Mes') }}</label>
                        <select name="birth_month" class="full-select">
                            @foreach(['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $month)
                                <option value="{{ $month }}" {{ old('birth_month') == $month ? 'selected' : '' }}>{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="select-wrapper">
                        <label>{{ __('Año') }}</label>
                        <select name="birth_year" class="full-select">
                            @for ($i = now()->subYears(18)->year; $i >= 1920; $i--)
                                <option value="{{ $i }}" {{ old('birth_year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('birth_day')" />
                <x-input-error :messages="$errors->get('birth_month')" />
                <x-input-error :messages="$errors->get('birth_year')" />
                <x-input-error :messages="$errors->get('birth_date')" />
            </div>

            <div class="mb-4 animate-fade-in" style="animation-delay: 0.2s;">
                <x-input-label value="{{ __('Condición glucémica') }}" class="text-uppercase extra-small fw-bold mb-2" />
                <div class="input-group">
                    <select class="full-select" name="diabetes_type">
                        @foreach(config('diabtrack.glycemic_conditions') as $value => $label)
                            <option value="{{ $value }}" {{ old('diabetes_type') === $value ? 'selected' : '' }}>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input-error :messages="$errors->get('diabetes_type')" />
            </div>

            <div class="row g-3 mb-4 animate-fade-in" style="animation-delay: 0.3s;">
                <div class="col-6">
                    <x-input-label value="{{ __('Peso (kg)') }}" class="text-uppercase extra-small fw-bold mb-2" />
                    <x-text-input type="number" step="0.1" name="weight" :value="old('weight')" placeholder="00.0" />
                    <x-input-error :messages="$errors->get('weight')" />
                </div>
                <div class="col-6">
                    <x-input-label value="{{ __('Estatura (cm)') }}" class="text-uppercase extra-small fw-bold mb-2" />
                    <x-text-input type="number" name="height" :value="old('height')" placeholder="000" />
                    <x-input-error :messages="$errors->get('height')" />
                </div>
            </div>

            <div class="mb-5 animate-fade-in" style="animation-delay: 0.4s;">
                <x-input-label value="{{ __('Género') }}" class="text-uppercase extra-small fw-bold mb-2 text-center d-block" />
                <div class="radio-group justify-content-center">
                    <label class="custom-radio">
                        <input type="radio" name="gender" value="Masculino" {{ old('gender') == 'Masculino' ? 'checked' : '' }}> 
                        <span>{{ __('Masculino') }}</span>
                    </label>
                    <label class="custom-radio">
                        <input type="radio" name="gender" value="Femenino" {{ old('gender') == 'Femenino' ? 'checked' : '' }}> 
                        <span>{{ __('Femenino') }}</span>
                    </label>
                </div>
                <x-input-error :messages="$errors->get('gender')" />
            </div>

            <button type="submit" class="btn-primary w-100 py-3 animate-fade-in" style="animation-delay: 0.5s;">
                {{ __('Registrar Datos') }}
            </button>
        </form>
    </x-auth-card>
</x-guest-layout>
