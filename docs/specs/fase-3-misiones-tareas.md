# Fase 3 — Misiones, tareas y calificaciones (BORRADOR con decisiones del 2026-10-24; pendiente de visto bueno)

Rama `feature/fase-3-misiones` (sale de `feature/fase-2-cursos` una vez aprobada). Tablas que ya existen: `tasks`, `submissions`, `topic_progress`, vista `v_gradebook`.
Comodines: necesitan `database/changes/001_comodines.sql` aplicado a mano antes de la tarea 7.

## Qué entra

- **Desbloqueo** (PLAN §4), calculado en el servidor:
  - `sequential`: un tema se abre cuando todos los temas obligatorios anteriores están completos.
  - `free`: todo abierto.
  - Si se entra a un tema bloqueado (aunque se pegue la URL), responde 403.
  - **Mapa de misiones** (DESIGN §6) en la vista del curso.
- **Completar un tema:** "Marcar como visto" + cada tarea obligatoria del tema con entrega `submitted` o `graded`. `returned` deja de contar.
  Solo escribe `topic_progress` un alumno inscrito `active`, nunca la vista previa.
- **Tareas** en el constructor (por tema): título, instrucciones (Markdown), fecha límite opcional, puntos máximos, peso, obligatoria, acepta entregas tarde.
- **Entregas** del alumno:
  - texto y/o archivo con las mismas reglas que los materiales (lista blanca, 10 MB, privado, nombre aleatorio, descarga autorizada);
  - se puede editar hasta que se califica; si el ponente la devuelve (`returned`), se puede editar otra vez.
  - Fuera de plazo: se rechaza si la tarea no acepta entregas tarde; si las acepta, se marca "tarde".
- **Ponente:** bandeja de pendientes (todas sus entregas `submitted`, la más antigua primero); calificar con nota y retroalimentación; devolver.
  Calificar queda en `audit_logs`.
- **Alumno:**
  - calificaciones en solo lectura, por bloque, con la retroalimentación junto a la nota;
  - marca "por reforzar" en las notas bajas;
  - promedio ponderado (consulta de DATABASE.md);
  - "próxima entrega" en Mis cursos.
- **Comodines** (PLAN §9), al final de la fase y solo para tareas:
  - el ponente configura reglas por curso;
  - se conceden al calificar;
  - el alumno ve su saldo y lo usa en "omitir tarea", "puntos extra" o "prórroga de 24 h";
  - el ponente puede revocarlos;
  - todo queda en `audit_logs`.

## Qué no entra

Exámenes (Fase 4), avisos y chat (Fase 5), tablero del director (Fase 6), entregas en grupo, revisión entre pares, rúbricas, notificaciones por correo de calificación.

## Permisos

- Entregar: alumno con inscripción `active` en un curso `published`, con el tema desbloqueado.
- Calificar y devolver: el ponente dueño y el super admin.
- Ver calificaciones:
  - el alumno, solo las suyas;
  - el ponente, las de sus cursos;
  - el director y el super admin, todas.
- El director nunca escribe.

## Pruebas clave

- Desbloqueo:
  - modo secuencial con temas opcionales intermedios;
  - una tarea `returned` vuelve a bloquear lo siguiente;
  - el modo libre no bloquea;
  - un tema bloqueado responde 403.
- Entregas:
  - se pueden editar hasta que se califican, y otra vez tras `returned`;
  - una entrega tarde se rechaza o se marca, según `allow_late`;
  - las subidas siguen las reglas de los materiales;
  - un alumno no ve ni descarga la entrega de otro (IDOR).
- Calificación:
  - nota entre 0 y el máximo;
  - queda en la bitácora;
  - el promedio ponderado coincide con la consulta SQL.
- La vista previa sigue sin escribir `topic_progress` ni `submissions`.
- Comodines:
  - racha de N con nota ≥ X % concede uno;
  - una tarea tarde, omitida o con nota baja reinicia la cuenta;
  - las tareas sin fecha no cuentan;
  - se respetan los topes;
  - revocar y usar quedan en la bitácora.
- Aceptación: el alumno entrega, el ponente califica, el siguiente bloque se abre y la nota aparece en la vista del alumno.

## Tareas (un commit cada una; auditoría de diseño en las de interfaz)

1. Servicio de desbloqueo con pruebas, más "marcar como visto".
2. Mapa de misiones en la vista del curso.
3. Tareas en el constructor.
4. Entregas del alumno.
5. Bandeja y calificación.
6. Calificaciones del alumno y "próxima entrega".
7. Comodines de tareas.
8. Cierre: aceptación, casillas, seguridad y auditoría.

## Decisiones del dueño (2026-10-24)

1. **Omitir tarea:** la tarea se **excluye** del promedio de ese alumno.
   La consulta del promedio (`v_gradebook` o su equivalente en Eloquent) debe saltar las tareas con un comodín `skip_task` usado.
2. **Topes:** los define el ponente por curso. Por defecto, **3 comodines disponibles** a la vez por alumno y **5 concedidos** en total por alumno en el curso
   (`courses.reward_max_available` y `courses.reward_max_granted` en `001_comodines.sql`).
   *Interpretación mía de "5 concedidos por curso": por alumno; si era el total del curso entre todos los alumnos, dímelo.*
3. **Puntos extra:**
   - se aplican a una tarea **ya calificada** que elige el alumno;
   - suman hasta el **10 % del máximo** de esa tarea, sin pasar del máximo;
   - lo aplicado se guarda en `reward_grants.bonus_points`.
4. **Prórroga de 24 h:** solo **antes** del vencimiento.
5. **Racha:** cuenta la **primera** entrega, con la hora del servidor (UTC).
6. **"Por reforzar":** menos de **70 %** del máximo.
7. **Alumno `completed`:** ya no entrega (solo lectura).
8. **Notas:** se muestran en puntos y porcentaje ("8/10 · 80 %").

## Esquema

- Desbloqueo, tareas, entregas y calificaciones usan tablas que ya existen.
- Los comodines necesitan `database/changes/001_comodines.sql`, ajustado a estas decisiones: dos columnas en `courses` (`reward_max_available`, `reward_max_granted`), `reward_rules` y `reward_grants`.
- Ese cambio **no está aplicado ni copiado a `schema.sql`**. Lo aplicas tú antes de la tarea 7; al aplicarlo, actualizo `schema.sql`.
- Instituciones (Fase 8a): nada que agregar aquí. Todo cuelga del curso (ver PLAN, Fase 8).
