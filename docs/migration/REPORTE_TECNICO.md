# Reporte técnico de migración

## Resumen

DiabTrack migró la capa de presentación de Blade a React con Inertia.js sin
reescribir la lógica de negocio. Se migraron 33 pantallas activas. Las cinco
plantillas de correo permanecen en Blade.

## Tecnologías

- Laravel 13, PHP 8.4, Eloquent, Policies y validación de servidor.
- Laravel Octane con RoadRunner.
- React 19 e Inertia.js con SSR habilitado.
- Vite y Tailwind CSS 3.4.
- `lucide-react` para iconos de interfaz, con imports directos.
- `react-icons` para iconos de redes sociales.
- Chart.js mediante `react-chartjs-2`.
- MySQL, Redis, Docker y Docker Compose.

## Arquitectura final

- `resources/js/Pages`: una página React por pantalla Inertia.
- `resources/js/Components`: componentes reutilizables como layouts,
  métricas, badges, tooltips, formularios y paginación.
- `resources/js/Layouts`: `GuestLayout`, `AuthenticatedLayout` y `AdminLayout`.
- `resources/js/Hooks`: hooks personalizados.
- `resources/js/Utils`: helpers puros de JavaScript.
- `resources/views/app.blade.php`: plantilla raíz mínima con `@inertia`.

## Decisiones clave

- Se mantuvo la lógica de controladores, modelos, policies y validaciones.
- Se eligió Tailwind puro para React; Bootstrap y Font Awesome no forman parte
  del frontend final.
- Los iconos de Lucide se importan por submódulo para controlar el tamaño del
  bundle.
- Los badges de medición y estado son componentes reutilizables y reflejan la
  semántica real del rango glucémico.
- `InfoTooltip` centraliza las explicaciones de tarjetas y campos de captura.
- Los estados vacíos conservan una estructura útil y accionable, no solo un
  mensaje de texto.
- Inertia SSR se mantiene habilitado. En tests se permite únicamente la
  petición interna SSR y se bloquean explícitamente llamadas a Claude, Gemini
  y Anthropic.
- Redis continúa siendo el cache de producción; los tests usan `array` para
  aislar rate limiting y caché entre casos.
- La caché de métricas se invalida al guardar signos vitales.

## Calidad

La suite final contiene **162 tests**, con **1150 assertions**, cubriendo
autenticación, autorización por rol, onboarding, dashboards, formularios de
tracking, validaciones, notificaciones, perfiles, administración, métricas,
serialización Inertia y ausencia de llamadas reales a proveedores de IA.

El build frontend se verifica con `npm run build`. Los comandos de Composer,
Artisan y tests se ejecutan dentro del contenedor `app`.

## Historial de commits recientes

- `fix(tests): aislar rate limiter y validar ausencia de llamadas IA`
- `fix(ui): badges y tooltips en dashboards y formularios`
- `feat(ui): componentes reutilizables de métricas y ayuda`
- `fix(ai): mejorar instrucciones de tips personalizados`
- `fix(cache): invalidar métricas tras registrar signos vitales`
- `chore(repo): actualizar reglas de archivos ignorados`
