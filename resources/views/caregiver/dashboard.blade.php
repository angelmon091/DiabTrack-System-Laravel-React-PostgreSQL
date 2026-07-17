@extends('layouts.app')

@section('title', 'DiabTrack - Panel de Cuidador')

@section('content')
<main class="container-fluid py-4 px-md-5">
    <div class="row g-4">

        {{-- Sidebar --}}
        <aside class="col-12 col-xl-4 order-2 order-xl-1">
            {{-- Panel de Cuidador --}}
            <div class="diab-card p-4 mb-4 animate-fade-in">
                <div class="tool-header mb-4 d-flex align-items-center text-diab-primary">
                    <i class="fa-solid fa-hand-holding-heart me-2"></i>
                    <span class="fw-bold">Panel de Cuidador</span>
                </div>

                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('caregiver.link') }}" class="action-item">
                        <div class="action-icon orange"><i class="fa-solid fa-link"></i></div>
                        <div class="ms-3">
                            <strong class="d-block">Vincular Paciente</strong>
                            <p class="mb-0 extra-small text-muted">Conecta usando un código</p>
                        </div>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="action-item">
                        <div class="action-icon gray"><i class="fa-solid fa-sliders"></i></div>
                        <div class="ms-3">
                            <strong class="d-block">Ajustes</strong>
                            <p class="mb-0 extra-small text-muted">Configurar perfil</p>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Lista de Pacientes --}}
            @if($patients->isNotEmpty())
                <div class="diab-card p-4 mb-4 animate-fade-in" style="animation-delay: 0.1s;">
                    <h6 class="fw-bold mb-3 text-muted text-uppercase letter-spacing-1 small">Pacientes Vinculados</h6>
                    <div class="d-flex flex-column gap-3">
                        @foreach($patients as $p)
                            @php
                                $isSelected = $selectedPatient && $p->id === $selectedPatient->id;
                                $gender = strtolower($p->patientProfile?->gender ?? '');
                                $avatar = $p->avatar;
                            @endphp
                            <div class="p-3 rounded-4 transition-hover position-relative" style="background: {{ $isSelected ? 'rgba(0, 194, 224, 0.08)' : 'var(--diab-card-bg, #fff)' }}; border: 2px solid {{ $isSelected ? 'var(--diab-primary)' : 'rgba(0,0,0,0.05)' }};">
                                <a href="{{ route('caregiver.dashboard', ['patient_id' => $p->id]) }}" class="text-decoration-none d-flex align-items-center mb-2">
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
                                        <span class="text-muted extra-small">Parentesco: {{ $p->pivot->relationship ?? (auth()->user()->caregiverProfile?->relationship ?? 'Paciente') }}</span>
                                    </div>
                                </a>
                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-light">
                                    <span class="extra-small text-muted" style="font-size: 0.7rem;">
                                        Glucosa: <strong>{{ $p->vitalSigns->sortByDesc('created_at')->first()?->glucose_level ?? '--' }} mg/dL</strong>
                                    </span>
                                    <form action="{{ route('caregiver.patient.unlink', $p) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas desvincular a este paciente? Perderás el acceso a sus datos de salud.');" class="d-inline">
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
                    <p class="small text-muted mb-3">¿Cuidas a alguien más? Vincula otro paciente para acompañar su salud desde aquí.</p>
                    <a href="{{ route('caregiver.link') }}" class="btn-diab-primary d-inline-flex align-items-center gap-2" style="font-size:0.82rem;">
                        <i class="fa-solid fa-link"></i> Vincular otro paciente
                    </a>
                </div>
            @endif

            @if($patients->isEmpty())
                <x-empty-sidebar-guide profile="caregiver" />
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

            @if($patients->isEmpty())
                <x-empty-linked-patients profile="caregiver" />
            @else
                @if($selectedPatient)
                    {{-- Encabezado del Paciente Seleccionado --}}
                    <div class="diab-card p-4 mb-4 animate-fade-in">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
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
                                        <span class="badge bg-diab-primary-light text-diab-primary extra-small">Parentesco: {{ $selectedPatient->pivot->relationship ?? (auth()->user()->caregiverProfile?->relationship ?? 'Paciente') }}</span>
                                        <span class="text-muted extra-small"><i class="fa-solid fa-calendar me-1"></i>{{ $selectedPatient->patientProfile ? \Carbon\Carbon::parse($selectedPatient->patientProfile->birth_date)->age : '--' }} años</span>
                                        <span class="text-muted extra-small"><i class="fa-solid fa-weight-scale me-1"></i>{{ $selectedPatient->patientProfile?->weight ?? '--' }} kg</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('caregiver.patient.vital.create', $selectedPatient) }}" class="btn-diab-primary py-2 px-3 text-decoration-none d-inline-flex align-items-center gap-2">
                                    <i class="fa-solid fa-plus"></i> Registrar Datos
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Tarjetas de Métricas del Paciente --}}
                    <div class="row g-4 mb-4">
                        {{-- Glucosa --}}
                        <div class="col-12 col-md-4 animate-fade-in">
                            <div class="diab-card p-4 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="admin-card-icon-wrapper bg-diab-primary-light text-diab-primary mb-0" style="width: 40px; height: 40px; border-radius: 12px;">
                                        <i class="fa-solid fa-droplet"></i>
                                    </div>
                                    <span class="badge {{ $ultimaMedicion && isset($ultimaMedicion['glucose_level']) && $ultimaMedicion['glucose_level'] > 140 ? 'bg-danger' : 'bg-success' }} bg-opacity-10 {{ $ultimaMedicion && isset($ultimaMedicion['glucose_level']) && $ultimaMedicion['glucose_level'] > 140 ? 'text-danger' : 'text-success' }} extra-small">
                                        {{ $ultimaMedicion && isset($ultimaMedicion['glucose_level']) && $ultimaMedicion['glucose_level'] > 140 ? 'Elevado' : 'Normal' }}
                                    </span>
                                </div>
                                <h6 class="text-muted extra-small text-uppercase fw-bold letter-spacing-1">Glucosa Actual</h6>
                                <div class="d-flex align-items-baseline">
                                    <h2 class="fw-extrabold mb-0">{{ $ultimaMedicion['glucose_level'] ?? '--' }}</h2>
                                    <span class="ms-1 text-muted small">mg/dL</span>
                                </div>
                            </div>
                        </div>

                        {{-- Tiempo en Rango --}}
                        <div class="col-12 col-md-4 animate-fade-in" style="animation-delay: 0.1s;">
                            <div class="diab-card p-4 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="admin-card-icon-wrapper bg-diab-success-light text-diab-primary mb-0" style="width: 40px; height: 40px; border-radius: 12px;">
                                        <i class="fa-solid fa-chart-pie"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted extra-small text-uppercase fw-bold letter-spacing-1">Tiempo en Rango</h6>
                                <div class="d-flex align-items-baseline">
                                    <h2 class="fw-extrabold mb-0">{{ $tiempoEnRango }}%</h2>
                                    <span class="ms-1 text-muted small">últimos 7 días</span>
                                </div>
                            </div>
                        </div>

                        {{-- HbA1c Estimada --}}
                        <div class="col-12 col-md-4 animate-fade-in" style="animation-delay: 0.2s;">
                            <div class="diab-card p-4 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="admin-card-icon-wrapper bg-diab-info-light text-diab-info mb-0" style="width: 40px; height: 40px; border-radius: 12px;">
                                        <i class="fa-solid fa-vial"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted extra-small text-uppercase fw-bold letter-spacing-1">HbA1c Estimada</h6>
                                <div class="d-flex align-items-baseline">
                                    <h2 class="fw-extrabold mb-0">{{ $ultimaHba1c['hba1c'] ?? '--' }}</h2>
                                    <span class="ms-1 text-muted small">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Gráfica de Tendencia --}}
                    <div class="diab-card p-4 mb-4 animate-fade-in" style="animation-delay: 0.3s;">
                        <h6 class="fw-bold mb-4 text-diab-text-secondary text-uppercase letter-spacing-1 small">Tendencia Semanal (Glucosa)</h6>
                        <div style="height: 300px;">
                            <canvas id="glucoseChart"></canvas>
                        </div>
                    </div>

                    {{-- Últimos Registros --}}
                    <div class="diab-card p-4 animate-fade-in" style="animation-delay: 0.4s;">
                        <h6 class="fw-bold mb-3 text-diab-text-secondary text-uppercase letter-spacing-1 small">Registros Recientes</h6>
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead class="text-muted extra-small text-uppercase fw-bold">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Nivel</th>
                                        <th>Momento</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentLogs as $log)
                                        <tr>
                                            <td class="small">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                            <td><strong class="text-diab-primary">{{ $log->glucose_level }} <small>mg/dL</small></strong></td>
                                            <td class="small text-muted">{{ $log->measurement_moment }}</td>
                                            <td>
                                                <span class="badge {{ $log->glucose_level > 140 ? 'bg-danger' : 'bg-success' }} bg-opacity-10 {{ $log->glucose_level > 140 ? 'text-danger' : 'text-success' }} extra-small">
                                                    {{ $log->glucose_level > 140 ? 'Elevado' : 'Normal' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted small">No hay registros recientes.</td></tr>
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
