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
| Fase 4: arquitectura de componentes | Pendiente |
| Fase 5: migración incremental | Pendiente, 33 páginas activas |
| Fase 6: formularios y estado | Pendiente/transversal |
| Fase 7: testing | Pendiente/transversal |
| Fase 8: limpieza y cierre | Pendiente |

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
