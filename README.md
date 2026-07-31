# DiabTrack

DiabTrack es una plataforma para el monitoreo integral de la diabetes. Permite a pacientes, médicos y cuidadores gestionar indicadores glucémicos, nutrición, actividad física y signos vitales, con superficies administrativas para roles, usuarios, aprobación médica y métricas de uso de APIs.

## Arquitectura

La aplicación mantiene un backend monolítico Laravel y una capa de presentación React servida mediante Inertia.js.

- Backend: Laravel 13, PHP 8.4, Eloquent, Policies, middleware y validación en servidor.
- Frontend: React 19 con componentes funcionales y hooks, Inertia.js y Tailwind CSS 3.4.
- Renderizado: Inertia SSR habilitado para las páginas React.
- Iconos: `lucide-react` con imports directos y `react-icons` para redes sociales.
- Gráficas: Chart.js mediante `react-chartjs-2`.
- Base de datos: MySQL.
- Cache: Redis.
- Servidor: Laravel Octane con RoadRunner.
- Bundler: Vite.
- Correo transaccional: Resend.
- Infraestructura: Docker y Docker Compose.

La estructura frontend se organiza en `resources/js/Pages`, `Components`,
`Layouts`, `Hooks` y `Utils`. `resources/views/app.blade.php` es únicamente la
plantilla raíz de Inertia; las cinco plantillas de correo permanecen en Blade.

Las páginas se encuentran en `resources/js/Pages`, los componentes compartidos en `resources/js/Components` y los layouts React en `resources/js/Layouts`. `resources/views/app.blade.php` es exclusivamente la plantilla raíz de Inertia. Las cinco plantillas bajo `resources/views/emails` permanecen en Blade para renderizado de correo en servidor.

## Desarrollo local

```bash
docker compose up -d app
npm install
npm run build
docker compose exec app php artisan test
```

Los comandos de Composer, Artisan y PHPUnit/Pest deben ejecutarse dentro del contenedor `app` para evitar diferencias entre el PHP del host y el entorno del proyecto.

Después de modificar controladores o middleware bajo Octane:

```bash
docker compose exec app php artisan octane:reload
```

## Funcionalidades principales

- Registro, autenticación, verificación de correo y recuperación de contraseña.
- Onboarding diferenciado por perfil.
- Captura de glucosa, presión arterial, peso, estrés, nutrición, síntomas y actividad física.
- Dashboards para pacientes, médicos y cuidadores.
- Vinculación controlada entre pacientes, médicos y cuidadores.
- Administración de usuarios, roles y aprobación médica.
- Resúmenes clínicos y visualizaciones históricas.
- Métricas administrativas de proveedores de IA.

## Verificación

```bash
docker compose exec app php artisan test
npm run build
npm audit
```

La documentación de la migración Blade a React/Inertia se encuentra en `docs/migration`.
