@extends('layouts.app')

@section('title', 'Detalle de Paciente - ' . $patient->name)

@section('content')
<main class="container-fluid py-4 px-md-5">
    <div class="row g-4">
        {{-- Sidebar con Info del Paciente --}}
        <aside class="col-12 col-xl-3">
            <div class="diab-card p-4 mb-4 animate-fade-in text-center">
                <div class="rounded-circle overflow-hidden shadow-sm mx-auto mb-3" style="width: 100px; height: 100px;">
                    @php
                        $gender = strtolower($patient->patientProfile?->gender ?? '');
                        $avatar = $patient->avatar;
                    @endphp
                    @if($avatar && str_starts_with($avatar, 'http'))
                        <img src="{{ $avatar }}" class="w-100 h-100 object-fit-cover" alt="{{ $patient->name }}">
                    @elseif($avatar)
                        <img src="{{ asset('storage/' . $avatar) }}" class="w-100 h-100 object-fit-cover" alt="{{ $patient->name }}">
                    @else
                        @if($gender === 'femenino')
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fs-1" style="background: linear-gradient(135deg, var(--diab-danger) 0%, #C0392B 100%);">
                                <i class="fa-solid fa-person-dress"></i>
                            </div>
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fs-1" style="background: linear-gradient(135deg, var(--diab-primary) 0%, var(--diab-primary-hover) 100%);">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                        @endif
                    @endif
                </div>
                <h4 class="fw-bold mb-1">{{ $patient->name }}</h4>
                <p class="text-muted small mb-0">{{ $patient->patientProfile?->diabetes_type ?? '--' }}</p>
                <hr class="my-4">
                <div class="d-flex flex-column gap-2 text-start">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Edad:</span>
                        <span class="fw-bold small">{{ $patient->patientProfile ? \Carbon\Carbon::parse($patient->patientProfile->birth_date)->age : '--' }} años</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Peso:</span>
                        <span class="fw-bold small">{{ $patient->patientProfile?->weight ?? '--' }} kg</span>
                    </div>
                </div>
            </div>

            {{-- Acción de Registro --}}
            <div class="diab-card p-4 mb-4 animate-fade-in" style="animation-delay: 0.1s;">
                <h6 class="fw-bold mb-3 text-diab-primary text-uppercase letter-spacing-1 small">Acciones Rápidas</h6>
                <p class="extra-small text-muted mb-3">Registra las mediciones de salud de tu paciente ahora.</p>
                <a href="{{ route('caregiver.patient.vital.create', $patient) }}" class="btn-diab-primary w-100 d-flex align-items-center justify-content-center gap-2 text-decoration-none">
                    {{ __('Añadir Registro') }}
                </a>
            </div>
            
            <a href="{{ route('caregiver.dashboard') }}" class="btn btn-outline-secondary w-100 animate-fade-in">
                <i class="fa-solid fa-arrow-left me-2"></i>Volver al Panel
            </a>
        </aside>

        {{-- Métricas y Gráficas --}}
        <section class="col-12 col-xl-9">
            @if(session('status'))
                <div class="alert alert-success border-0 bg-success bg-opacity-10 animate-fade-in mb-4">
                    <i class="fa-solid fa-circle-check me-2 text-success"></i>
                    <span class="text-success fw-medium">{{ session('status') }}</span>
                </div>
            @endif

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
        </section>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('glucoseChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($glucosaLabels) !!},
            datasets: [{
                label: 'Glucosa Promedio',
                data: {!! json_encode($glucosaData) !!},
                borderColor: 'var(--diab-primary)',
                backgroundColor: 'var(--diab-primary-light)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'var(--diab-primary)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { 
                    beginAtZero: false,
                    grid: { color: 'rgba(0,0,0,0.03)' }
                },
                x: { 
                    grid: { display: false }
                }
            }
        }
    });

</script>
@endsection
