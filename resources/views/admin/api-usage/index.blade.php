@extends('layouts.admin')

@section('title', 'Uso de APIs de IA - DiabTrack')

@section('content')
    <div class="admin-title-section animate-fade-in">
        <h2 class="fw-extrabold mb-1 text-dark">Uso de APIs de Inteligencia Artificial</h2>
        <p class="text-diab-text-secondary mb-0">Consumo de tokens y costos estimados por proveedor en la generación de tips diarios.</p>
    </div>

    {{-- ── KPI Cards ──────────────────────────────────────────────────────── --}}
    <div class="row g-4 mb-5 animate-fade-in" style="animation-delay: 0.1s;">

        <div class="col-6 col-lg-3">
            <div class="diab-card p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: var(--diab-primary-light); width:40px; height:40px;">
                        <i class="fa-solid fa-bolt text-diab-primary"></i>
                    </div>
                    <span class="text-muted small fw-medium">Total Tokens</span>
                </div>
                <div class="fw-extrabold text-dark" style="font-size: 1.6rem; line-height:1;">
                    {{ number_format($summary['total_tokens']) }}
                </div>
                <span class="extra-small text-muted">histórico acumulado</span>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="diab-card p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: var(--diab-success-light); width:40px; height:40px;">
                        <i class="fa-solid fa-dollar-sign text-diab-success" style="color: var(--diab-success) !important;"></i>
                    </div>
                    <span class="text-muted small fw-medium">Costo Estimado</span>
                </div>
                <div class="fw-extrabold text-dark" style="font-size: 1.6rem; line-height:1;">
                    ${{ number_format($summary['total_cost'], 4) }}
                </div>
                <span class="extra-small text-muted">USD total histórico</span>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="diab-card p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: var(--diab-info-light); width:40px; height:40px;">
                        <i class="fa-solid fa-comment-medical" style="color: var(--diab-info);"></i>
                    </div>
                    <span class="text-muted small fw-medium">Llamadas IA</span>
                </div>
                <div class="fw-extrabold text-dark" style="font-size: 1.6rem; line-height:1;">
                    {{ number_format($summary['total_calls']) }}
                </div>
                <span class="extra-small text-muted">tips + recordatorios · histórico</span>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="diab-card p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: var(--diab-warning-light); width:40px; height:40px;">
                        <i class="fa-solid fa-chart-pie" style="color: var(--diab-warning);"></i>
                    </div>
                    <span class="text-muted small fw-medium">Costo Prom./Llamada</span>
                </div>
                <div class="fw-extrabold text-dark" style="font-size: 1.6rem; line-height:1;">
                    ${{ number_format($summary['avg_cost_per_tip'], 6) }}
                </div>
                <span class="extra-small text-muted">USD · últimos 30 días</span>
            </div>
        </div>

    </div>

    {{-- ── Gráfica principal con tabs ─────────────────────────────────────── --}}
    <div class="diab-card p-4 mb-4 animate-fade-in" style="animation-delay: 0.2s;">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h6 class="fw-bold text-dark mb-0">Consumo de Tokens por Período</h6>
                <p class="text-muted extra-small mb-0">Comparativa Anthropic vs Gemini</p>
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-diab-primary period-btn active" data-period="weekly">
                    7 días
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary period-btn" data-period="daily">
                    30 días
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary period-btn" data-period="monthly">
                    6 meses
                </button>
            </div>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas id="tokensChart"></canvas>
        </div>
    </div>

    {{-- ── Fila: Pie proveedor + Costos por proveedor ────────────────────── --}}
    <div class="row g-4 mb-4 animate-fade-in" style="animation-delay: 0.3s;">

        <div class="col-12 col-lg-5">
            <div class="diab-card p-4 h-100">
                <h6 class="fw-bold text-dark mb-1">Distribución por Proveedor</h6>
                <p class="text-muted extra-small mb-4">Total de llamadas históricas</p>
                <div style="position: relative; height: 220px;">
                    <canvas id="providerPieChart"></canvas>
                </div>
                <div class="d-flex justify-content-center gap-4 mt-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#00B4D8;"></span>
                        <span class="extra-small text-muted">Anthropic</span>
                        <span class="extra-small fw-bold text-dark">{{ $summary['anthropic_calls'] }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#28C76F;"></span>
                        <span class="extra-small text-muted">Gemini</span>
                        <span class="extra-small fw-bold text-dark">{{ $summary['gemini_calls'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="diab-card p-4 h-100">
                <h6 class="fw-bold text-dark mb-1">Costo Estimado por Proveedor</h6>
                <p class="text-muted extra-small mb-4">USD acumulado histórico</p>
                <div style="position: relative; height: 220px;">
                    <canvas id="costBarChart"></canvas>
                </div>
                <p class="extra-small text-muted mt-3 mb-0">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Tarifas de referencia: Anthropic Haiku $0.80/$4.00 por MTok · Gemini 2.5 Flash $0.075/$0.30 por MTok
                </p>
            </div>
        </div>

    </div>

    {{-- ── Tabla de llamadas recientes ───────────────────────────────────── --}}
    <div class="animate-fade-in" style="animation-delay: 0.4s;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="fw-bold text-dark mb-0">Registro de Llamadas Recientes</h6>
                <p class="text-muted extra-small mb-0">Últimas {{ $recentLogs->count() }} llamadas a las APIs</p>
            </div>
        </div>

        <x-admin-table :headers="['Proveedor', 'Modelo', 'Concepto', 'Tokens Entrada', 'Tokens Salida', 'Costo Est.', 'Paciente', 'Fecha']">
            @forelse ($recentLogs as $log)
                <tr>
                    <td>
                        @if ($log->provider === 'anthropic')
                            <span class="badge rounded-pill fw-semibold" style="background: var(--diab-primary-light); color: var(--diab-primary); font-size: 0.72rem;">
                                <i class="fa-solid fa-a me-1"></i> Anthropic
                            </span>
                        @else
                            <span class="badge rounded-pill fw-semibold" style="background: var(--diab-success-light); color: var(--diab-success); font-size: 0.72rem;">
                                <i class="fa-brands fa-google me-1"></i> Gemini
                            </span>
                        @endif
                    </td>
                    <td class="text-muted small font-monospace">{{ $log->model }}</td>
                    <td>
                        @if ($log->daily_tip_id)
                            <span class="badge rounded-pill fw-semibold" style="background: var(--diab-info-light); color: var(--diab-info); font-size: 0.72rem;">
                                <i class="fa-regular fa-lightbulb me-1"></i> Tip
                            </span>
                        @else
                            <span class="badge rounded-pill fw-semibold" style="background: rgba(0,180,216,0.15); color: var(--diab-primary); font-size: 0.72rem;">
                                <i class="fa-solid fa-bell me-1"></i> Recordatorio
                            </span>
                        @endif
                    </td>
                    <td class="text-dark fw-medium">{{ number_format($log->input_tokens) }}</td>
                    <td class="text-dark fw-medium">{{ number_format($log->output_tokens) }}</td>
                    <td class="text-dark fw-medium">${{ number_format($log->estimated_cost_usd, 6) }}</td>
                    <td class="text-muted small">{{ $log->patient?->name ?? '—' }}</td>
                    <td class="text-muted small">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="mb-3 text-muted opacity-25">
                            <i class="fa-solid fa-microchip display-1"></i>
                        </div>
                        <h5 class="text-muted fw-bold">Sin registros aún</h5>
                        <p class="text-muted small">Los registros aparecerán aquí después de ejecutar <code>app:generate-daily-tips</code>.</p>
                    </td>
                </tr>
            @endforelse
        </x-admin-table>
    </div>

    @if ($recentLogs->hasPages())
        <div class="mt-5 d-flex justify-content-center">
            {{ $recentLogs->links('pagination::bootstrap-5') }}
        </div>
    @endif

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const primary   = '#00B4D8';
    const success   = '#28C76F';
    const primaryBg = 'rgba(0, 180, 216, 0.15)';
    const successBg = 'rgba(40, 199, 111, 0.15)';

    const weeklyData  = @json($weeklyStats);
    const dailyData   = @json($dailyStats);
    const monthlyData = @json($monthlyStats);

    // ── Gráfica principal (tokens por período) ──────────────────────────
    const tokensCtx = document.getElementById('tokensChart').getContext('2d');

    function buildDatasets(data) {
        return {
            labels: data.map(r => r.label),
            datasets: [
                {
                    label: 'Anthropic',
                    data: data.map(r => r.anthropic_tokens),
                    backgroundColor: primaryBg,
                    borderColor: primary,
                    borderWidth: 2,
                    borderRadius: 6,
                },
                {
                    label: 'Gemini',
                    data: data.map(r => r.gemini_tokens),
                    backgroundColor: successBg,
                    borderColor: success,
                    borderWidth: 2,
                    borderRadius: 6,
                }
            ]
        };
    }

    const tokensChart = new Chart(tokensCtx, {
        type: 'bar',
        data: buildDatasets(weeklyData),
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { family: 'Inter', size: 11 }, usePointStyle: true, pointStyleWidth: 8 }
                },
                tooltip: {
                    backgroundColor: '#0F172A',
                    titleFont: { family: 'Inter', size: 11 },
                    bodyFont: { family: 'Inter', size: 12, weight: 600 },
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString() + ' tokens'
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 10 }, color: '#94A3B8' } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { family: 'Inter', size: 10 }, color: '#94A3B8' } }
            }
        }
    });

    // Tab switcher
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.period-btn').forEach(b => {
                b.classList.remove('btn-diab-primary', 'active');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.add('btn-diab-primary', 'active');
            this.classList.remove('btn-outline-secondary');

            const period = this.dataset.period;
            const data = period === 'weekly' ? weeklyData : period === 'daily' ? dailyData : monthlyData;
            const built = buildDatasets(data);
            tokensChart.data.labels = built.labels;
            tokensChart.data.datasets[0].data = built.datasets[0].data;
            tokensChart.data.datasets[1].data = built.datasets[1].data;
            tokensChart.update();
        });
    });

    // ── Pie proveedor ───────────────────────────────────────────────────
    new Chart(document.getElementById('providerPieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Anthropic', 'Gemini'],
            datasets: [{
                data: [{{ $summary['anthropic_calls'] }}, {{ $summary['gemini_calls'] }}],
                backgroundColor: [primary, success],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0F172A',
                    bodyFont: { family: 'Inter', size: 12, weight: 600 },
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' llamadas'
                    }
                }
            }
        }
    });

    // ── Barras horizontales de costo ────────────────────────────────────
    new Chart(document.getElementById('costBarChart'), {
        type: 'bar',
        data: {
            labels: ['Anthropic', 'Gemini'],
            datasets: [{
                label: 'Costo USD',
                data: [{{ $summary['anthropic_cost'] }}, {{ $summary['gemini_cost'] }}],
                backgroundColor: [primaryBg, successBg],
                borderColor: [primary, success],
                borderWidth: 2,
                borderRadius: 8,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0F172A',
                    bodyFont: { family: 'Inter', size: 12, weight: 600 },
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => ' $' + ctx.parsed.x.toFixed(6) + ' USD'
                    }
                }
            },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { family: 'Inter', size: 10 }, color: '#94A3B8', callback: v => '$' + v } },
                y: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 12, weight: 600 }, color: '#0F172A' } }
            }
        }
    });

});
</script>
@endsection
