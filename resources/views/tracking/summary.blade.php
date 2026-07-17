@extends('layouts.app')

@section('title', 'DiabTrack - Resumen Integral')

@section('styles')
    @vite('resources/css/visualizacion.css')
    <style>
        .stat-card {
            border: 1px solid rgba(255, 255, 255, 0.45);
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px !important;
            overflow: hidden;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
        }
        .diab-card {
            background: rgba(255, 255, 255, 0.75) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
            border-radius: 20px !important;
        }
        .stat-card.border-4 {
            border-width: 1px !important;
            border-left-width: 6px !important;
        }

        .info-icon {
            cursor: help;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        .info-icon:hover {
            color: var(--diab-primary) !important;
            opacity: 1 !important;
        }
        .nav-tabs-custom {
            border-bottom: 2px solid #f0f0f0;
            gap: 2rem;
            margin-bottom: 0 !important;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            color: var(--diab-text-secondary);
            font-weight: 600;
            padding: 1rem 0;
            position: relative;
            background: transparent;
        }
        .nav-tabs-custom .nav-link.active {
            color: var(--diab-primary);
            background: transparent !important;
            border: none;
        }
        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--diab-primary);
        }
        .history-table th {
            background: rgba(0, 0, 0, 0.03);
            color: var(--diab-text-secondary);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: none;
            padding: 1rem;
        }
        .history-table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            background: transparent !important;
        }
        .history-table {
            background: transparent !important;
            margin-bottom: 0;
        }
        .badge-glucose {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 700;
        }
        .chart-container-sm {
            position: relative;
            height: 220px;
            width: 100%;
        }
        .chart-empty-state {
            min-height: 220px;
            color: var(--diab-text-secondary);
        }
        .chart-empty-state .empty-icon {
            width: 52px;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            color: var(--diab-primary);
            background: var(--diab-primary-light);
            font-size: 1.25rem;
        }
        .insight-card {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 16px;
            padding: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(5px);
        }
        @media (max-width: 768px) {
            .nav-tabs-custom {
                gap: 0.5rem;
                display: flex;
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 0;
            }
            .nav-tabs-custom .nav-link {
                white-space: nowrap;
                font-size: 0.85rem;
            }
            .stat-card {
                padding: 1.25rem !important;
            }
        }
    .period-btn {
        padding: 0.35rem 1rem;
        border: 1.5px solid rgba(0,0,0,0.1);
        border-radius: 50px;
        background: transparent;
        color: var(--diab-text-secondary, #64748b);
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Inter', sans-serif;
        white-space: nowrap;
    }
    .period-btn:hover {
        border-color: var(--diab-primary);
        color: var(--diab-primary);
    }
    .period-btn.active {
        background: var(--diab-primary);
        border-color: var(--diab-primary);
        color: #fff;
    }
    .btn-show-more {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.5rem 1.5rem;
        border: 1.5px solid rgba(0, 180, 216, 0.35);
        border-radius: 50px;
        background: transparent;
        color: var(--diab-primary);
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Inter', sans-serif;
    }
    .btn-show-more:hover {
        background: var(--diab-primary-light, #e0f7fc);
        border-color: var(--diab-primary);
    }
    .show-more-counter {
        background: rgba(0, 180, 216, 0.12);
        border-radius: 50px;
        padding: 0.1rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .pager-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1.5px solid rgba(0, 180, 216, 0.35);
        background: transparent;
        color: var(--diab-primary);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .pager-btn:hover:not(:disabled) {
        background: var(--diab-primary-light, #e0f7fc);
        border-color: var(--diab-primary);
    }
    .pager-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }
    </style>
@endsection

@section('content')
<main class="container-fluid py-4 px-md-5 mt-2">
        
        <!-- Encabezado -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div>
                <h2 class="fw-extrabold mb-1 fs-3">Visualización <span class="text-diab-primary">Integral</span></h2>
                <p class="text-muted small mb-0">Análisis detallado de todos tus registros históricos</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary rounded-pill px-3 px-md-4 btn-sm">
                    <i class="fa-solid fa-calendar-day me-2"></i> <span class="d-none d-sm-inline">Historial</span> Completo
                </button>
                <div class="btn btn-diab-primary rounded-pill px-3 px-md-4 btn-sm shadow-sm d-inline-flex align-items-center gap-2"
                     style="opacity: 0.55; cursor: not-allowed; pointer-events: none;">
                    <i class="fa-solid fa-file-pdf"></i> Reporte Médico
                    <span class="badge rounded-pill" style="font-size: 0.55rem; background: rgba(255,255,255,0.25); color: #fff; border: 1px solid rgba(255,255,255,0.4); letter-spacing: 0.03em;">
                        <i class="fa-solid fa-clock me-1" style="font-size: 0.5rem;"></i>Próximamente
                    </span>
                </div>
            </div>
        </div>

        <!-- Primera fila de métricas: glucosa y rangos -->
        <div class="row g-3 g-md-4 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card p-4 h-100 shadow-sm border-start border-4 border-primary">
                    <div class="extra-small fw-bold text-muted text-uppercase mb-2 letter-spacing-1 d-flex align-items-center justify-content-between">
                        <span>Glucosa Promedio</span>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="El promedio de tus niveles de azúcar en la sangre en tus últimos 30 registros."></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h2 class="fw-extrabold text-dark mb-0">{{ $avgGlucose ?: '--' }}</h2>
                        <span class="text-muted extra-small">mg/DL</span>
                    </div>
                    <div class="mt-3 extra-small {{ $glucoseStatus['color'] }}">
                        @if($glucoseStatus['icon'])<i class="fa-solid {{ $glucoseStatus['icon'] }} me-1"></i>@endif
                        <span class="d-none d-lg-inline">{{ $glucoseStatus['label'] }}</span>
                        <span class="d-inline d-lg-none">{{ $glucoseStatus['short'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card p-4 h-100 shadow-sm border-start border-4 border-success">
                    <div class="extra-small fw-bold text-muted text-uppercase mb-2 letter-spacing-1 d-flex align-items-center justify-content-between">
                        <span>Tiempo en Rango</span>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="Mide cuántas veces tu azúcar salió normal (ni muy alta ni muy baja). Lo ideal es que al menos 7 de cada 10 veces estés en el nivel adecuado."></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h2 class="fw-extrabold text-dark mb-0">{{ $tiempoEnRango }}%</h2>
                        <span class="text-muted extra-small">Meta: 70% o más</span>
                    </div>
                    <div class="progress mt-3" style="height: 6px; border-radius: 10px;">
                        <div class="progress-bar {{ $tiempoEnRango >= 70 ? 'bg-success' : ($tiempoEnRango > 0 ? 'bg-warning' : 'bg-secondary') }}" style="width: {{ $tiempoEnRango }}%"></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card p-4 h-100 shadow-sm border-start border-4 border-primary">
                    <div class="extra-small fw-bold text-muted text-uppercase mb-2 letter-spacing-1 d-flex align-items-center justify-content-between">
                        <span>HbA1c Estimada</span>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="Un cálculo de cómo ha estado tu azúcar en los últimos 3 meses en promedio."></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h2 class="fw-extrabold text-dark mb-0">{{ $ultimaHba1c ? number_format($ultimaHba1c['hba1c'], 1) : '--' }}</h2>
                        <span class="text-muted extra-small">%</span>
                    </div>
                    <p class="text-muted extra-small mt-3 mb-0">Basado en últimos 90 días</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card p-4 h-100 shadow-sm border-start border-4 border-secondary">
                    <div class="extra-small fw-bold text-muted text-uppercase mb-2 letter-spacing-1 d-flex align-items-center justify-content-between">
                        <span>Peso Actual</span>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="Tu último peso registrado."></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h2 class="fw-extrabold text-dark mb-0">{{ $totalWeight }}</h2>
                        <span class="text-muted extra-small">kg</span>
                    </div>
                    <div class="mt-3 extra-small text-muted">
                        <i class="fa-solid fa-scale-balanced me-1"></i> {{ $weightCount }} mediciones
                    </div>
                </div>
            </div>
        </div>

        <!-- Segunda fila de métricas: signos vitales y actividad -->
        <div class="row g-3 g-md-4 mb-5">
            <div class="col-6 col-md-3">
                <div class="stat-card p-4 h-100 shadow-sm border-start border-4 border-info">
                    <div class="extra-small fw-bold text-muted text-uppercase mb-2 letter-spacing-1 d-flex align-items-center justify-content-between">
                        <span>Presión Media</span>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="El promedio de tu presión arterial en tus últimos 30 registros."></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h2 class="fw-extrabold text-dark mb-0">{{ $avgSystolic }}/{{ $avgDiastolic }}</h2>
                        <span class="text-muted extra-small">mmHg</span>
                    </div>
                    @if($bpStatus['label'] === 'Sin datos')
                        <div class="mt-3 extra-small text-muted">Sin datos</div>
                    @else
                        <p class="text-muted extra-small mt-3 mb-0">Estado: <span class="{{ $bpStatus['color'] }} fw-bold">{{ $bpStatus['label'] }}</span></p>
                    @endif
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card p-4 h-100 shadow-sm border-start border-4 border-info">
                    <div class="extra-small fw-bold text-muted text-uppercase mb-2 letter-spacing-1 d-flex align-items-center justify-content-between">
                        <span>Frecuencia Cardiaca</span>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="El promedio de los latidos de tu corazón por minuto en tus últimos 30 registros."></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h2 class="fw-extrabold text-dark mb-0">{{ $avgHeartRate }}</h2>
                        <span class="text-muted extra-small">bpm (Prom)</span>
                    </div>
                    @if($hrStatus['label'] === 'Sin datos')
                        <div class="mt-3 extra-small text-muted">Sin datos</div>
                    @else
                        <div class="mt-3 extra-small text-muted">
                            <i class="fa-solid fa-heart-pulse {{ $hrStatus['color'] }} me-1"></i> <span class="{{ $hrStatus['color'] }} fw-bold">{{ $hrStatus['label'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card p-4 h-100 shadow-sm border-start border-4 border-warning">
                    <div class="extra-small fw-bold text-muted text-uppercase mb-2 letter-spacing-1 d-flex align-items-center justify-content-between">
                        <span>Carbs Totales</span>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="La cantidad total de carbohidratos (harinas, azúcares) que has comido hoy."></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h2 class="fw-extrabold text-dark mb-0">{{ number_format($nutritionHistory->sum('carbs_grams')) }}</h2>
                        <span class="text-muted extra-small">g registrados</span>
                    </div>
                    <p class="text-muted extra-small mt-3 mb-0">{{ $medicationCount }} tomas de medicación</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card p-4 h-100 shadow-sm border-start border-4 border-success">
                    <div class="extra-small fw-bold text-muted text-uppercase mb-2 letter-spacing-1 d-flex align-items-center justify-content-between">
                        <span>Actividad Total</span>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="El tiempo total que te has movido o hecho ejercicio hoy."></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h2 class="fw-extrabold text-dark mb-0">{{ round($totalActivityMinutes / 60, 1) }}</h2>
                        <span class="text-muted extra-small">horas totales</span>
                    </div>
                    <p class="text-muted extra-small mt-3 mb-0">{{ $symptomsCount }} síntomas reportados</p>
                </div>
            </div>
        </div>

        <!-- Fila de gráficas principales -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-lg-8">
                <div class="diab-card p-4 p-md-5 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Dinámica de Glucosa (7 días)</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div class="badge bg-diab-primary-light text-diab-primary rounded-pill px-3 py-2">Tendencia Temporal</div>
                            <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="Este gráfico te muestra de forma fácil si tu azúcar ha subido o bajado en los últimos 7 días."></i>
                        </div>
                    </div>
                    @if(collect($glucosaData)->contains(fn ($value) => (float) $value > 0))
                    <div style="height: 320px;">
                        <canvas id="mainDetailedChart"></canvas>
                    </div>
                    @else
                    <div class="chart-empty-state d-flex flex-column align-items-center justify-content-center text-center px-3" style="height:320px;">
                        <span class="empty-icon mb-3"><i class="fa-solid fa-chart-line"></i></span>
                        <p class="fw-bold text-dark mb-1">Aún no hay mediciones de glucosa</p>
                        <p class="small mb-0">Registra una medición para visualizar la tendencia de los últimos 7 días.</p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="diab-card p-4 p-md-5 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Composición de Dieta</h5>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="Te muestra visualmente qué tipo de comida estás comiendo más (ej. si comes más proteínas o más carbohidratos)."></i>
                    </div>
                    @if(!empty($foodCategoryData))
                    <div class="chart-container-sm flex-grow-1 d-flex align-items-center justify-content-center">
                        <canvas id="dietCompositionChart"></canvas>
                    </div>
                    @else
                    <div class="chart-container-sm flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center text-muted">
                        <i class="fa-solid fa-utensils mb-2" style="font-size:1.6rem;opacity:0.3;"></i>
                        <p class="small mb-0">Aún no hay datos de alimentación. Registra tus comidas para ver la composición de tu dieta.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Fila de gráficas secundarias -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-md-6">
                <div class="diab-card p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Frecuencia de Síntomas</h5>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="Cuáles han sido los síntomas o malestares que más has sentido últimamente."></i>
                    </div>
                    @if($symptomsHistory->isNotEmpty())
                    <div class="chart-container-sm">
                        <canvas id="symptomsFrequencyChart"></canvas>
                    </div>
                    @else
                    <div class="chart-container-sm chart-empty-state d-flex flex-column align-items-center justify-content-center text-center px-3">
                        <span class="empty-icon mb-3"><i class="fa-solid fa-notes-medical"></i></span>
                        <p class="fw-bold text-dark mb-1">Sin síntomas registrados</p>
                        <p class="small mb-0">Los síntomas aparecerán aquí cuando se agreguen al historial.</p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="diab-card p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Glucosa por Momento del Día</h5>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="Tu glucosa promedio según el momento en que la mides (en ayunas, antes/después de comer, al dormir). Te ayuda a ver a qué hora se te sube o baja el azúcar."></i>
                    </div>
                    @if(collect($glucoseByMomentData)->contains(fn ($value) => (float) $value > 0))
                    <div class="chart-container-sm">
                        <canvas id="glucoseMomentChart"></canvas>
                    </div>
                    @else
                    <div class="chart-container-sm chart-empty-state d-flex flex-column align-items-center justify-content-center text-center px-3">
                        <span class="empty-icon mb-3"><i class="fa-solid fa-clock"></i></span>
                        <p class="fw-bold text-dark mb-1">Sin mediciones por momento del día</p>
                        <p class="small mb-0">Registra la glucosa y su momento para comparar tus promedios.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pestañas del historial detallado -->
        <div class="diab-card shadow-sm border-0">
            <div class="px-4 pt-4 px-md-5 pt-md-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold mb-0">Explorador de Datos Históricos</h5>
                        <i class="fa-solid fa-circle-info info-icon opacity-50" data-bs-toggle="tooltip" title="Filtra por período y explora tus registros históricos por categoría."></i>
                    </div>
                    <div class="d-flex gap-2 flex-wrap" id="period-filters">
                        <button class="period-btn" data-period="hoy"    onclick="filterRows('hoy')">Hoy</button>
                        <button class="period-btn" data-period="semana" onclick="filterRows('semana')">Semana</button>
                        <button class="period-btn active" data-period="mes" onclick="filterRows('mes')">Mes</button>
                        <button class="period-btn" data-period="todo"   onclick="filterRows('todo')">Todo</button>
                    </div>
                </div>
                <ul class="nav nav-tabs nav-tabs-custom" id="historyTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="vitals-tab" data-bs-toggle="tab" data-bs-target="#vitals" type="button">Signos Vitales</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="nutrition-tab" data-bs-toggle="tab" data-bs-target="#nutrition" type="button">Nutrición</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button">Actividad</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="symptoms-tab" data-bs-toggle="tab" data-bs-target="#symptoms" type="button">Síntomas</button>
                    </li>
                </ul>
            </div>
            <hr class="m-0 opacity-10">
            <div class="p-4 p-md-5">
                <div class="tab-content" id="historyTabsContent">
                    <!-- Signos vitales -->
                    <div class="tab-pane fade show active" id="vitals">
                        <div class="table-responsive">
                            <table class="table history-table" id="vitals-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Glucosa</th>
                                        <th class="d-none d-md-table-cell">Momento</th>
                                        <th>Presión</th>
                                        <th class="d-none d-md-table-cell">FC</th>
                                        <th>Peso</th>
                                        <th>Estado</th>
                                        <th class="d-none d-lg-table-cell">Estrés</th>
                                        <th class="d-none d-lg-table-cell">Notas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vitalsHistory as $vital)
                                    <tr class="history-row" data-date="{{ $vital->created_at->format('Y-m-d') }}">
                                        <td class="small fw-semibold">{{ $vital->created_at->format('d M, H:i') }}</td>
                                        <td>
                                            <span class="badge-glucose {{ $vital->glucose_level > 140 ? 'bg-danger-light text-danger' : ($vital->glucose_level < 70 ? 'bg-warning-light text-warning' : 'bg-success-light text-success') }}">
                                                {{ $vital->glucose_level ?? '--' }}
                                            </span>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            @if($vital->measurement_moment)
                                                <span class="badge bg-light text-dark border extra-small">{{ $vital->measurement_moment }}</span>
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>{{ $vital->systolic && $vital->diastolic ? $vital->systolic . '/' . $vital->diastolic : '--' }}</td>
                                        <td class="d-none d-md-table-cell">{{ $vital->heart_rate ? $vital->heart_rate . ' bpm' : '--' }}</td>
                                        <td class="fw-bold">{{ $vital->weight ? $vital->weight . ' kg' : '--' }}</td>
                                        <td>
                                            @if($vital->glucose_level)
                                                @if($vital->glucose_level > 140)
                                                    <i class="fa-solid fa-circle-exclamation text-danger"></i>
                                                @elseif($vital->glucose_level < 70)
                                                    <i class="fa-solid fa-droplet-slash text-warning"></i>
                                                @else
                                                    <i class="fa-solid fa-circle-check text-success"></i>
                                                @endif
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            @if($vital->stress_level)
                                                <span class="badge bg-light text-dark border extra-small">
                                                    <i class="fa-solid fa-face-{{ $vital->stress_level == 'Bajo' ? 'smile' : ($vital->stress_level == 'Medio' ? 'meh' : 'frown') }} me-1"></i>
                                                    {{ $vital->stress_level }}
                                                </span>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            @if($vital->notes)
                                                <i class="fa-solid fa-note-sticky text-muted cursor-help" data-bs-toggle="tooltip" title="{{ $vital->notes }}"></i>
                                            @else
                                                --
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="9" class="text-center text-muted py-4 small">Sin registros aún.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center align-items-center gap-3 pt-3" id="vitals-pager" style="display:none;">
                            <button class="pager-btn" id="vitals-prev" onclick="pageTable('vitals-table', -1)" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
                            <span class="extra-small text-muted" id="vitals-pageinfo">Página 1 de 1</span>
                            <button class="pager-btn" id="vitals-next" onclick="pageTable('vitals-table', 1)" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>

                    <!-- Nutrición -->
                    <div class="tab-pane fade" id="nutrition">
                        <div class="table-responsive">
                            <table class="table history-table" id="nutrition-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Comida</th>
                                        <th>Carbs</th>
                                        <th class="d-none d-md-table-cell">Kcal</th>
                                        <th class="d-none d-md-table-cell">Categorías</th>
                                        <th>Medic.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($nutritionHistory as $log)
                                    <tr class="history-row" data-date="{{ \Carbon\Carbon::parse($log->consumed_at)->format('Y-m-d') }}">
                                        <td class="small fw-semibold">{{ \Carbon\Carbon::parse($log->consumed_at)->format('d M, H:i') }}</td>
                                        <td class="text-capitalize small">{{ $log->meal_type }}</td>
                                        <td class="fw-bold">{{ $log->carbs_grams }}g</td>
                                        <td class="text-muted d-none d-md-table-cell">{{ $log->carbs_grams * 4 }}</td>
                                        <td class="d-none d-md-table-cell">
                                            @if($log->food_categories)
                                                @foreach($log->food_categories as $cat)
                                                    <span class="badge bg-light text-dark border extra-small">{{ $cat }}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->medication_taken)
                                                <span class="text-diab-primary extra-small fw-bold" title="{{ $log->medication_taken }}"><i class="fa-solid fa-pills"></i> Sí</span>
                                            @else
                                                <span class="text-muted extra-small">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4 small">Sin registros aún.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center align-items-center gap-3 pt-3" id="nutrition-pager" style="display:none;">
                            <button class="pager-btn" id="nutrition-prev" onclick="pageTable('nutrition-table', -1)" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
                            <span class="extra-small text-muted" id="nutrition-pageinfo">Página 1 de 1</span>
                            <button class="pager-btn" id="nutrition-next" onclick="pageTable('nutrition-table', 1)" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>

                    <!-- Actividad -->
                    <div class="tab-pane fade" id="activity">
                        <div class="table-responsive">
                            <table class="table history-table" id="activity-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Duración</th>
                                        <th class="d-none d-md-table-cell">Intensidad</th>
                                        <th>Energía</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activityHistory as $act)
                                    <tr class="history-row" data-date="{{ $act->created_at->format('Y-m-d') }}">
                                        <td class="small fw-semibold">{{ $act->created_at->format('d M') }}</td>
                                        <td class="text-capitalize fw-bold small">{{ $act->activity_type }}</td>
                                        <td><span class="badge bg-diab-primary-light text-diab-primary rounded-pill">{{ $act->duration_minutes }} min</span></td>
                                        <td class="text-capitalize d-none d-md-table-cell small">{{ $act->intensity }}</td>
                                        <td>
                                            @php
                                                $energyIcons = [
                                                    'muy_baja' => '<i class="fa-solid fa-battery-empty text-danger"></i>',
                                                    'baja' => '<i class="fa-solid fa-battery-quarter text-warning"></i>',
                                                    'normal' => '<i class="fa-solid fa-battery-half text-info"></i>',
                                                    'alta' => '<i class="fa-solid fa-battery-three-quarters text-success"></i>',
                                                    'muy_alta' => '<i class="fa-solid fa-battery-full text-success"></i>',
                                                ];
                                            @endphp
                                            {!! $energyIcons[$act->energy_level] ?? '<i class="fa-solid fa-battery-half text-info"></i>' !!}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4 small">Sin registros aún.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center align-items-center gap-3 pt-3" id="activity-pager" style="display:none;">
                            <button class="pager-btn" id="activity-prev" onclick="pageTable('activity-table', -1)" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
                            <span class="extra-small text-muted" id="activity-pageinfo">Página 1 de 1</span>
                            <button class="pager-btn" id="activity-next" onclick="pageTable('activity-table', 1)" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>

                    <!-- Síntomas -->
                    <div class="tab-pane fade" id="symptoms">
                        <div class="table-responsive">
                            <table class="table history-table" id="symptoms-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Síntoma</th>
                                        <th>Categoría</th>
                                        <th>Hora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($symptomsHistory as $symptom)
                                    <tr class="history-row" data-date="{{ \Carbon\Carbon::parse($symptom->logged_at)->format('Y-m-d') }}">
                                        <td class="small fw-semibold">{{ \Carbon\Carbon::parse($symptom->logged_at)->format('d M') }}</td>
                                        <td class="fw-bold small">{{ $symptom->name }}</td>
                                        <td class="text-capitalize small">{{ $symptom->category }}</td>
                                        <td class="small text-muted">{{ \Carbon\Carbon::parse($symptom->logged_at)->format('H:i') }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4 small">Sin registros aún.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center align-items-center gap-3 pt-3" id="symptoms-pager" style="display:none;">
                            <button class="pager-btn" id="symptoms-prev" onclick="pageTable('symptoms-table', -1)" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
                            <span class="extra-small text-muted" id="symptoms-pageinfo">Página 1 de 1</span>
                            <button class="pager-btn" id="symptoms-next" onclick="pageTable('symptoms-table', 1)" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</main>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--diab-primary').trim() || '#00B4D8';
        const successColor = getComputedStyle(document.documentElement).getPropertyValue('--diab-success').trim() || '#28C76F';
        const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--diab-danger').trim() || '#EA5455';
        const warningColor = getComputedStyle(document.documentElement).getPropertyValue('--diab-warning').trim() || '#FF9F43';
        const infoColor = getComputedStyle(document.documentElement).getPropertyValue('--diab-info').trim() || '#00CFE8';
        const targetMin = @json($targetGlucoseMin ?? \App\Models\VitalSign::GLUCOSE_DEFAULT_MIN);
        const targetMax = @json($targetGlucoseMax ?? \App\Models\VitalSign::GLUCOSE_DEFAULT_MAX);

        const targetRangePlugin = {
            id: 'targetRange',
            beforeDatasetsDraw(chart, args, options) {
                if (!options || !chart.scales.y) return;
                const {ctx, chartArea: {left, right}, scales: {y}} = chart;
                const top = y.getPixelForValue(options.max);
                const bottom = y.getPixelForValue(options.min);
                ctx.save();
                ctx.fillStyle = 'rgba(40, 199, 111, 0.09)';
                ctx.fillRect(left, top, right - left, bottom - top);
                ctx.setLineDash([5, 4]);
                ctx.strokeStyle = 'rgba(40, 199, 111, 0.55)';
                [top, bottom].forEach(position => {
                    ctx.beginPath();
                    ctx.moveTo(left, position);
                    ctx.lineTo(right, position);
                    ctx.stroke();
                });
                ctx.restore();
            }
        };

        const barValueLabelsPlugin = {
            id: 'barValueLabels',
            afterDatasetsDraw(chart, args, options) {
                if (!options?.labels) return;
                const {ctx} = chart;
                ctx.save();
                ctx.textAlign = 'center';
                chart.getDatasetMeta(0).data.forEach((bar, index) => {
                    const value = chart.data.datasets[0].data[index];
                    if (!value) return;
                    ctx.fillStyle = '#0F172A';
                    ctx.font = '600 11px Inter, sans-serif';
                    ctx.fillText(`${value} mg/dL`, bar.x, Math.max(bar.y - 18, 12));
                    ctx.fillStyle = '#64748B';
                    ctx.font = '500 9px Inter, sans-serif';
                    ctx.fillText(options.labels[index] || '', bar.x, Math.max(bar.y - 6, 24));
                });
                ctx.restore();
            }
        };

        // Gráfica principal de glucosa con una vista simplificada de 30 días.
        const mainCtx = document.getElementById('mainDetailedChart');
        if (mainCtx) {
            new Chart(mainCtx, {
                type: 'line',
                data: {
                    labels: @json($glucosaLabels),
                    datasets: [{
                        label: 'Glucosa',
                        data: @json($glucosaData),
                        borderColor: primaryColor,
                        backgroundColor: 'rgba(0, 180, 216, 0.1)',
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: (context) => {
                            const value = context.raw;
                            if (value < targetMin) return warningColor;
                            if (value > targetMax) return dangerColor;
                            return successColor;
                        },
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        spanGaps: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F172A', padding: 12, cornerRadius: 8,
                            callbacks: { label: (context) => `Glucosa: ${context.parsed.y} mg/dL` }
                        },
                        targetRange: { min: targetMin, max: targetMax }
                    },
                    scales: {
                        y: {
                            title: { display: true, text: 'mg/dL', color: '#64748B', font: { size: 11, weight: 600 } },
                            grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 11 } }
                        },
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                },
                plugins: [targetRangePlugin]
            });
        }

        // Gráfica circular de composición de la dieta.
        const dietCtx = document.getElementById('dietCompositionChart');
        if (dietCtx) {
            new Chart(dietCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($foodCategoryLabels),
                    datasets: [{
                        data: @json($foodCategoryData),
                        backgroundColor: [primaryColor, successColor, '#FFB55E', '#7C83FD', '#48CAE4', '#A78BFA', '#64748B', '#90E0EF'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        cutout: '65%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true, 
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 14, font: { size: 10, family: 'Inter' } }
                        },
                        tooltip: { backgroundColor: '#0F172A', padding: 12, cornerRadius: 8 }
                    }
                }
            });
        }

        // Gráfica de barras para la frecuencia de síntomas.
        const sympCtx = document.getElementById('symptomsFrequencyChart');
        if (sympCtx) {
            // Prepara los datos de síntomas utilizados por la gráfica.
            const symptoms = @json($symptomsHistory);
            const counts = {};
            symptoms.forEach(s => counts[s.name] = (counts[s.name] || 0) + 1);
            const labels = Object.keys(counts).slice(0, 6);
            const data = Object.values(counts).slice(0, 6);

            new Chart(sympCtx, {
                type: 'bar',
                data: {
                    labels: labels.length ? labels : ['Sin datos'],
                    datasets: [{
                        data: data.length ? data : [0],
                        backgroundColor: 'rgba(244, 124, 130, 0.78)',
                        borderColor: '#F47C82',
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (context) => `${context.parsed.x} registro${context.parsed.x === 1 ? '' : 's'}` } }
                    },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0, stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.03)' } },
                        y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        }

        // Glucosa promedio por momento del día (datos reales de measurement_moment)
        const momentCtx = document.getElementById('glucoseMomentChart');
        if (momentCtx) {
            const gLabels = @json($glucoseByMomentLabels);
            const gData   = @json($glucoseByMomentData);
            const colors  = @json($glucoseByMomentColors);
            const counts  = @json($glucoseByMomentCounts ?? array_fill(0, 4, 0));
            const statuses = @json($glucoseByMomentStatuses ?? array_fill(0, 4, 'Sin registros'));

            new Chart(momentCtx, {
                type: 'bar',
                data: {
                    labels: gLabels,
                    datasets: [{
                        label: 'Glucosa promedio (mg/dL)',
                        data: gData,
                        backgroundColor: (colors && colors.length) ? colors : 'rgba(0,180,216,0.6)',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        barValueLabels: { labels: statuses },
                        tooltip: {
                            backgroundColor: '#0F172A', padding: 12, cornerRadius: 8,
                            callbacks: {
                                label: (c) => c.parsed.y > 0 ? `${c.parsed.y} mg/dL · ${statuses[c.dataIndex]}` : 'Sin registros',
                                afterLabel: (c) => counts[c.dataIndex] ? `${counts[c.dataIndex]} medición${counts[c.dataIndex] === 1 ? '' : 'es'}` : ''
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: Math.max(...gData, targetMax) + 40,
                            title: { display: true, text: 'mg/dL', color: '#64748B', font: { size: 11, weight: 600 } },
                            grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 11 } }
                        },
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                },
                plugins: [barValueLabelsPlugin]
            });
        }
    });

    // Inicializar Tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // ── Filtro por período + paginación ──
    const PER_PAGE = 10;
    let activePeriod = 'mes';
    const currentPage = {};

    const TABLES = ['vitals-table', 'nutrition-table', 'activity-table', 'symptoms-table'];

    function periodCutoff() {
        const today = new Date(); today.setHours(23, 59, 59, 999);
        if (activePeriod === 'hoy')    { const c = new Date(); c.setHours(0, 0, 0, 0); return c; }
        if (activePeriod === 'semana') { return new Date(today - 7  * 86400000); }
        if (activePeriod === 'mes')    { return new Date(today - 30 * 86400000); }
        return null;
    }

    function visibleRows(tableId) {
        const cutoff = periodCutoff();
        return [...document.querySelectorAll('#' + tableId + ' tbody tr.history-row')].filter(row => {
            const d = new Date(row.dataset.date + 'T00:00:00');
            const passes = !cutoff || d >= cutoff;
            if (!passes) row.style.display = 'none';
            return passes;
        });
    }

    function renderPage(tableId) {
        const prefix = tableId.replace('-table', '');
        const rows = visibleRows(tableId);
        const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
        let page = currentPage[tableId] || 1;
        page = Math.min(Math.max(page, 1), totalPages);
        currentPage[tableId] = page;

        const start = (page - 1) * PER_PAGE;
        const end = start + PER_PAGE;
        rows.forEach((row, i) => { row.style.display = (i >= start && i < end) ? '' : 'none'; });

        // Fila vacía si no hay resultados en el período
        const emptyRow = document.querySelector('#' + tableId + ' tbody tr.empty-period');
        if (emptyRow) emptyRow.style.display = rows.length === 0 ? '' : 'none';

        // Controles del paginador (se ocultan si cabe todo en una página)
        const pager = document.getElementById(prefix + '-pager');
        if (pager) pager.style.display = rows.length > PER_PAGE ? 'flex' : 'none';
        const info = document.getElementById(prefix + '-pageinfo');
        if (info) info.textContent = 'Página ' + page + ' de ' + totalPages;
        const prev = document.getElementById(prefix + '-prev');
        const next = document.getElementById(prefix + '-next');
        if (prev) prev.disabled = page <= 1;
        if (next) next.disabled = page >= totalPages;
    }

    function pageTable(tableId, delta) {
        currentPage[tableId] = (currentPage[tableId] || 1) + delta;
        renderPage(tableId);
    }

    function filterRows(period) {
        activePeriod = period;
        document.querySelectorAll('.period-btn').forEach(b =>
            b.classList.toggle('active', b.dataset.period === period)
        );
        TABLES.forEach(t => { currentPage[t] = 1; renderPage(t); });
    }

    // Arrancar con período "Mes"
    filterRows('mes');
</script>
@endsection
