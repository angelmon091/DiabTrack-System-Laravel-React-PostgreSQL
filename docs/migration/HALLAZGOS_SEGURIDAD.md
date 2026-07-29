# Hallazgos de seguridad

## Cobertura incompleta de `doctor.approved`

### Hallazgo

El middleware `doctor.approved` protege únicamente las rutas `GET` y `POST` de `/doctor/link`. Las rutas de consulta de pacientes, actualización de objetivos y desvinculación no están cubiertas por ese middleware.

### Riesgo

Un médico con perfil rechazado conserva acceso funcional a datos de pacientes mediante esas rutas, aunque ya no pueda vincular nuevos pacientes.

### Estado

Este comportamiento es preexistente y no fue introducido por la migración. Su corrección queda fuera del alcance del proyecto de migración a React/Inertia.

### Recomendación

Aplicar `doctor.approved`, o revisar la autorización mediante una Policy, a las rutas de consulta, actualización y desvinculación de pacientes. El cambio debe realizarse por separado y evaluarse con el negocio antes de implementarlo.
