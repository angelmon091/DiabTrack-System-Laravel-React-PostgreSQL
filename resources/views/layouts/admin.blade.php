<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', 'DiabTrack Administración')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Unified Design System -->
    @vite('resources/css/design-system.css')
    <!-- Custom CSS para Admin -->
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
    
    @yield('styles')
</head>
<body class="admin-body animate-fade-in">

    <!-- Encabezado y barra de navegación principal -->
    <header class="navbar shadow-sm border-bottom glass-effect sticky-top py-2">
        <div class="navbar-content container-fluid px-md-5 d-flex justify-content-between align-items-center">
            <a href="{{ route('dashboard') }}" class="diab-logo text-decoration-none">
                D<span>ia</span>bTrack
            </a>
            
            <div class="user-section d-flex align-items-center gap-3">

                
                <div class="user-card border bg-white shadow-sm p-1 ps-3 rounded-pill d-flex align-items-center gap-2">
                    <div class="user-text text-end d-none d-md-block me-2">
                        <span class="user-name d-block fw-bold small">{{ auth()->user()->name }}</span>
                        <span class="user-email text-muted extra-small" style="font-size: 0.7rem;">Administrador</span>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="user-avatar rounded-circle overflow-hidden shadow-sm d-block" style="width: 36px; height: 36px; cursor: pointer;">
                        @php
                            $user = auth()->user();
                            $gender = strtolower($user->patientProfile?->gender ?? '');
                            $avatar = $user->avatar;
                        @endphp
                        @if($avatar && str_starts_with($avatar, 'http'))
                            <img src="{{ $avatar }}" alt="User" class="w-100 h-100 object-fit-cover">
                        @elseif($avatar && trim($avatar) !== '')
                            <img src="{{ asset('storage/' . $avatar) }}" alt="User" class="w-100 h-100 object-fit-cover">
                        @elseif($gender === 'femenino')
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white" style="background: linear-gradient(135deg, #FF6B6B, #C0392B);">
                                <i class="fa-solid fa-person-dress fs-5"></i>
                            </div>
                        @elseif($gender === 'masculino')
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white" style="background: linear-gradient(135deg, #4A90E2, #2980B9);">
                                <i class="fa-solid fa-user-tie fs-5"></i>
                            </div>
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white bg-secondary">
                                <i class="fa-solid fa-user fs-5"></i>
                            </div>
                        @endif
                    </a>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ms-2 pe-2 border-start ps-2">
                    @csrf
                    <button type="submit" class="btn btn-link p-0 text-danger" title="Cerrar Sesión">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar shadow-sm">
            <div class="mb-4 d-none d-lg-block">
                <h6 class="text-uppercase text-muted fw-bold mb-0" style="font-size: 0.75rem; letter-spacing: 1px;">Menu Principal</h6>
            </div>
            
            <nav class="d-flex flex-lg-column gap-2 w-100">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span class="d-none d-md-inline">Dashboard</span>
                </a>
                
                <a href="{{ route('admin.users.index') }}" class="admin-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-gear"></i>
                    <span class="d-none d-md-inline">Usuarios</span>
                </a>
                
                <a href="{{ route('admin.roles.index') }}" class="admin-nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span class="d-none d-md-inline">Roles y Permisos</span>
                </a>

                <a href="{{ route('admin.doctors.index') }}" class="admin-nav-item {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-doctor"></i>
                    <span class="d-none d-md-inline">Aprobar médicos</span>
                </a>

                <a href="{{ route('admin.api-usage.index') }}" class="admin-nav-item {{ request()->routeIs('admin.api-usage.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-microchip"></i>
                    <span class="d-none d-md-inline">Uso de APIs</span>
                </a>
            </nav>
            
            <div class="mt-auto d-none d-lg-block pt-4 text-center">
                <p class="text-muted" style="font-size: 0.75rem;">Protegido por DiabTrack Security</p>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            
            <!-- Global Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-10 text-success fw-medium mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-10 text-danger fw-medium mb-4" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
            
        </main>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('modals')
    @yield('scripts')
</body>
</html>
