@extends('layouts.app')

@section('title', 'DiabTrack - Alimentación Inteligente')

@section('styles')
    @vite('resources/css/alimentacion.css')
    <style>
        .btn-ia {
            background: linear-gradient(135deg, var(--diab-primary) 0%, #0077B6 100%) !important;
            border: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 20px rgba(0, 180, 216, 0.2);
        }
        .btn-ia:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 15px 30px rgba(0, 180, 216, 0.3);
        }
        .food-card {
            cursor: pointer;
            transition: all 0.4s ease;
            border: 2px solid transparent;
        }
        .food-card:hover {
            border-color: var(--diab-primary);
            z-index: 10;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .carrousel-container {
            overflow: hidden;
            border-radius: 24px;
        }
        .food-track {
            display: flex;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            gap: 1.5rem;
        }
        .ia-recommendation-card {
            background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
            border-left: 5px solid var(--diab-primary);
            border-radius: var(--diab-radius);
        }
        .pulse-ia {
            animation: pulse-blue 2s infinite;
        }
        @keyframes pulse-blue {
            0% { box-shadow: 0 0 0 0 rgba(0, 180, 216, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(0, 180, 216, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 180, 216, 0); }
        }
    </style>
@endsection

@section('content')
<main class="container-fluid py-4 px-md-5">
    
    <section class="diab-card mb-4 p-4 p-md-5 animate-fade-in">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-extrabold mb-1 fs-3">Alimentación <span class="text-diab-primary">Con IA</span></h2>
                <p class="text-muted small mb-0">Recomendaciones personalizadas basadas en tu metabolismo</p>
            </div>
            <a href="{{ route('tracking.nutrition.create') }}" class="btn btn-diab-primary rounded-pill px-4">
                <i class="fa-solid fa-plus me-2"></i> Registrar Comida
            </a>
        </div>
        
        <div class="position-relative px-md-5">
            <button class="arrow prev position-absolute start-0 top-50 translate-middle-y btn btn-light rounded-circle shadow-sm z-3 d-none d-md-flex align-items-center justify-content-center" style="width: 45px; height: 45px;" onclick="moveCarousel(-1)">
                <i class="fa-solid fa-chevron-left text-diab-primary"></i>
            </button>
            
            <div class="carrousel-container py-3">
                <div class="food-track" id="foodTrack">
                    @php
                        $placeholderFoods = [
                            ['img' => 'https://mirecetafacil.com/wp-content/uploads/2021/01/pollo-saludable-con-lechuga-y-aguacate.jpg', 'name' => 'Pollo con Aguacate', 'cal' => '350 kcal'],
                            ['img' => 'https://thumbs.dreamstime.com/b/plato-de-cena-diab%C3%A9tico-equilibrado-con-prote%C3%ADna-verduras-y-granos-para-una-alimentaci%C3%B3n-saludable-un-controlado-en-parte-394989319.jpg', 'name' => 'Plato Equilibrado', 'cal' => '420 kcal'],
                            ['img' => 'https://foodsmartcolorado.colostate.edu/wp-content/uploads/2020/01/Screenshot-50.png', 'name' => 'Ensalada de Granos', 'cal' => '280 kcal'],
                            ['img' => 'https://th.bing.com/th/id/OIP.fM_L4M7wB_E_R4X6uI_m1AHaE8?rs=1&pid=ImgDetMain', 'name' => 'Salmón a la Plancha', 'cal' => '310 kcal'],
                            ['img' => 'https://cdn.loveandlemons.com/wp-content/uploads/2021/04/pesto-pasta.jpg', 'name' => 'Pasta Integral con Pesto', 'cal' => '390 kcal'],
                        ];
                    @endphp
                    @foreach($placeholderFoods as $food)
                        <div class="food-item" style="min-width: calc(33.333% - 1rem);">
                            <div class="food-card overflow-hidden shadow-sm h-100" style="border-radius: var(--diab-radius);">
                                <div class="position-relative h-100">
                                    <img src="{{ $food['img'] }}" alt="{{ $food['name'] }}" class="w-100 h-100 object-fit-cover" style="min-height: 200px;">
                                    <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-50 text-white">
                                        <div class="fw-bold small">{{ $food['name'] }}</div>
                                        <div class="extra-small opacity-75">{{ $food['cal'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button class="arrow next position-absolute end-0 top-50 translate-middle-y btn btn-light rounded-circle shadow-sm z-3 d-none d-md-flex align-items-center justify-content-center" style="width: 45px; height: 45px;" onclick="moveCarousel(1)">
                <i class="fa-solid fa-chevron-right text-diab-primary"></i>
            </button>
        </div>
        
        <div class="dots text-center mt-4">
            @foreach($placeholderFoods as $index => $food)
                <span class="dot {{ $index === 0 ? 'active' : '' }}" onclick="goToSlide({{ $index }})" style="cursor:pointer"></span>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <button class="btn btn-primary btn-ia px-5 py-3 rounded-pill fw-bold pulse-ia" data-bs-toggle="modal" data-bs-target="#iaModal">
                <i class="fa-solid fa-robot me-2"></i> Generar Recomendación IA
            </button>
        </div>
    </section>

    <div class="diab-card p-4 mb-4 animate-fade-in">
        <h5 class="fw-bold mb-4 text-diab-text-secondary text-uppercase letter-spacing-1 small">Métricas de Nutrición Hoy</h5>
        
        <div class="row g-4">
            <!-- Calorías -->
            <div class="col-12 col-md-4">
                <div class="diab-card metric-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="act-icon red shadow-sm"><i class="fa-solid fa-fire"></i></div>
                        <span class="badge {{ $porcentajeCalorias > 90 ? 'bg-danger' : 'bg-success' }} rounded-pill">{{ $porcentajeCalorias }}%</span>
                    </div>
                    <div class="metric-value mb-2">
                        <span class="big-num text-dark">{{ number_format($caloriasHoy) }}</span> 
                        <span class="text-muted fs-6">/ {{ number_format($metaCalorias) }} kcal</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px; border-radius: 4px; background: #f0f0f0;">
                        <div class="progress-bar {{ $porcentajeCalorias > 90 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $porcentajeCalorias }}%"></div>
                    </div>
                    <p class="extra-small text-muted mb-0">Consumo calórico estimado basado en carbohidratos registrados.</p>
                </div>
            </div>

            <!-- Carbohidratos -->
            <div class="col-12 col-md-4">
                <div class="diab-card metric-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="act-icon orange shadow-sm"><i class="fa-solid fa-bread-slice"></i></div>
                        <span class="text-muted extra-small fw-bold">META: {{ $metaCarbs }}g</span>
                    </div>
                    <div class="metric-value mb-2">
                        <span class="big-num text-dark">{{ $carbsHoy }}</span> 
                        <span class="text-muted fs-6">g Carbs</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px; border-radius: 4px; background: #f0f0f0;">
                        @php $porcCarbs = ($carbsHoy / ($metaCarbs ?: 1)) * 100; @endphp
                        <div class="progress-bar bg-warning" style="width: {{ min($porcCarbs, 100) }}%"></div>
                    </div>
                    <p class="extra-small text-muted mb-0">Los carbohidratos son la fuente principal de energía y picos de glucosa.</p>
                </div>
            </div>

            <!-- Proteínas/Info -->
            <div class="col-12 col-md-4">
                <div class="diab-card metric-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="act-icon blue shadow-sm"><i class="fa-solid fa-circle-info"></i></div>
                    </div>
                    <h6 class="fw-bold mb-2">Tip Nutricional</h6>
                    <p class="small text-muted mb-4">{{ $tipDelDia }}</p>
                    <div class="mt-auto">
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark border">Fibra</span>
                            <span class="badge bg-light text-dark border">Proteína</span>
                            <span class="badge bg-light text-dark border">Bajo IG</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Modal IA -->
<div class="modal fade" id="iaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-robot text-diab-primary me-2"></i> Analizador DiabTrack IA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div id="iaLoading" class="text-center py-4">
                    <div class="spinner-border text-diab-primary mb-3" role="status"></div>
                    <p class="text-muted">Procesando tus métricas de hoy...</p>
                </div>
                <div id="iaResult" class="d-none">
                    <div class="ia-recommendation-card p-4 shadow-sm mb-3">
                        <h6 class="fw-bold text-diab-primary mb-2">Estado Actual:</h6>
                        <p class="small mb-3">Has consumido <strong>{{ $carbsHoy }}g</strong> de carbohidratos. Tu glucosa se mantiene en <strong>{{ $ultimaMedicion['glucose_level'] ?? '--' }} mg/dL</strong>.</p>
                        <hr class="opacity-10">
                        <h6 class="fw-bold text-success mb-2">Recomendación IA:</h6>
                        <p class="small mb-0" id="iaText"></p>
                    </div>
                    <div class="alert alert-info border-0 small py-2">
                        <i class="fa-solid fa-lightbulb me-2"></i> Esta recomendación es generada algorítmicamente.
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-diab-primary rounded-pill px-4" onclick="generateNewIA()">Siguiente Idea</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentSlide = 0;
    const track = document.getElementById('foodTrack');
    const totalSlides = {{ count($placeholderFoods) }};
    const itemsToShow = window.innerWidth > 768 ? 3 : 1;
    const maxSlide = totalSlides - itemsToShow;

    function updateCarousel() {
        const offset = currentSlide * -(100 / itemsToShow);
        track.style.transform = `translateX(${offset}%)`;
        
        // Actualiza los indicadores visuales del carrusel.
        document.querySelectorAll('.dot').forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });
    }

    function moveCarousel(direction) {
        currentSlide += direction;
        if (currentSlide < 0) currentSlide = 0;
        if (currentSlide > maxSlide) currentSlide = maxSlide;
        updateCarousel();
    }

    function goToSlide(index) {
        currentSlide = index;
        if (currentSlide > maxSlide) currentSlide = maxSlide;
        updateCarousel();
    }

    // Modal Logic
    const recommendations = [
        "Para tu próxima comida, prioriza vegetales de hoja verde y una proteína magra. Estás cerca de tu límite de carbohidratos.",
        "Tus niveles están estables. Podrías incluir una porción pequeña de fruta (manzana o fresas) como snack.",
        "Considera realizar 15 minutos de caminata ligera después de tu siguiente ingesta para optimizar la sensibilidad a la insulina.",
        "Excelente balance hoy. Mantén la hidratación; intenta beber 2 vasos de agua extra antes de la cena.",
        "Vemos una tendencia estable. Tu elección de 'Plato Equilibrado' sería ideal para mantener estos niveles."
    ];

    document.getElementById('iaModal').addEventListener('shown.bs.modal', function () {
        generateNewIA();
    });

    function generateNewIA() {
        const loading = document.getElementById('iaLoading');
        const result = document.getElementById('iaResult');
        const text = document.getElementById('iaText');

        loading.classList.remove('d-none');
        result.classList.add('d-none');

        setTimeout(() => {
            loading.classList.add('d-none');
            result.classList.remove('d-none');
            text.innerText = recommendations[Math.floor(Math.random() * recommendations.length)];
        }, 1500);
    }

    // Aplica los ajustes necesarios para pantallas pequeñas.
    window.addEventListener('resize', () => {
        location.reload(); // Refresh to recalculate itemsToShow
    });
</script>
@endsection
