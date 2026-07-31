# Inventario de pantallas Blade

## Alcance y método

Inventario de la capa de presentación existente antes de introducir React e Inertia. Se contrastaron los 69 archivos `resources/views/**/*.blade.php` con `php artisan route:list --json`, `routes/web.php`, `routes/auth.php`, los controladores, los componentes de vista y las referencias Blade.

La complejidad indica el riesgo de migración de presentación, no la complejidad de la lógica de negocio:

- **Baja:** contenido estático o formulario simple sin estado cliente relevante.
- **Media:** formularios múltiples, paginación, componentes compartidos o UI condicional.
- **Alta:** gráficas, AJAX, estado cliente considerable, integración de IA o lógica de permisos embebida.

Los roles confirmados en el código son `paciente`, `cuidador`, `médico` y administración mediante `users.is_admin`/rol `admin`. El CRUD permite roles adicionales; no debe hardcodearse una lista cerrada en React.

## Pantallas y vistas navegables

| Pantalla | Archivo Blade | Ruta(s) | Controlador@método | Roles con acceso | Complejidad | Partials/componentes compartidos | Comportamiento JS |
|---|---|---|---|---|---|---|---|
| Landing pública | `resources/views/welcome.blade.php` | `GET /` | Closure en `routes/web.php` | Público | Baja | Ninguno | Bootstrap JS CDN; navegación/elementos del landing. |
| Login | `resources/views/auth/login.blade.php` | `GET /login`, `POST /login`; OAuth por `/auth/{provider}/*` | `AuthenticatedSessionController@create/store`; `SocialiteController` | Invitado | Media | `x-guest-layout`, `x-auth-card`, inputs, errores, estado | Formulario tradicional, CSRF, enlaces Socialite. |
| Registro | `resources/views/auth/register.blade.php` | `GET /register`, `POST /register` | `RegisteredUserController@create/store` | Invitado | Baja | `x-guest-layout`, `x-auth-card`, inputs, errores | Formulario tradicional y validación backend. |
| Solicitar restablecimiento | `resources/views/auth/forgot-password.blade.php` | `GET /forgot-password`, `POST /forgot-password` | `PasswordResetLinkController@create/store` | Invitado | Baja | `x-guest-layout`, `x-auth-card`, inputs, errores | Formulario tradicional y mensajes de sesión. |
| Restablecer contraseña | `resources/views/auth/reset-password.blade.php` | `GET /reset-password/{token}`, `POST /reset-password` | `NewPasswordController@create/store` | Invitado | Baja | `x-guest-layout`, `x-auth-card`, inputs, errores | Formulario tradicional con token. |
| Confirmar contraseña | `resources/views/auth/confirm-password.blade.php` | `GET/POST /confirm-password` | `ConfirmablePasswordController@show/store` | Autenticado | Baja | `x-guest-layout`, `x-auth-card`, inputs, errores | Formulario tradicional. |
| Verificar correo por código | `resources/views/auth/verify-email.blade.php` | `GET /verify-email`, `POST /verify-email`, `POST /email/verification-notification`, `POST /logout` | `EmailVerificationPromptController`; `VerifyEmailCodeController`; `EmailVerificationNotificationController`; sesión | Autenticado no verificado | Media | `x-guest-layout`, `x-auth-card`, label/input/error/button | Tres formularios; captura de código, reenvío y logout. |
| Selección de rol de onboarding | `resources/views/onboarding/role-selection.blade.php` | `GET /onboarding` | `OnboardingController@index` | Autenticado verificado sin onboarding | Baja | `x-guest-layout`, `x-auth-card` | Navegación a formularios por rol. |
| Onboarding de paciente | `resources/views/onboarding/personal-data.blade.php` | `GET/POST /onboarding/patient` | `OnboardingController@showPatientForm/storePatient` | Autenticado verificado sin onboarding | Media | `x-guest-layout`, `x-auth-card`, inputs, labels, errores | Formulario tradicional con campos clínicos/personales. |
| Onboarding de cuidador | `resources/views/onboarding/caregiver-data.blade.php` | `GET/POST /onboarding/caregiver` | `OnboardingController@showCaregiverForm/storeCaregiver` | Autenticado verificado sin onboarding | Media | `x-guest-layout`, `x-auth-card`, labels, errores | Selector de género con `onclick` y campo oculto. |
| Onboarding de médico | `resources/views/onboarding/doctor-data.blade.php` | `GET/POST /onboarding/doctor` | `OnboardingController@showDoctorForm/storeDoctor` | Autenticado verificado sin onboarding | Media | `x-guest-layout`, `x-auth-card`, inputs, labels, errores | Selector de género con `onclick` y campo oculto. |
| Dashboard de paciente | `resources/views/dashboard.blade.php` | `GET /dashboard`; `POST /dashboard/weight`; `POST /dashboard/invite` | `DashboardController@index/storeWeight/generateInviteCode` | Paciente; `index` redirige otros perfiles | Alta | `layouts.app` | Chart.js, formularios rápidos, invitaciones, `fetch`, manipulación DOM y estado de carga. |
| Resumen/historial | `resources/views/tracking/summary.blade.php` | `GET /tracking/summary` | `DashboardController@summary` | Paciente | Alta | `layouts.app` | Chart.js, filtros por periodo, tabs, cuatro tablas paginadas en cliente y múltiples handlers inline. |
| Registrar signos vitales | `resources/views/tracking/vital/create.blade.php` | `GET/POST /tracking/vitals` | `VitalSignController@create/store` | Paciente | Media | `layouts.app`, `x-tracking-nav`, `x-input-error` | Selectores de momento/estrés con handlers inline; formulario. |
| Registrar actividad | `resources/views/tracking/activity/create.blade.php` | `GET/POST /tracking/activity` | `ActivityLogController@create/store` | Paciente | Media | `layouts.app`, `x-tracking-nav`, `x-input-error` | Selectores de energía/intensidad con handlers inline; formulario. |
| Ideas de alimentación | `resources/views/tracking/nutrition/index.blade.php` | `GET /tracking/nutrition` | `NutritionLogController@index` | Paciente | Alta | `layouts.app` | Carrusel manual, navegación de slides, modal/estado de generación de idea IA y manipulación DOM. |
| Registrar nutrición | `resources/views/tracking/nutrition/create.blade.php` | `GET /tracking/nutrition/create`, `POST /tracking/nutrition` | `NutritionLogController@create/store` | Paciente | Media | `layouts.app`, `x-tracking-nav`, `x-input-error` | Selector de tipo de comida con handler inline; formulario. |
| Registrar síntomas | `resources/views/tracking/symptom/create.blade.php` | `GET/POST /tracking/symptoms` | `SymptomLogController@create/store` | Paciente | Media | `layouts.app`, `x-tracking-nav`, `x-input-error` | Formulario de selección dinámica renderizada por categorías. |
| Editar perfil | `resources/views/profile/edit.blade.php` | `GET/PATCH/DELETE /profile`; `DELETE /profile/unlink/{linkedUser}` | `ProfileController@edit/update/destroy/unlinkCarer` y `PasswordController@update` | Autenticado verificado | Alta | `layouts.app` y tres partials de perfil | Alpine para modal, visibilidad y mensajes; confirmación de desvinculación; varios formularios. UI de vinculados solo para paciente. |
| Dashboard de cuidador | `resources/views/caregiver/dashboard.blade.php` | `GET /caregiver`; `DELETE /caregiver/patient/{patient}/unlink` | `CaregiverController@dashboard/unlinkPatient` | Cuidador | Alta | `layouts.app`, `x-empty-linked-patients`, `x-empty-sidebar-guide` | Chart.js, selección de paciente, confirmación de desvinculación y estado condicional. |
| Vincular paciente como cuidador | `resources/views/caregiver/link-patient.blade.php` | `GET/POST /caregiver/link` | `CaregiverController@showLinkForm/linkPatient` | Cuidador | Baja | `layouts.app` | Formulario tradicional de código y parentesco. |
| Detalle legado de paciente para cuidador | `resources/views/caregiver/patient-detail.blade.php` | Sin render vigente; `GET /caregiver/patient/{patient}` redirige a `/caregiver?patient_id=...` | `CaregiverController@showPatient` | Cuidador | Alta | `layouts.app` | Chart.js. Vista huérfana/candidata a limpieza solo después de validar que no existe uso indirecto. |
| Registrar vital de paciente como cuidador | `resources/views/caregiver/tracking/vital-create.blade.php` | `GET/POST /caregiver/patient/{patient}/vital[/create]` | `CaregiverController@createVital/storeVital` | Cuidador vinculado | Media | `layouts.app`, `x-input-error` | Selectores de momento/estrés con handlers inline; formulario. |
| Dashboard de médico | `resources/views/doctor/dashboard.blade.php` | `GET /doctor`; `PATCH /doctor/patient/{patient}/targets`; `DELETE /doctor/patient/{patient}/unlink` | `DoctorController@dashboard/updateTargets/unlinkPatient` | Médico | Alta | `layouts.app`, `x-empty-linked-patients`, `x-empty-sidebar-guide` | Chart.js, selección de paciente, formulario de metas, confirmación de desvinculación; UI por estado de aprobación. |
| Vincular paciente como médico | `resources/views/doctor/link-patient.blade.php` | `GET/POST /doctor/link` | `DoctorController@showLinkForm/linkPatient` | Médico aprobado | Baja | `layouts.app` | Formulario tradicional de código. |
| Detalle legado de paciente para médico | `resources/views/doctor/patient-detail.blade.php` | Sin render vigente; `GET /doctor/patient/{patient}` redirige a `/doctor?patient_id=...` | `DoctorController@showPatient` | Médico vinculado | Alta | `layouts.app` | Chart.js y formulario de metas. Vista huérfana/candidata a limpieza tras validación. |
| Dashboard administrativo | `resources/views/admin/dashboard.blade.php` | `GET /admin` | Closure en `routes/web.php` | Administrador | Baja | `layouts.admin` | Sin JS propio; enlaces a módulos. |
| Usuarios: listado | `resources/views/admin/users/index.blade.php` | `GET /admin/users`; `DELETE /admin/users/{user}` | `Admin\\UserController@index/destroy` | Administrador | Media | `layouts.admin`, `x-admin-table`, `x-admin-modal` | Búsqueda GET, modal de eliminación; UI impide autoeliminación. |
| Usuarios: crear | `resources/views/admin/users/create.blade.php` | `GET /admin/users/create`, `POST /admin/users` | `Admin\\UserController@create/store` | Administrador | Media | `layouts.admin`, `x-admin-form-input` | Formulario con roles dinámicos e indicador `is_admin`. |
| Usuarios: editar | `resources/views/admin/users/edit.blade.php` | `GET /admin/users/{user}/edit`, `PUT/PATCH /admin/users/{user}`, `DELETE /admin/users/{user}` | `Admin\\UserController@edit/update/destroy` | Administrador | Media | `layouts.admin`, `x-admin-form-input`, `x-admin-modal` | Dos formularios; roles dinámicos; UI condicional para evitar revocar/eliminar al usuario actual. |
| Roles: listado | `resources/views/admin/roles/index.blade.php` | `GET /admin/roles`; `DELETE /admin/roles/{role}` | `Admin\\RoleController@index/destroy` | Administrador | Media | `layouts.admin`, `x-admin-table`, `x-admin-modal` | Paginación backend y modal de eliminación. |
| Roles: crear | `resources/views/admin/roles/create.blade.php` | `GET /admin/roles/create`, `POST /admin/roles` | `Admin\\RoleController@create/store` | Administrador | Baja | `layouts.admin`, `x-admin-form-input` | Formulario tradicional. |
| Roles: editar | `resources/views/admin/roles/edit.blade.php` | `GET /admin/roles/{role}/edit`, `PUT/PATCH /admin/roles/{role}`, `DELETE /admin/roles/{role}` | `Admin\\RoleController@edit/update/destroy` | Administrador | Media | `layouts.admin`, `x-admin-form-input`, `x-admin-modal` | Formularios de edición/eliminación; UI consulta usuarios asociados. |
| Aprobación de médicos | `resources/views/admin/doctors/index.blade.php` | `GET /admin/doctors`; `PATCH .../{doctorProfile}/approve|reject` | `Admin\\DoctorApprovalController@index/approve/reject` | Administrador | Media | `layouts.admin` | Filtro/paginación backend y dos formularios de decisión por perfil. |
| Uso de API/IA | `resources/views/admin/api-usage/index.blade.php` | `GET /admin/api-usage` | `Admin\\ApiUsageController@index` | Administrador | Alta | `layouts.admin`, `x-admin-table` | Chart.js, cambio de periodo/dataset y métricas de proveedores. |

Notas de rutas resource: `admin.users.show` y `admin.roles.show` están registradas por `Route::resource`, pero los controladores no implementan pantallas `show`; no existe Blade correspondiente. Esto es estado preexistente y no se corrige dentro de esta auditoría.

## Layouts, partials y componentes Blade

| Elemento | Archivo Blade | Consumidores/ruta(s) | Rol | Complejidad | Reutilización | Comportamiento JS |
|---|---|---|---|---|---|---|
| Layout autenticado principal | `resources/views/layouts/app.blade.php` | Dashboards, tracking, perfil, cuidador y médico | Autenticado; UI especial para paciente | Alta | `@yield('content')`; cabecera, sidebar, notificaciones, búsqueda, flash | Bootstrap y SweetAlert2 CDN; `fetch` de notificaciones, búsqueda, envío dinámico, menús y manipulación DOM. Consulta notificaciones directamente desde la vista. |
| Layout administrativo | `resources/views/layouts/admin.blade.php` | Todas las páginas `admin.*` | Administrador | Media | `@yield('content')`; navegación y flash | Bootstrap JS CDN; formulario logout. |
| Layout invitado | `resources/views/layouts/guest.blade.php` | Auth y onboarding mediante `GuestLayout` | Invitado o autenticado en onboarding/verificación | Media | Shell HTML/SEO y slots Blade | Carga Alpine global; sin JS inline propio. |
| Navegación Breeze legado | `resources/views/layouts/navigation.blade.php` | Sin inclusión directa encontrada | Autenticado | Media | Componentes de navegación/dropdown | Alpine para menú responsive/dropdown; formularios logout. Candidata a código no activo. |
| Partial: información de perfil | `resources/views/profile/partials/update-profile-information-form.blade.php` | `profile.edit` | Autenticado | Alta | Formulario embebido | Dos formularios, Alpine para contraseña/estado, errores y verificación de cambio de email. |
| Partial: contraseña | `resources/views/profile/partials/update-password-form.blade.php` | `profile.edit` | Autenticado | Media | Formulario embebido | Alpine para mensaje temporal. |
| Partial: eliminar cuenta | `resources/views/profile/partials/delete-user-form.blade.php` | `profile.edit` | Autenticado | Media | `x-modal` | Alpine abre/cierra modal; formulario DELETE. |
| Botón admin | `resources/views/components/admin-button.blade.php` | Sin uso `<x-admin-button>` encontrado | Administrador | Baja | Componente atómico | Ninguno. |
| Input admin | `resources/views/components/admin-form-input.blade.php` | Crear/editar usuarios y roles | Administrador | Baja | Componente atómico | Refleja errores backend. |
| Modal admin | `resources/views/components/admin-modal.blade.php` | Listados/edición de usuarios y roles | Administrador | Media | Modal reutilizable | Depende del comportamiento modal de Bootstrap. |
| Select admin | `resources/views/components/admin-select.blade.php` | Sin uso encontrado | Administrador | Baja | Componente atómico | Ninguno. |
| Tabla admin | `resources/views/components/admin-table.blade.php` | Usuarios, roles, uso API | Administrador | Baja | Wrapper de tabla con slots | Ninguno. |
| Logo | `resources/views/components/application-logo.blade.php` | Navegación legado | Cualquiera | Baja | SVG/imagen atómica | Ninguno. |
| Tarjeta auth | `resources/views/components/auth-card.blade.php` | Auth y onboarding | Invitado/autenticado | Baja | Contenedor con slot | Ninguno. |
| Estado de sesión auth | `resources/views/components/auth-session-status.blade.php` | Login | Invitado | Baja | Mensaje reusable | Ninguno. |
| Botón peligro | `resources/views/components/danger-button.blade.php` | Sin uso directo encontrado | Cualquiera | Baja | Botón atómico | Ninguno. |
| Dropdown | `resources/views/components/dropdown.blade.php` | Navegación legado | Autenticado | Media | Dropdown con slots | Alpine controla apertura, cierre y click exterior. |
| Enlace dropdown | `resources/views/components/dropdown-link.blade.php` | Navegación legado | Autenticado | Baja | Enlace atómico | Ninguno. |
| Estado vacío de vinculados | `resources/views/components/empty-linked-patients.blade.php` | Dashboards cuidador/médico | Cuidador o médico | Media | Variante por prop `profile` | UI condicional por perfil y enlaces de vinculación. |
| Guía lateral vacía | `resources/views/components/empty-sidebar-guide.blade.php` | Dashboards cuidador/médico | Cuidador o médico | Baja | Variante por prop `profile` | UI condicional por perfil. |
| Error de input | `resources/views/components/input-error.blade.php` | Auth, onboarding y tracking | Cualquiera | Baja | Componente atómico | Errores backend. |
| Label de input | `resources/views/components/input-label.blade.php` | Auth/onboarding | Cualquiera | Baja | Componente atómico | Ninguno. |
| Modal Breeze | `resources/views/components/modal.blade.php` | Eliminación de cuenta | Autenticado | Alta | Modal accesible genérico | Alpine: focus trap, Escape, eventos globales y transiciones. |
| Enlace nav | `resources/views/components/nav-link.blade.php` | Navegación legado | Autenticado | Baja | Enlace atómico | Clase activa por prop. |
| Botón primario | `resources/views/components/primary-button.blade.php` | Auth | Cualquiera | Baja | Botón atómico | Ninguno. |
| Enlace nav responsive | `resources/views/components/responsive-nav-link.blade.php` | Navegación legado | Autenticado | Baja | Enlace atómico | Clase activa por prop. |
| Botón secundario | `resources/views/components/secondary-button.blade.php` | Sin uso directo encontrado | Cualquiera | Baja | Botón atómico | Ninguno. |
| Text input | `resources/views/components/text-input.blade.php` | Auth/onboarding | Cualquiera | Baja | Input atómico | Autofocus opcional. |
| Navegación de tracking | `resources/views/components/tracking-nav.blade.php` | Formularios de tracking | Paciente | Baja | Navegación compartida | Estado activo derivado de la ruta. |

## Plantillas de correo

Estas vistas no deben convertirse en páginas Inertia. Deben conservarse como Blade porque Laravel las renderiza del lado servidor para email.

| Plantilla | Archivo Blade | Emisor | Audiencia | Complejidad | Dependencias | Comportamiento JS |
|---|---|---|---|---|---|---|
| Médico aprobado | `resources/views/emails/doctor-approved.blade.php` | `DoctorApprovedNotification` | Médico | Baja | Datos de notificación | Ninguno; HTML inline para email. |
| Alerta de cambio de email | `resources/views/emails/email-change-alert.blade.php` | Flujo de cambio de email | Usuario | Baja | Datos de notificación/mail | Ninguno. |
| Restablecimiento de contraseña | `resources/views/emails/reset-password.blade.php` | `ResetPasswordNotification` | Usuario | Baja | Datos de notificación | Ninguno. |
| Verificación de cambio de email | `resources/views/emails/verify-email-change.blade.php` | Flujo de cambio de email | Usuario | Baja | Datos de notificación/mail | Ninguno. |
| Código de verificación | `resources/views/emails/verify-email-code.blade.php` | `VerifyEmailCodeNotification` | Usuario | Baja | Datos de notificación | Ninguno. |

## Jerarquía y reutilización actual

- `layouts.app` es el layout de mayor riesgo: concentra navegación, búsqueda, notificaciones, flash, consultas de modelo dentro de Blade y gran parte del JavaScript legacy.
- `layouts.admin` contiene la navegación administrativa y los mensajes flash.
- `layouts.guest` es servido por `App\\View\\Components\\GuestLayout` y envuelve auth/onboarding mediante `<x-guest-layout>`.
- `profile.edit` incluye los tres partials de perfil.
- Los formularios de tracking reutilizan `<x-tracking-nav>` y `<x-input-error>`.
- Los dashboards de cuidador y médico reutilizan los dos componentes de estados vacíos.
- Las pantallas administrativas reutilizan tabla, modal e input; `admin-button` y `admin-select` no tienen consumidores encontrados.
- `layouts.navigation` y varios componentes Breeze asociados no tienen una inclusión directa desde layouts activos; deben considerarse legado potencial, no eliminarse hasta validar en ejecución y tests.

## Lógica condicional de rol y permisos en UI

- `layouts.app` usa `auth()->user()->isPatient()` para mostrar navegación, notificaciones y búsqueda exclusivas del paciente.
- `profile.edit` consulta vinculaciones desde la vista solamente para pacientes y deriva si cada vinculado es médico o cuidador a partir del primer rol.
- Los dashboards de cuidador y médico seleccionan variantes de estado vacío y acciones específicas por perfil.
- `doctor.dashboard` muestra estados distintos para aprobación pendiente, aprobada o rechazada; el middleware `doctor.approved` sigue protegiendo la acción de vincular.
- Las vistas administrativas muestran roles dinámicos de base de datos y `is_admin`; además ocultan acciones peligrosas sobre la cuenta administrativa actual.
- Estas condiciones solo controlan presentación. Los middleware y comprobaciones backend existentes continúan siendo la autoridad real.

## JavaScript y dependencias que deben preservarse durante la migración

| Dependencia/comportamiento | Ubicación principal | Riesgo |
|---|---|---|
| Alpine.js | Modales, dropdowns, navegación y perfil | Debe reemplazarse por estado React por pantalla; no retirar globalmente hasta migrar al último consumidor Blade. |
| Bootstrap JS CDN | `welcome`, `layouts.app`, `layouts.admin` | Dropdowns/modales/collapse pueden depender del runtime aunque la apariencia venga de CSS propio/Bootstrap. Retirada incremental. |
| SweetAlert2 CDN | `layouts.app` | Confirmaciones/alertas globales; sustituir por Modal/Toast React antes de retirarlo. |
| Chart.js CDN | Dashboards, resumen, detalles legacy y uso API | Mantener configuración y datos; envolver con `react-chartjs-2` según la estrategia aprobada. |
| `fetch()` | Notificaciones, búsqueda y dashboard | Migrar formularios/navegación a Inertia; conservar llamadas puramente de datos cuando corresponda. |
| Handlers inline | Tracking, onboarding, nutrición y confirmaciones | Convertir a eventos React manteniendo selección, accesibilidad y mensajes. |

No se encontró jQuery instalado ni uso explícito de jQuery en las vistas auditadas.

## Hallazgos y riesgos para el plan

1. `resources/views/app.blade.php` no existe actualmente. El futuro root template de Inertia deberá crearse; no debe confundirse con `resources/views/layouts/app.blade.php`.
2. `layouts.app` ejecuta consultas de notificaciones directamente en la vista. Para Inertia deberán moverse a props compartidas o a un servicio/controlador sin alterar reglas de negocio.
3. Los dashboards y el resumen reciben arrays grandes de métricas y modelos. Hay que definir serialización explícita y evitar exponer atributos Eloquent no necesarios.
4. `tracking.nutrition.index` combina presentación con interacción de sugerencias IA; debe migrarse al final.
5. Las vistas `caregiver.patient-detail` y `doctor.patient-detail` no son renderizadas por sus rutas actuales. Se conservarán hasta confirmar con pruebas que no hay acceso indirecto.
6. Las plantillas de correo permanecen Blade y quedan fuera del reemplazo por Inertia.
7. Tailwind 3.4 está activo vía PostCSS. `@tailwindcss/vite` 4 está instalado pero inactivo; no se habilitará durante esta migración.
8. La aplicación mezcla clases Bootstrap y Tailwind/CSS propio. La paridad visual requiere revisar estilos por pantalla, no una traducción mecánica global.
9. La autorización está distribuida entre middleware de ruta, comprobaciones de vínculo y condiciones de UI. La migración no debe trasladar seguridad al cliente.

## Cobertura del inventario

- Vistas Blade encontradas: **69**.
- Pantallas/vistas de contenido: **35** (incluye 2 vistas legacy no renderizadas actualmente).
- Layouts: **4**.
- Partials de perfil: **3**.
- Componentes Blade: **22**.
- Plantillas de correo que permanecen Blade: **5**.
- Total documentado: **69**.
