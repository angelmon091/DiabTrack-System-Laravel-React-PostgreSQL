# Cierre consolidado previo a Fase 8

Fecha de corte: 29 de julio de 2026. Rama: `feature/migracion-react-inertia`.

Este documento registra el estado comprobado antes de autorizar la limpieza física. En este bloque no se eliminó ningún archivo ni dependencia.

## 1. Pantallas activas migradas

Las 33 pantallas activas del inventario están migradas. Los layouts y servicios transversales no se contabilizan como pantalla.

| # | Nivel | Pantalla Blade original | Página React | Commit |
|---:|---:|---|---|---|
| 1 | 1 | `auth/login.blade.php` | `Auth/Login` | `c7d7cb0` — Login a React + Inertia |
| 2 | 1 | `auth/register.blade.php` | `Auth/Register` | `34fbc5e` — corregir navegación entre Login y Registro |
| 3 | 1 | `auth/forgot-password.blade.php` | `Auth/ForgotPassword` | `2f14d14` — Recuperar contraseña a React + Inertia |
| 4 | 1 | `auth/reset-password.blade.php` | `Auth/ResetPassword` | `432fe94` — Restablecer contraseña a React + Inertia |
| 5 | 1 | `auth/confirm-password.blade.php` | `Auth/ConfirmPassword` | `4039e3d` — Confirmar contraseña a React + Inertia |
| 6 | 1 | `auth/verify-email.blade.php` | `Auth/VerifyEmail` | `d5b9fce` — Verificar email a React + Inertia |
| 7 | 1 | `welcome.blade.php` | `Welcome` | `8ba8b27` — Welcome a React + Inertia |
| 8 | 1 | `onboarding/role-selection.blade.php` | `Onboarding/RoleSelection` | `6fc6597` — Selección de rol a React + Inertia |
| 9 | 1 | `onboarding/personal-data.blade.php` | `Onboarding/PatientData` | `c2a658b` — Datos de paciente a React + Inertia |
| 10 | 1 | `onboarding/caregiver-data.blade.php` | `Onboarding/CaregiverData` | `a431b36` — Datos de cuidador a React + Inertia |
| 11 | 1 | `onboarding/doctor-data.blade.php` | `Onboarding/DoctorData` | `53528fb` — Datos de médico a React + Inertia |
| 12 | 1 | `caregiver/link-patient.blade.php` | `Caregiver/LinkPatient` | `401f7c8` — Vincular paciente cuidador a React + Inertia |
| 13 | 1 | `doctor/link-patient.blade.php` | `Doctor/LinkPatient` | `bfc0675` — Vincular paciente médico a React + Inertia |
| 14 | 2 | `admin/dashboard.blade.php` | `Admin/Dashboard` | `2e1b6b1` — Dashboard administrativo a React + Inertia |
| 15 | 2 | `admin/roles/index.blade.php` | `Admin/Roles/Index` | `84b7b21` — Listado de roles a React + Inertia |
| 16 | 2 | `admin/roles/create.blade.php` | `Admin/Roles/Create` | `f1b9845` — Crear rol a React + Inertia |
| 17 | 2 | `admin/roles/edit.blade.php` | `Admin/Roles/Edit` | `2b668c5` — Editar rol a React + Inertia |
| 18 | 2 | `admin/users/index.blade.php` | `Admin/Users/Index` | `0cf0dd1` — Listado de usuarios a React + Inertia |
| 19 | 2 | `admin/users/create.blade.php` | `Admin/Users/Create` | `1713f2e` — Crear usuario a React + Inertia |
| 20 | 2 | `admin/users/edit.blade.php` | `Admin/Users/Edit` | `e6aed9f` — Editar usuario a React + Inertia |
| 21 | 2 | `admin/doctors/index.blade.php` | `Admin/Doctors/Index` | `dc535de` — Aprobación de médicos a React + Inertia |
| 22 | 2 | `tracking/vital/create.blade.php` | `Tracking/Vitals/Create` | `1eca64c` — Captura de signos vitales a React + Inertia |
| 23 | 2 | `tracking/activity/create.blade.php` | `Tracking/Activity/Create` | `eceab82` — Captura de actividad a React + Inertia |
| 24 | 2 | `tracking/nutrition/create.blade.php` | `Tracking/Nutrition/Create` | `b56a6ae` — Captura de nutrición a React + Inertia |
| 25 | 2 | `tracking/symptom/create.blade.php` | `Tracking/Symptoms/Create` | `9207bb3` — Captura de síntomas a React + Inertia |
| 26 | 2 | `caregiver/tracking/vital-create.blade.php` | `Caregiver/Tracking/Vitals/Create` | `cd2e1ba` — Captura de vitales del cuidador a React + Inertia |
| 27 | 2 | `profile/edit.blade.php` | `Profile/Edit` | `d540629` — Perfil a React + Inertia |
| 28 | 3 | `caregiver/dashboard.blade.php` | `Caregiver/Dashboard` | `5c456a9` — Dashboard de cuidador a React + Inertia |
| 29 | 3 | `doctor/dashboard.blade.php` | `Doctor/Dashboard` | `5259cda` — Dashboard médico a React + Inertia |
| 30 | 3 | `dashboard.blade.php` | `Dashboard` | `9cf0ef6` — Dashboard principal a React + Inertia |
| 31 | 3 | `tracking/summary.blade.php` | `Tracking/Summary` | `484ca9e` — Resumen de salud a React + Inertia |
| 32 | 3 | `tracking/nutrition/index.blade.php` | `Tracking/Nutrition/Index` | `4c5aa85` — Alimentación inteligente a React + Inertia |
| 33 | 3 | `admin/api-usage/index.blade.php` | `Admin/ApiUsage/Index` | `f7df7b4` — Métricas de uso API a React + Inertia |

Infraestructura transversal asociada: `99d2fac` (`AuthenticatedLayout` y servicios compartidos), `c0c9435` (búsqueda y notificaciones globales) y `3b25e7b` (retiro de AJAX legacy de notificaciones).

## 2. Vistas legacy descartadas desde el inventario

| Archivo | Comprobación y razón |
|---|---|
| `resources/views/caregiver/patient-detail.blade.php` | Ruta muerta como vista: `GET /caregiver/patient/{patient}` ejecuta `CaregiverController@showPatient`, que redirige a `/caregiver?patient_id=...`; no renderiza este Blade. Marcada para eliminación en Fase 8, no para migración. |
| `resources/views/doctor/patient-detail.blade.php` | Ruta muerta como vista: `GET /doctor/patient/{patient}` ejecuta `DoctorController@showPatient`, que redirige a `/doctor?patient_id=...`; no renderiza este Blade. Marcada para eliminación en Fase 8, no para migración. |

## 3. Plantillas de correo excluidas

Estas cinco plantillas permanecen en Blade porque se renderizan del lado servidor para correo. Quedan explícitamente fuera del alcance de Fase 8 y no deben tocarse:

1. `resources/views/emails/doctor-approved.blade.php` — `DoctorApprovedNotification`.
2. `resources/views/emails/email-change-alert.blade.php` — `EmailChangeAlert`.
3. `resources/views/emails/reset-password.blade.php` — `ResetPasswordNotification`.
4. `resources/views/emails/verify-email-change.blade.php` — `VerifyEmailChange`.
5. `resources/views/emails/verify-email-code.blade.php` — `VerifyEmailCodeNotification`.

## 4. Auditoría de rutas y renderizado

La búsqueda `rg -n 'return\s+view\(|Route::view\(|->view\(' app routes --glob '*.php'` confirma que no existe `return view()` ni `Route::view()` en `routes/web.php`, `routes/auth.php` o sus controladores de página.

Todas las rutas GET de presentación activas terminan en una página Inertia, excepto las dos rutas legacy de detalle indicadas arriba, que redirigen deliberadamente al dashboard correspondiente. Las rutas de búsqueda y prueba local devuelven JSON; las acciones POST/PATCH/DELETE redirigen o devuelven el contrato de datos que les corresponde y no son renderizados de pantalla.

Las cinco plantillas de correo son referenciadas por tres Notifications y dos Mailables. La búsqueda también encuentra dos componentes Blade Breeze todavía presentes:

- `app/View/Components/AppLayout.php` devuelve `view('layouts.app')`.
- `app/View/Components/GuestLayout.php` devuelve `view('layouts.guest')`.

Estos dos componentes no son rutas ni controladores de pantalla activos; son infraestructura Blade legacy candidata a eliminación en Fase 8. Por precisión, hasta ejecutar esa limpieza no es correcto afirmar que los Mailables son los únicos `return view()` de todo `app/`, aunque sí son los únicos renderizados Blade que deben permanecer después de Fase 8.

## 5. Auditoría final de Alpine, Bootstrap JS y SweetAlert2

Se ejecutó una búsqueda global en el código del proyecto, excluyendo dependencias instaladas, artefactos de build, logs y documentación. Resultado:

### Alpine.js

Persisten referencias fuera de Blade, todas pertenecientes a la entrada frontend legacy:

- `resources/js/app.js`: importación, exposición en `window` e inicialización de Alpine.
- `package.json` y `package-lock.json`: dependencia `alpinejs`.
- `vite.config.js`: mantiene `resources/js/app.js` como entrada porque todavía existen archivos Blade conservados físicamente.

Persisten directivas Alpine dentro de Blade legacy en:

- `resources/views/profile/partials/update-profile-information-form.blade.php`.
- `resources/views/profile/partials/update-password-form.blade.php`.
- `resources/views/profile/partials/delete-user-form.blade.php`.
- `resources/views/layouts/navigation.blade.php`.
- `resources/views/components/dropdown.blade.php`.
- `resources/views/components/modal.blade.php`.

Por tanto, antes de Fase 8 no es correcto afirmar que Alpine no deja rastros fuera de Blade. `resources/js/app.js`, su entrada Vite y la dependencia npm son deuda legacy identificada para eliminar conjuntamente con sus consumidores Blade.

Las coincidencias `node:20-alpine` y `redis:alpine` de Docker describen la distribución Linux de las imágenes y no guardan relación con Alpine.js.

### Bootstrap JS

Las únicas cargas del bundle JavaScript de Bootstrap están dentro de Blade destinados a eliminación:

- `resources/views/welcome.blade.php`.
- `resources/views/layouts/app.blade.php`.
- `resources/views/layouts/admin.blade.php`.

No se encontró importación npm ni uso de Bootstrap JS dentro de `resources/js/**/*.jsx`.

### SweetAlert2

La única carga y uso (`Swal`/`DiabSwal`) está en `resources/views/layouts/app.blade.php`, destinado a eliminación. No existe dependencia npm ni referencia dentro del frontend React.

### Conclusión para autorizar Fase 8

- No hay consumidores activos de Alpine.js, Bootstrap JS o SweetAlert2 en las 33 páginas React.
- Bootstrap JS y SweetAlert2 solo permanecen en Blade legacy que se eliminarán.
- Alpine.js conserva además la entrada `resources/js/app.js` y su dependencia npm; ambos deben incluirse expresamente en la limpieza de Fase 8 después de eliminar los Blade consumidores.
- Las cinco vistas `resources/views/emails/*.blade.php` quedan protegidas y fuera de toda eliminación.

## 6. Resultado de Fase 8

La limpieza física se ejecutó en bloques secuenciales y quedó completada:

- Se eliminaron los Blade de las 33 pantallas migradas y las dos vistas de detalle con rutas muertas. Por el orden de limpieza, `welcome.blade.php` se retiró junto con los layouts en el bloque siguiente.
- Se eliminaron layouts, partials, componentes Blade y las clases Breeze `AppLayout`/`GuestLayout` después de retirar todos sus consumidores.
- `resources/views` conserva únicamente `app.blade.php`, raíz mínima de Inertia, y las cinco plantillas de correo excluidas.
- Se eliminó la entrada legacy `resources/js/app.js`, se retiró Alpine.js de npm y se regeneró `package-lock.json`.
- La búsqueda final no encontró referencias de Alpine.js, Bootstrap JS o SweetAlert2 en el código activo ni en archivos legacy restantes.
- El README documenta el stack final Laravel, React, Inertia.js, Tailwind CSS, Chart.js, Octane/RoadRunner, Redis y Docker.
- Gate final repetido después de todos los cambios: 161 pruebas y 1130 assertions correctas en 14.35 s; build Vite 8.1.5 correcto con 674 módulos; `npm audit` sin vulnerabilidades; `git diff --check` correcto.
- El Dockerfile completo construyó correctamente, sin caché, la imagen local temporal `diabtrack-migration-qa-final:latest`, incluido `npm ci`, el build Vite de 674 módulos, Composer de producción y RoadRunner 2025.1.15. La inspección dentro de la imagen confirmó `public/build/manifest.json`, exactamente cinco plantillas de correo y seis Blade totales. La imagen temporal se eliminó después de aprobar la verificación.
- QA visual final en navegador: Welcome, Login, listado administrativo de usuarios, dashboard del paciente y resumen de salud cargaron correctamente. Se verificaron layouts, títulos, navegación, tablas, métricas y estados vacíos después de la limpieza.
