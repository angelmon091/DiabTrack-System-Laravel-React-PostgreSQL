@extends('layouts.admin')

@section('title', 'Aprobación de médicos - DiabTrack')

@section('content')
    <div class="admin-title-section d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 animate-fade-in">
        <div>
            <h2 class="fw-extrabold mb-1 text-dark">Aprobación de médicos</h2>
            <p class="text-diab-text-secondary mb-0">Comprueba la identidad profesional, cédula y especialidad antes de habilitar la vinculación de pacientes.</p>
        </div>
        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">{{ $pendingCount }} pendientes</span>
    </div>

    <div class="diab-card p-4 mb-4 animate-fade-in" style="animation-delay: 0.1s;">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="btn-action flex-shrink-0" aria-hidden="true">
                    <i class="fa-solid fa-filter text-diab-primary"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Filtrar solicitudes</h6>
                    <p class="text-muted extra-small mb-0">Consulta los perfiles por estado de validación</p>
                </div>
            </div>
            <div class="btn-group flex-wrap" role="group" aria-label="Estado de las solicitudes médicas">
            @foreach(['pending' => 'Pendientes', 'approved' => 'Aprobados', 'rejected' => 'Rechazados', 'all' => 'Todos'] as $value => $label)
                    <a href="{{ route('admin.doctors.index', ['status' => $value]) }}"
                       class="btn btn-sm {{ $status === $value ? 'btn-diab-primary' : 'btn-outline-secondary' }}"
                       @if($status === $value) aria-current="page" @endif>
                        {{ $label }}
                    </a>
            @endforeach
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse($doctors as $profile)
            <div class="col-12 col-xl-6">
                <article class="diab-card p-4 h-100 animate-fade-in">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="admin-card-icon-wrapper bg-diab-info-light text-diab-info mb-0"><i class="fa-solid fa-user-doctor"></i></div>
                            <div>
                                <h5 class="fw-bold mb-1">{{ $profile->user->name }}</h5>
                                <div class="text-muted small">{{ $profile->user->email }}</div>
                            </div>
                        </div>
                        @if($profile->approval_status === 'approved')
                            <span class="badge bg-success-subtle text-success">Aprobado</span>
                        @elseif($profile->approval_status === 'rejected')
                            <span class="badge bg-danger-subtle text-danger">Rechazado</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis">Pendiente</span>
                        @endif
                    </div>

                    <dl class="row mb-4 small">
                        <dt class="col-5 text-muted">Cédula profesional</dt><dd class="col-7 fw-bold">{{ $profile->license_number }}</dd>
                        <dt class="col-5 text-muted">Especialidad</dt><dd class="col-7">{{ $profile->specialty }}</dd>
                        <dt class="col-5 text-muted">Correo verificado</dt><dd class="col-7">{{ $profile->user->hasVerifiedEmail() ? 'Sí' : 'No' }}</dd>
                        <dt class="col-5 text-muted">Solicitud</dt><dd class="col-7">{{ $profile->created_at->format('d/m/Y H:i') }}</dd>
                        @if($profile->approver)
                            <dt class="col-5 text-muted">Revisado por</dt><dd class="col-7">{{ $profile->approver->name }}</dd>
                        @endif
                    </dl>

                    @if($profile->review_notes)
                        <div class="alert alert-light border small"><strong>Observaciones:</strong> {{ $profile->review_notes }}</div>
                    @endif

                    @if($profile->approval_status === 'approved')
                        <div class="d-flex align-items-center gap-3 rounded-4 bg-success bg-opacity-10 p-3 text-success">
                            <i class="fa-solid fa-circle-check fs-4"></i>
                            <div>
                                <strong class="d-block small">Validación completada</strong>
                                <span class="extra-small">El médico ya fue notificado y puede vincular pacientes.</span>
                            </div>
                        </div>
                    @else
                        <form method="POST" class="mb-2" action="{{ route('admin.doctors.approve', $profile) }}">
                            @csrf
                            @method('PATCH')
                            <label class="form-label small fw-bold" for="approve_notes_{{ $profile->id }}">Observaciones internas (opcional)</label>
                            <textarea id="approve_notes_{{ $profile->id }}" name="review_notes" class="form-control mb-3" rows="2" maxlength="1000"></textarea>
                            <button class="btn btn-success w-100" type="submit"><i class="fa-solid fa-circle-check me-2"></i>Aprobar y notificar</button>
                        </form>
                    @endif

                    @if($profile->approval_status === 'pending')
                        <form method="POST" action="{{ route('admin.doctors.reject', $profile) }}">
                            @csrf
                            @method('PATCH')
                            <label class="form-label small fw-bold" for="reject_notes_{{ $profile->id }}">Motivo del rechazo</label>
                            <textarea id="reject_notes_{{ $profile->id }}" name="review_notes" class="form-control mb-3" rows="2" maxlength="1000" required></textarea>
                            <button class="btn btn-outline-danger w-100" type="submit"><i class="fa-solid fa-xmark me-2"></i>Rechazar solicitud</button>
                        </form>
                    @endif
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="diab-card p-5 text-center">
                    <i class="fa-solid fa-user-doctor display-4 text-muted opacity-25 mb-3"></i>
                    <h5 class="fw-bold">No hay médicos en esta categoría</h5>
                    <p class="text-muted mb-0">Las nuevas solicitudes aparecerán aquí después de completar el onboarding.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($doctors->hasPages())
        <div class="mt-4 d-flex justify-content-center">{{ $doctors->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection
