@props(['profile' => 'doctor'])

@php
    $isDoctor = $profile === 'doctor';
    $linkRoute = $isDoctor ? 'doctor.link' : 'caregiver.link';
    $accentClass = $isDoctor ? 'text-diab-info' : 'text-diab-warning';
    $accentBackground = $isDoctor ? 'bg-diab-info-light' : 'bg-diab-warning-light';
    $solidAccent = $isDoctor ? 'blue' : 'orange';
    $capabilities = $isDoctor
        ? [
            ['fa-chart-line', 'Consultar tendencias', 'Revisa registros y evolución de las métricas compartidas.'],
            ['fa-bullseye', 'Definir objetivos', 'Personaliza los rangos glucémicos de cada paciente.'],
            ['fa-notes-medical', 'Dar seguimiento', 'Accede al historial autorizado desde un mismo lugar.'],
        ]
        : [
            ['fa-chart-line', 'Acompañar tendencias', 'Consulta los registros que el paciente comparte contigo.'],
            ['fa-bell', 'Mantenerte informado', 'Identifica cambios recientes para brindar acompañamiento.'],
            ['fa-heart-pulse', 'Apoyar su seguimiento', 'Observa su actividad clínica desde un mismo lugar.'],
        ];
@endphp

<div class="d-flex flex-column gap-4">
    <section class="diab-card p-4 p-md-5 text-center animate-fade-in">
        <div class="admin-card-icon-wrapper mx-auto {{ $accentBackground }} mb-4">
            <i class="fa-solid fa-user-plus fs-2 {{ $accentClass }}"></i>
        </div>
        <h4 class="fw-bold mb-2">Aún no tienes pacientes vinculados</h4>
        <p class="text-muted mx-auto mb-4" style="max-width: 700px;">
            Pide al paciente que genere un <strong>código de invitación</strong> desde su panel. El código es temporal y permite confirmar que autorizó el acceso.
        </p>
        <a href="{{ route($linkRoute) }}" class="btn-diab-primary d-inline-flex align-items-center gap-2 text-decoration-none">
            <i class="fa-solid fa-link"></i> Vincular paciente
        </a>

        <div class="row g-3 mt-4 text-start">
            @foreach([
                ['1', 'Solicita el código', 'El paciente genera una invitación vigente desde su cuenta.'],
                ['2', 'Confirma la vinculación', $isDoctor ? 'Ingresa el código para validar el acceso profesional.' : 'Ingresa el código e indica tu relación con el paciente.'],
                ['3', 'Comienza el seguimiento', 'Los datos autorizados aparecerán automáticamente en este panel.'],
            ] as [$number, $title, $description])
                <div class="col-12 col-md-4">
                    <div class="h-100 p-4 rounded-4 bg-light border border-white">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle {{ $accentBackground }} {{ $accentClass }} fw-bold" style="width: 34px; height: 34px;">{{ $number }}</span>
                            <strong class="small">{{ $title }}</strong>
                        </div>
                        <p class="extra-small text-muted mb-0">{{ $description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="row g-4 animate-fade-in" style="animation-delay: 0.1s;">
        <div class="col-12 col-lg-8">
            <section class="diab-card p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="action-icon {{ $solidAccent }} flex-shrink-0"><i class="fa-solid fa-wand-magic-sparkles text-white"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0">Tu panel después de vincular</h6>
                        <p class="extra-small text-muted mb-0">Herramientas disponibles con autorización del paciente</p>
                    </div>
                </div>
                <div class="row g-3">
                    @foreach($capabilities as [$icon, $title, $description])
                        <div class="col-12 col-md-4">
                            <div class="h-100 p-3 rounded-4 bg-light">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-3 {{ $accentBackground }} {{ $accentClass }} mb-3" style="width: 36px; height: 36px;">
                                    <i class="fa-solid {{ $icon }}"></i>
                                </div>
                                <strong class="d-block small mb-1">{{ $title }}</strong>
                                <p class="extra-small text-muted mb-0">{{ $description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
        <div class="col-12 col-lg-4">
            <aside class="diab-card p-4 h-100">
                <div class="action-icon {{ $solidAccent }} mb-3"><i class="fa-solid fa-lock text-white"></i></div>
                <h6 class="fw-bold mb-2">Acceso privado y autorizado</h6>
                <p class="small text-muted mb-3">DiabTrack solo muestra información de pacientes que aceptaron vincular su cuenta mediante un código temporal.</p>
                <div class="d-flex align-items-center gap-2 text-success small fw-bold">
                    <i class="fa-solid fa-circle-check"></i>
                    El paciente conserva el control
                </div>
            </aside>
        </div>
    </div>
</div>
