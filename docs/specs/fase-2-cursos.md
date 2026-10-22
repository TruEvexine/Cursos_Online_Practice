# Fase 2 — Cursos y contenido (especificación aprobada el 2026-10-22)

Rama `feature/fase-2-cursos` (sale de `fase-0-1`). El esquema actual ya cubre la fase: sin cambios en `database/changes/`.

## Qué entra

- **Ponente** (super admin en todos los cursos):
  - Cursos: crear, editar y borrar. Estado `draft` / `published` / `archived`.
    Borrar: con inscripciones activas **no se borra** (mensaje que sugiere archivarlo); sin ellas, borrado lógico.
  - Bloques y temas: crear, editar y borrar. El orden se da con un campo numérico `position`; los temas tienen `is_required`.
  - Contenido del tema en Markdown: `Str::markdown($texto, ['html_input' => 'strip', 'allow_unsafe_links' => false])`.
  - Materiales:
    - **archivo:** pdf, docx, pptx, xlsx, png, jpg o webp, máx. 10 MB; nombre aleatorio y disco privado; se descarga por un controlador que autoriza.
    - **enlace** y **video:** solo URL `https://`, que se abre con `rel="noopener noreferrer"`. El video no se incrusta.
    - Si PHP rechaza el archivo por tamaño (`upload_max_filesize` / `post_max_size`), se muestra un mensaje claro, no un error genérico.
  - Alumnos por correo:
    - Alumno existente → se inscribe (si estaba `removed`, se reactiva).
    - Otro rol → se rechaza.
    - Correo nuevo → se crea una cuenta de alumno y se envía el enlace de recuperación de Fortify como "fija tu contraseña". El correo explica que, si el enlace caducó (60 min), se pide otro en "¿Olvidaste tu contraseña?". Al fijar la contraseña, el correo queda verificado.
    - Quitar → `status='removed'` y `removed_at`, con registro en `audit_logs`.
- **Alumno:** "Mis cursos" (cursos publicados con inscripción `active` o `completed`), vista del curso (bloques, temas y materiales) y lectura del tema (72ch).
- **Director:** ve cualquier curso en solo lectura.

## Qué no entra

Bloqueos y progreso de misiones, tareas, exámenes, avisos, portada del curso, reordenar arrastrando, video incrustado.

## Permisos (`CoursePolicy`; bloques, temas y materiales se autorizan con la de su curso)

| Habilidad | Quién |
|---|---|
| `view` | super admin, director, el ponente dueño, alumno con inscripción `active` o `completed` en un curso `published` (`removed` → 403) |
| `create` | ponente, super admin |
| `update`, `delete`, `manageStudents` | el ponente dueño, super admin |

## Rutas

`/cursos` (lista y CRUD) · `cursos/{course}/bloques` · `bloques/{block}/temas` · `cursos/{course}/materiales` ·
`materiales/{material}/descargar` · `cursos/{course}/alumnos` · `temas/{topic}` (lectura) · `/alumno` = "Mis cursos".

## Pruebas

- 403 para: otro ponente que edita un curso ajeno, alumno que crea un curso, director que edita.
- Alumno inscrito (`active` o `completed`) lee el curso, el tema y descarga materiales; alumno no inscrito, quitado o en un curso en borrador recibe 403.
- El Markdown quita `<script>` y los enlaces `javascript:`. Se rechazan subidas svg, html y zip; la ruta guardada es aleatoria y privada.
- Inscripción: alumno existente, correo nuevo (cuenta, aviso con texto de caducidad y verificación al fijar la contraseña), otro rol, reinscripción; quitar deja registro en la bitácora.
- No se borra un curso con inscripciones activas.
- Aceptación: el ponente arma el curso, el alumno inscrito lo lee y otro alumno recibe 403.

## Tareas (un commit por tarea)

1. Modelos y `CoursePolicy`, con pruebas.
2. CRUD de cursos, con pruebas.
3. Bloques y temas con Markdown, con pruebas.
4. Materiales y descarga autorizada, con pruebas.
5. Agregar y quitar alumnos, invitación y bitácora, con pruebas.
6. Vista del alumno, con pruebas de 403.
7. Lectura del director, prueba de aceptación, casillas del PLAN y revisión de seguridad.

Auditoría de diseño en las tareas 2, 3, 4, 6 y 7:
- `ui-ux-pro-max`: contra `docs/DESIGN.md`.
- `design-motion-principles` en modo audit.
- Revisión en navegador a 375, 768 y 1280 px.
- Se corrige lo encontrado antes del commit y se anota en el mensaje.
