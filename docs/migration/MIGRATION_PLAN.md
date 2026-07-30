# Plan de migración Blade a React con Inertia.js

## Reglas permanentes

### Infraestructura y entorno

- Todos los comandos de Composer, Artisan y tests se ejecutan dentro del contenedor:
  `docker compose exec app composer ...`
  `docker compose exec app php artisan ...`
  Nunca con el PHP/Composer del host.
- Después de cualquier cambio en un controlador o middleware, ejecutar
  `docker compose exec app php artisan octane:reload`
  antes de la prueba manual. Octane mantiene el código anterior en memoria hasta
  el reload.
- Redis se queda como está (activo en producción). No modificar el mecanismo de
  cache como efecto colateral de esta migración.
- Tailwind se mantiene en 3.4. No activar el plugin de Tailwind 4; esa sería una
  migración separada, fuera de alcance.
- Toda corrección de paridad visual en React usa exclusivamente Tailwind CSS y
  `lucide-react`. Bootstrap CSS, Bootstrap JS, Font Awesome y los CSS legacy
  (`design-system.css`, `dashboardc.css`, `admin.css`, `auth-global.css`) pueden
  consultarse como referencia de medidas, colores, tipografía y breakpoints,
  pero nunca importarse ni consumirse como dependencias reales del bundle React.
  Esta regla aplica a `AuthenticatedLayout`, `AdminLayout`, `GuestLayout` y a
  todas las pantallas corregidas posteriormente.
- Los iconos de `lucide-react` se importan siempre desde su submódulo directo
  (`lucide-react/dist/esm/icons/<icono>.mjs`, o el subpath equivalente soportado
  por la versión instalada), nunca desde el índice general `lucide-react`. Esto
  evita que Vite recorra el catálogo completo de iconos durante cada build.

### Alcance y lógica de negocio

- Esta es una migración de capa de presentación, no una reescritura de lógica de
  negocio. Controladores, validaciones, Policies y modelos se mantienen intactos
  salvo cambios estrictamente necesarios para exponer datos a Inertia (ej. usar
  API Resources en vez de arrays sueltos).
- Las 5 plantillas de correo quedan excluidas del alcance; permanecen en Blade.
- Los roles no se hardcodean en el frontend, porque el admin puede crear roles
  adicionales. El backend expone permisos calculados (esAdmin, puedeVerVitales,
  puedeVincularPacientes, y los que se necesiten después) como datos compartidos
  de Inertia vía HandleInertiaRequests. React nunca compara nombres de rol
  directamente para decidir autorización real.
- Ningún archivo Blade se elimina hasta que su equivalente en React esté
  confirmado funcionando en producción local. La eliminación real ocurre en la
  Fase 8, no antes.

### Reemplazo de JS legacy (decisión fija para todo el proyecto, no se re-decide pantalla por pantalla)

- Alpine.js -> reemplazar por estado de React (useState, Context donde aplique).
- Bootstrap JS (CDN) -> eliminar; Tailwind + React cubre el comportamiento.
- SweetAlert2 (CDN) -> reemplazar por componentes propios Components/Modal y
  Components/Toast.
- Chart.js -> mantener, envolver con react-chartjs-2.
- fetch() -> migrar a router/useForm de Inertia donde el flujo sea navegación o
  formulario; mantener fetch() nativo solo para llamadas de datos puras en
  segundo plano (ej. polling de notificaciones) que no calzan en el modelo de
  página de Inertia.

### Arquitectura de componentes

- Solo componentes funcionales con hooks. Nada de componentes de clase.
- PascalCase para componentes, camelCase para props y hooks.
- layouts.app se divide en GuestLayout y AuthenticatedLayout (Nivel 0,
  infraestructura). Ambos deben existir y estar probados antes de migrar
  cualquier pantalla que dependa de ellos. No se cuentan como "pantalla migrada"
  dentro del conteo de avance.
- Componentes reutilizables en Components/, páginas en Pages/, layouts en
  Layouts/, hooks personalizados en Hooks/, helpers puros en Utils/.
- Preferir componentes pequeños de una sola responsabilidad sobre pantallas
  monolíticas.

### Git

- Commit local después de cada pantalla, únicamente si tests + build + prueba
  manual pasaron sin problemas. El commit es el registro de "esto funciona
  verificado", no de intención.
- Nunca ejecutar git push. Todo se queda en la rama feature/migracion-react-inertia
  hasta autorización explícita.
- Si algo falla después de un commit, repórtalo de inmediato y espera instrucción;
  no lo arregles por cuenta propia sin avisar primero.
- No mezclar en los commits de migración los cambios preexistentes ajenos en
  tests/TestCase.php, documentacion/ y tools/.

### Calidad

- Sin emojis en código, comentarios ni copy de la interfaz, en ningún artefacto
  generado.
- NINGUNA pantalla se marca como QA visual aprobado sin una captura de pantalla
  real comparada contra producción, guardada en `docs/migration/qa-visual/`. Un
  reporte de texto describiendo que "se ve bien" no es evidencia suficiente.
- Ante cualquier duda visual, revisar primero el Blade y CSS originales. Si la
  implementación React no coincide, corregirla de forma autónoma con base en esa
  evidencia; no introducir reinterpretaciones o rediseños libres.
- Para cada pantalla y layout corregido, guardar evidencia desktop `1920x1080`
  y móvil `390x844` contra `localhost:8081` en `docs/migration/qa-visual/`.
  Solo crear el commit atómico cuando estructura, colores, iconos y espaciado
  sean reconocibles como la misma pantalla y tests/build permanezcan en verde.
- La corrección visual continúa autónomamente en el orden del inventario y se
  reporta de forma consolidada al cerrar cada Nivel. Solo se detiene por una
  ambigüedad no resoluble desde el original, un problema arquitectónico que
  exija propagación retroactiva, o una falla de tests/build sin causa evidente.

### Protocolo de ejecución en lote

- Avanzar de forma autónoma por las pantallas restantes del Nivel 2, en el orden definido, sin esperar aprobación después de cada una.
- Cada pantalla mantiene el flujo completo: implementación, `octane:reload`, QA real en navegador, tests, build y commit local atómico únicamente cuando todo pasa.
- Antes de cerrar una pantalla, comparar cada campo, límite y validación contra el Blade original. Si un slider, dropdown, `maxlength` u otro control React es más restrictivo que el backend y que el Blade anterior, tratarlo como regresión y corregirlo antes de continuar. Si la limitación ya existía en Blade, conservarla como paridad.
- Detener el lote y reportar de inmediato únicamente si una regresión de paridad requiere decisión de producto; un test falla sin causa evidente tras una revisión rápida; aparece un hallazgo de seguridad; se requiere una decisión de arquitectura no cubierta por estas reglas; o una integración externa necesita confirmación antes de probarse en vivo.
- Fuera de esas condiciones, continuar hasta completar todo el Nivel 2 y entregar un único resumen consolidado con pantallas migradas, commits, ajustes de paridad y estado final de tests/build.

## 1. Objetivo y límites

Migrar incrementalmente la capa de presentación de DiabTrack desde Blade a componentes funcionales React, usando Inertia.js y Tailwind CSS 3.4, sin reescribir la lógica de negocio ni cambiar URLs, autorización, validaciones, modelos, Redis, Octane/RoadRunner o el mecanismo de despliegue.

Este plan parte del inventario aprobado en `docs/migration/inventario_pantallas.md` y organiza el trabajo por dependencia y riesgo:

- **Nivel 0:** infraestructura React/Inertia y componentes compartidos. No cuenta como pantalla migrada.
- **Nivel 1:** autenticación y formularios simples sin JavaScript complejo.
- **Nivel 2:** CRUD, perfil, listados y formularios con estado local, sin gráficas ni IA.
- **Nivel 3:** dashboards, resumen, IA, métricas, Chart.js, SweetAlert2 y notificaciones/búsqueda en vivo.

Las cinco plantillas de correo permanecen en Blade. Las dos vistas legacy de detalle no se migran y se eliminarán en Fase 8 después de la comprobación final.

## 2. Decisiones confirmadas

### 2.1 Stack

- Laravel 13 con middleware configurado en `bootstrap/app.php`.
- Octane sobre RoadRunner.
- Redis mediante `phpredis`; no se cambia como parte de esta migración.
- Vite 8; no se introduce Laravel Mix.
- Tailwind CSS 3.4 continúa vía PostCSS. No se activa `@tailwindcss/vite` 4.
- React, React DOM, `@inertiajs/react` y `@vitejs/plugin-react` se incorporarán en Fase 3.
- Chart.js se conserva y se integrará mediante `react-chartjs-2`.
- El JavaScript actual no usa Ziggy ni un helper `route()` en cliente. Los usos encontrados son llamadas PHP/Blade evaluadas en servidor. Por tanto, Ziggy no se instalará en Fase 3 sin una necesidad concreta; las primeras páginas recibirán URLs desde backend o usarán los contratos HTTP existentes de forma centralizada.

### 2.2 Dependencias legacy

| Dependencia actual | Decisión para la migración |
|---|---|
| Alpine.js | Reemplazar por hooks y estado React. Mantenerlo mientras existan consumidores Blade activos. |
| Bootstrap JS CDN | Reemplazar dropdowns, collapse y modales por React/Tailwind. Retirarlo solo cuando no queden consumidores. |
| SweetAlert2 CDN | Sustituir por `Modal` y `Toast` propios. |
| Chart.js | Mantener la configuración y envolver con `react-chartjs-2`. |
| `fetch()` | Usar Inertia para navegación/formularios; conservar `fetch` únicamente para endpoints puramente de datos cuando sea adecuado. |

### 2.3 Roles y autorización

- No se codificará una lista cerrada de roles en React.
- `HandleInertiaRequests` compartirá el usuario, sus roles dinámicos y booleanos/permisos calculados por backend, por ejemplo `esAdmin`, `esPaciente`, `esCuidador`, `esMedico` y capacidades específicas cuando sean necesarias.
- Middleware, validaciones y comprobaciones de vínculo seguirán protegiendo todas las acciones. Ocultar un control en React no sustituye autorización backend.
- La administración actual combina `users.is_admin` con el rol `admin`; ambos comportamientos se preservan.

## 3. Exclusiones

### 3.1 Correos que permanecen Blade

No son páginas Inertia y no se eliminarán:

1. `resources/views/emails/doctor-approved.blade.php`
2. `resources/views/emails/email-change-alert.blade.php`
3. `resources/views/emails/reset-password.blade.php`
4. `resources/views/emails/verify-email-change.blade.php`
5. `resources/views/emails/verify-email-code.blade.php`

### 3.2 Vistas legacy para eliminar en Fase 8

| Vista | Comprobación de ruta | Decisión |
|---|---|---|
| `resources/views/caregiver/patient-detail.blade.php` | `caregiver.patient.show` apunta a `CaregiverController@showPatient`, que valida el vínculo y redirige a `caregiver.dashboard?patient_id=...`; no ejecuta `view('caregiver.patient-detail')`. | No migrar. Eliminar en Fase 8 después de tests y búsqueda final de referencias. Mantener la ruta y el redirect. |
| `resources/views/doctor/patient-detail.blade.php` | `doctor.patient.show` apunta a `DoctorController@showPatient`, que valida el vínculo y redirige a `doctor.dashboard?patient_id=...`; no ejecuta `view('doctor.patient-detail')`. | No migrar. Eliminar en Fase 8 después de tests y búsqueda final de referencias. Mantener la ruta y el redirect. |

La confirmación se realizó con `php artisan route:list --path=caregiver/patient --json`, `php artisan route:list --path=doctor/patient --json` y búsqueda de referencias a ambos nombres de vista.

## 4. Nivel 0: infraestructura y componentes compartidos

Este nivel se ejecuta en Fases 3 y 4 y no se reporta como una pantalla migrada.

### 4.1 Infraestructura Inertia

1. Instalar `inertiajs/inertia-laravel` y generar `HandleInertiaRequests`.
2. Registrar el middleware dentro del grupo `web` en `bootstrap/app.php`.
3. Instalar React, React DOM, `@inertiajs/react`, `@vitejs/plugin-react`, `react-chartjs-2` y una versión de `chart.js` compatible con la configuración actual.
4. No instalar Ziggy: no existe uso actual del helper `route()` en JavaScript. Si una pantalla demuestra que las URLs entregadas por backend o centralizadas en `Utils` son insuficientes, la decisión se revisará explícitamente antes de añadir la dependencia.
5. Añadir React al `vite.config.js` y cambiar la entrada JS a `resources/js/app.jsx`.
6. Crear `resources/views/app.blade.php` como root mínimo con `@inertiaHead`, `@vite` y `@inertia`. No reemplazar ni eliminar todavía `resources/views/layouts/app.blade.php`.
7. Ampliar `tailwind.config.js` con `./resources/js/**/*.{js,jsx}` manteniendo Tailwind 3.4 vía PostCSS.
8. Mantener temporalmente `resources/js/app.js` y Alpine para páginas Blade aún no migradas. Retirarlos solo cuando no tengan consumidores.
9. Confirmar que `npm run build` continúa ejecutándose en la etapa de assets del Dockerfile.

### 4.2 Props compartidas

`HandleInertiaRequests::share()` expondrá datos evaluados por request, sin estado global mutable:

- `auth.user`: representación mínima del usuario autenticado.
- `auth.roles`: roles dinámicos serializados como nombres/identificadores.
- `auth.permissions`: capacidades calculadas requeridas por la UI, por ejemplo `esAdmin`, `puedeVerVitales` o `puedeVincularPacientes`; los componentes no decidirán acceso comparando nombres de rol hardcodeados.
- `flash.success`, `flash.error`, `flash.status` y cualquier mensaje existente realmente usado.
- Errores de validación mediante el mecanismo estándar de Inertia.

Las notificaciones y la búsqueda no se cargarán globalmente en el primer setup si eso reproduce consultas innecesarias para todos los roles. Se incorporarán como props diferidas o endpoints de datos al construir el layout autenticado completo en Nivel 3.

### 4.3 Estructura obligatoria

```text
resources/js/
  Pages/
  Components/
  Layouts/
  Hooks/
  Utils/
```

### 4.4 Componentes a construir antes de sus consumidores

| Grupo | Componentes previstos | Origen Blade/uso |
|---|---|---|
| Layout | `GuestLayout`, `AuthenticatedLayout`, `AdminLayout` | `layouts.guest`, `layouts.app`, `layouts.admin` |
| Navegación | `Navbar`, `Sidebar`, `AdminSidebar`, `TrackingNav`, `Dropdown` | Navegación de layouts y `x-tracking-nav` |
| Formularios | `FormInput`, `FormSelect`, `FormError`, `InputLabel`, `SubmitButton`, `Checkbox` | Componentes Breeze/admin y formularios repetidos |
| Feedback | `Alert`, `Toast`, `Modal`, `ConfirmDialog`, `LoadingState` | Flash, modales Alpine/Bootstrap y SweetAlert2 |
| Datos | `Card`, `Table`, `Pagination`, `Badge`, `EmptyState` | Dashboards, CRUD administrativo y estados vacíos |
| Gráficas | `ChartCard` y wrappers de tipos usados realmente | Dashboards, resumen y métricas API |
| Funciones globales | `NotificationMenu`, `GlobalSearch` | JavaScript y markup actuales de `layouts.app` |

Los componentes se crearán cuando tengan al menos dos consumidores conocidos o cuando sean infraestructura transversal. Los bloques exclusivos de una página permanecerán locales si extraerlos no aporta reutilización o legibilidad clara.

## Estado de corrección visual de las 33 pantallas

Esta tabla usa las 33 pantallas activas del inventario original. Los layouts de infraestructura no se contabilizan como pantallas. `Completa` exige comparación real contra la versión de producción en escritorio y móvil, con evidencia guardada en `docs/migration/qa-visual/`.

| Orden | Nivel | Pantalla | Página React | Corrección visual |
|---:|---:|---|---|---|
| 1 | 1 | Login | `Pages/Auth/Login.jsx` | Completa |
| 2 | 1 | Registro | `Pages/Auth/Register.jsx` | Completa |
| 3 | 1 | Solicitar restablecimiento | `Pages/Auth/ForgotPassword.jsx` | Completa |
| 4 | 1 | Restablecer contraseña | `Pages/Auth/ResetPassword.jsx` | Completa |
| 5 | 1 | Confirmar contraseña | `Pages/Auth/ConfirmPassword.jsx` | Completa |
| 6 | 1 | Verificar correo por código | `Pages/Auth/VerifyEmail.jsx` | Completa |
| 7 | 1 | Landing pública | `Pages/Welcome.jsx` | Completa |
| 8 | 1 | Selección de rol de onboarding | `Pages/Onboarding/RoleSelection.jsx` | Completa |
| 9 | 1 | Onboarding de paciente | `Pages/Onboarding/PatientData.jsx` | Completa |
| 10 | 1 | Onboarding de cuidador | `Pages/Onboarding/CaregiverData.jsx` | Completa |
| 11 | 1 | Onboarding de médico | `Pages/Onboarding/DoctorData.jsx` | Completa |
| 12 | 1 | Vincular paciente como cuidador | `Pages/Caregiver/LinkPatient.jsx` | Completa |
| 13 | 1 | Vincular paciente como médico | `Pages/Doctor/LinkPatient.jsx` | Completa |
| 14 | 2 | Dashboard administrativo | `Pages/Admin/Dashboard.jsx` | Completa |
| 15 | 2 | Roles: listado | `Pages/Admin/Roles/Index.jsx` | Completa |
| 16 | 2 | Roles: crear | `Pages/Admin/Roles/Create.jsx` | Completa |
| 17 | 2 | Roles: editar | `Pages/Admin/Roles/Edit.jsx` | Completa |
| 18 | 2 | Usuarios: listado | `Pages/Admin/Users/Index.jsx` | Completa |
| 19 | 2 | Usuarios: crear | `Pages/Admin/Users/Create.jsx` | Completa |
| 20 | 2 | Usuarios: editar | `Pages/Admin/Users/Edit.jsx` | Completa |
| 21 | 2 | Aprobación de médicos | `Pages/Admin/Doctors/Index.jsx` | Completa |
| 22 | 2 | Registrar signos vitales | `Pages/Tracking/Vitals/Create.jsx` | Completa |
| 23 | 2 | Registrar actividad | `Pages/Tracking/Activity/Create.jsx` | Completa |
| 24 | 2 | Registrar nutrición | `Pages/Tracking/Nutrition/Create.jsx` | Completa |
| 25 | 2 | Registrar síntomas | `Pages/Tracking/Symptoms/Create.jsx` | Completa |
| 26 | 2 | Registrar vital de paciente como cuidador | `Pages/Caregiver/Tracking/Vitals/Create.jsx` | Pendiente |
| 27 | 2 | Editar perfil | `Pages/Profile/Edit.jsx` | Pendiente |
| 28 | 3 | Dashboard de cuidador | `Pages/Caregiver/Dashboard.jsx` | Pendiente |
| 29 | 3 | Dashboard de médico | `Pages/Doctor/Dashboard.jsx` | Pendiente |
| 30 | 3 | Dashboard de paciente | `Pages/Dashboard.jsx` | Pendiente |
| 31 | 3 | Resumen/historial | `Pages/Tracking/Summary.jsx` | Pendiente |
| 32 | 3 | Ideas de alimentación | `Pages/Tracking/Nutrition/Index.jsx` | Pendiente |
| 33 | 3 | Uso de API/IA | `Pages/Admin/ApiUsage/Index.jsx` | Pendiente |

Estado al 30 de julio de 2026: **14 completas y 19 pendientes**. `Admin/Dashboard` quedó corregida durante el bloque de `AdminLayout`: las tarjetas ahora son superficies blancas y el color se limita a los iconos/acento, conforme al Blade y `admin.css` originales. La página cuenta con capturas comparativas de escritorio y móvil, por lo que no se volverá a contabilizar como pendiente del Nivel 2.

Corrección transversal posterior del footer del Nivel 1: `Welcome` y las diez páginas que consumen `GuestLayout` conservan estado `Completa` después de repetir su evidencia visual. En escritorio, el contenido principal de `GuestLayout` recupera el `min-height: 100vh` del Blade/CSS original, que empuja naturalmente el footer fuera del viewport inicial; no se oculta de forma artificial. Los footers usan superficie blanca, borde y texto gris como los originales. Los iconos oficiales de marca se sirven mediante imports específicos de `react-icons/fa`: Instagram, Facebook y Reddit en `GuestLayout`; Instagram, Facebook y Twitter en `Welcome`, conforme a sus Blade originales. El desplazamiento suave original de `index.css` se conserva globalmente mediante `scroll-behavior: smooth`, por lo que “Saber más” y la flecha del hero animan el recorrido hasta `#features`.

## 5. Orden de migración por nivel

El orden dentro de cada nivel es obligatorio salvo que una dependencia descubierta durante implementación justifique documentar un cambio.

### Nivel 1: autenticación y formularios simples

| Orden | Blade | Página React | Ruta principal | Motivo |
|---:|---|---|---|---|
| 1 | `auth/login.blade.php` | `Pages/Auth/Login.jsx` | `login` | Primera validación del flujo GuestLayout, `useForm`, errores, sesión y Socialite. |
| 2 | `auth/register.blade.php` | `Pages/Auth/Register.jsx` | `register` | Formulario simple y reutiliza los componentes del login. |
| 3 | `auth/forgot-password.blade.php` | `Pages/Auth/ForgotPassword.jsx` | `password.request` | Formulario simple y flash/status. |
| 4 | `auth/reset-password.blade.php` | `Pages/Auth/ResetPassword.jsx` | `password.reset` | Valida props de token/email y errores backend. |
| 5 | `auth/confirm-password.blade.php` | `Pages/Auth/ConfirmPassword.jsx` | `password.confirm` | Flujo autenticado simple sin layout de aplicación. |
| 6 | `auth/verify-email.blade.php` | `Pages/Auth/VerifyEmail.jsx` | `verification.notice` | Reúne verificación por código, reenvío y logout, todavía sin JS complejo. |
| 7 | `welcome.blade.php` | `Pages/Welcome.jsx` | `/` | Página pública sin datos; permite retirar Bootstrap JS de esta superficie. |
| 8 | `onboarding/role-selection.blade.php` | `Pages/Onboarding/RoleSelection.jsx` | `onboarding.index` | Navegación simple y roles provenientes del backend. |
| 9 | `onboarding/personal-data.blade.php` | `Pages/Onboarding/PatientData.jsx` | `onboarding.patient` | Formulario backend existente sin JS complejo. |
| 10 | `onboarding/caregiver-data.blade.php` | `Pages/Onboarding/CaregiverData.jsx` | `onboarding.caregiver` | Sustituye selector inline por estado local React. |
| 11 | `onboarding/doctor-data.blade.php` | `Pages/Onboarding/DoctorData.jsx` | `onboarding.doctor` | Igual patrón que cuidador, con campos profesionales. |
| 12 | `caregiver/link-patient.blade.php` | `Pages/Caregiver/LinkPatient.jsx` | `caregiver.link` | Formulario corto; autorización existente permanece intacta. |
| 13 | `doctor/link-patient.blade.php` | `Pages/Doctor/LinkPatient.jsx` | `doctor.link` | Formulario corto con middleware `doctor.approved`. |

Gate para cerrar Nivel 1: autenticación local/Socialite, recuperación, verificación, onboarding de los tres perfiles, enlaces y errores de validación funcionan; las URLs no cambian; build y suite backend pasan.

### Nivel 2: CRUD, perfil, listados y captura de datos

| Orden | Blade | Página React | Ruta principal | Motivo |
|---:|---|---|---|---|
| 14 | `admin/dashboard.blade.php` | `Pages/Admin/Dashboard.jsx` | `admin.dashboard` | Entrada administrativa simple; valida `AdminLayout`. |
| 15 | `admin/roles/index.blade.php` | `Pages/Admin/Roles/Index.jsx` | `admin.roles.index` | Primer listado paginado y confirmación de eliminación. |
| 16 | `admin/roles/create.blade.php` | `Pages/Admin/Roles/Create.jsx` | `admin.roles.create` | CRUD simple con componentes ya creados. |
| 17 | `admin/roles/edit.blade.php` | `Pages/Admin/Roles/Edit.jsx` | `admin.roles.edit` | Edición/eliminación y condición de usuarios asociados. |
| 18 | `admin/users/index.blade.php` | `Pages/Admin/Users/Index.jsx` | `admin.users.index` | Tabla, búsqueda, paginación y restricciones sobre el usuario actual. |
| 19 | `admin/users/create.blade.php` | `Pages/Admin/Users/Create.jsx` | `admin.users.create` | Roles dinámicos y `is_admin`; no hardcodear roles. |
| 20 | `admin/users/edit.blade.php` | `Pages/Admin/Users/Edit.jsx` | `admin.users.edit` | Formulario compuesto y protección visual de la cuenta actual. |
| 21 | `admin/doctors/index.blade.php` | `Pages/Admin/Doctors/Index.jsx` | `admin.doctors.index` | Listado paginado con aprobación/rechazo y filtros. |
| 22 | `tracking/vital/create.blade.php` | `Pages/Tracking/Vitals/Create.jsx` | `tracking.vital.create` | Captura con selectores locales, sin dependencias gráficas. |
| 23 | `tracking/activity/create.blade.php` | `Pages/Tracking/Activity/Create.jsx` | `tracking.activity.create` | Captura con selectores locales reutilizando componentes. |
| 24 | `tracking/nutrition/create.blade.php` | `Pages/Tracking/Nutrition/Create.jsx` | `tracking.nutrition.create` | Captura de comida; no incluye la pantalla IA. |
| 25 | `tracking/symptom/create.blade.php` | `Pages/Tracking/Symptoms/Create.jsx` | `tracking.symptom.create` | Serialización de catálogo agrupado y formulario. |
| 26 | `caregiver/tracking/vital-create.blade.php` | `Pages/Caregiver/Tracking/Vitals/Create.jsx` | `caregiver.patient.vital.create` | Variante de captura para paciente vinculado. |
| 27 | `profile/edit.blade.php` | `Pages/Profile/Edit.jsx` | `profile.edit` | Varios formularios, modal, cambio de email y vinculaciones; se hace después de estabilizar componentes. |

Gate para cerrar Nivel 2: CRUD, filtros, paginación, formularios, modales, permisos visuales y validaciones mantienen paridad; ninguna acción depende de Alpine, Bootstrap JS o SweetAlert2 en páginas ya migradas.

### Nivel 3: pantallas complejas y comportamiento en vivo

| Orden | Blade | Página React | Ruta principal | Dependencias/riesgo |
|---:|---|---|---|---|
| 28 | `caregiver/dashboard.blade.php` | `Pages/Caregiver/Dashboard.jsx` | `caregiver.dashboard` | Selección de paciente, métricas, Chart.js y desvinculación. |
| 29 | `doctor/dashboard.blade.php` | `Pages/Doctor/Dashboard.jsx` | `doctor.dashboard` | Métricas, Chart.js, aprobación, metas clínicas y desvinculación. |
| 30 | `dashboard.blade.php` | `Pages/Dashboard.jsx` | `dashboard` | Dashboard principal, gráficas, peso, invitaciones, flash y acciones dinámicas. |
| 31 | `tracking/summary.blade.php` | `Pages/Tracking/Summary.jsx` | `tracking.summary` | Mayor vista del repo: varias gráficas, filtros, tabs, tablas y paginación cliente. |
| 32 | `tracking/nutrition/index.blade.php` | `Pages/Tracking/Nutrition/Index.jsx` | `tracking.nutrition.index` | Carrusel, generación de sugerencias IA y estados asíncronos. |
| 33 | `admin/api-usage/index.blade.php` | `Pages/Admin/ApiUsage/Index.jsx` | `admin.api-usage.index` | Chart.js, periodos, costes, proveedores y logs paginados. |

Antes de dar por cerrado el Nivel 3 se migrará el comportamiento transversal de `layouts.app`:

1. `NotificationMenu` con lectura, borrado individual/masivo y contadores.
2. `GlobalSearch` para paciente, conservando throttle y contrato JSON.
3. `Toast`/`ConfirmDialog` como reemplazo final de SweetAlert2.
4. Eliminación de dependencias Alpine/Bootstrap JS solamente después de buscar consumidores Blade restantes.

Gate para cerrar Nivel 3: paridad de datasets y escalas de gráficas, navegación por teclado, estados vacíos/error/carga, operaciones asíncronas, notificaciones y búsqueda; prueba bajo Octane/RoadRunner y build Docker.

## 6. Cambios de backend previstos

### 6.1 Regla general

Cada método que hoy retorna una página activa cambiará exclusivamente de `return view(...)` a `return Inertia::render(...)`. Se conservan consultas, servicios, validaciones, redirects, middleware y nombres de ruta salvo ajustes mínimos para serializar props.

### 6.2 Mapeo de controladores

| Controlador/origen | Métodos de render | Cambio previsto |
|---|---|---|
| Closures de `routes/web.php` | `/`, `admin.dashboard` | Renderizar `Welcome` y `Admin/Dashboard`. Se puede extraer a controlador solo si resulta necesario, sin lógica nueva. |
| `AuthenticatedSessionController` | `create` | `Inertia::render('Auth/Login')`. |
| `RegisteredUserController` | `create` | `Inertia::render('Auth/Register')`. |
| `PasswordResetLinkController` | `create` | `Inertia::render('Auth/ForgotPassword')`. |
| `NewPasswordController` | `create` | `Inertia::render('Auth/ResetPassword', props mínimas de request)`. |
| `ConfirmablePasswordController` | `show` | `Inertia::render('Auth/ConfirmPassword')`. |
| `EmailVerificationPromptController` | `__invoke` | Mantener redirect si ya verificó; en caso contrario renderizar `Auth/VerifyEmail`. |
| `OnboardingController` | `index`, `showPatientForm`, `showCaregiverForm`, `showDoctorForm` | Renderizar las cuatro páginas Onboarding. |
| `DashboardController` | `index`, `summary` | Renderizar `Dashboard` y `Tracking/Summary`; conservar `DashboardMetricsService`. |
| `ProfileController` | `edit` | Renderizar `Profile/Edit`; preparar vinculaciones en backend en vez de consultarlas desde Blade. |
| `CaregiverController` | `dashboard`, `showLinkForm`, `createVital` | Renderizar las páginas Caregiver; `showPatient` conserva redirect. |
| `DoctorController` | `dashboard`, `showLinkForm` | Renderizar las páginas Doctor; `showPatient` conserva redirect. |
| Controladores `Tracking` | `create`/`index` correspondientes | Renderizar páginas Tracking conservando Form Requests y servicios. |
| `Admin\\UserController` | `index`, `create`, `edit` | Renderizar CRUD de usuarios con roles dinámicos. |
| `Admin\\RoleController` | `index`, `create`, `edit` | Renderizar CRUD de roles. |
| `Admin\\DoctorApprovalController` | `index` | Renderizar listado y filtros. |
| `Admin\\ApiUsageController` | `index` | Renderizar métricas y logs de uso. |

### 6.3 API Resources y serialización

Actualmente no existe `app/Http/Resources`. Se crearán Resources únicamente donde se pasan modelos/colecciones; no se envolverán innecesariamente props escalares o catálogos estáticos.

| Resource previsto | Consumidores | Campos/relaciones a exponer |
|---|---|---|
| `UserResource` | Auth compartido, perfil, admin y vinculaciones | Identidad y campos explícitamente visibles; roles cuando se carguen. Nunca password, tokens o atributos no requeridos. |
| `RoleResource` | CRUD de usuarios/roles | `id`, `name`, `description`, conteo cuando aplique. |
| `DoctorProfileResource` | Aprobaciones y dashboard médico | Datos profesionales visibles, estado de aprobación y usuario necesario. |
| `LinkedPatientResource` | Dashboards cuidador/médico | Identidad mínima, vínculo/parentesco y resumen necesario. |
| `VitalSignResource` | Dashboards, resumen y registros recientes | Valores mostrados, clasificación y fechas formateables. |
| `NutritionLogResource` | Resumen/dashboard | Campos mostrados y fecha; sin atributos internos innecesarios. |
| `ActivityLogResource` | Resumen/dashboard | Campos mostrados y fecha. |
| `SymptomLogResource` | Resumen/dashboard | Síntomas/relaciones visibles y fecha. |
| `SymptomResource` | Formulario de síntomas | `id`, nombre y categoría. |
| `PatientNotificationResource` | Menú de notificaciones | `id`, título, cuerpo, estado, fecha y destino permitido. |
| `ApiUsageLogResource` | Métricas administrativas | Proveedor/modelo, tokens, coste, estado, paciente mínimo y fechas. |

Antes de crear cada Resource se leerá la vista consumidora y el modelo real para fijar exactamente sus campos. Los arrays agregados producidos por servicios de métricas pueden seguir como DTO/arrays explícitos si no son modelos Eloquent y su contrato queda documentado.

### 6.4 Datos que deben salir de Blade

- Consulta de notificaciones realizada dentro de `layouts.app`.
- Consulta de vinculados y derivación de roles realizada dentro de `profile.edit`.
- Conteos/consultas como `role->users()->count()` dentro de vistas administrativas, cuando no estén precargados.
- Cualquier acceso a relaciones no eager-loaded que pueda causar N+1 al serializar props.

Mover estas operaciones al controlador, servicio o capa de props compartidas es un cambio de exposición de datos, no de regla de negocio.

## 7. Convenciones de implementación por pantalla

Para cada fila del orden de migración:

1. Leer Blade, controlador, Form Request, middleware/policy y JS asociado.
2. Capturar una referencia visual antes del cambio cuando la pantalla sea accesible localmente.
3. Crear la página React con el nombre exacto usado por `Inertia::render`.
4. Usar `useForm()` para POST, PUT/PATCH y DELETE; mostrar `errors`, `processing` y flash.
5. Usar `Link`/`router` con URLs suministradas por backend o helpers puros centralizados, sin cambiar URLs ni contratos de ruta. Ziggy no se añade mientras no exista una necesidad demostrada.
6. Extraer solo componentes compartidos o bloques cuya extracción mejore claramente la lectura.
7. Añadir `data-testid` donde Dusk pierda selectores estables.
8. Ejecutar tests backend relevantes y `npm run build`.
9. Hacer QA por rol, validación, navegación y paridad visual.
10. Eliminar el Blade solamente después de la revisión funcional/visual de su equivalente.
11. Crear un commit atómico: `migrate(react): <NombrePantalla> a React + Inertia`.

Durante la convivencia, las rutas ya migradas sirven Inertia y las pendientes siguen devolviendo Blade. Los layouts Blade y React coexistirán hasta que desaparezca el último consumidor correspondiente.

## 8. Testing y gates de no regresión

### Por pantalla

- URL y nombre de ruta sin cambios.
- Acceso permitido y denegado por rol igual al estado anterior.
- Props sin atributos sensibles.
- Formulario correcto, validación inválida, doble submit bloqueado y flash.
- Estados loading, vacío y error cuando apliquen.
- Navegación directa, back/forward e historial Inertia.
- Paridad visual responsive.
- Tests PHP focalizados y build Vite.

### Por nivel

- `php artisan test` completo.
- Dusk actualizado y ejecutado cuando el entorno esté disponible.
- `npm run build` sin warnings de imports o clases Tailwind faltantes.
- Verificación de fugas de memoria/estado entre requests bajo Octane para props compartidas.
- Revisión de consultas para evitar N+1 introducidos por Resources.

### Cierre

- Build Docker completo de punta a punta.
- Smoke test sobre imagen con RoadRunner y Redis configurado.
- Búsqueda final de `return view`, `@extends`, Alpine, Bootstrap JS, SweetAlert2 y scripts inline.
- Conservar exclusivamente los Blade necesarios: root de Inertia y las cinco plantillas de correo, más cualquier excepción documentada.

## 9. Riesgos y mitigaciones

| Riesgo | Mitigación |
|---|---|
| Migrar `layouts.app` de una vez rompe páginas Blade pendientes | Coexistencia de layouts; componentes React se activan solo en páginas Inertia y el Blade se conserva hasta el último consumidor. |
| Props globales costosas en todos los requests | Compartir identidad/permisos mínimos; notificaciones diferidas o limitadas por rol/contexto. |
| Serialización accidental de modelos completos | API Resources con campos explícitos y relaciones eager-loaded. |
| Roles adicionales creados por administradores | Exponer roles dinámicos y capacidades backend; evitar comparaciones cerradas en frontend. |
| Duplicidad temporal Alpine/React | Nunca montar ambos sistemas sobre el mismo árbol; retirar Alpine por consumidor y al final globalmente. |
| Pérdida de comportamiento Bootstrap/SweetAlert | Inventario por pantalla y reemplazo por componentes React antes de retirar CDN. |
| Diferencias de gráficas | Conservar Chart.js, datasets, opciones, colores, escalas y tooltips; validar capturas y valores. |
| Estado mutable incompatible con Octane | Props calculadas por request; sin singletons mutables ni caché frontend en bootstrap PHP. |
| Tailwind 3/4 mezclado | Mantener Tailwind 3.4/PostCSS; no activar ni migrar a Tailwind 4 en este proyecto. |
| Vistas legacy confundidas con páginas activas | No migrarlas; conservar redirects y eliminarlas únicamente en limpieza final. |

## 10. Estado base pre-migración

Línea base tomada antes de instalar Inertia, React o cualquier otra dependencia, y validada dentro del contenedor el 28 de julio de 2026.

### Entorno de ejecución y rama

| Comprobación | Resultado |
|---|---|
| Rama inicial | `main` |
| Rama de trabajo creada | `feature/migracion-react-inertia` |
| Composer en host Windows | No disponible en `PATH`; `composer --version` devuelve comando no reconocido. |
| Docker CLI | Disponible, versión 29.5.2. |
| Docker Compose | Disponible, versión v5.1.3. |
| Docker daemon durante la primera comprobación | Estaba detenido. Se inició Docker Desktop y luego `app`, `db` y `redis` quedaron activos; MySQL y Redis reportaron estado healthy. |
| Servicio PHP con Composer | `app`, definido en `docker-compose.yml`; la imagen lo instala desde `composer:latest` y monta el repositorio en `/var/www/html`. |

El comando exacto aprobado para la futura instalación backend es:

```powershell
docker compose exec app composer require inertiajs/inertia-laravel
```

Prerrequisitos: iniciar Docker Desktop y tener el servicio `app` en ejecución. Si no está levantado, ejecutar primero `docker compose up -d app` y confirmar con `docker compose ps`. No ejecutar `composer require` en el host.

Política de entorno para toda la migración: **todos los comandos de Composer, Artisan y tests PHP se ejecutan dentro del servicio `app` con `docker compose exec app ...`; nunca con PHP o Composer del host**. Esto evita drift entre PHP local, la imagen desplegable y sus extensiones. Los comandos Node/npm siguen ejecutándose en el workspace del host, salvo la validación final de la imagen Docker.

### Suite Pest/PHPUnit

Comando inicial ejecutado en el host para detectar el estado previo:

```powershell
php artisan test
```

Resultado del host: **falla por configuración local**, antes de la migración.

- 11 pruebas pasan.
- 75 pruebas fallan.
- 14 assertions alcanzadas.
- Duración reportada por PHPUnit: 23.80 s.
- Tiempo de pared medido: 31.714 s.
- Código de salida: 2.
- Causa común de los 75 fallos: `could not find driver` al abrir SQLite `:memory:`.
- PHP CLI es 8.4.19. Están cargados `PDO`, `pdo_mysql` y `sqlite3`, pero no `pdo_sqlite`, que es el driver requerido por la conexión PDO usada en los tests.
- Las pruebas unitarias de `DailyTipSuggestionServiceTest`, `ExampleTest` y el feature `ExampleTest` sí alcanzaron resultado PASS; los features dependientes de base de datos no pudieron inicializar migraciones.

Después se levantó el entorno y se ejecutó el baseline autoritativo:

```powershell
docker compose up -d app
docker compose ps
docker compose exec app php artisan test
```

Resultado base autoritativo dentro del contenedor: **pasa completamente**.

- 86 pruebas pasan.
- 0 pruebas fallan.
- 247 assertions.
- Duración reportada por PHPUnit: 12.70 s.
- Tiempo aproximado de pared dentro del contenedor: 14 s.
- Código de salida: 0.
- No quedan fallas legítimas de código que deban exceptuarse del baseline.

Comparación: los 75 fallos del host desaparecen dentro de `app`, por lo que eran exclusivamente drift del PHP local y no un defecto del proyecto. No es necesario modificar el Dockerfile ni instalar `pdo_sqlite` adicionalmente: la imagen actual ya ejecuta correctamente toda la suite. Desde este punto, el baseline de regresión es **86/86 tests en verde dentro del contenedor**; el resultado del host se conserva solo como evidencia del motivo para prohibir su uso en esta migración.

### Build frontend

Comando ejecutado:

```powershell
npm run build
```

Resultado base: **pasa**.

- Vite 8.0.8.
- 66 módulos transformados.
- Build interno de Vite: 3.21 s.
- Tiempo de pared medido: 5.341 s.
- Código de salida: 0.
- Se generaron `public/build/manifest.json`, 12 assets CSS y un asset JS principal.
- Warning no bloqueante `[PLUGIN_TIMINGS]`: el tiempo de plugins se concentró en `vite:css` 65 %, `laravel` 27 % y `vite:css-post` 7 %.

### Auditoría del helper de rutas en JavaScript

- `resources/js/app.js` y `resources/js/bootstrap.js` no llaman `route()` y no importan Ziggy.
- Los scripts inline Blade tampoco llaman un helper de rutas JavaScript.
- Las llamadas `route(...)` encontradas están en expresiones Blade/PHP para `href`, `action` o props y se resuelven en servidor.
- Ziggy no está en `composer.json`, `package.json` ni sus lockfiles como dependencia instalada del proyecto.
- Decisión: no instalar Ziggy en Fase 3 sin una necesidad nueva y explícita.

### Confirmaciones Laravel 13 e Inertia

- `HandleInertiaRequests` se registrará en el grupo web de `bootstrap/app.php` mediante `->withMiddleware()`. No se buscará ni creará `app/Http/Kernel.php`.
- Los datos compartidos expondrán roles dinámicos como información descriptiva y permisos/capacidades calculados por backend para decisiones de UI, por ejemplo `esAdmin`, `puedeVerVitales` y `puedeVincularPacientes`.
- La autorización real seguirá en middleware, Gates/Policies y comprobaciones de vínculo; React no protegerá acciones mediante nombres de rol.

## 11. Resultado de Fase 3: infraestructura base

Completada el 28 de julio de 2026 sin migrar pantallas ni convertir los layouts existentes.

### Dependencias instaladas

- Backend: `inertiajs/inertia-laravel` 3.2.0.
- Frontend: `@inertiajs/react` 3.6.1, React 19.2.8 y React DOM 19.2.8.
- Desarrollo: `@vitejs/plugin-react` 6.0.4.
- Ziggy y `react-chartjs-2` no se instalaron: Ziggy no tiene necesidad actual y las gráficas pertenecen a un bloque posterior.

### Configuración realizada

- `HandleInertiaRequests` generado y registrado en el grupo web de `bootstrap/app.php`.
- Props compartidas: usuario mínimo, roles dinámicos, `esAdmin`, `puedeVerVitales`, `puedeVincularPacientes`, flash de éxito/error/status y errores de validación suministrados por el middleware base de Inertia.
- `resources/js/app.jsx` creado con `createInertiaApp` y componentes funcionales React.
- `resources/views/app.blade.php` creado como root mínimo de Inertia.
- Plugin React añadido a Vite. `resources/js/app.js` y `resources/js/app.jsx` permanecen como entradas simultáneas para permitir convivencia Blade/Inertia.
- Tailwind amplió su búsqueda a `resources/js/**/*.jsx`; continúa en 3.4 mediante PostCSS y el plugin Tailwind 4 sigue inactivo.
- El Dockerfile ya ejecutaba `npm run build` en su etapa de assets; no requirió cambios.

### Verificación posterior

- `docker compose exec app php artisan test`: 86 tests pasan, 0 fallan, 247 assertions, 10.66 s reportados por PHPUnit.
- `npm run build`: correcto, 616 módulos transformados y build Vite de 2.69 s.
- Sintaxis PHP válida en middleware y bootstrap.
- Redis, MySQL y RoadRunner permanecen configurados sin cambios.

### Advisories observados

- `composer audit` reportó tres advisories medios de `guzzlehttp/guzzle` para versiones anteriores a 7.15.1. La instalación solicitada solo añadió Inertia; no se ejecutó una actualización general fuera de alcance.
- `npm install` reportó inicialmente cinco vulnerabilidades: tres altas y dos críticas. Después de revisar `npm audit fix --dry-run`, se aplicó `npm audit fix` sin `--force`; todas las correcciones permanecieron dentro de la misma versión mayor y `npm audit --json` terminó con cero vulnerabilidades.

Versiones finales después de la corrección no breaking:

| Paquete | Antes | Después | Relación |
|---|---:|---:|---|
| `concurrently` | 9.2.1 | 9.2.4 | Dependencia directa de desarrollo |
| `shell-quote` | 1.8.3 | 1.9.0 | Transitiva de `concurrently` |
| `postcss` | 8.5.8 | 8.5.24 | Dependencia directa de build |
| `vite` | 8.0.8 | 8.1.5 | Dependencia directa de build |
| `form-data` | 4.0.5 | 4.0.6 | Transitiva de `axios`, adaptador Node |

La validación posterior conservó 616 módulos en el build, 86/86 tests PHP y 247 assertions. No se utilizó `npm audit fix --force` ni se realizó un salto de versión mayor.

### Nivel 0 parcial: layout invitado y formularios

Se crearon `GuestLayout`, `BrandMark`, `FormInput`, `FormSelect`, `FormError`, `Checkbox`, `SubmitButton` y `AuthSessionStatus` como componentes funcionales React/Tailwind. Permanecen desconectados de rutas hasta migrar la primera pantalla de autenticación; las vistas Blade actuales siguen siendo la interfaz activa.

## 12. Estado del proyecto de migración

| Fase | Estado |
|---|---|
| Fase 0: confirmación de alcance | Completada y aprobada |
| Fase 1: inventario | Completada y aprobada |
| Fase 2: plan de migración | Preparada para revisión |
| Fase 2.5: estrategia JS legacy | Decisiones incorporadas en este plan |
| Fase 3: infraestructura | Completada; pendiente de revisión |
| Fase 4: arquitectura de componentes | Completada |
| Fase 5: migración incremental | Completada, 33 de 33 páginas activas migradas |
| Fase 6: formularios y estado | Completada/transversal |
| Fase 7: testing | Completada/transversal; limpieza final de selectores Blade reservada para Fase 8 |
| Fase 8: limpieza y cierre | Completada; conservadas únicamente la raíz Inertia y cinco plantillas Blade de correo |

## 13. Nivel 1: Login

Completada el 28 de julio de 2026 como primera pantalla del Nivel 1.

### Alcance implementado

- `GET /login` conserva la URL y ahora renderiza `Auth/Login` mediante Inertia.
- El formulario React usa `GuestLayout`, `FormInput`, `Checkbox`, `SubmitButton` y `AuthSessionStatus`; `FormInput` delega la presentación del error al componente compartido `FormError`.
- El estado de `email`, `password` y `remember`, el envío, los errores y el estado de carga se gestionan con `useForm()`.
- El método `store()` conserva la autenticación, regeneración de sesión y decisiones de destino existentes. Solo se añadió la adaptación de sus redirecciones para devolver `Inertia::location()` cuando el origen es una petición Inertia y el destino todavía es Blade.
- `resources/views/auth/login.blade.php` permanece en el repositorio y se eliminará únicamente en Fase 8 después de la confirmación final.

### Verificación funcional

- La respuesta inicial de `GET /login` fue HTTP 200, expuso el componente `Auth/Login` y entregó la cookie `XSRF-TOKEN`. El root de Inertia no incluye un meta tag CSRF manual; Axios/Inertia usa la cookie y el encabezado XSRF automáticamente.
- El POST Inertia exitoso quedó cubierto por una prueba Feature: autentica al usuario y devuelve HTTP 409 con `X-Inertia-Location`, instruyendo al cliente a hacer una navegación de página completa hacia el destino Blade. La lógica contempla dashboard, onboarding, verificación y dashboard administrativo sin cambiar sus reglas.
- El POST Inertia fallido quedó cubierto por una prueba Feature: conserva al visitante como invitado, redirige a `/login` y comparte el error de `email`, que `Login.jsx` presenta mediante `FormError`.
- No se observaron respuestas HTTP 419 en las verificaciones. El token CSRF se resuelve mediante la cookie `XSRF-TOKEN`, sin `@csrf` ni meta tag manual en la página React.
- El navegador integrado de Codex bloqueó el acceso a `localhost`/`127.0.0.1` por aislamiento de red. Por ello no fue posible certificar visualmente la consola del navegador en este entorno; la carga de módulos quedó validada mediante el build de producción y la transición full-page mediante la respuesta `X-Inertia-Location`. La inspección visual/consola queda como punto explícito de QA en un navegador con acceso al puerto local.

### Resultado de regresión

- `docker compose exec app php artisan test --filter=AuthenticationTest`: 7 pruebas pasan, 0 fallan, 39 assertions.
- `docker compose exec app php artisan test`: 88 pruebas pasan, 0 fallan, 273 assertions, 10.68 s reportados por PHPUnit.
- `npm run build`: correcto con Vite 8.1.5; 624 módulos transformados y chunk `Auth/Login` generado.
- La pantalla siguiente sugerida es Registro: pertenece al Nivel 1, reutiliza el mismo `GuestLayout` y los componentes de formulario ya estabilizados, sin depender de gráficas, IA ni JavaScript legacy complejo.

### Corrección de navegación Login ↔ Registro

Corregida el 28 de julio de 2026 después de reproducir que un `<Link>` de Inertia apuntaba a `/register`, pero ese endpoint todavía devolvía Blade. La respuesta no-Inertia impedía que el cliente completara correctamente el historial y actualizara el título.

- `GET /register` ahora renderiza `Auth/Register` mediante Inertia y conserva exactamente la URL `/register`.
- Login mantiene `<Link href={registerUrl}>`, donde `registerUrl` es `/register`.
- Registro usa `<Link href={loginUrl}>`, donde `loginUrl` es `/login`.
- `RegisteredUserController::store()` conserva validación, creación de usuario, evento, login y destino. Solo adapta la respuesta a `Inertia::location()` cuando el destino posterior sigue siendo Blade.
- Verificación manual en navegador: el clic desde Login cambió la URL a `/register`, actualizó el título a `Registro - DiabTrack` y mostró `register-form`; un refresh mantuvo URL, título y formulario. El enlace inverso volvió a `/login`, restauró `Iniciar sesión - DiabTrack` y mostró `login-form`.
- La consola del navegador no registró warnings ni errores durante la navegación completa.
- Fue necesario ejecutar `docker compose exec app php artisan octane:reload` antes de repetir el QA, porque los workers persistentes de RoadRunner todavía conservaban la versión anterior del controlador.
- Regresión posterior: 89 pruebas pasan, 0 fallan, 307 assertions; build Vite correcto con 625 módulos transformados; `git diff --check` correcto.

## 14. Nivel 1: Recuperar contraseña

Completada el 28 de julio de 2026 como siguiente pantalla pendiente del Nivel 1.

- `GET /forgot-password` conserva la URL y ahora renderiza `Auth/ForgotPassword` mediante Inertia.
- `ForgotPassword.jsx` reutiliza `GuestLayout`, `FormInput`, `SubmitButton` y `AuthSessionStatus`; usa `useForm()` para enviar el correo a la ruta existente.
- `PasswordResetLinkController::store()` y toda su validación y lógica de envío permanecen intactos.
- `resources/views/auth/forgot-password.blade.php` permanece en el repositorio hasta la limpieza de Fase 8.
- Después del cambio del controlador se ejecutó `docker compose exec app php artisan octane:reload` antes del QA manual.
- QA manual: el enlace desde Login navegó a `/forgot-password`, el título cambió a `Recuperar contraseña - DiabTrack`, F5 mantuvo la pantalla, el backend mostró el error español para un correo inválido y el enlace inverso volvió a `/login`. No hubo warnings ni errores de consola.
- Suite específica: 5 pruebas pasan, 0 fallan, 24 assertions.
- Suite completa: 90 pruebas pasan, 0 fallan, 323 assertions, 10.53 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 626 módulos transformados y chunk `ForgotPassword` generado.
- Siguiente pantalla sugerida: Restablecer contraseña (`Auth/ResetPassword`), porque es la siguiente del Nivel 1 y completa el flujo iniciado por esta pantalla sin introducir JavaScript complejo.

## 15. Nivel 1: Restablecer contraseña

Completada el 28 de julio de 2026 como última pieza pendiente del flujo de recuperación de contraseña.

- `GET /reset-password/{token}` conserva la URL y ahora renderiza `Auth/ResetPassword` con `token`, `email` y la URL existente del POST como props.
- `ResetPassword.jsx` reutiliza `GuestLayout`, `FormInput` y `SubmitButton`; usa `useForm()` con `token`, `email`, `password` y `password_confirmation`.
- El token también se representa como un campo oculto y viaja en el cuerpo del POST a `/reset-password`; no se incorpora a la URL de destino del formulario.
- `NewPasswordController::store()` permanece intacto, incluida validación, password broker, actualización de contraseña, evento y redirección.
- Después del cambio del controlador se ejecutó `docker compose exec app php artisan octane:reload` antes del QA manual.
- QA manual con un usuario temporal y un token generado por el password broker real de Laravel: la página mostró el email y título correctos; un token inválido conservó la pantalla y mostró `Este token de restablecimiento de contraseña es inválido.`; un token válido actualizó la contraseña y redirigió a `/login` con el estado `¡Tu contraseña ha sido restablecida!`. No hubo warnings ni errores de consola. El usuario y token temporales se eliminaron al terminar.
- El caso expirado está cubierto con el tiempo de prueba adelantado 61 minutos sobre la expiración configurada de 60 minutos y devuelve error sin cambiar la contraseña.
- Suite específica: 7 pruebas pasan, 0 fallan, 43 assertions.
- Suite completa: 92 pruebas pasan, 0 fallan, 342 assertions, 12.48 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 627 módulos transformados y chunk `ResetPassword` generado.
- `resources/views/auth/reset-password.blade.php` permanece en el repositorio hasta Fase 8.

### Estado de verificación de email

- La pantalla sí existe en `resources/views/auth/verify-email.blade.php` y la ruta activa es `GET /verify-email`, nombre `verification.notice`.
- Registro redirige a esa ruta después de crear y autenticar al usuario no verificado. La pantalla permite verificar un código de seis dígitos, reenviarlo y cerrar sesión.
- Sigue pendiente de migración: es la pantalla 6 del Nivel 1, después de `ConfirmPassword`, según el orden aprobado. No se migró dentro de este bloque.

### Auditoría previa de verificación de email

- El flujo principal real no depende de que el usuario abra un enlace: `User::sendEmailVerificationNotification()` genera un código aleatorio de seis dígitos, guarda únicamente su hash en `email_verification_codes`, fija una expiración de 10 minutos y envía `VerifyEmailCodeNotification`.
- La pantalla actual no usa polling ni revalidación automática. El usuario introduce el código en `POST /verify-email`; al acertar se actualiza `email_verified_at` y se redirige a onboarding. `POST /email/verification-notification` permite reenviar y sustituye el código anterior. Si el usuario ya está verificado al pedir un reenvío, el backend redirige al dashboard.
- La ruta firmada estándar `GET /verify-email/{id}/{hash}` sigue activa, pero la notificación personalizada actual entrega un código, no ese enlace. No se añadirá polling como parte de la migración de presentación salvo que se apruebe como un cambio funcional separado.
- El mailer `log` existe en `config/mail.php` y `MAIL_MAILER=log` es el valor de `.env.example`, pero el contenedor local en ejecución tiene como mailer efectivo `resend`. Los tests fuerzan el mailer `array`. Por tanto, actualmente no existe un archivo de log local autoritativo del que leer el código o un enlace; el QA sin correo real debe usar `Notification::fake()` y un `EmailVerificationCode` controlado, como ya hace la suite Feature. Cambiar temporalmente el mailer requeriría una decisión explícita de entorno y no es necesario para probar la pantalla.

Rutas protegidas por `verified`, agrupadas por dependencia funcional:

- Aplicación principal: `dashboard`, `dashboard/invite`, `dashboard/weight`.
- Onboarding: GET/POST de `onboarding`, `onboarding/patient`, `onboarding/caregiver` y `onboarding/doctor`.
- Perfil: GET/PATCH/DELETE de `profile`, `profile/unlink/{linkedUser}` y `profile/verify-email/{token}`.
- Paciente y seguimiento: `search`; GET/POST de actividad, nutrición, síntomas y vitales; `tracking/summary`.
- Notificaciones: marcar una/todas como leídas y eliminar una/todas.
- Cuidador: dashboard, vinculación, detalle/desvinculación de paciente y captura de vitales.
- Médico: dashboard, vinculación, detalle/desvinculación de paciente y actualización de objetivos.
- Administración: dashboard, métricas API, aprobación/rechazo de médicos y CRUD de roles/usuarios.

Estas rutas no se modificaron durante la auditoría. `GET/POST /confirm-password` usan únicamente `auth`, mientras que `GET /verify-email`, envío del código, reenvío y logout también permanecen accesibles bajo `auth` sin `verified`, lo que evita bloquear el propio proceso de verificación.

## 16. Nivel 1: Verificar email

Completada el 28 de julio de 2026 sin añadir polling ni modificar la lógica de verificación.

- `GET /verify-email` conserva la URL y ahora renderiza `Auth/VerifyEmail` con email y URLs de verificar, reenviar y cerrar sesión.
- `VerifyEmail.jsx` reutiliza `GuestLayout`, `FormInput`, `SubmitButton` y `AuthSessionStatus`; usa `useForm()` para código, reenvío y logout.
- Se preservó exactamente la semántica de intentos: los intentos fallidos 1 a 5 muestran `El código ingresado no es válido.` e incrementan el contador; el sexto intento detecta el contador en 5, elimina el código y muestra `Se alcanzó el límite de intentos. Solicita un código nuevo.`. El usuario debe reenviar para continuar.
- Los redirects desde una petición Inertia hacia onboarding, dashboard o la página pública usan `Inertia::location()` para destinos Blade. No se cambiaron decisiones de autorización ni negocio.
- Se ejecutó `octane:reload` después de los cambios de controladores y antes del QA.
- QA real: `.env` local se cambió temporalmente de `resend` a `log`, sin modificar archivos versionados; tras el QA se restauraron todas sus entradas a `resend`, se limpió configuración y se recargó Octane. El mailer efectivo final quedó confirmado como `resend`.
- El registro real creó un usuario no verificado y abrió `/verify-email` con título `Verificar correo - DiabTrack`. El primer código controlado mediante la misma generación y hasheo del sistema quedó inválido después de pulsar Reenviar. Los códigos nuevos se leyeron del correo escrito en `storage/logs/laravel.log`.
- Código incorrecto: error visible y formulario estable. Código expirado: tras ajustar `expires_at` del usuario temporal, mostró `El código venció. Solicita uno nuevo para continuar.`. Reenvío: mostró estado de éxito, generó otro código y el anterior dejó de funcionar. Código correcto: verificó al usuario y realizó full-page reload a `/onboarding`, todavía Blade.
- F5 conservó `/verify-email`, título y formulario. La consola no registró warnings ni errores. El usuario y códigos temporales se eliminaron al terminar.
- Suite específica: 10 pruebas pasan, 0 fallan, 62 assertions.
- Suite completa: 95 pruebas pasan, 0 fallan, 381 assertions, 13.31 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 628 módulos transformados y chunk `VerifyEmail` generado.
- `resources/views/auth/verify-email.blade.php` permanece hasta Fase 8.

## 17. Nivel 1: Confirmar contraseña

Completada el 28 de julio de 2026 después de Verificar email, sin depender del middleware `verified`.

- `GET /confirm-password` conserva la URL y ahora renderiza `Auth/ConfirmPassword` mediante Inertia.
- `ConfirmPassword.jsx` reutiliza `GuestLayout`, `FormInput` y `SubmitButton`; usa `useForm()` únicamente para la contraseña actual.
- La validación con `Auth::guard('web')->validate()`, el mensaje backend y la escritura de `auth.password_confirmed_at` permanecen intactos.
- El redirect Inertia posterior usa `Inertia::location()` porque el destino `dashboard` todavía pertenece al stack Blade.
- Se ejecutó `docker compose exec app php artisan octane:reload` antes del QA manual.
- QA manual con usuario verificado temporal: `/confirm-password`, título `Confirmar contraseña - DiabTrack` y F5 conservaron el formulario; una contraseña incorrecta mostró `La contraseña es incorrecta.`; la contraseña correcta confirmó la sesión y realizó full-page reload hacia `dashboard`. El usuario sin perfil continuó mediante la lógica existente a `/onboarding` Blade. No hubo warnings ni errores de consola.
- Suite específica: 4 pruebas pasan, 0 fallan, 18 assertions.
- Suite completa: 96 pruebas pasan, 0 fallan, 395 assertions, 11.35 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 629 módulos transformados y chunk `ConfirmPassword` generado.
- `resources/views/auth/confirm-password.blade.php` permanece hasta Fase 8.
- Estado local del mailer después del QA de autenticación: todas las entradas `MAIL_MAILER` de `.env` están en `resend`; `.env` está ignorado, no está trackeado y no aparece en el historial Git.

## 18. Nivel 1: Welcome

Completada el 28 de julio de 2026 como pantalla pública posterior al bloque de autenticación.

- `GET /` conserva la URL y ahora renderiza `Welcome` mediante Inertia, con URLs generadas en backend, metadatos absolutos y año actual.
- `Welcome.jsx` replica hero, características, llamada a la acción y footer con Tailwind 3.4. Conserva los metadatos SEO, Open Graph, Twitter y favicon del Blade.
- El collapse de Bootstrap JS fue reemplazado por `useState`; la página React no carga Bootstrap JS ni Font Awesome CDN. Los iconos necesarios son SVG locales del componente.
- Login y Registro usan `<Link>` de Inertia. Dashboard conserva navegación de página completa porque ese destino todavía es Blade.
- No se cambió lógica de negocio, autenticación, Redis ni rutas. `resources/views/welcome.blade.php` permanece hasta Fase 8.
- Se ejecutó `docker compose exec app php artisan octane:reload` antes del QA porque cambió la closure persistente de la ruta pública.
- QA manual de invitado: `/`, título `Monitorea tu salud, vive mejor - DiabTrack`, descripción SEO, contenido principal y F5 correctos; el enlace a Login actualizó URL/título y el historial volvió correctamente a Welcome.
- QA responsive a 390×844: el menú inició cerrado, el botón React cambió a estado expandido y mostró todos los enlaces sin Bootstrap. El viewport se restauró al terminar. No hubo warnings ni errores de consola.
- Suite específica: 2 pruebas pasan, 0 fallan, 19 assertions.
- Suite completa: 96 pruebas pasan, 0 fallan, 412 assertions, 11.59 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 630 módulos transformados y chunk `Welcome` generado.
- Siguiente pantalla del Nivel 1: selección de rol de onboarding (`Pages/Onboarding/RoleSelection.jsx`).

## 19. Nivel 1: Selección de rol de onboarding

Completada el 28 de julio de 2026.

- `GET /onboarding` conserva la URL y ahora renderiza `Onboarding/RoleSelection` mediante Inertia con las tres URLs generadas en backend.
- La pantalla reutiliza `GuestLayout` y presenta las opciones paciente, cuidador y médico sin comparar nombres de rol para autorización. Las decisiones reales permanecen en rutas, middleware y controladores.
- Los redirects de administrador o usuario con onboarding completo usan `Inertia::location()` cuando el destino todavía es Blade.
- Los enlaces a los tres formularios usan navegación completa temporalmente porque esos destinos todavía eran Blade al validar este commit; se convertirán a `<Link>` conforme cada destino sea migrado.
- Se ejecutó `octane:reload` antes del QA manual.
- QA manual con usuario verificado sin onboarding: Login redirigió a `/onboarding`, título `Seleccionar rol - DiabTrack`, tres opciones y URLs correctas; F5 conservó la pantalla y el enlace paciente abrió `/onboarding/patient` Blade. No hubo warnings ni errores de consola.
- Suite específica: 6 pruebas pasan, 0 fallan, 36 assertions.
- Suite completa: 96 pruebas pasan, 0 fallan, 426 assertions, 10.68 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 631 módulos transformados y chunk `RoleSelection` generado.
- `resources/views/onboarding/role-selection.blade.php` permanece hasta Fase 8.
- Siguiente pantalla: datos de paciente (`Pages/Onboarding/PatientData.jsx`).

## 20. Nivel 1: Datos de paciente

Completada el 28 de julio de 2026.

- `GET /onboarding/patient` conserva la URL y ahora renderiza `Onboarding/PatientData` mediante Inertia. Las opciones de condición glucémica, meses y límites de año se exponen desde el backend.
- `PatientData.jsx` reutiliza `GuestLayout`, `FormInput`, `FormSelect`, `FormError` y `SubmitButton`; usa `useForm()` para fecha de nacimiento, condición glucémica, peso, altura y género.
- `POST /onboarding/patient` conserva validaciones, creación del perfil, asignación del rol y transacción originales. Para peticiones Inertia, el destino Blade existente se abre mediante `Inertia::location()`.
- El enlace Paciente de `RoleSelection` se convirtió a `<Link>` de Inertia; las opciones de cuidador y médico permanecen como navegación completa hasta migrar sus destinos.
- Se ejecutó `docker compose exec app php artisan octane:reload` antes del QA manual.
- QA manual con usuario verificado temporal: navegación Inertia desde `/onboarding` a `/onboarding/patient`, URL y título `Datos personales - DiabTrack` correctos; F5 conservó formulario y ruta; el envío vacío mostró los tres errores requeridos; un envío válido creó el perfil y realizó full-page reload a `/dashboard` Blade. No hubo warnings ni errores de consola.
- Suite específica: 7 pruebas pasan, 0 fallan, 51 assertions.
- Suite completa: 97 pruebas pasan, 0 fallan, 441 assertions, 12.12 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 633 módulos transformados y chunk `PatientData` generado.
- `git diff --check` finalizó sin errores.
- `resources/views/onboarding/personal-data.blade.php` permanece hasta Fase 8.
- Siguiente pantalla: datos de cuidador (`Pages/Onboarding/CaregiverData.jsx`).

## 21. Nivel 1: Datos de cuidador

Completada el 28 de julio de 2026.

- `GET /onboarding/caregiver` conserva la URL y ahora renderiza `Onboarding/CaregiverData` mediante Inertia; las relaciones disponibles se entregan como props desde el backend.
- `CaregiverData.jsx` reutiliza `GuestLayout`, `FormSelect`, `FormError` y `SubmitButton`; el selector de género reemplaza el script inline por estado de `useForm()`.
- `POST /onboarding/caregiver` conserva validación, perfil y asignación de rol originales. El destino Blade nominal `caregiver.dashboard`, cuya URL real es `/caregiver`, usa `Inertia::location()`.
- El enlace Cuidador de `RoleSelection` se convirtió a `<Link>` de Inertia; Médico permanece como navegación completa hasta migrar su destino.
- Se recargó Octane antes del QA manual.
- QA manual con usuario verificado temporal: navegación Inertia desde `/onboarding`, URL y título `Perfil de cuidador - DiabTrack`, F5 estable, errores requeridos visibles, selección de género y parentesco funcional, y envío válido con full-page reload a `/caregiver` Blade. No hubo warnings ni errores de consola.
- Suite específica: 9 pruebas pasan, 0 fallan, 68 assertions.
- Suite completa: 99 pruebas pasan, 0 fallan, 458 assertions, 15.23 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 634 módulos transformados y chunk `CaregiverData` generado.
- `git diff --check` finalizó sin errores.
- `resources/views/onboarding/caregiver-data.blade.php` permanece hasta Fase 8.
- Siguiente pantalla: datos de médico (`Pages/Onboarding/DoctorData.jsx`).

## 22. Nivel 1: Datos de médico

Completada el 28 de julio de 2026.

- `GET /onboarding/doctor` conserva la URL y ahora renderiza `Onboarding/DoctorData` mediante Inertia; las especialidades se entregan desde el backend.
- `DoctorData.jsx` reutiliza componentes de Nivel 0 y reemplaza el script inline de género por `useForm()`; incluye género, cédula profesional y especialidad.
- `POST /onboarding/doctor` mantiene validaciones, asignación del rol y creación del perfil con estado `pending`. El destino Blade nominal `doctor.dashboard`, cuya URL real es `/doctor`, usa `Inertia::location()`.
- Los tres destinos de `RoleSelection` usan ahora `<Link>` de Inertia.
- Se recargó Octane antes del QA manual.
- QA manual con usuario verificado temporal: navegación desde `/onboarding`, URL y título `Perfil profesional - DiabTrack`, F5 estable, tres errores requeridos visibles, controles funcionales y envío válido. El dashboard Blade `/doctor` mostró la cédula, especialidad y estado `En revisión`, sin warnings ni errores de consola.
- Suite específica: 11 pruebas pasan, 0 fallan, 85 assertions.
- Suite completa: 101 pruebas pasan, 0 fallan, 475 assertions, 13.58 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 635 módulos transformados y chunk `DoctorData` generado.
- `git diff --check` finalizó sin errores.
- `resources/views/onboarding/doctor-data.blade.php` permanece hasta Fase 8.

## 23. Nivel 0: Layout autenticado y servicios compartidos

Completado el 28 de julio de 2026 antes de iniciar las páginas autenticadas.

- Se creó `AuthenticatedLayout` con cabecera, identidad, logout, mensajes flash y pie de página.
- Búsqueda global mantiene `fetch()` nativo por ser consulta de datos en segundo plano, con espera de 250 ms y cancelación de solicitudes anteriores.
- Notificaciones usan estado React y `router` de Inertia para leer y eliminar; Bootstrap JS y SweetAlert2 no se incorporan al layout React.
- `HandleInertiaRequests` comparte navegación, las últimas ocho notificaciones y el permiso calculado `puedeBuscar`. React no compara nombres de roles.
- Redis y Tailwind 3.4 permanecen sin cambios.
- Suite completa: 101 pruebas pasan, 0 fallan, 475 assertions, 10.41 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 635 módulos transformados; `git diff --check` sin errores.

## 24. Nivel 1: Vincular paciente como cuidador

Completada el 28 de julio de 2026.

- `GET /caregiver/link` conserva URL y autorización, y renderiza `Caregiver/LinkPatient` mediante Inertia con parentescos proporcionados por backend.
- El formulario usa `AuthenticatedLayout`, `useForm()`, mayúsculas para el código, errores backend y estado `processing`.
- `POST /caregiver/link` conserva búsqueda del vínculo pendiente, expiración, actualización, parentesco y notificación. El dashboard Blade se abre con `Inertia::location()`.
- QA real: título `Vincular paciente - DiabTrack`, F5 estable; el código ya utilizado `USED12` mostró `El código es inválido o ha expirado.`; el código válido se normalizó a mayúsculas, vinculó al paciente y redirigió a `/caregiver` con flash. MySQL confirmó vínculo activo y notificación. Consola limpia.
- Suite específica: 3 pruebas pasan, 20 assertions.
- Suite completa: 104 pruebas pasan, 495 assertions, 12.50 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 640 módulos; `git diff --check` sin errores.
- `resources/views/caregiver/link-patient.blade.php` permanece hasta Fase 8.

## 25. Nivel 1: Vincular paciente como médico

Completada el 28 de julio de 2026.

- `GET /doctor/link` conserva URL y middleware `doctor.approved`, y renderiza `Doctor/LinkPatient` mediante Inertia.
- El formulario usa `AuthenticatedLayout`, `useForm()`, normalización a mayúsculas, errores backend y estado de carga.
- `POST /doctor/link` mantiene consulta del vínculo pendiente, expiración, actualización y notificación; el dashboard Blade usa `Inertia::location()`.
- QA real con médico aprobado: título `Vincular paciente - DiabTrack`, F5 estable, código usado rechazado, código válido vinculado y full-page reload a `/doctor` con flash; MySQL confirmó vínculo y notificación. Consola limpia.
- La restricción para médicos pendientes permanece cubierta y redirige al dashboard.
- Suite específica: 4 pruebas pasan, 20 assertions.
- Suite completa: 108 pruebas pasan, 515 assertions, 12.38 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 641 módulos; `git diff --check` sin errores.
- `resources/views/doctor/link-patient.blade.php` permanece hasta Fase 8.

## 26. Nivel 2: Dashboard administrativo

Completada el 28 de julio de 2026 como primera pantalla del Nivel 2.

- `GET /admin` conserva URL y middleware `auth`, `verified` y `admin`, y ahora renderiza `Admin/Dashboard` mediante Inertia.
- Se creó `AdminLayout` con identidad administrativa, sidebar, perfil, logout y mensajes flash. Los destinos aún Blade usan navegación completa.
- La página conserva accesos a resumen, usuarios, roles, aprobación de médicos, métricas de API y el placeholder de auditoría.
- La autorización real permanece en backend: un usuario no administrador conserva la redirección existente a `dashboard`.
- QA real: login administrativo redirigió a `/admin`; título `Panel administrativo - DiabTrack`, F5, identidad, sidebar y tarjetas correctos. El acceso a Usuarios abrió `/admin/users` Blade y el regreso mantuvo el dashboard React. Consola limpia.
- Suite específica: 2 pruebas pasan, 22 assertions.
- Suite completa: 110 pruebas pasan, 537 assertions, 12.87 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 643 módulos; `git diff --check` sin errores.
- `resources/views/admin/dashboard.blade.php` permanece hasta Fase 8.

## 27. Nivel 2: Listado de roles

Completada el 28 de julio de 2026 reutilizando el `AdminLayout` existente.

- `RoleController@index` conserva paginación y `withCount`, y renderiza `Admin/Roles/Index` mediante Inertia.
- Los modelos se serializan con `RoleResource`; se exponen únicamente campos visibles, conteo y URLs necesarias.
- Se añadieron componentes reutilizables `Table`, `Pagination` y `Modal`. `Modal` acepta título, contenido y acciones; cierra con Escape, botón o clic en el backdrop, bloquea el scroll de fondo y lo restaura al cerrar. El modal React reemplaza Bootstrap JS y conserva el bloqueo cuando el rol tiene usuarios.
- `destroy()` y sus reglas de negocio permanecen intactos.
- `adminNavigation` pasó a ser dato compartido para todos los administradores; Dashboard, Roles y futuras pantallas reutilizan el mismo `AdminLayout`.
- QA real: `/admin/roles`, título `Control de roles - DiabTrack`, F5 y tabla correctos; un rol con usuario mostró acción bloqueada; un rol vacío fue eliminado, desapareció y mostró flash. Consola limpia.
- Suite específica: 3 pruebas pasan, 22 assertions.
- Suite completa: 113 pruebas pasan, 559 assertions, 12.59 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 647 módulos; `git diff --check` sin errores.
- `resources/views/admin/roles/index.blade.php` permanece hasta Fase 8.

## 28. Nivel 2: Crear rol

Completada el 28 de julio de 2026 reutilizando `AdminLayout`.

- `RoleController@create` renderiza `Admin/Roles/Create` mediante Inertia; `store()` y `AdminRoleRequest` permanecen intactos.
- El formulario usa `useForm()`, `FormInput`, el nuevo `FormTextarea`, errores backend y estado `processing`.
- QA real: `/admin/roles/create`, título `Crear rol - DiabTrack` y F5 correctos; un nombre duplicado mostró el mensaje backend; nombre y descripción válidos se persistieron en MySQL y redirigieron al listado React con flash. Consola limpia.
- Suite específica: 3 pruebas pasan, 21 assertions.
- Suite completa: 116 pruebas pasan, 580 assertions, 13.13 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 649 módulos; `git diff --check` sin errores.
- `resources/views/admin/roles/create.blade.php` permanece hasta Fase 8.

## 29. Nivel 2: Editar rol

Completada el 28 de julio de 2026 reutilizando `AdminLayout`.

- `GET /admin/roles/{role}/edit` conserva URL y autorización, y `RoleController@edit` renderiza `Admin/Roles/Edit` mediante Inertia con `RoleResource`.
- El formulario usa `useForm()` precargado, `FormInput`, `FormTextarea`, `SubmitButton` y el `Modal` genérico para informar la restricción de eliminación cuando corresponde.
- `AdminRoleRequest` permanece intacto: su regla `unique()->ignore($roleId)` permite guardar el rol sin cambiar su propio nombre y rechaza el nombre de cualquier otro registro.
- La lógica existente permite editar un rol con usuarios asignados. La única restricción relacionada permanece en `destroy()`, que impide eliminarlo mientras tenga usuarios.
- Se recargó Octane antes del QA manual.
- QA real: `/admin/roles/9/edit`, título `Editar rol - DiabTrack`, F5 estable y datos precargados; el nombre de otro rol mostró `Este nombre ya ha sido registrado.`; el propio nombre se guardó sin colisión, la descripción se actualizó y el listado React mostró el flash de éxito. El modal informó el bloqueo de eliminación y la consola permaneció limpia.
- Suite específica: 4 pruebas pasan, 31 assertions.
- Suite completa: 120 pruebas pasan, 0 fallan, 611 assertions, 13.10 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 650 módulos; `git diff --check` sin errores.
- `resources/views/admin/roles/edit.blade.php` permanece hasta Fase 8.

## 30. Nivel 2: Listado de usuarios

Completada el 28 de julio de 2026 reutilizando `AdminLayout`.

- `GET /admin/users` conserva URL, middleware, búsqueda por nombre/correo y paginación, y ahora renderiza `Admin/Users/Index` mediante Inertia.
- Se creó `UserResource` para exponer identidad, tipo de cuenta, roles y URLs de acciones sin serializar el modelo Eloquent completo.
- La página reutiliza `Table`, `Pagination` y el `Modal` genérico. La búsqueda usa `router.get`; crear, editar, limpiar búsqueda y paginación conservan sus destinos existentes.
- `destroy()` permanece intacto y sigue impidiendo que el administrador elimine su propia cuenta. React oculta esa acción para la sesión actual, pero la protección real continúa en backend.
- Se recargó Octane y se copió el build al volumen aislado `public/build` del contenedor antes del QA.
- QA real: `/admin/users`, título `Control de usuarios - DiabTrack`, F5 estable, usuario actual identificado y sin botón de eliminación; la búsqueda actualizó la URL a `?search=Role%20Assigned%20QA` y persistió tras recargar; el modal bloqueó el scroll y una eliminación válida redirigió al listado con flash. Consola limpia.
- Suite específica: 3 pruebas pasan, 30 assertions.
- Suite completa: 123 pruebas pasan, 0 fallan, 641 assertions, 14.40 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 651 módulos; `git diff --check` sin errores.
- `resources/views/admin/users/index.blade.php` permanece hasta Fase 8.

## 31. Nivel 2: Crear usuario

Completada el 28 de julio de 2026 reutilizando `AdminLayout` y componentes de formulario.

- `GET /admin/users/create` conserva URL y autorización, y ahora renderiza `Admin/Users/Create` mediante Inertia.
- Los roles se consultan con `Role::all()` y se entregan como props; React no contiene nombres ni opciones de rol hardcodeadas.
- Se creó `Components/Admin/UserForm`, compartido con la futura edición, usando `FormInput`, `Checkbox`, `FormError` y `SubmitButton`.
- El comportamiento backend permanece intacto: el administrador define manualmente la contraseña inicial y el cast del modelo la cifra. No se genera contraseña temporal ni se envía correo de bienvenida, credenciales o verificación, por lo que Resend no participa en este flujo.
- `AdminUserRequest` conserva normalización y unicidad case-insensitive del correo, contraseña confirmada y validación de cada identificador de rol contra la base de datos.
- Se recargó Octane antes del QA manual.
- QA real: `/admin/users/create`, título `Crear usuario - DiabTrack`, F5 estable y rol temporal de base de datos visible; un correo duplicado en mayúsculas mostró `Este correo electrónico ya ha sido registrado.`; un usuario válido se creó con contraseña definida por el administrador, acceso administrativo y rol dinámico, redirigió a `/admin/users` con flash y apareció en el listado. Consola limpia.
- Suite específica: 3 pruebas pasan, 24 assertions.
- Suite completa: 126 pruebas pasan, 0 fallan, 665 assertions, 14.17 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 653 módulos; `git diff --check` sin errores.
- `resources/views/admin/users/create.blade.php` permanece hasta Fase 8.

## 32. Nivel 2: Editar usuario

Completada el 28 de julio de 2026 y con ella se cierra el CRUD de usuarios en React.

- `GET /admin/users/{user}/edit` conserva URL y autorización, y ahora renderiza `Admin/Users/Edit` mediante Inertia con `UserResource` y roles consultados dinámicamente desde la base de datos.
- La página reutiliza `AdminLayout`, `Components/Admin/UserForm` y el `Modal` genérico. El formulario se precarga con identidad, acceso administrativo y roles actuales; la contraseña queda vacía y solo cambia cuando el administrador proporciona una nueva.
- `AdminUserRequest` permanece intacto: permite conservar el propio correo y rechaza el correo de otro usuario, sin distinguir mayúsculas por la normalización existente.
- Se preservó la semántica de los checkboxes Blade: React omite `is_admin` del payload cuando está desmarcado para que el `request()->has('is_admin')` heredado siga funcionando igual. El administrador actual no puede desmarcar su propio acceso y el backend conserva la protección real.
- QA real: `/admin/users/30/edit`, título `Editar usuario - DiabTrack`, F5 estable y datos/rol dinámico precargados; correo duplicado mostró el error backend; nombre y privilegio administrativo se actualizaron con flash, el rol se conservó y MySQL confirmó que la contraseña permaneció intacta. El modal de eliminación bloqueó el scroll; para la sesión actual no aparecen acciones de eliminar ni de revocar administración. Consola limpia en una pestaña nueva tras la corrección final.
- Suite específica: 4 pruebas pasan, 28 assertions.
- Suite completa: 130 pruebas pasan, 0 fallan, 693 assertions, 13.79 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 654 módulos; `git diff --check` sin errores.
- `resources/views/admin/users/edit.blade.php` permanece hasta Fase 8.

## 33. Nivel 2: Aprobación de médicos

Completada el 29 de julio de 2026 reutilizando `AdminLayout`, `Table`, `Pagination`, `Modal` y componentes de formulario.

- `GET /admin/doctors` conserva URL, autorización administrativa, filtros y paginación, y ahora renderiza `Admin/Doctors/Index` mediante Inertia con `DoctorProfileResource`.
- Los únicos estados del modelo son `pending`, `approved` y `rejected`. Las transiciones siguen usando exclusivamente los endpoints administrativos existentes: aprobar registra administrador/fecha y notifica solo si antes no estaba aprobado; rechazar exige observaciones, limpia administrador/fecha y no envía notificación.
- La interfaz permite aprobar perfiles pendientes o rechazados, rechazar pendientes y revocar la aprobación de perfiles aprobados mediante confirmación. No se modificó la lógica de los métodos `approve()` ni `reject()`.
- La aprobación se verificó con `MAIL_MAILER=resend` y el destinatario controlado `delivered@resend.dev`; el envío síncrono fue aceptado sin excepción y el flash confirmó la notificación. No se enviaron mensajes a direcciones personales externas. El rechazo no disparó correo, conforme al flujo existente.
- El middleware `doctor.approved` consulta el estado en cada petición. QA con la misma cookie médica: `/doctor/link` respondió correctamente mientras estaba aprobado; tras el rechazo, la siguiente petición redirigió inmediatamente a `/doctor` con warning y estado `Requiere corrección`, sin cerrar sesión y sin periodo de gracia.
- Limitación preexistente documentada, fuera del alcance de esta migración: `doctor.approved` solo envuelve GET/POST de `/doctor/link`. Las rutas directas `doctor.patient.show`, `doctor.patient.targets.update` y `doctor.patient.unlink` quedan fuera de ese middleware; el dashboard oculta pacientes al rechazarse, pero la cobertura backend no es global.
- QA administrativo: `/admin/doctors`, título `Aprobación de médicos - DiabTrack`, filtros con query string y F5 estable; aprobación, validación requerida del rechazo, revocación, flashes, cierre del modal, bloqueo de scroll y consola limpia confirmados.
- Suite específica: 6 pruebas pasan, 51 assertions.
- Suite completa: 131 pruebas pasan, 0 fallan, 722 assertions, 16.75 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 655 módulos; `git diff --check` sin errores.
- `resources/views/admin/doctors/index.blade.php` permanece hasta Fase 8.

## 34. Nivel 2: Captura de signos vitales

Completada el 29 de julio de 2026 reutilizando `AuthenticatedLayout` y los componentes de formulario del Nivel 0.

- `GET /tracking/vitals` conserva URL y autorización, y ahora renderiza `Tracking/Vitals/Create` mediante Inertia. La navegación compartida del módulo se extrajo a `TrackingNav`.
- La pantalla conserva exactamente los campos visibles del Blade: glucosa obligatoria; presión sistólica, presión diastólica, frecuencia cardiaca, HbA1c, estrés y notas opcionales; y momento de medición obligatorio. El backend también acepta peso, pero la pantalla original no lo capturaba y no se añadió a esta migración de presentación.
- `VitalSignRequest` permanece intacto: glucosa 20–600 mg/dL; sistólica 40–250 mmHg; diastólica 30–150 mmHg; frecuencia cardiaca 30–220 bpm; peso 20–350 kg; HbA1c 3–15%; momento entre Ayunas, Antes de Comer, Después de Comer y Al Dormir; estrés con máximo 255 caracteres; notas con máximo 1000.
- Se añadieron los componentes reutilizables `RangeField` y `ChoiceCards`, aptos para otras capturas de tracking, además de reutilizar `FormInput`, `FormTextarea` y `SubmitButton`.
- Guardar un signo vital únicamente crea `VitalSign`; el evento `saved` invalida la caché del dashboard. No realiza llamadas a Gemini, Anthropic ni otra API de IA. Los tips diarios se generan por separado mediante el comando programado `app:generate-daily-tips` a las 02:00, por lo que el QA no consumió API ni generó costo.
- No queda JavaScript legacy activo ni dependencia AJAX temporal en esta pantalla. El único consumidor antiguo era el propio formulario Blade interceptado genéricamente por `layouts.app`; ningún dashboard u otra pantalla consume este endpoint por AJAX. Se retiró la respuesta JSON heredada para no mantener convivencia innecesaria. Una petición Inertia válida usa `Inertia::location()` para realizar la recarga completa hacia el dashboard Blade; existe una prueba de regresión con los encabezados reales, incluido `X-Requested-With`.
- QA real: `/tracking/vitals`, título `Registro de signos vitales - DiabTrack` y F5 estables; HbA1c 16 mostró el error backend; una medición válida persistió glucosa 120, presión 118/76, pulso 75, HbA1c 5.90, momento Después de Comer y estrés Medio; redirigió a `/dashboard`, mostró el flash de éxito y permaneció estable tras F5. La consola quedó limpia y MySQL confirmó cero registros de `DailyTip` para el usuario de QA.
- Suite específica final: 6 pruebas pasan, 43 assertions.
- Suite completa tras retirar la compatibilidad AJAX sin consumidores: 132 pruebas pasan, 0 fallan, 751 assertions.
- Build Vite 8.1.5 correcto con 659 módulos; `git diff --check` sin errores.
- `resources/views/tracking/vitals.blade.php` permanece hasta Fase 8.

## 35. Nivel 2: Captura de actividad física

Completada el 29 de julio de 2026 reutilizando `AuthenticatedLayout`, `TrackingNav`, `RangeField`, `ChoiceCards` y los componentes de formulario del Nivel 0.

- `GET /tracking/activity` conserva URL y autorización, y ahora renderiza `Tracking/Activity/Create` mediante Inertia. Las opciones que antes vivían dentro del Blade se entregan como props del controlador.
- La pantalla captura tipo de actividad obligatorio, duración obligatoria, intensidad obligatoria, hora de inicio y fin opcionales y nivel de energía opcional. Ofrece caminar, correr, nadar, bicicleta, yoga, gimnasio, baile, estiramiento y otro.
- `ActivityLogRequest` permanece intacto: tipo de actividad como texto de máximo 100 caracteres; duración entera entre 1 y 480 minutos; intensidad entre `baja`, `media` y `alta`; horarios con formato `H:i`; energía entre `muy_baja`, `baja`, `normal`, `alta` y `muy_alta`. El slider conserva el máximo visual de 180 minutos del Blade original, mientras el backend continúa aceptando hasta 480.
- No se conservó la respuesta AJAX genérica del Blade ni existe JavaScript legacy activo en la página React. Intensidad y energía, que antes dependían de funciones inline y manipulación del DOM, ahora usan estado de React mediante `useForm()` y `ChoiceCards`.
- Guardar únicamente crea `ActivityLog` e invalida la caché del dashboard. No llama a una API de IA. El job diario puede usar posteriormente la actividad como contexto al generar tips, pero el QA de esta pantalla no consumió API y MySQL confirmó cero `DailyTip` nuevos.
- QA real: `/tracking/activity`, título `Registro de movimiento - DiabTrack` y formulario correctos; omitir el tipo mostró `Selecciona un tipo de actividad.`; una actividad de caminar con intensidad y energía altas se persistió, redirigió al dashboard Blade y mostró el flash de éxito. URL y título de destino permanecieron estables después de F5 y no hubo errores de consola.
- Suite específica: 6 pruebas pasan, 42 assertions. Incluye todos los rangos, ausencia de llamadas HTTP de IA, invalidación de caché y redirección Inertia hacia el dashboard Blade.
- Suite completa: 133 pruebas pasan, 0 fallan, 779 assertions, 13.95 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 660 módulos; `git diff --check` sin errores.
- `resources/views/tracking/activity/create.blade.php` permanece hasta Fase 8.

## 36. Nivel 2: Captura de síntomas

Completada el 29 de julio de 2026 reutilizando `AuthenticatedLayout`, `TrackingNav`, `FormError` y `SubmitButton`.

- `GET /tracking/symptoms` conserva URL y autorización, y ahora renderiza `Tracking/Symptoms/Create` mediante Inertia. Los síntomas continúan consultándose dinámicamente desde MySQL y agrupándose por categoría; no existe un catálogo hardcodeado en React.
- Se creó `SymptomResource` para exponer únicamente identificador, nombre y categoría. El controlador aporta las etiquetas de las categorías físicas, nocturnas, neurológicas y atípicas, con fallback para categorías futuras.
- `SymptomLogRequest` permanece intacto: `symptoms` debe ser un arreglo con al menos un elemento y cada valor debe ser un entero correspondiente a un registro existente en `symptoms`.
- La selección múltiple usa `useForm()` y estado de React. No existe JavaScript legacy activo ni consumidor externo de la respuesta AJAX genérica del Blade, por lo que esa rama fue retirada.
- `store()` conserva su lógica: adjunta cada síntoma al usuario con `logged_at` e invalida la caché de métricas. No llama a una API de IA; el QA confirmó cero `DailyTip` nuevos.
- QA real: `/tracking/symptoms`, título `Registro de síntomas - DiabTrack`, 16 síntomas en cuatro categorías y F5 estable; enviar vacío mostró `Selecciona al menos un síntoma.`; seleccionar Fatiga y Mareos persistió ambas relaciones, actualizó el contador del dashboard a dos y mostró el flash de éxito. El destino permaneció estable tras F5 y no hubo errores de consola.
- Suite específica: 6 pruebas pasan, 37 assertions. Incluye catálogo serializado, selección múltiple, arreglo vacío, ID inexistente, caché y redirección Inertia hacia el dashboard Blade.
- Suite completa: 134 pruebas pasan, 0 fallan, 800 assertions, 14.62 s reportados por PHPUnit.
- Build Vite 8.1.5 correcto con 661 módulos; `git diff --check` sin errores.
- `resources/views/tracking/symptom/create.blade.php` permanece hasta Fase 8.

## 37. Nivel 2: Captura de nutrición

Completada el 29 de julio de 2026 reutilizando `AuthenticatedLayout`, `TrackingNav`, `RangeField`, `ChoiceCards` y componentes de formulario.

- `GET /tracking/nutrition/create` conserva URL y autorización, y ahora renderiza `Tracking/Nutrition/Create` mediante Inertia. Tipos de comida y categorías se entregan como props del controlador.
- Se preservaron campos y límites: comida obligatoria entre cinco opciones; carbohidratos enteros 0–500 en backend y slider visual 0–300, idéntico al Blade; hora opcional `H:i`; categorías opcionales; medicamento máximo 100 y dosis máximo 50 caracteres.
- La selección de comida reemplaza la función inline por estado React. No hay JavaScript legacy activo ni consumidor externo del JSON genérico, por lo que se retiró esa rama.
- Guardar solo crea `NutritionLog` e invalida caché. No llama IA; el QA confirmó cero tips nuevos.
- QA real: `/tracking/nutrition/create`, título y F5 correctos; se guardó comida con 50 g, frutas, lácteos y medicación, redirigió al dashboard Blade con flash y actualizó carbohidratos/calorías.
- Suite específica: 6 pruebas pasan, 40 assertions. Suite completa: 135 pruebas, 825 assertions, 12.84 s. Build Vite correcto con 662 módulos; `git diff --check` correcto.
- `resources/views/tracking/nutrition/create.blade.php` permanece hasta Fase 8.

## 38. Nivel 2: Captura de vitales por cuidador

Completada el 29 de julio de 2026 reutilizando los componentes de captura de vitales.

- `GET /caregiver/patient/{patient}/vital/create` conserva URL y `checkLink()`, y renderiza `Caregiver/Tracking/Vitals/Create`. `LinkedPatientResource` limita los datos del paciente.
- Se preservaron los controles Blade: glucosa visual 40–300 y backend 20–600; pulso visual 40–200 y backend 30–220; sistólica 40–250, diastólica 30–180, HbA1c 3–20, estrés 255 y notas 1000. No se modificó lógica de vínculo ni persistencia.
- Se retiraron funciones inline y JSON genérico sin consumidores. Inertia realiza recarga completa al dashboard Blade del cuidador.
- QA real: vínculo activo requerido; HbA1c 21 mostró error; valores válidos se guardaron para el paciente correcto, dashboard mostró flash y métricas, F5 estable, consola sin warnings/errores y cero tips IA.
- Suite específica: 5 pruebas, 33 assertions. Suite completa: 140 pruebas, 858 assertions, 14.11 s. Build correcto con 663 módulos; `git diff --check` correcto.
- `resources/views/caregiver/tracking/vital-create.blade.php` permanece hasta Fase 8.

## 39. Nivel 2: Configuración de perfil

Completada el 29 de julio de 2026 reutilizando `AuthenticatedLayout`, los componentes de formulario y el `Modal` genérico.

- `GET /profile` conserva URL y autorización, y ahora renderiza `Profile/Edit` mediante Inertia. Los datos expuestos se limitan a perfil, solicitud pendiente de correo, zonas horarias y personas vinculadas; las etiquetas de relación se calculan en backend y React no compara nombres de rol para autorizar.
- Se preservaron campos y validaciones existentes: nombre y correo con máximo de 255 caracteres; avatar de imagen con máximo de 5 MB; contraseña actual obligatoria solo al solicitar cambio de correo; zona horaria con máximo de 100 caracteres y las mismas diez opciones del Blade; cambio de contraseña confirmado y validado contra la actual; eliminación de cuenta protegida por contraseña actual.
- La actualización de perfil usa `PATCH` con `forceFormData`, incluida la carga de avatar. El QA detectó y corrigió una regresión inicial que enviaba `POST`; se agregó una prueba Inertia específica para impedir que reaparezca.
- Las bolsas de errores nombradas `updatePassword` y `userDeletion` se enlazaron explícitamente a sus formularios React. El QA confirmó que una contraseña actual incorrecta se muestra tanto al cambiar contraseña como dentro del modal de eliminación.
- Para pacientes, la lista de médicos y cuidadores vinculados conserva la desvinculación existente mediante confirmación con `Modal`; el QA confirmó persistencia, bloqueo de scroll y eliminación correcta del vínculo.
- La suite usa `Mail::fake()` para confirmar los dos correos del cambio de dirección: `EmailChangeAlert` llega al correo actual con el nuevo destinatario en el contenido, y `VerifyEmailChange` llega al correo nuevo con destinatario, asunto, token y llamada a verificar esperados. También confirma que sin contraseña actual no se despacha ningún correo.
- QA real con `MAIL_MAILER=resend`: se usaron exclusivamente `delivered@resend.dev` y `delivered+profile-change@resend.dev`. Ambos envíos síncronos terminaron sin excepción y la respuesta HTTP regresó con flash en 3149 ms; no se usaron correos personales ni de terceros. El correo actual permaneció sin cambios y la solicitud pendiente quedó visible hasta confirmar el enlace.
- QA adicional: actualización de nombre y zona horaria, solicitud de cambio de correo, contraseña inválida, modal de eliminación, desvinculación, URL `/profile`, título `Configuración de perfil - DiabTrack`, estado estable tras F5 y consola limpia.
- Suite específica: 10 pruebas, 72 assertions. Suite completa: 145 pruebas, 910 assertions, 15.22 s. Build Vite 8.1.5 correcto con 664 módulos; `git diff --check` correcto.
- `resources/views/profile/edit.blade.php` permanece hasta Fase 8.
- Con esta pantalla queda completado el Nivel 2 del plan de migración.

## 40. Nivel 3: Dashboard de cuidador

Completada el 29 de julio de 2026 con `AuthenticatedLayout`, `Modal`, `Table` y el nuevo `ChartCard` basado en `react-chartjs-2`.

- Se instalaron `react-chartjs-2` 5.3.1 y `chart.js` 4.5.1; npm reportó cero vulnerabilidades. Tailwind permaneció sin cambios.
- `GET /caregiver` conserva URL, selección mediante `patient_id`, autorización y fallback al primer paciente, y ahora renderiza `Caregiver/Dashboard` mediante Inertia con props explícitas.
- Se preservaron paciente seleccionado, parentesco, última glucosa, edad, peso, diabetes, tiempo en rango, HbA1c, tendencia semanal de siete días y cinco registros recientes. La fecha de nacimiento sin cast se procesa con `Carbon::parse`, igual que en Blade.
- La gráfica conserva línea suavizada, relleno, puntos y huecos entre días sin datos. El estado vacío evita crear una gráfica sin valores.
- La confirmación nativa de desvinculación fue reemplazada por el `Modal` compartido; Escape, bloqueo y restauración de scroll se verificaron sin ejecutar la eliminación durante QA.
- No se ejecutó `app:generate-daily-tips` ni se llamó a Claude/Gemini. Las métricas solo leyeron datos locales y el fallback ya existente.
- QA real: `/caregiver`, título `Panel de cuidador - DiabTrack`, paciente y métricas correctos, canvas de Chart.js presente, enlace de captura correcto, modal accesible, consola de la versión final sin errores de Chart.js y F5 estable.
- Suite específica: 2 pruebas, 30 assertions. Suite completa: 147 pruebas, 940 assertions, 13.58 s. Build Vite correcto con 670 módulos; `git diff --check` correcto.
- `resources/views/caregiver/dashboard.blade.php` permanece hasta Fase 8.

## 41. Nivel 3: Dashboard médico

Completada el 29 de julio de 2026 reutilizando `AuthenticatedLayout`, `ChartCard`, `Table`, `Modal` y formularios del Nivel 0.

- `GET /doctor` conserva URL, selección de paciente, estados pendiente/aprobado/rechazado y métricas clínicas, y ahora renderiza `Doctor/Dashboard` mediante Inertia.
- Se preservaron última glucosa, tiempo en rango, HbA1c, calorías, tendencia semanal, presión, pulso, estado respecto a la meta y cinco registros recientes.
- El formulario de metas conserva validaciones backend mínima 40–150 y máxima 100–300. El QA detectó un método `PUT` incompatible con la ruta `PATCH`; se corrigió y se añadió una prueba de regresión. Una actualización real a 80/150 persistió y reclasificó 145 mg/dL de fuera de rango a en rango.
- Los perfiles no aprobados conservan el bloqueo del middleware para vincular pacientes y muestran el estado/revisión existente. Se actualizó la prueba antigua que buscaba copy Blade para afirmar el componente y props Inertia.
- La confirmación de desvinculación reutiliza el `Modal` genérico. No se modificó la lógica de autorización ni el hallazgo preexistente de cobertura parcial de `doctor.approved` ya documentado.
- No se ejecutó generación de tips ni llamadas a Claude/Gemini.
- QA real: `/doctor` y `/doctor?patient_id=45`, título `Panel médico - DiabTrack`, canvas presente, métricas, tabla, límites del formulario, actualización exitosa, F5 y consola final sin errores.
- Suite específica: 3 pruebas, 38 assertions. Suite completa: 150 pruebas, 989 assertions, 13.80 s. Build correcto con 671 módulos; `git diff --check` correcto.
- `resources/views/doctor/dashboard.blade.php` permanece hasta Fase 8.

## 42. Nivel 3: Dashboard principal del paciente

Completada el 29 de julio de 2026 reutilizando `AuthenticatedLayout`, `ChartCard`, `Table` y formularios compartidos.

- `GET /dashboard` conserva todas las redirecciones por rol/onboarding y ahora renderiza `Dashboard` mediante Inertia para pacientes.
- Se preservaron glucosa y clasificación clínica, tendencia semanal, HbA1c, calorías, carbohidratos, actividad, tiempo en rango, pasos, síntomas, objetivos, peso mensual, registros recientes y tip diario.
- El tip de QA se insertó directamente como `DailyTip` aprobado. No se ejecutó el job `app:generate-daily-tips` ni se llamó a Claude/Gemini; una prueba con `Http::fake()` confirma ausencia de solicitudes externas.
- Peso conserva validación 20–350 kg y actualización simultánea del perfil sin alterar la medición previa. QA guardó 72.5 kg, retiró el recordatorio y mostró flash.
- El código de vinculación conserva roles `caregiver`/`doctor`, invalida códigos pendientes anteriores y muestra el nuevo código de seis caracteres. El QA detectó la respuesta JSON legacy incompatible con una petición Inertia; al no existir ya otro consumidor, se retiró esa rama y se añadió una prueba de regresión.
- También se corrigió el selector React para usar opciones hijas reales de `FormSelect`; el control quedó navegable y seleccionable.
- Se actualizó la prueba Blade obsoleta del distintivo IA para validar `tip.isAi` y `tip.text` en las props Inertia.
- QA real: `/dashboard`, título `Dashboard - DiabTrack`, canvas presente, tip local, métricas, tabla, peso, código médico, flash, F5 y consola final sin errores.
- Suite específica: 4 pruebas, 47 assertions. Suite completa: 153 pruebas, 1035 assertions, 13.92 s. Build correcto con 672 módulos; `git diff --check` correcto.
- `resources/views/dashboard.blade.php` permanece hasta Fase 8.

## 43. Nivel 3: Resumen de salud

Completada el 29 de julio de 2026 con `AuthenticatedLayout`, `DataChart`, `Table` y estado React para pestañas, filtros y paginación.

- `GET /tracking/summary` conserva URL y los cálculos existentes del controlador, y ahora renderiza `Tracking/Summary` mediante Inertia con historiales explícitamente serializados.
- Se preservaron promedios de glucosa, presión y pulso, tiempo en rango, HbA1c, peso, carbohidratos, medicación, actividad, síntomas y clasificaciones clínicas.
- Se migraron las cuatro visualizaciones: tendencia semanal lineal, composición nutricional en dona, frecuencia de síntomas y glucosa promedio por momento. Los datasets, colores clínicos y estados vacíos permanecen derivados del backend.
- Las pestañas Bootstrap y funciones inline se reemplazaron por `useState`; el periodo de 7/30/90 días y todo el historial se filtran en React. La paginación cliente conserva ocho filas por página.
- No se ejecutó generación de IA ni solicitudes externas; la prueba usa `Http::fake()` y confirma cero llamadas.
- QA real: `/tracking/summary`, título `Resumen de salud - DiabTrack`, tres canvas con los datos disponibles y estado vacío para síntomas, pestañas de nutrición/actividad, filtro de siete días, tablas correctas, F5 estable y consola limpia.
- Suite específica: 1 prueba, 24 assertions. Suite completa: 154 pruebas, 1059 assertions, 13.55 s. Build correcto con 674 módulos; `git diff --check` correcto.
- `resources/views/tracking/summary.blade.php` permanece hasta Fase 8.

## 44. Nivel 3: Alimentación inteligente

Completada el 29 de julio de 2026 reutilizando `AuthenticatedLayout` y el `Modal` genérico.

- `GET /tracking/nutrition` conserva URL y métricas del servicio, y ahora renderiza `Tracking/Nutrition/Index` mediante Inertia.
- La revisión confirmó que el botón llamado “Generar recomendación IA” nunca llamaba a Claude, Gemini ni a un endpoint: elegía aleatoriamente entre cinco textos hardcodeados después de un temporizador. Se preservó como carrusel algorítmico local, ahora explícitamente serializado desde backend y ciclado con estado React.
- El QA no ejecutó el job de tips ni una integración externa. El tip mostrado provino de un `DailyTip` local ya creado; `Http::fake()` confirma cero solicitudes en la prueba.
- El carrusel de cinco comidas reemplaza funciones globales y recarga en resize por estado React, mostrando tres tarjetas y controles anterior/siguiente. Las imágenes y datos son los mismos del Blade.
- El modal reemplaza Bootstrap JS y preserva estado actual, siguiente idea, Escape y bloqueo/restauración de scroll.
- Se mantienen calorías, meta personalizada, carbohidratos, porcentajes, última glucosa y acceso a registrar comida.
- QA real: `/tracking/nutrition`, título `Alimentación inteligente - DiabTrack`, carrusel, métricas, tip local, modal, cambio de idea, Escape, F5 y consola limpia.
- Suite específica: 1 prueba, 20 assertions. Suite completa: 155 pruebas, 1079 assertions, 13.63 s. Build correcto con 675 módulos; `git diff --check` correcto.
- `resources/views/tracking/nutrition/index.blade.php` permanece hasta Fase 8.

## 45. Nivel 3: Métricas de uso de APIs

Completada el 29 de julio de 2026 reutilizando `AdminLayout`, `DataChart`, `Table` y `Pagination`.

- `GET /admin/api-usage` conserva URL y autorización administrativa, y ahora renderiza `Admin/ApiUsage/Index` mediante Inertia.
- Se preservaron tokens totales, costo total, llamadas, costo promedio, desglose por proveedor, periodos de 7/30 días y 6 meses, y el registro paginado de llamadas.
- Las tres visualizaciones usan Chart.js mediante `react-chartjs-2`: tokens comparados por periodo, llamadas por proveedor y costo por proveedor. Los botones de periodo exponen su estado con `aria-pressed`.
- El controlador mantiene `ApiUsageService` como fuente de los agregados. Los arrays calculados se serializan explícitamente; los logs Eloquent se limitan a los campos necesarios para la tabla.
- Las pruebas aíslan el servicio porque `getMonthlyStats()` usa `DATE_FORMAT`, propio del MySQL de producción y no disponible en SQLite. El QA real dentro del stack Docker sí ejercitó el servicio y MySQL reales.
- No se llamó a Claude/Gemini ni se ejecutó generación de tips. Para QA se insertaron dos logs locales controlados, uno por proveedor.
- QA real: `/admin/api-usage`, título `Uso de APIs - DiabTrack`, cifras agregadas correctas, tres canvas, periodos de 7/30 días y 6 meses, tabla, F5 estable y consola limpia.
- Suite específica: 2 pruebas, 31 assertions. Suite completa: 157 pruebas, 1110 assertions, 15.23 s. Build correcto con 676 módulos; `git diff --check` correcto.
- `resources/views/admin/api-usage/index.blade.php` permanece hasta Fase 8.

## 46. Cierre transversal del Nivel 3

Completado el 29 de julio de 2026 sobre `AuthenticatedLayout`, `GlobalSearch` y `NotificationMenu`.

- La búsqueda conserva el endpoint JSON con `throttle:60,1` y el debounce cliente de 250 ms. Las secciones ahora exponen `title` además del `label` legacy, por lo que comparten contrato visual con los resultados clínicos; la navegación usa `Link` de Inertia.
- La auditoría de rutas confirmó que ningún controlador activo renderiza ya una vista que extienda `layouts.app`; los `fetch()` de notificaciones de ese layout solo permanecen en archivos Blade inactivos reservados para eliminarse en Fase 8. Al no existir consumidores AJAX reales, se retiró la respuesta JSON legacy: las cuatro mutaciones responden con redirección 303 y `NotificationMenu` usa exclusivamente el router de Inertia.
- Se agregó cobertura automatizada del aislamiento por propietario en notificaciones, que ya existía correctamente tanto en el Blade original como en la query de Inertia. No hubo exposición cruzada de datos entre usuarios en ningún momento. Las pruebas verifican además el rechazo 403 de mutaciones sobre notificaciones ajenas.
- Se verificaron lectura y borrado individual, lectura y borrado masivo. El menú incorpora cierre con Escape y clic exterior, además de semántica `aria-expanded`, `aria-haspopup` y `role="menu"`.
- QA real: búsqueda de `glucosa` mostró `Registrar signo vital` con URL correcta; una notificación local controlada se marcó como leída y el borrado masivo dejó el estado vacío sin modal de error de Inertia. URL, título y F5 permanecieron estables.
- El `fetch()` de `GlobalSearch` se mantiene por ser una consulta de datos pura en segundo plano, conforme a las reglas permanentes. No queda compatibilidad AJAX temporal en las mutaciones de notificaciones.
- La búsqueda final de consumidores confirmó que Alpine, Bootstrap JS, SweetAlert2 y Chart.js CDN solo permanecen en vistas Blade conservadas o legacy. `resources/js/app.js` sigue siendo entrada de esos Blade. Su eliminación se difiere a Fase 8, junto con los Blade, para no romper consumidores todavía activos.
- No se ejecutó `app:generate-daily-tips` ni se realizaron solicitudes a Claude/Gemini durante ninguna prueba del Nivel 3.
- Suite transversal específica final: 7 pruebas, 28 assertions. Suite completa final: 161 pruebas, 1130 assertions, 19.78 s. Build Vite 8.1.5 correcto con 676 módulos; el diagnóstico de tiempos indicó únicamente que el plugin de Laravel concentró 83% del trabajo del build. Auditoría npm de producción con cero vulnerabilidades; `git diff --check` correcto.
- Con este gate quedan completadas todas las pantallas y comportamientos transversales del Nivel 3. La limpieza de Blade y JS legacy continúa reservada para Fase 8.

## 47. Fase 8: limpieza y cierre final

Completada el 29 de julio de 2026 después de migrar las 33 pantallas activas.

- Se eliminaron 64 archivos Blade legacy: las 33 pantallas migradas, dos vistas de detalle con rutas muertas, layouts, partials y componentes sin consumidores.
- `resources/views` conserva exclusivamente `app.blade.php` como raíz mínima de Inertia y las cinco plantillas Blade de correo excluidas del alcance.
- Se eliminaron las clases Breeze `AppLayout` y `GuestLayout`, la entrada `resources/js/app.js` y la dependencia Alpine.js. La búsqueda final no encontró rastros de Alpine.js, Bootstrap JS ni SweetAlert2 en el código restante.
- El frontend final contiene 33 páginas React y usa Inertia.js, React 19, Tailwind CSS 3.4 y Chart.js mediante `react-chartjs-2`.
- Gate definitivo: 161 pruebas, 1130 assertions, cero fallos, 14.35 s; build Vite correcto con 674 módulos; `npm audit` con cero vulnerabilidades.
- El Dockerfile se verificó con un build completo sin caché. La etapa Node ejecutó `npm ci` y compiló los 674 módulos; la imagen PHP instaló Composer de producción, incorporó RoadRunner 2025.1.15 y copió correctamente `public/build`.
- La inspección dentro de la imagen confirmó el manifiesto de Vite, 85 líneas de rutas sin dependencias vendor y exactamente seis Blade: raíz Inertia más cinco correos.
- QA visual final correcto en Welcome, Login, Usuarios administrativos, Dashboard del paciente y Resumen de salud.
