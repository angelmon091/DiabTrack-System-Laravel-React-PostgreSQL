@extends('layouts.app')

@section('title', 'Expediente Clínico - ' . $patient->name)

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
                        <span class="text-muted small">Peso Inicial:</span>
                        <span class="fw-bold small">{{ $patient->patientProfile?->weight ?? '--' }} kg</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Estatura:</span>
                        <span class="fw-bold small">{{ $patient->patientProfile?->height ?? '--' }} cm</span>
                    </div>
                </div>
            </div>

            {{-- Ajustar Metas Clínicas --}}
            <div class="diab-card p-4 mb-4 animate-fade-in" style="animation-delay: 0.1s;">
                <h6 class="fw-bold mb-1 text-diab-primary text-uppercase letter-spacing-1 small">Metas Glucémicas <span class="text-muted">(en ayunas)</span></h6>
                <p class="text-muted extra-small mb-3">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Este rango se aplica a las mediciones <strong>en ayunas</strong>. Las lecturas antes/después de comer y al dormir se evalúan con umbrales clínicos estándar.
                </p>
                <form action="{{ route('doctor.patient.targets.update', $patient) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label extra-small fw-bold">Mínimo en ayunas (mg/dL)</label>
                        <input type="number" name="target_glucose_min" class="form-control diab-input" value="{{ $patient->patientProfile?->target_glucose_min ?? 70 }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label extra-small fw-bold">Máximo en ayunas (mg/dL)</label>
                        <input type="number" name="target_glucose_max" class="form-control diab-input" value="{{ $patient->patientProfile?->target_glucose_max ?? \App\Models\VitalSign::GLUCOSE_DEFAULT_MAX }}" required>
                    </div>
                    <button type="submit" class="btn-diab-primary w-100 extra-small">Actualizar Metas</button>
                </form>
            </div>
            
            <a href="{{ route('doctor.dashboard') }}" class="btn btn-outline-secondary w-100 animate-fade-in">
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
                <div class="col-12 col-md-3 animate-fade-in">
                    <div class="diab-card p-4 h-100 text-center">
                        <h6 class="text-muted extra-small text-uppercase fw-bold letter-spacing-1 mb-2">Glucosa Actual</h6>
                        <h2 class="fw-extrabold mb-0 text-diab-primary">{{ $ultimaMedicion['glucose_level'] ?? '--' }}</h2>
                        <span class="text-muted extra-small">mg/dL</span>
                    </div>
                </div>

                {{-- Tiempo en Rango --}}
                <div class="col-12 col-md-3 animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="diab-card p-4 h-100 text-center">
                        <h6 class="text-muted extra-small text-uppercase fw-bold letter-spacing-1 mb-2">Tiempo en Rango</h6>
                        <h2 class="fw-extrabold mb-0 text-success">{{ $tiempoEnRango }}%</h2>
                        <span class="text-muted extra-small">últimos 7 días</span>
                    </div>
                </div>

                {{-- HbA1c --}}
                <div class="col-12 col-md-3 animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="diab-card p-4 h-100 text-center">
                        <h6 class="text-muted extra-small text-uppercase fw-bold letter-spacing-1 mb-2">HbA1c Estimada</h6>
                        <h2 class="fw-extrabold mb-0 text-diab-info">{{ $ultimaHba1c['hba1c'] ?? '--' }}%</h2>
                        <span class="text-muted extra-small">basado en promedios</span>
                    </div>
                </div>

                {{-- Calorías Hoy --}}
                <div class="col-12 col-md-3 animate-fade-in" style="animation-delay: 0.3s;">
                    <div class="diab-card p-4 h-100 text-center">
                        <h6 class="text-muted extra-small text-uppercase fw-bold letter-spacing-1 mb-2">Consumo Hoy</h6>
                        <h2 class="fw-extrabold mb-0 text-diab-warning">{{ $caloriasHoy }}</h2>
                        <span class="text-muted extra-small">kcal consumidas</span>
                    </div>
                </div>
            </div>

            {{-- Gráfica de Tendencia --}}
            <div class="diab-card p-4 mb-4 animate-fade-in" style="animation-delay: 0.4s;">
                <h6 class="fw-bold mb-4 text-diab-text-secondary text-uppercase letter-spacing-1 small">Análisis de Tendencia Glucémica</h6>
                <div style="height: 300px;">
                    <canvas id="glucoseChart"></canvas>
                </div>
            </div>

            {{-- Tabla Clínica --}}
            <div class="diab-card p-4 animate-fade-in" style="animation-delay: 0.5s;">
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
                                        <span class="badge {{ $log->glucose_level > ($patient->patientProfile?->target_glucose_max ?? \App\Models\VitalSign::GLUCOSE_DEFAULT_MAX) ? 'bg-danger' : 'bg-success' }} bg-opacity-10 {{ $log->glucose_level > ($patient->patientProfile?->target_glucose_max ?? \App\Models\VitalSign::GLUCOSE_DEFAULT_MAX) ? 'text-danger' : 'text-success' }} extra-small">
                                            {{ $log->glucose_level > ($patient->patientProfile?->target_glucose_max ?? \App\Models\VitalSign::GLUCOSE_DEFAULT_MAX) ? 'Fuera de Rango' : 'En Rango' }}
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
                                <tr><td colspan="5" class="text-center py-4 text-muted small">No hay registros clínicos suficientes.</td></tr>
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

    function toggleRejectionForm(id) {
        const form = document.getElementById('reject-form-' + id);
        if (form.classList.contains('d-none')) {
            form.classList.remove('d-none');
        } else {
            form.classList.add('d-none');
        }
    }
</script>
@endsection
