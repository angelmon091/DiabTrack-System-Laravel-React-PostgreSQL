@props(['profile' => 'doctor'])

@php
    $isDoctor = $profile === 'doctor';
    $solidAccent = $isDoctor ? 'blue' : 'orange';
    $accentBackground = $isDoctor ? 'bg-diab-info-light' : 'bg-diab-warning-light';
    $accentClass = $isDoctor ? 'text-diab-info' : 'text-diab-warning';
@endphp

<div class="diab-card p-4 animate-fade-in" style="animation-delay: 0.12s;">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="action-icon {{ $solidAccent }} flex-shrink-0"><i class="fa-solid fa-clipboard-check text-white"></i></div>
        <div>
            <h6 class="fw-bold mb-0">Todo listo para comenzar</h6>
            <p class="extra-small text-muted mb-0">Solo falta vincular al primer paciente</p>
        </div>
    </div>

    <div class="d-flex flex-column gap-3">
        @foreach([
            ['fa-circle-check', 'Cuenta configurada', 'Tu información de acceso está completa.'],
            $isDoctor
                ? ['fa-id-card', 'Perfil profesional aprobado', 'Tu cédula ya fue validada por DiabTrack.']
                : ['fa-user-shield', 'Perfil de cuidador activo', 'Ya puedes recibir autorización de un paciente.'],
            ['fa-key', 'Código pendiente', 'Solicita al paciente una invitación temporal.'],
        ] as $index => [$icon, $title, $description])
            <div class="d-flex gap-3">
                <div class="flex-shrink-0 d-inline-flex align-items-center justify-content-center rounded-circle {{ $index < 2 ? 'bg-success bg-opacity-10 text-success' : $accentBackground.' '.$accentClass }}" style="width: 34px; height: 34px;">
                    <i class="fa-solid {{ $icon }} small"></i>
                </div>
                <div>
                    <strong class="d-block small">{{ $title }}</strong>
                    <p class="extra-small text-muted mb-0">{{ $description }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 pt-3 border-top">
        <div class="d-flex gap-2 text-muted">
            <i class="fa-solid fa-circle-info mt-1"></i>
            <p class="extra-small mb-0">El código vence por seguridad. Si deja de funcionar, el paciente puede generar uno nuevo desde su panel.</p>
        </div>
    </div>
</div>
