<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gestiona tu salud y controla tu diabetes con el dashboard inteligente de DiabTrack. Visualiza tus progresos y registros diarios.">
    <meta name="keywords" content="diabetes dashboard, seguimiento salud, control glucemia, registro diabetes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Meta etiquetas para Redes Sociales -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:title" content="DiabTrack - Dashboard de Salud">
    <meta property="og:description" content="Gestiona tu salud de manera efectiva con DiabTrack. Registra signos vitales, alimentación y síntomas.">
    <meta property="og:image" content="{{ asset('og-image.jpg') }}">
    
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="DiabTrack - Tu Dashboard de Salud">
    <meta property="twitter:image" content="{{ asset('og-image.jpg') }}">

    <title>@yield('title', 'DiabTrack - Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/design-system.css', 'resources/css/dashboardc.css', 'resources/js/app.js'])
    @yield('styles')
</head>
<body class="animate-fade-in">
    <div class="main-content-push">
        <div class="content-body">
            <header class="navbar shadow-sm border-bottom glass-effect sticky-md-top py-2">
                <div class="navbar-content container-fluid px-md-5 d-flex justify-content-between align-items-center">
                    <a href="{{ route('dashboard') }}" class="diab-logo text-decoration-none">
                        D<span>ia</span>bTrack
                    </a>
                    
                    @if(auth()->user()->isPatient())
                    <div class="nav-search d-none d-lg-block position-relative">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" id="globalSearch" class="form-control" placeholder="Buscar secciones o registros..." autocomplete="off">
                        <div id="searchResults" class="search-results shadow"></div>
                    </div>
                    @endif

                    <!-- Desktop Navigation (hidden on mobile via CSS) -->
                    <nav class="nav-menu d-none d-md-flex">
                        @if(auth()->user()->isPatient())
                        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-house"></i>
                            <span>Inicio</span>
                        </a>
                        <a href="{{ route('tracking.summary') }}" class="nav-item {{ request()->routeIs('tracking.summary') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-column"></i>
                            <span>Resumen</span>
                        </a>
                        <a href="{{ route('tracking.vital.create') }}" class="nav-item {{ request()->routeIs('tracking.*') && !request()->routeIs('tracking.summary') ? 'active' : '' }}">
                            <i class="fa-solid fa-plus"></i>
                            <span>Nuevo</span>
                        </a>
                        @endif
                    </nav>

                <div class="user-section d-flex align-items-center">
                        @auth
                        @php
                            $notifs       = \App\Models\PatientNotification::where('user_id', auth()->id())->latest()->take(8)->get();
                            $notifUnread  = $notifs->whereNull('read_at')->count();
                        @endphp
                        <div class="dropdown me-3">
                            <button class="btn p-0 border-0 bg-transparent position-relative nav-item text-muted" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="line-height:1;">
                                <i class="fa-solid fa-bell fs-5"></i>
                                @if($notifUnread > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;padding:3px 5px;">
                                    {{ $notifUnread > 9 ? '9+' : $notifUnread }}
                                </span>
                                @endif
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0" id="notif-dropdown-menu" style="width:340px;max-width:95vw;border-radius:16px;overflow:hidden;">
                                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                                    <span class="fw-bold" style="font-size:0.95rem;">Notificaciones</span>
                                    @if($notifs->isNotEmpty())
                                    <div class="d-flex align-items-center gap-3">
                                        @if($notifUnread > 0)
                                        <button class="notif-action btn p-0 border-0 bg-transparent text-muted" title="Marcar todas como leídas" onclick="markAllRead()">
                                            <i class="fa-solid fa-check-double"></i>
                                        </button>
                                        @endif
                                        <button class="notif-action notif-action-danger btn p-0 border-0 bg-transparent text-muted" title="Borrar todas" onclick="deleteAllNotifs()">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                                <div style="max-height:360px;overflow-y:auto;">
                                    @forelse($notifs as $notif)
                                    <div class="notif-item d-flex gap-3 px-4 py-3 border-bottom {{ $notif->read_at ? '' : 'notif-unread' }}"
                                         id="notif-{{ $notif->id }}"
                                         onclick="markRead({{ $notif->id }}, this)">
                                        <div class="notif-icon flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                                             style="width:36px;height:36px;background:{{ $notif->type === 'ai_reminder' ? 'rgba(0,180,216,0.12)' : 'rgba(99,102,241,0.12)' }};">
                                            <i class="{{ $notif->icon }} {{ $notif->type === 'ai_reminder' ? 'text-diab-primary' : 'text-indigo' }}" style="font-size:0.85rem;"></i>
                                        </div>
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="fw-semibold" style="font-size:0.82rem;line-height:1.3;">{{ $notif->title }}</span>
                                                @if($notif->type === 'ai_reminder')
                                                <span class="badge rounded-pill" style="font-size:0.58rem;padding:2px 6px;background:rgba(0,180,216,0.15);color:var(--diab-primary);border:1px solid rgba(0,180,216,0.25);">✦ IA</span>
                                                @endif
                                                @if(!$notif->read_at)
                                                <span class="ms-auto flex-shrink-0 rounded-circle bg-primary" style="width:7px;height:7px;display:inline-block;"></span>
                                                @endif
                                            </div>
                                            <p class="mb-1 text-muted" style="font-size:0.78rem;line-height:1.4;">{{ $notif->body }}</p>
                                            <span class="text-muted" style="font-size:0.7rem;">{{ $notif->created_at->diffForHumans() }}</span>
                                        </div>
                                        <button type="button" class="notif-delete btn p-0 border-0 bg-transparent text-muted flex-shrink-0 align-self-start" title="Eliminar" onclick="deleteNotif({{ $notif->id }}, event)">
                                            <i class="fa-solid fa-xmark" style="font-size:0.8rem;"></i>
                                        </button>
                                    </div>
                                    @empty
                                    <div class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-bell-slash mb-2" style="font-size:1.5rem;opacity:0.3;"></i>
                                        <p class="small mb-0">Sin notificaciones</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @endauth
                        <div class="user-card border bg-white shadow-sm p-1 ps-3 rounded-pill d-flex align-items-center">
                            <div class="user-text d-none d-xl-block me-2">
                                <span class="user-name fw-bold small d-block">{{ auth()->user()->name }}</span>
                                <span class="user-email text-muted extra-small" style="font-size: 0.7rem;">{{ auth()->user()->email }}</span>
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
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white" style="background: linear-gradient(135deg, #95a5a6, #7f8c8d);">
                                        <i class="fa-solid fa-user fs-5"></i>
                                    </div>
                                @endif
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="ms-2 pe-2 border-start ps-2">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 text-danger" title="Cerrar Sesión">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            @yield('content')
        </div>

        <footer class="site-footer bg-white border-top py-5 mt-auto">
            <div class="container-fluid px-md-5">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                        <a href="{{ route('dashboard') }}" class="diab-logo text-decoration-none mb-3">
                            D<span>ia</span>bTrack
                        </a>
                        <div class="footer-links d-flex gap-4 justify-content-center justify-content-md-start">
                            <a href="#" class="text-muted text-decoration-none small">Políticas</a>
                            <a href="#" class="text-muted text-decoration-none small">Términos</a>
                            <a href="#" class="text-muted text-decoration-none small">Ayuda</a>
                        </div>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <div class="social-icons fs-4 d-flex gap-3 justify-content-center justify-content-md-end mb-3">
                            <a href="#" class="text-muted hover-diab-primary"><i class="fa-brands fa-instagram"></i></a>
                            <a href="#" class="text-muted hover-diab-primary"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="#" class="text-muted hover-diab-primary"><i class="fa-brands fa-twitter"></i></a>
                        </div>
                        <p class="text-muted small mb-0">&copy; {{ date('Y') }} DiabTrack App. Cuidando tu salud.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Mobile Navigation (fixed bottom, shown only on mobile via CSS) -->
    <nav class="nav-menu d-flex d-md-none">
        @if(auth()->user()->isPatient())
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-house"></i>
            <span>Inicio</span>
        </a>
        <a href="{{ route('tracking.summary') }}" class="nav-item {{ request()->routeIs('tracking.summary') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-column"></i>
            <span>Resumen</span>
        </a>
        <a href="{{ route('tracking.vital.create') }}" class="nav-item {{ request()->routeIs('tracking.*') && !request()->routeIs('tracking.summary') ? 'active' : '' }}">
            <i class="fa-solid fa-plus"></i>
            <span>Nuevo</span>
        </a>
        @endif
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ── DiabTrack SweetAlert theme ────────────────────────────────────────
        const DiabSwal = Swal.mixin({
            customClass: {
                popup:          'diabswal-popup',
                title:          'diabswal-title',
                htmlContainer:  'diabswal-html',
                confirmButton:  'diabswal-btn diabswal-btn-confirm',
                cancelButton:   'diabswal-btn diabswal-btn-cancel',
                icon:           'diabswal-icon',
            },
            buttonsStyling: false,
            showClass: {
                popup: 'animate__animated animate__fadeInDown animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp animate__faster'
            }
        });

        // Inject DiabSwal styles once
        (function() {
            const style = document.createElement('style');
            style.textContent = `
                .diabswal-popup {
                    border-radius: 24px !important;
                    font-family: 'Inter', sans-serif !important;
                    background: #FFFFFF !important;
                    box-shadow: 0 20px 40px -10px rgba(0,0,0,0.12), 0 8px 16px -4px rgba(0,180,216,0.08) !important;
                    padding: 2rem !important;
                    border: 1px solid rgba(0,180,216,0.12) !important;
                }
                .diabswal-title {
                    font-family: 'Inter', sans-serif !important;
                    font-weight: 700 !important;
                    font-size: 1.2rem !important;
                    color: #0F172A !important;
                    letter-spacing: -0.02em !important;
                }
                .diabswal-html {
                    font-family: 'Inter', sans-serif !important;
                    font-size: 0.9rem !important;
                    color: #64748B !important;
                }
                .diabswal-btn {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    padding: 0.6rem 1.6rem !important;
                    border-radius: 12px !important;
                    font-family: 'Inter', sans-serif !important;
                    font-size: 0.875rem !important;
                    font-weight: 600 !important;
                    border: none !important;
                    cursor: pointer !important;
                    transition: all 0.25s cubic-bezier(0.4,0,0.2,1) !important;
                    letter-spacing: -0.01em !important;
                }
                .diabswal-btn-confirm {
                    background: linear-gradient(135deg, #00B4D8 0%, #0096C7 100%) !important;
                    color: #fff !important;
                    box-shadow: 0 4px 14px rgba(0,180,216,0.35) !important;
                }
                .diabswal-btn-confirm:hover {
                    background: linear-gradient(135deg, #0096C7 0%, #0077A8 100%) !important;
                    box-shadow: 0 6px 20px rgba(0,180,216,0.45) !important;
                    transform: translateY(-1px) !important;
                }
                .diabswal-btn-cancel {
                    background: rgba(0,180,216,0.08) !important;
                    color: #00B4D8 !important;
                    border: 1px solid rgba(0,180,216,0.2) !important;
                }
                .diabswal-btn-cancel:hover {
                    background: rgba(0,180,216,0.15) !important;
                    transform: translateY(-1px) !important;
                }
                .swal2-actions { gap: 0.6rem !important; }
                .diabswal-icon.swal2-success { border-color: #28C76F !important; }
                .diabswal-icon.swal2-success [class^="swal2-success-line"] { background: #28C76F !important; }
                .diabswal-icon.swal2-success .swal2-success-ring { border-color: rgba(40,199,111,0.25) !important; }
                .diabswal-icon.swal2-error { border-color: #EA5455 !important; }
                .diabswal-icon.swal2-error [class^="swal2-x-mark-line"] { background: #EA5455 !important; }
                .diabswal-icon.swal2-warning { border-color: #FF9F43 !important; color: #FF9F43 !important; }
                .diabswal-icon.swal2-info { border-color: #00CFE8 !important; color: #00CFE8 !important; }
                .diabswal-icon.swal2-question { border-color: #00B4D8 !important; color: #00B4D8 !important; }
            `;
            document.head.appendChild(style);
        })();
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Intercept health tracking forms
            document.querySelectorAll('form.tracking-form-layout').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.disabled = true;

                    const formData = new FormData(form);
                    const action = form.getAttribute('action');
                    const method = form.getAttribute('method') || 'POST';

                    fetch(action, {
                        method: method,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                    .then(response => {
                        if (submitBtn) submitBtn.disabled = false;
                        
                        if (response.status === 422) {
                            return response.json().then(data => {
                                let errorHtml = '<ul class="text-start" style="list-style-type: none; padding-left: 0; margin-bottom: 0;">';
                                Object.values(data.errors).forEach(errArray => {
                                    errArray.forEach(err => {
                                        errorHtml += `<li><i class="fa-solid fa-circle-xmark text-danger me-2"></i>${err}</li>`;
                                    });
                                });
                                errorHtml += '</ul>';
                                
                                DiabSwal.fire({
                                    icon: 'error',
                                    title: 'Errores de Validación',
                                    html: errorHtml,
                                });
                            });
                        } else if (!response.ok) {
                            throw new Error('Server error');
                        } else {
                            return response.json().then(data => {
                                DiabSwal.fire({
                                    icon: 'success',
                                    title: '¡Guardado!',
                                    text: data.message || 'El registro se guardó correctamente.',
                                }).then(() => {
                                    form.reset();
                                    
                                    // Trigger range inputs oninput events to reset text values
                                    form.querySelectorAll('input[type="range"]').forEach(range => {
                                        if (range.oninput) range.oninput();
                                    });
                                });
                            });
                        }
                    })
                    .catch(err => {
                        if (submitBtn) submitBtn.disabled = false;
                        DiabSwal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al guardar el registro. Inténtalo de nuevo.',
                        });
                    });
                });
            });
        });
    </script>
    @yield('scripts')
    <script>
    function markRead(id, el) {
        if (!el.classList.contains('notif-unread')) return;
        fetch('/notifications/' + id + '/read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        }).then(() => {
            el.classList.remove('notif-unread');
            el.querySelector('.bg-primary.rounded-circle')?.remove();
            const badge = document.querySelector('#notifDropdown .badge');
            if (badge) {
                const n = parseInt(badge.textContent) - 1;
                n <= 0 ? badge.remove() : badge.textContent = n;
            }
        });
    }
    function markAllRead() {
        fetch('/notifications/read-all', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        }).then(() => {
            document.querySelectorAll('.notif-unread').forEach(el => {
                el.classList.remove('notif-unread');
                el.querySelector('.bg-primary.rounded-circle')?.remove();
            });
            document.querySelector('#notifDropdown .badge')?.remove();
            document.querySelector('[onclick="markAllRead()"]')?.remove();
        });
    }
    function decrementNotifBadge() {
        const badge = document.querySelector('#notifDropdown .badge');
        if (!badge) return;
        const n = parseInt(badge.textContent) - 1;
        n <= 0 ? badge.remove() : badge.textContent = n;
    }
    function renderEmptyNotifsIfNeeded() {
        const list = document.querySelector('#notif-dropdown-menu > div[style*="overflow-y"]');
        if (list && !list.querySelector('.notif-item')) {
            list.innerHTML = '<div class="text-center py-5 text-muted">' +
                '<i class="fa-solid fa-bell-slash mb-2" style="font-size:1.5rem;opacity:0.3;"></i>' +
                '<p class="small mb-0">Sin notificaciones</p></div>';
        }
    }
    function deleteNotif(id, event) {
        event.stopPropagation();
        const el = document.getElementById('notif-' + id);
        const wasUnread = el?.classList.contains('notif-unread');
        fetch('/notifications/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        }).then(() => {
            el?.remove();
            if (wasUnread) decrementNotifBadge();
            renderEmptyNotifsIfNeeded();
        });
    }
    function deleteAllNotifs() {
        fetch('/notifications/all', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        }).then(() => {
            document.querySelectorAll('.notif-item').forEach(el => el.remove());
            document.querySelector('#notifDropdown .badge')?.remove();
            document.querySelector('[onclick="markAllRead()"]')?.remove();
            document.querySelector('[onclick="deleteAllNotifs()"]')?.remove();
            renderEmptyNotifsIfNeeded();
        });
    }
    </script>
    <script>
    (function () {
        const input   = document.getElementById('globalSearch');
        const panel   = document.getElementById('searchResults');
        if (!input || !panel) return;

        let timer = null;
        let items = [];      // enlaces navegables actuales
        let active = -1;

        const esc = (s) => (s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

        function close() { panel.classList.remove('show'); panel.innerHTML = ''; active = -1; items = []; }

        function renderItem(it) {
            return '<a class="search-item" href="' + esc(it.url) + '">' +
                '<span class="search-item-icon"><i class="' + esc(it.icon) + '"></i></span>' +
                '<span style="min-width:0;">' +
                    '<span class="search-item-title d-block">' + esc(it.title || it.label) + '</span>' +
                    (it.subtitle ? '<span class="search-item-sub d-block">' + esc(it.subtitle) + '</span>' : '') +
                '</span></a>';
        }

        function render(data) {
            let html = '';
            if (data.sections.length) {
                html += '<div class="search-group-title">Secciones</div>';
                data.sections.forEach(s => html += renderItem(s));
            }
            if (data.records.length) {
                html += '<div class="search-group-title">Mis registros</div>';
                data.records.forEach(r => html += renderItem(r));
            }
            if (!html) html = '<div class="search-empty">Sin resultados para tu búsqueda</div>';
            panel.innerHTML = html;
            panel.classList.add('show');
            items = Array.from(panel.querySelectorAll('.search-item'));
            active = -1;
        }

        function run(q) {
            fetch('/search?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.ok ? r.json() : { sections: [], records: [] })
            .then(render)
            .catch(() => close());
        }

        input.addEventListener('input', function () {
            const q = this.value.trim();
            clearTimeout(timer);
            if (q.length < 2) { close(); return; }
            timer = setTimeout(() => run(q), 250);
        });

        input.addEventListener('keydown', function (e) {
            if (!panel.classList.contains('show') || !items.length) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); active = (active + 1) % items.length; }
            else if (e.key === 'ArrowUp') { e.preventDefault(); active = (active - 1 + items.length) % items.length; }
            else if (e.key === 'Enter') { if (active >= 0) { e.preventDefault(); items[active].click(); } return; }
            else if (e.key === 'Escape') { close(); return; }
            else return;
            items.forEach((el, i) => el.classList.toggle('active', i === active));
            items[active]?.scrollIntoView({ block: 'nearest' });
        });

        input.addEventListener('focus', function () {
            if (this.value.trim().length >= 2 && panel.innerHTML) panel.classList.add('show');
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.nav-search')) close();
        });
    })();
    </script>
    <style>
    .notif-item { cursor: pointer; transition: background 0.15s; }
    .notif-item:hover { background: rgba(0,0,0,0.02); }
    .notif-unread { background: rgba(0,180,216,0.04); }
    .notif-unread:hover { background: rgba(0,180,216,0.08); }
    .text-indigo { color: #6366f1; }
    .notif-delete { opacity: 0; transition: opacity 0.15s, color 0.15s; }
    .notif-item:hover .notif-delete { opacity: 0.6; }
    .notif-delete:hover { opacity: 1 !important; color: var(--diab-danger) !important; }
    @media (hover: none) { .notif-delete { opacity: 0.6; } }
    .notif-action { font-size: 0.95rem; transition: color 0.15s; }
    .notif-action:hover { color: var(--diab-primary) !important; }
    .notif-action-danger:hover { color: var(--diab-danger) !important; }
    .nav-search input { padding-left: 42px; }
    .nav-search .search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--diab-text-secondary); font-size: 0.85rem; pointer-events: none; z-index: 2; }
    .search-results { display: none; position: absolute; top: calc(100% + 8px); left: 0; right: 0; background: #fff; border-radius: 14px; overflow: hidden; z-index: 1050; max-height: 380px; overflow-y: auto; border: 1px solid rgba(0,0,0,0.05); }
    .search-results.show { display: block; }
    .search-group-title { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--diab-text-secondary); padding: 10px 16px 4px; }
    .search-item { display: flex; align-items: center; gap: 12px; padding: 9px 16px; text-decoration: none; color: inherit; transition: background 0.12s; cursor: pointer; }
    .search-item:hover, .search-item.active { background: rgba(0,180,216,0.08); }
    .search-item .search-item-icon { width: 30px; height: 30px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: var(--diab-bg); color: var(--diab-primary); font-size: 0.8rem; }
    .search-item-title { font-size: 0.82rem; font-weight: 600; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .search-item-sub { font-size: 0.72rem; color: var(--diab-text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .search-empty { padding: 22px 16px; text-align: center; color: var(--diab-text-secondary); font-size: 0.8rem; }
    </style>
</body>
</html>
