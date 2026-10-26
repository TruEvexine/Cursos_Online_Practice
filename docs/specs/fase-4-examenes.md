# Fase 4 — Exámenes (BORRADOR con decisiones del 2026-10-26; va después del hito DEMO)

Rama `feature/fase-4-examenes` (sale de la Fase 3 cerrada). Tablas que ya existen: `exams`, `exam_questions`, `exam_options`, `exam_attempts`, `exam_answers`, `exam_answer_options`.
Comodín de intento extra: depende de `reward_grants` (Fase 3, tarea 7).

## Qué entra

- **Constructor de exámenes** (ponente dueño o super admin):
  - Datos del examen: título, instrucciones, bloque opcional (`exams.block_id`), ventana `available_from` / `available_until`, tiempo límite, intentos (`max_attempts`, por defecto 1), calificación mínima y peso.
  - Preguntas `single`, `multiple`, `true_false` y `open`, con puntos y orden numérico.
  - Estados `draft` / `published` / `closed`. Un examen publicado con intentos ya no cambia sus preguntas.
- **Presentar examen** (alumno `active`):
  - Solo dentro de la ventana y, si tiene bloque, con el bloque completo (PLAN §4).
  - Al empezar se crea el intento con `started_at`.
  - Cronómetro visible en JS (módulo de Vite, sin JS en línea por la CSP).
  - Las respuestas se guardan pregunta por pregunta.
  - El servidor rechaza todo después de `started_at` + `time_limit_minutes` + 30 s, aunque el navegador mande la petición.
- **Calificación:**
  - automática para `single`, `true_false` y `multiple` (todo o nada);
  - las abiertas, a mano, con retroalimentación;
  - el intento pasa a `graded` cuando no quedan abiertas;
  - cuenta el mejor intento calificado.
  - Calificar queda en `audit_logs`.
- **Alumno:** la nota del examen aparece en sus calificaciones por bloque (Fase 3) con la retroalimentación.
- **Comodín de intento extra** (PLAN §9): suma un intento a ese alumno en un examen; usarlo queda en `audit_logs`.

## Qué no entra

Banco de preguntas aleatorio, orden aleatorio de preguntas u opciones, supervisión (cámara o bloqueo de pestaña), exportar resultados, preguntas con imagen (dependen de la Fase 2b).

## Permisos

- Crear, editar y calificar: el ponente dueño y el super admin.
- Presentar: alumno `active`, curso publicado, examen publicado y abierto.
- Ver resultados:
  - el alumno, solo los suyos;
  - el director y el super admin, todos.
- Las respuestas correctas nunca se envían al navegador antes de terminar el intento.

## Pruebas clave

- Tiempo:
  - se rechaza una respuesta y el envío después del tiempo más 30 s;
  - se acepta dentro de la gracia;
  - fuera de la ventana de fechas → 403.
- Intentos:
  - no se puede presentar más allá de `max_attempts`;
  - el comodín suma exactamente uno y queda en la bitácora.
- Autocalificación:
  - `single`, `true_false` y `multiple` todo o nada (incluido marcar de más);
  - las abiertas dejan el intento en `submitted` hasta calificarlas.
- Otro alumno no ve ni responde un intento ajeno (IDOR). El HTML del examen no contiene `is_correct`.
- La vista previa del examen para el ponente no crea intentos.
- Aceptación: un examen mixto se presenta y se califica de punta a punta, incluido el rechazo por tiempo vencido.

## Tareas (un commit cada una; auditoría de diseño en las de interfaz)

1. Modelos y Policy, con pruebas de reglas.
2. Constructor de exámenes.
3. Presentar examen (cronómetro y guardado).
4. Autocalificación y calificación manual de abiertas.
5. Nota en las calificaciones del alumno.
6. Comodín de intento extra.
7. Cierre: aceptación, casillas, seguridad y auditoría.

## Decisiones del dueño (2026-10-24)

1. **"Ayuda en el examen":** es el comodín de **un intento extra**, y solo se puede usar **con el examen abierto** (dentro de `available_from` / `available_until`).
2. **Se acaba el tiempo:** se **envía lo respondido**. El intento se cierra con lo guardado y se califica (automático y, si hay abiertas, queda pendiente del ponente).
3. **Cronómetro:** módulo JS propio en `resources/js` (sin paquetes nuevos, sin JS en línea). **El servidor valida el tiempo**: `started_at` + límite + 30 s de gracia.
4. **Mejor intento calificado:** se mantiene la regla del PLAN §5, también para el intento extra (confirmado el 2026-10-26).
5. **Calificación mínima (`passing_score`):** solo se muestra "aprobado / no aprobado"; no bloquea nada (confirmado el 2026-10-26).
6. **Benchmark P2:** cada respuesta se guarda al contestarla y el servidor envía lo guardado al acabarse el tiempo, aunque el navegador no mande nada; hay prueba con JS apagado.

## Esquema

Tablas existentes, más `reward_grants.used_exam_id` de `001_comodines.sql`. Instituciones (Fase 8a): `exams.course_id` ya lo cubre.
