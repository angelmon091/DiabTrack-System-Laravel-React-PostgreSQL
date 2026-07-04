@extends('layouts.app')

@section('title', 'DiabTrack - Configuración de Perfil')

@section('content')
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            
            <div class="settings-header animate-fade-in mb-5">
                <h2 class="fw-extrabold mb-1 fs-2">Configuración de <span class="text-diab-primary">Cuenta</span></h2>
                <p class="text-muted">Gestiona tu información personal y seguridad de forma segura</p>
            </div>

            <div class="space-y-6">
                <!-- Profile Info -->
                <div class="diab-card p-4 p-md-5 mb-4 animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="section-icon bg-diab-primary-light text-diab-primary mb-4" style="width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    @include('profile.partials.update-profile-information-form')


                </div>

                <!-- Password Update -->
                <div class="diab-card p-4 p-md-5 mb-4 animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="section-icon bg-diab-warning-light text-diab-warning mb-4" style="width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    @include('profile.partials.update-password-form')
                </div>

                <!-- Personas vinculadas (solo pacientes) -->
                @if(auth()->user()->isPatient())
                @php $linkedCarers = auth()->user()->linkedCarers()->get(); @endphp
                @if($linkedCarers->isNotEmpty())
                <div class="diab-card p-4 p-md-5 mb-4 animate-fade-in" style="animation-delay: 0.25s;">
                    <div class="section-icon mb-4" style="width:50px;height:50px;border-radius:15px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;background:rgba(0,180,216,0.1);color:var(--diab-primary);">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                    <div class="mb-4">
                        <h3 class="fw-bold text-dark fs-5 mb-1">Personas vinculadas</h3>
                        <p class="text-muted small mb-0">Médicos y cuidadores con acceso a tus datos</p>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        @foreach($linkedCarers as $carer)
                        @php
                            $role = $carer->roles->first()?->name ?? 'cuidador';
                            $isDoctor = $role === 'médico';
                            $icon  = $isDoctor ? 'fa-solid fa-user-doctor' : 'fa-solid fa-user-nurse';
                            $label = $isDoctor ? 'Médico' : 'Cuidador';
                            $color = $isDoctor ? '#6366f1' : '#0077b6';
                            $bg    = $isDoctor ? 'rgba(99,102,241,0.1)' : 'rgba(0,119,182,0.1)';
                        @endphp
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:var(--diab-bg);">
                            @if($carer->avatar && str_starts_with($carer->avatar, 'http'))
                                <img src="{{ $carer->avatar }}" alt="{{ $carer->name }}" class="rounded-circle flex-shrink-0 object-fit-cover" style="width:44px;height:44px;">
                            @elseif($carer->avatar && trim($carer->avatar) !== '')
                                <img src="{{ asset('storage/' . $carer->avatar) }}" alt="{{ $carer->name }}" class="rounded-circle flex-shrink-0 object-fit-cover" style="width:44px;height:44px;">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:44px;height:44px;background:{{ $bg }};color:{{ $color }};">
                                    <i class="{{ $icon }}"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size:0.9rem;">{{ $carer->name }}</div>
                                <div class="text-muted" style="font-size:0.78rem;">
                                    <span class="badge rounded-pill me-1" style="font-size:0.65rem;background:{{ $bg }};color:{{ $color }};">{{ $label }}</span>
                                    {{ $carer->email }}
                                </div>
                            </div>
                            <form method="POST" action="{{ route('profile.unlink', $carer) }}" onsubmit="return confirm('¿Desvincular a {{ $carer->name }}? Ya no tendrá acceso a tus datos.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" style="font-size:0.78rem;">
                                    <i class="fa-solid fa-link-slash me-1"></i>Desvincular
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endif

                <!-- Delete Account -->
                <div class="diab-card p-4 p-md-5 mb-5 animate-fade-in border-danger-subtle" style="animation-delay: 0.3s;">
                    <div class="section-icon bg-diab-danger-light text-diab-danger mb-4" style="width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</main>
@endsection
