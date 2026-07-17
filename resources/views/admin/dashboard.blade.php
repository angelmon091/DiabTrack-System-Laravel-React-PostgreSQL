@extends('layouts.admin')

@section('title', 'Admin Dashboard - DiabTrack')

@section('content')
    <div class="admin-title-section animate-fade-in">
        <h2 class="fw-extrabold mb-1 text-dark">Panel de Control</h2>
        <p class="text-diab-text-secondary mb-0">Visión general del sistema y accesos rápidos de administración.</p>
    </div>

    <div class="row g-4 mb-5">
        <!-- Tarjeta de resumen del panel -->
        <div class="col-12 col-md-4">
            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none h-100 d-block">
                <div class="admin-card-metrics animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="admin-card-icon-wrapper shadow-sm">
                        <i class="fa-solid fa-chart-pie text-diab-primary fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Resumen General</h5>
                    <p class="text-muted extra-small mb-0">KPIs y métricas de salud</p>
                </div>
            </a>
        </div>

        <!-- Tarjeta de usuarios -->
        <div class="col-12 col-md-4">
            <a href="{{ route('admin.users.index') }}" class="text-decoration-none h-100 d-block">
                <div class="admin-card-metrics animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="admin-card-icon-wrapper shadow-sm">
                        <i class="fa-solid fa-users text-diab-primary fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Control de Usuarios</h5>
                    <p class="text-muted extra-small mb-0">Gestión de accesos y perfiles</p>
                </div>
            </a>
        </div>

        <!-- Tarjeta de roles -->
        <div class="col-12 col-md-4">
            <a href="{{ route('admin.roles.index') }}" class="text-decoration-none h-100 d-block">
                <div class="admin-card-metrics animate-fade-in" style="animation-delay: 0.3s;">
                    <div class="admin-card-icon-wrapper shadow-sm">
                        <i class="fa-solid fa-shield-halved text-diab-success fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Roles y Permisos</h5>
                    <p class="text-muted extra-small mb-0">Configuración de privilegios</p>
                </div>
            </a>
        </div>

        <!-- Placeholder Cards -->
        <div class="col-12 col-md-6">
            <a href="{{ route('admin.doctors.index') }}" class="text-decoration-none h-100 d-block">
                <div class="diab-card p-4 animate-fade-in h-100">
                    <div class="d-flex align-items-center gap-4">
                        <div class="glass-effect p-3 rounded-4 shadow-sm"><i class="fa-solid fa-user-doctor text-diab-info fs-4"></i></div>
                        <div><h6 class="fw-bold text-dark mb-0">Aprobación de médicos</h6><p class="text-muted extra-small mb-0">Validar cédulas y perfiles profesionales</p></div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6">
            <a href="{{ route('admin.api-usage.index') }}" class="text-decoration-none h-100 d-block">
                <div class="diab-card p-4 animate-fade-in h-100" style="animation-delay: 0.4s;">
                    <div class="d-flex align-items-center gap-4">
                        <div class="glass-effect p-3 rounded-4 shadow-sm">
                            <i class="fa-solid fa-microchip text-diab-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Uso de APIs de IA</h6>
                            <p class="text-muted extra-small mb-0">Tokens, costos y estadísticas por proveedor</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6">
            <div class="diab-card p-4 animate-fade-in" style="animation-delay: 0.5s; background: rgba(255,255,255,0.4) !important; border-style: dashed !important;">
                <div class="d-flex align-items-center gap-4">
                    <div class="glass-effect p-3 rounded-4 shadow-sm">
                        <i class="fa-solid fa-clipboard-list text-muted fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Auditoría de Sistema</h6>
                        <span class="badge bg-light text-muted rounded-pill small mt-1 border">Próximamente</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
