@extends('layouts.app')

@section('title', 'DiabTrack - Panel Médico')

@section('content')
<main class="container-fluid py-4 px-md-5">
    @php
        $doctorProfile = $user->doctorProfile;
    @endphp
    <div class="row g-4">

        {{-- Sidebar --}}
        <aside class="col-12 col-xl-4 order-2 order-xl-1">
            {{-- Panel General del Médico --}}
            <div class="diab-card p-4 mb-4 animate-fade-in">
                <div class="tool-header mb-4 d-flex align-items-center text-diab-info">
                    <i class="fa-solid fa-stethoscope me-2"></i>
                    <span class="fw-bold">Panel Médico</span>
                </div>

                <div class="d-flex flex-column gap-2">
                    @if($doctorProfile?->isApproved())
                        <a href="{{ route('doctor.link') }}" class="action-item">
                            <div class="action-icon blue"><i class="fa-solid fa-link"></i></div>
                            <div class="ms-3"><strong class="d-block">Vincular Paciente</strong><p class="mb-0 extra-small text-muted">Conecta usando un código</p></div>
                        </a>
                    @else
                        <div class="action-item opacity-50" aria-disabled="true">
                            <div class="action-icon gray"><i class="fa-solid fa-lock"></i></div>
                            <div class="ms-3"><strong class="d-block">Vinculación bloqueada</strong><p class="mb-0 extra-small text-muted">Esperando aprobación administrativa</p></div>
                        </div>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="action-item">
                        <div class="action-icon gray"><i class="fa-solid fa-sliders"></i></div>
                        <div class="ms-3">
                            <strong class="d-block">Ajustes</strong>
                            <p class="mb-0 extra-small text-muted">Configurar perfil</p>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Información Profesional --}}
            <div class="diab-card p-4 mb-4 animate-fade-in" style="animation-delay: 0.05s;">
                <h6 class="fw-bold mb-3 text-muted text-uppercase letter-spacing-1 small">Tu Información</h6>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Especialidad:</span>
                        <span class="fw-bold small">{{ auth()->user()->doctorProfile?->specialty ?? '--' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Cédula:</span>
                        <span class="fw-bold small">{{ auth()->user()->doctorProfile?->license_number ?? '--' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Verificación:</span>
                        @if($doctorProfile?->isApproved())
                            <span class="badge bg-success-subtle text-success">Aprobado</span>
                        @elseif($doctorProfile?->approval_status === 'rejected')
                            <span class="badge bg-danger-subtle text-danger">Requiere corrección</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis">En revisión</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Lista de Pacientes Vinculados --}}
            @if($doctorProfile?->isApproved() && $patients->isNotEmpty())
                <div class="diab-card p-4 mb-4 animate-fade-in" style="animation-delay: 0.1s;">
                    <h6 class="fw-bold mb-3 text-muted text-uppercase letter-spacing-1 small">Pacientes Vinculados</h6>
                    <div class="d-flex flex-column gap-3">
                        @foreach($patients as $p)
                            @php
                                $isSelected = $selectedPatient && $p->id === $selectedPatient->id;
                                $gender = strtolower($p->patientProfile?->gender ?? '');
                                $avatar = $p->avatar;
                            @endphp
                            <div class="p-3 rounded-4 transition-hover position-relative" style="background: {{ $isSelected ? 'rgba(0, 180, 216, 0.08)' : 'var(--diab-card-bg, #fff)' }}; border: 2px solid {{ $isSelected ? 'var(--diab-primary)' : 'rgba(0,0,0,0.05)' }};">
                                <a href="{{ route('doctor.dashboard', ['patient_id' => $p->id]) }}" class="text-decoration-none d-flex align-items-center mb-2">
                                    <div class="rounded-circle overflow-hidden shadow-sm flex-shrink-0" style="width: 40px; height: 40px;">
                                        @if($avatar && str_starts_with($avatar, 'http'))
                                            <img src="{{ $avatar }}" class="w-100 h-100 object-fit-cover" alt="{{ $p->name }}">
                                        @elseif($avatar)
                                            <img src="{{ asset('storage/' . $avatar) }}" class="w-100 h-100 object-fit-cover" alt="{{ $p->name }}">
                                        @else
                                            @if($gender === 'femenino')
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white" style="background: linear-gradient(135deg, var(--diab-danger) 0%, #C0392B 100%); font-size: 0.8rem;">
                                                    <i class="fa-solid fa-person-dress"></i>
                                                </div>
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white" style="background: linear-gradient(135deg, var(--diab-primary) 0%, var(--diab-primary-hover) 100%); font-size: 0.8rem;">
                                                    <i class="fa-solid fa-user-tie"></i>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <strong class="d-block text-dark small" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">{{ $p->name }}</strong>
                                        <span class="text-muted extra-small">{{ $p->patientProfile?->diabetes_type ?? '--' }}</span>
                                    </div>
                                </a>
                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-light">
                                    <span class="extra-small text-muted" style="font-size: 0.7rem;">
                                        Glucosa: <strong>{{ $p->vitalSigns->sortByDesc('created_at')->first()?->glucose_level ?? '--' }} mg/dL</strong>
                                    </span>
                                    <form action="{{ route('doctor.patient.unlink', $p) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas desvincular a este paciente de tu panel clínico?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 d-flex align-items-center gap-1 text-decoration-none" title="Desvincular Paciente" style="font-size: 0.7rem; font-weight: 600;">
                                            <i class="fa-solid fa-link-slash"></i> Desvincular
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- CTA: vincular más pacientes --}}
                <div class="diab-card p-4 text-center animate-fade-in" style="animation-delay:0.15s; border:2px dashed rgba(0,0,0,0.1); box-shadow:none;">
                    <i class="fa-solid fa-user-plus text-diab-primary mb-2" style="font-size:1.4rem; opacity:0.75;"></i>
                    <p class="small text-muted mb-3">¿Atiendes a más pacientes? Vincula otro para monitorear su salud desde tu panel.</p>
                    <a href="{{ route('doctor.link') }}" class="btn-diab-primary d-inline-flex align-items-center gap-2" style="font-size:0.82rem;">
                        <i class="fa-solid fa-link"></i> Vincular otro paciente
                    </a>
                </div>
            @endif

            @if($doctorProfile?->isApproved() && $patients->isEmpty())
                <x-empty-sidebar-guide profile="doctor" />
            @endif
        </aside>

        {{-- Contenido Principal --}}
        <section class="col-12 col-xl-8 order-1 order-xl-2">
            @if(session('status'))
                <div class="alert alert-success border-0 bg-success bg-opacity-10 animate-fade-in mb-4">
                    <i class="fa-solid fa-circle-check me-2 text-success"></i>
                    <span class="text-success fw-medium">{{ session('status') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning border-0 bg-warning bg-opacity-10 animate-fade-in mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('warning') }}</div>
            @endif

            @if(!$doctorProfile?->isApproved())
                <div class="diab-card p-5 text-center animate-fade-in">
                    <div class="admin-card-icon-wrapper mx-auto bg-diab-warning-light mb-4"><i class="fa-solid fa-user-shield fs-2 text-diab-warning"></i></div>
                    @if($doctorProfile?->approval_status === 'rejected')
                        <h4 class="fw-bold mb-2">Tu información profesional requiere correcciones</h4>
                        <p class="text-muted mb-3">Un administrador revisó tu solicitud. Actualiza tus datos profesionales o comunícate con soporte antes de vincular pacientes.</p>
                        @if($doctorProfile->review_notes)<div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-start"><strong>Observaciones:</strong> {{ $doctorProfile->review_notes }}</div>@endif
                    @else
                        <h4 class="fw-bold mb-2">Estamos verificando tu perfil médico</h4>
                        <p class="text-muted mb-4">La cédula <strong>{{ $doctorProfile?->license_number }}</strong> y tu especialidad serán revisadas por un administrador. Recibirás un correo cuando puedas comenzar a vincular pacientes.</p>
                        <div class="row g-3 text-start">
                            <div class="col-md-4"><div class="p-3 rounded-4 bg-light h-100"><i class="fa-solid fa-file-circle-check text-success me-2"></i><strong class="small">Datos recibidos</strong></div></div>
                            <div class="col-md-4"><div class="p-3 rounded-4 bg-warning bg-opacity-10 h-100"><i class="fa-solid fa-magnifying-glass text-warning me-2"></i><strong class="small">Validación en proceso</strong></div></div>
                            <div class="col-md-4"><div class="p-3 rounded-4 bg-light h-100"><i class="fa-solid fa-envelope text-muted me-2"></i><strong class="small">Aviso por correo</strong></div></div>
                        </div>
                    @endif
                </div>
            @elseif($patients->isEmpty())
                <x-empty-linked-patients profile="doctor" />
            @else
                @if($selectedPatient)
                    {{-- Encabezado del Paciente Seleccionado --}}
                    <div class="diab-card p-4 mb-4 animate-fade-in">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle overflow-hidden shadow-sm flex-shrink-0" style="width: 60px; height: 60px; border: 2px solid var(--diab-primary);">
                                @php
                                    $gender = strtolower($selectedPatient->patientProfile?->gender ?? '');
                                    $avatar = $selectedPatient->avatar;
                                @endphp
                                @if($avatar && str_starts_with($avatar, 'http'))
                                    <img src="{{ $avatar }}" class="w-100 h-100 object-fit-cover" alt="{{ $selectedPatient->name }}">
                                @elseif($avatar)
                                    <img src="{{ asset('storage/' . $avatar) }}" class="w-100 h-100 object-fit-cover" alt="{{ $selectedPatient->name }}">
                                @else
                                    @if($gender === 'femenino')
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fs-3" style="background: linear-gradient(135deg, var(--diab-danger) 0%, #C0392B 100%);">
                                            <i class="fa-solid fa-person-dress"></i>
                                        </div>
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fs-3" style="background: linear-gradient(135deg, var(--diab-primary) 0%, var(--diab-primary-hover) 100%);">
                                            <i class="fa-solid fa-user-tie"></i>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <div class="ms-3">
                                <h3 class="fw-bold mb-1 fs-4 text-dark">{{ $selectedPatient->name }}</h3>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="badge bg-light text-dark border extra-small">{{ $selectedPatient->patientProfile?->diabetes_type ?? '--' }}</span>
                                    <span class="text-muted extra-small"><i class="fa-solid fa-calendar me-1"></i>{{ $selectedPatient->patientProfile ? \Carbon\Carbon::parse($selectedPatient->patientProfile->birth_date)->age : '--' }} años</span>
                                    <span class="text-muted extra-small"><i class="fa-solid fa-weight-scale me-1"></i>{{ $selectedPatient->patientProfile?->weight ?? '--' }} kg</span>
                                    <span class="text-muted extra-small"><i class="fa-solid fa-arrows-up-down me-1"></i>{{ $selectedPatient->patientProfile?->height ?? '--' }} cm</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Formulario de Metas Clínicas --}}
                    <div class="diab-card p-4 mb-4 animate-fade-in" style="animation-delay: 0.05s;">
                        <h6 class="fw-bold mb-3 text-diab-primary text-uppercase letter-spacing-1 small">Establecer Metas Glucémicas</h6>
                        <form action="{{ route('doctor.patient.targets.update', $selectedPatient) }}" method="POST" class="row g-3 align-items-end">
                            @csrf
                            @method('PATCH')
                            <div class="col-12 col-sm-5">
                                <label class="form-label extra-small fw-bold">Límite Mínimo (mg/dL)</label>
                                <input type="number" name="target_glucose_min" class="form-control diab-input" value="{{ $selectedPatient->patientProfile?->target_glucose_min ?? 70 }}" required>
                            </div>
                            <div class="col-12 col-sm-5">
                                <label class="form-label extra-small fw-bold">Límite Máximo (mg/dL)</label>
                                <input type="number" name="target_glucose_max" class="form-control diab-input" value="{{ $selectedPatient->patientProfile?->target_glucose_max ?? \App\Models\VitalSign::GLUCOSE_DEFAULT_MAX }}" required>
                            </div>
                            <div class="col-12 col-sm-2">
                                <button type="submit" class="btn-diab-primary w-100 py-2 extra-small">Actualizar</button>
                            </div>
                        </form>
                    </div>

                    {{-- Tarjetas de Métricas Clínicas --}}
                    <div class="row g-4 mb-4">
                        {{-- Glucosa Actual --}}
                        <div class="col-12 col-md-3 animate-fade-in" style="animation-delay: 0.1s;">
                            <div class="diab-card p-4 h-100 text-center">
                                <h6 class="text-muted extra-small text-uppercase fw-bold letter-spacing-1 mb-2">Glucosa Actual</h6>
                                <h2 class="fw-extrabold mb-0 text-diab-primary">{{ $ultimaMedicion['glucose_level'] ?? '--' }}</h2>
                                <span class="text-muted extra-small">mg/dL</span>
                            </div>
                        </div>

                        {{-- Tiempo en Rango --}}
                        <div class="col-12 col-md-3 animate-fade-in" style="animation-delay: 0.15s;">
                            <div class="diab-card p-4 h-100 text-center">
                                <h6 class="text-muted extra-small text-uppercase fw-bold letter-spacing-1 mb-2">Tiempo en Rango</h6>
                                <h2 class="fw-extrabold mb-0 text-success">{{ $tiempoEnRango }}%</h2>
                                <span class="text-muted extra-small">últimos 7 días</span>
                            </div>
                        </div>

                        {{-- HbA1c Estimada --}}
                        <div class="col-12 col-md-3 animate-fade-in" style="animation-delay: 0.2s;">
                            <div class="diab-card p-4 h-100 text-center">
                                <h6 class="text-muted extra-small text-uppercase fw-bold letter-spacing-1 mb-2">HbA1c Estimada</h6>
                                <h2 class="fw-extrabold mb-0 text-diab-info">{{ $ultimaHba1c['hba1c'] ?? '--' }}%</h2>
                                <span class="text-muted extra-small">basado en promedios</span>
                            </div>
                        </div>

                        {{-- Calorías --}}
                        <div class="col-12 col-md-3 animate-fade-in" style="animation-delay: 0.25s;">
                            <div class="diab-card p-4 h-100 text-center">
                                <h6 class="text-muted extra-small text-uppercase fw-bold letter-spacing-1 mb-2">Consumo Hoy</h6>
                                <h2 class="fw-extrabold mb-0 text-diab-warning">{{ $caloriasHoy }}</h2>
                                <span class="text-muted extra-small">kcal consumidas</span>
                            </div>
                        </div>
                    </div>

                    {{-- Gráfica de Tendencia --}}
                    <div class="diab-card p-4 mb-4 animate-fade-in" style="animation-delay: 0.3s;">
                        <h6 class="fw-bold mb-4 text-diab-text-secondary text-uppercase letter-spacing-1 small">Análisis de Tendencia Glucémica</h6>
                        <div style="height: 300px;">
                            <canvas id="glucoseChart"></canvas>
                        </div>
                    </div>

                    {{-- Historial Clínico Reciente --}}
                    <div class="diab-card p-4 animate-fade-in" style="animation-delay: 0.4s;">
                        <h6 class="fw-bold mb-3 text-diab-text-secondary text-uppercase letter-spacing-1 small">Historial Clínico Reciente</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="text-muted extra-small text-uppercase fw-bold">
                                    <tr>
                                        <th>Fecha/Hora</th>
                                        <th>Glucosa</th>
                                        <th>Presión (S/D)</th>
                                        <th>Ritmo Cardiaco</th>
                                        <th>Estado</th>
                                        <th>Estrés</th>
                                        <th>Notas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentLogs as $log)
                                        <tr>
                                            <td class="small">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                            <td><strong class="text-diab-primary">{{ $log->glucose_level ?? '--' }} <small>mg/dL</small></strong></td>
                                            <td class="small">{{ $log->systolic ?? '--' }}/{{ $log->diastolic ?? '--' }} <small>mmHg</small></td>
                                            <td class="small">{{ $log->heart_rate ?? '--' }} <small>bpm</small></td>
                                            <td>
                                                <span class="badge {{ $log->glucose_level > ($selectedPatient->patientProfile?->target_glucose_max ?? \App\Models\VitalSign::GLUCOSE_DEFAULT_MAX) ? 'bg-danger' : 'bg-success' }} bg-opacity-10 {{ $log->glucose_level > ($selectedPatient->patientProfile?->target_glucose_max ?? \App\Models\VitalSign::GLUCOSE_DEFAULT_MAX) ? 'text-danger' : 'text-success' }} extra-small">
                                                    {{ $log->glucose_level > ($selectedPatient->patientProfile?->target_glucose_max ?? \App\Models\VitalSign::GLUCOSE_DEFAULT_MAX) ? 'Fuera de Rango' : 'En Rango' }}
                                                </span>
                                            </td>
                                            <td class="small">
                                                @if($log->stress_level)
                                                    <span class="badge bg-light text-dark border extra-small">
                                                        <i class="fa-solid fa-face-{{ $log->stress_level == 'Bajo' ? 'smile' : ($log->stress_level == 'Medio' ? 'meh' : 'frown') }} me-1"></i>
                                                        {{ $log->stress_level }}
                                                    </span>
                                                @else
                                                    --
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->notes)
                                                    <i class="fa-solid fa-note-sticky text-muted cursor-help" data-bs-toggle="tooltip" title="{{ $log->notes }}"></i>
                                                @else
                                                    --
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center py-4 text-muted small">No hay registros clínicos suficientes.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif
        </section>
    </div>
</main>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('glucoseChart');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--diab-primary').trim() || '#00B4D8';
            const primaryLight = getComputedStyle(document.documentElement).getPropertyValue('--diab-primary-light').trim() || 'rgba(0, 180, 216, 0.08)';

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($glucosaLabels ?? []) !!},
                    datasets: [{
                        label: 'Glucosa Promedio (mg/dL)',
                        data: {!! json_encode($glucosaData ?? []) !!},
                        borderColor: primaryColor,
                        backgroundColor: primaryLight,
                        borderWidth: 2.5,
                        pointBackgroundColor: primaryColor,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: true,
                        spanGaps: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleFont: { family: 'Inter', size: 11 },
                            bodyFont: { family: 'Inter', size: 12, weight: 600 },
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.parsed.y + ' mg/dL';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: false,
                            grid: { color: 'rgba(0,0,0,0.03)' },
                            ticks: {
                                font: { family: 'Inter', size: 10 },
                                color: '#94A3B8'
                            }
                        },
                        x: { 
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Inter', size: 10 },
                                color: '#94A3B8'
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
