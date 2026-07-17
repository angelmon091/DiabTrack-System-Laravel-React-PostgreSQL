<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Metadatos para motores de búsqueda -->
    <meta name="description" content="DiabTrack: La plataforma inteligente para el monitoreo de diabetes. Controla tu glucosa, nutrición y actividad con análisis de IA para una vida más saludable.">
    <meta name="keywords" content="diabetes, monitoreo de salud, glucosa, insulina, salud inteligente, seguimiento médico, nutrición diabetes">
    <meta name="author" content="DiabTrack">
    <meta name="robots" content="index, follow">

    <!-- Metadatos para Open Graph y Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="DiabTrack - Monitorea tu salud, vive mejor">
    <meta property="og:description" content="Control inteligente de la diabetes con análisis de IA y monitoreo constante de signos vitales.">
    <meta property="og:image" content="{{ asset('og-image.jpg') }}">

    <!-- Metadatos para Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="DiabTrack - Monitorea tu salud, vive mejor">
    <meta property="twitter:description" content="Control inteligente de la diabetes con análisis de IA y monitoreo constante de signos vitales.">
    <meta property="twitter:image" content="{{ asset('og-image.jpg') }}">

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <title>DiabTrack - Monitorea tu salud, vive mejor</title>

    <!-- Hojas de estilo -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/index.css', 'resources/js/app.js'])
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark position-absolute w-100 bg-transparent pt-4 z-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ url('/') }}" class="diab-logo text-white text-decoration-none">
                D<span>ia</span>bTrack
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse justify-content-end" id="menuPrincipal">
                <ul class="navbar-nav gap-3 text-center mt-3 mt-md-0 pb-3 pb-md-0 bg-dark bg-opacity-75 bg-md-transparent rounded px-3 align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#">Información</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Nosotros</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Soporte</a></li>
                    @if (Route::has('login'))
                        @auth
                            <li class="nav-item">
                                <a href="{{ url('/dashboard') }}" class="btn btn-diab-primary rounded-pill px-4 ms-md-3">Dashboard</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a href="{{ route('login') }}" class="nav-link">Iniciar Sesión</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a href="{{ route('register') }}" class="btn btn-diab-primary rounded-pill px-4 ms-md-3">Registrarse</a>
                                </li>
                            @endif
                        @endauth
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 col-md-10 text-center text-md-start animate-fade-in">
                        <h1 class="display-2 fw-extrabold mb-3 hero-title">Monitorea tu salud, <span class="text-diab-primary">vive mejor</span></h1>
                        <p class="fs-4 mb-5 opacity-75">Control inteligente para una vida más saludable. Análisis con IA y monitoreo constante de tus signos vitales.</p>
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-md-start">
                            <a href="{{ route('register') }}" class="btn btn-acceder rounded-pill fw-bold shadow-lg">Comenzar ahora</a>
                            <a href="#features" class="btn btn-outline-light rounded-pill px-5 py-3 fw-bold border-2">Saber más</a>
                </div>
            </div>

            <!-- Indicador de desplazamiento -->
            <a href="#features" class="scroll-indicator">
                <i class="fa-solid fa-chevron-down"></i>
            </a>
        </section>

        <section class="features" id="features">
            <div class="container">
                <div class="text-center mb-5 pb-3">
                    <h2 class="display-5 features-title mb-4">Gestión Inteligente de la Diabetes</h2>
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <p class="text-muted fs-5">
                                DiabTrack mejora tu experiencia en la gestión de la salud, reduciendo riesgos mediante el monitoreo constante y análisis avanzados.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4 justify-content-center">
                    <div class="col-md-4">
                        <div class="service-item text-center">
                            <div class="admin-card-icon-wrapper mx-auto mb-4" style="background: var(--diab-primary-light);">
                                <i class="fa-solid fa-chart-line fs-2 text-diab-primary"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Gestión de Datos</h4>
                            <p class="text-muted">Centraliza toda tu información de salud en un solo lugar seguro y accesible.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="service-item text-center">
                            <div class="admin-card-icon-wrapper mx-auto mb-4" style="background: var(--diab-success-light);">
                                <i class="fa-solid fa-microchip fs-2 text-diab-success"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Análisis con IA</h4>
                            <p class="text-muted">Recibe información valiosa procesada por inteligencia artificial sobre tus hábitos.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="service-item text-center">
                            <div class="admin-card-icon-wrapper mx-auto mb-4" style="background: var(--diab-warning-light);">
                                <i class="fa-solid fa-heart-pulse fs-2 text-diab-warning"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Consejos Vitales</h4>
                            <p class="text-muted">Recomendaciones personalizadas basadas en tu monitoreo diario y necesidades.</p>
                        </div>
                    </div>
                </div>    
            </div>
        </section>

        <section class="py-5 bg-diab-primary-light">
            <div class="container py-5 text-center">
                <h2 class="fw-extrabold mb-4">¿Listo para tomar el control?</h2>
                <p class="fs-5 text-muted mb-5">Únete a miles de personas que ya están mejorando su calidad de vida.</p>
                <a href="{{ route('register') }}" class="btn btn-diab-primary btn-lg rounded-pill px-5">Crear mi cuenta gratuita</a>
            </div>
        </section>
    </main>
   
    <footer class="site-footer py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start mb-4 mb-md-0">
                    <a href="#" class="diab-logo text-decoration-none mb-2">
                        D<span>ia</span>bTrack
                    </a>
                    <p class="text-muted small">Tu compañero inteligente en el cuidado de la diabetes.</p>
                </div>
                <div class="col-md-4 text-center mb-4 mb-md-0">
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="#" class="text-decoration-none">Privacidad</a>
                        <a href="#" class="text-decoration-none">Términos</a>
                        <a href="#" class="text-decoration-none">Soporte</a>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <div class="social-icons d-flex gap-3 justify-content-center justify-content-md-end mb-3 fs-4">
                        <a href="#"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#"><i class="fa-brands fa-facebook"></i></a>
                        <a href="#"><i class="fa-brands fa-twitter"></i></a>
                    </div>
                    <p class="text-muted small mb-0">&copy; {{ date('Y') }} DiabTrack. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
