# DiabTrack

##DOMAIN
https://Diabtrack.app

DiabTrack is a comprehensive platform for diabetes monitoring. It enables patients, doctors, and caregivers to manage glycemic indicators, nutrition, physical activity, and vital signs, featuring administrative surfaces for roles, users, medical approval, and API usage metrics.

## Architecture

The application maintains a monolithic Laravel backend and a React presentation layer served via Inertia.js.

- Backend: Laravel 13, PHP 8.4, Eloquent, Policies, middleware, and server-side validation.
- Frontend: React 19 with functional components and hooks, Inertia.js, and Tailwind CSS 3.4.
- Rendering: Inertia SSR enabled for React pages.
- Icons: `lucide-react` with direct imports and `react-icons` for social networks.
- Charts: Chart.js via `react-chartjs-2`.
- Database: MySQL.
- Cache: Redis.
- Server: Laravel Octane with RoadRunner.
- Bundler: Vite.
- Transactional Email: Resend.
- Infrastructure: Docker and Docker Compose.

The frontend structure is organized into `resources/js/Pages`, `Components`, `Layouts`, `Hooks`, and `Utils`. `resources/views/app.blade.php` is exclusively the Inertia root template; the five email templates remain in Blade.

Pages are located in `resources/js/Pages`, shared components in `resources/js/Components`, and React layouts in `resources/js/Layouts`. `resources/views/app.blade.php` is exclusively the Inertia root template. The five templates under `resources/views/emails` remain in Blade for server-side email rendering.

## Local Development

```bash
docker compose up -d app
npm install
npm run build
docker compose exec app php artisan test
```

Composer, Artisan, and PHPUnit/Pest commands must be executed inside the `app` container to prevent differences between the host PHP and the project environment.

After modifying controllers or middleware under Octane:

```bash
docker compose exec app php artisan octane:reload
```

## Main Features

- Registration, authentication, email verification, and password recovery.
- Differentiated onboarding by profile.
- Tracking of glucose, blood pressure, weight, stress, nutrition, symptoms, and physical activity.
- Dashboards for patients, doctors, and caregivers.
- Controlled linking between patients, doctors, and caregivers.
- User management, roles, and medical approval.
- Clinical summaries and historical visualizations.
- Administrative metrics for AI providers.

## Verification

```bash
docker compose exec app php artisan test
npm run build
npm audit
```

Documentation for the Blade to React/Inertia migration can be found in `docs/migration`.
