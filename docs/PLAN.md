# Plan — Gaining Grades

Plataforma web de cursos en línea con cuatro roles: **alumno**, **ponente**, **director** y **super admin**.
Estado: planeación (repo vacío). Última revisión: 2026-10-13.

## 1. Stack y por qué

| Decisión | Elección | Razón |
|---|---|---|
| Backend | **Laravel 13** (PHP 8.3+) | Es PHP, y trae lo difícil ya resuelto: sesiones, CSRF, hash de contraseñas, validación, políticas de permisos, subida de archivos. Soporte de seguridad hasta marzo 2028. |
| Base de datos | **MySQL local**, SQL a mano | Requisito tuyo. `database/schema.sql` manda; sin migraciones. |
| Autenticación | **Laravel Fortify** (sin interfaz propia) + vistas Blade nuestras | Login, recuperar contraseña y verificación de correo con límite de intentos, sin imponer el diseño de un starter kit. |
| Interfaz | **Blade + Tailwind CSS + Alpine.js** (Vite) | Casi todo PHP del lado del servidor; JS solo donde hace falta. Sin React/Vue/Livewire: menos piezas. |
| Contenido de temas y avisos | **Markdown**, renderizado con HTML desactivado | Evita XSS sin añadir un editor pesado ni un sanitizador extra. |
| Chat en vivo | **Laravel Reverb** (WebSockets en PHP) | Se queda en PHP. Es un proceso aparte (`php artisan reverb:start`). Eventos con `ShouldBroadcastNow` para no necesitar cola. |
| Videollamada | **Jitsi vía JaaS (8x8)** con su iFrame API, o `meeting_url` externo (Meet/Zoom) | `meet.jit.si` ya no permite incrustar. Programar solo el chat y dejar el video a un servicio. **Verificar en un spike de 1 hora** (cuenta JaaS, límites del plan gratis, token de moderador) antes de la Fase 5. |
| Sesiones / caché / colas | `file` / `file` / `sync` | Cero tablas extra para un proyecto chico. |
| Correo | `log` en desarrollo; SMTP real en producción | Invitaciones, verificación y recuperación de contraseña. |

Idioma: textos de la interfaz en español (es-MX, trato de "tú"); identificadores de código en inglés.

## 2. Supuestos a confirmar (cámbialos antes de la Fase 1 si no coinciden)

1. **Sin registro público.** El super admin crea directores y ponentes; el ponente agrega alumnos por correo (si no existe, se crea la cuenta y se le envía un enlace para fijar su contraseña).
2. **Director global:** ve a todos los ponentes y alumnos. Si habrá varios directores con grupos distintos, hay que añadir una tabla de asignación.
3. **Un ponente por curso** (sin co-ponentes).
4. Un alumno puede estar en varios cursos; un curso, en varios alumnos.
5. El examen "generado" se arma con preguntas que el ponente escribe (sin banco aleatorio todavía).
6. Sin pagos ni certificados en esta versión.

## 3. Roles y permisos

| Acción | Super admin | Director | Ponente | Alumno |
|---|:-:|:-:|:-:|:-:|
| Crear/editar/desactivar usuarios y asignar roles | Sí | — | — | — |
| Ver a todos los alumnos y ponentes (progreso, actividad, promedios) | Sí | Sí (solo lectura) | — | — |
| Ver bitácora y panel del sistema | Sí | Sí (solo lectura) | — | — |
| Crear/editar/archivar sus cursos | Sí (todos) | — | Sí (los suyos) | — |
| Agregar/quitar alumnos de un curso | Sí | — | Sí (sus cursos) | — |
| Bloques, temas, tareas, materiales | Sí | Ver | Sí (sus cursos) | Ver lo desbloqueado |
| Entregar tareas | — | — | — | Sí (inscrito) |
| Calificar tareas y exámenes | Sí | — | Sí (sus cursos) | — |
| Crear exámenes / presentarlos | Sí / — | — / — | Sí / — | — / Sí |
| Ver calificaciones | Todas | Todas | Las de sus cursos | Solo las suyas, lectura |
| Avisos del curso | Sí | Ver | Crear/editar (sus cursos) | Ver |
| Clase en vivo: activar y moderar chat | Sí | Ver | Sí (sus cursos) | Escribir según el modo |
| Editar su propio perfil | Sí | Sí | Sí (completo) | Sí (limitado: foto, bio, intereses, color) |

Toda acción se valida en el servidor con **Policies** (propiedad del curso, inscripción activa). Ocultar un botón no cuenta como permiso.

## 4. Reglas de misiones (desbloqueo)

- Curso con `unlock_mode = sequential`: los temas se recorren en orden (bloque, luego posición). Un tema se abre cuando todos los temas **obligatorios** anteriores están completados. Un bloque se abre cuando se abre su primer tema.
- Curso con `unlock_mode = free`: todo abierto desde el inicio; el progreso solo se muestra.
- Un tema queda **completado** cuando el alumno lo marca como visto **y** cada tarea obligatoria del tema tiene entrega en estado `submitted` o `graded` (si el ponente la devuelve a `returned`, deja de contar).
- Un examen ligado a un bloque (`exams.block_id`) solo se abre cuando ese bloque está completo.
- La app escribe `topic_progress`; nunca se calcula "al vuelo" desde el navegador. Una pantalla bloqueada responde 403 aunque se pegue la URL.

## 5. Reglas de exámenes

- Un intento por defecto (`max_attempts`). El límite de tiempo se valida en el servidor (hora de inicio + `time_limit_minutes`, con 30 s de gracia).
- `single` y `true_false`: puntos completos si la opción elegida es correcta. `multiple`: todo o nada. `open`: calificación manual del ponente.
- El intento pasa a `graded` cuando no quedan preguntas abiertas sin calificar; si no, queda `submitted`.
- Nota del examen en la vista de calificaciones: el mejor intento calificado.

## 6. Fases

Cada fase termina con la app funcionando, sin código a medias. Al inicio de cada una, el agente escribe una mini-especificación (qué entra, qué no) y la confirma contigo.

### Fase 0 — Preparación
- [x] Base de datos creada por ti (comandos en `docs/DATABASE.md`) y clave de `cursos_app` cambiada.
- [x] Esqueleto de Laravel 13 en la raíz del repo (créalo en carpeta temporal y muévelo sin sobrescribir `database/schema.sql`, `docs/` ni `CLAUDE.md`).
- [x] `.env` con `DB_USERNAME=cursos_app`, `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=log`; `.env` en `.gitignore`. `phpunit.xml` apunta a `cursos_el_mai_test`.
- [x] Skills instaladas en `.claude/skills/` (sección 7).
- [x] Comando `php artisan app:create-super-admin` (pide correo y clave por consola; no hay clave en el repo).
- **Hecho cuando:** `php artisan serve` abre la página de inicio y una consulta a la tabla `roles` devuelve 4 filas.

### Fase 1 — Acceso y roles
- [x] Fortify: login, logout, recuperar contraseña, verificación de correo; registro público desactivado; cuentas con `is_active = 0` no entran.
- [x] Middleware de rol, layout con navegación según rol, inicio vacío por rol. *(Las Policies se crean en la Fase 2 con los cursos: aún no hay recursos con dueño.)*
- [x] Perfil: ver y editar el propio (ver tabla de permisos); subida de foto segura.
- [x] Página pública mínima con botón de acceso.
- **Hecho cuando:** los 4 roles inician sesión y cada uno solo ve lo suyo; pruebas de permisos en verde.

### Fase 2 — Cursos y contenido
- [ ] Ponente: CRUD de cursos, bloques, temas (Markdown) y materiales (archivo/enlace/video).
- [ ] Agregar y quitar alumnos (invitación por correo; "quitar" = `removed`).
- [ ] Alumno: "Mis cursos" y vista del curso (aún sin bloqueos).
- **Hecho cuando:** un ponente arma un curso completo y un alumno inscrito lo lee; otro alumno recibe 403.

### Fase 3 — Misiones, tareas y calificaciones
- [ ] Reglas de desbloqueo (sección 4) con pruebas, mapa de misiones.
- [ ] Tareas y entregas (texto y/o archivo), edición de la entrega hasta que se califica.
- [ ] Ponente: bandeja de pendientes, calificar con retroalimentación, devolver.
- [ ] Alumno: calificaciones en solo lectura + promedio ponderado.
- **Hecho cuando:** un alumno entrega, el ponente califica, el siguiente bloque se abre y la nota aparece en su vista.

### Fase 4 — Exámenes
- [ ] Constructor de exámenes, publicación con ventana de fechas y tiempo límite.
- [ ] Presentar examen (cronómetro, guardado de respuestas), autocalificación y calificación manual de abiertas.
- **Hecho cuando:** un examen mixto se presenta y califica de punta a punta, incluido el rechazo por tiempo vencido.

### Fase 5 — Avisos y clase en vivo
- [ ] Avisos por curso (crear, editar, fijar, borrador/publicado).
- [ ] Sesión en vivo: video incrustado (o `meeting_url`) + chat con Reverb; modos `off` / `open` / `moderated`; el ponente aprueba, oculta y cambia de modo en vivo.
- **Hecho cuando:** ponente y dos alumnos chatean en los tres modos; un mensaje oculto desaparece para todos al instante.

### Fase 6 — Monitoreo y administración
- [ ] Director: tablero de ponentes (cursos, último acceso, pendientes por calificar) y de alumnos (progreso, promedio, último acceso), con detalle por curso; solo lectura.
- [ ] Super admin: gestión de usuarios y roles, bitácora (`audit_logs`) con filtros.
- **Hecho cuando:** el director ve datos reales sin poder modificar nada (verificado con pruebas).

### Fase 7 — Endurecimiento y entrega
- [ ] Revisión de seguridad con la skill `security-and-hardening` sobre auth, subidas, chat y permisos.
- [ ] Revisión de accesibilidad y movimiento (`design-motion-principles`, modo audit).
- [ ] Respaldo (`mysqldump` + carpeta de archivos privados) y guía de despliegue con la plataforma que elijas.
- **Hecho cuando:** lista de seguridad de `CLAUDE.md` sin pendientes y respaldo restaurado con éxito en una máquina limpia.

## 7. Skills (auditadas el 2026-10-13, fijadas a un commit)

Se copian carpetas concretas a `.claude/skills/` en una revisión exacta. No se ejecutan scripts de instalación de esos repos,
ni `npx`, ni el kit `stack/` de UI UX Pro Max.

| Skill | Repo @ commit | Carpeta del repo origen → se copia a `.claude/skills/<nombre>/` | Cuándo se usa |
|---|---|---|---|
| ponytail | `DietrichGebert/ponytail` @ `2961b52fc1fe6a5a87c7a959a79512e5c2f3ffe5` | `skills/ponytail/` | Siempre, en todo código. |
| security-and-hardening | `addyosmani/agent-skills` @ `aeac41d4d005999432bb7312f7ff06383a91d59d` | `skills/security-and-hardening/` | Auth, subidas, chat, permisos; revisión final. |
| spec-driven-development | (mismo repo y commit) | `skills/spec-driven-development/` | Mini-especificación al inicio de cada fase. |
| planning-and-task-breakdown | (mismo repo y commit) | `skills/planning-and-task-breakdown/` | Dividir cada fase en tareas. |
| ui-ux-pro-max | `nextlevelbuilder/ui-ux-pro-max-skill` @ `7246805992a42b7bba37d4cac83b01416f0c6c44` | `.claude/skills/ui-ux-pro-max/` (requiere Python 3) | Pantallas, tablas, gráficas, formularios. |
| design-motion-principles | `kylezantos/design-motion-principles` @ `4a9ca879f24a361f4dca4174fe2da0f67b5ddee3` | `skills/design-motion-principles/` | Mapa de misiones, transiciones, auditoría de movimiento. |
| taste-skill *(opcional)* | `Leonxlnx/taste-skill` @ `a60a36dc2bb3bb77ccf17f2f62041f5ca8a458b9` | `skills/taste-skill/` | Solo la página pública; no sirve para pantallas de la app. Necesita Node 22+ y Chrome. |

No se instalan: **Graphify** (hooks de git y de Claude Code, y archivos grandes; reconsiderar si el repo pasa de ~150 archivos) y **awesome-claude-design**
(no es una skill: es una lista de `DESIGN.md` de marcas para usar como referencia de estilo).

## 8. Riesgos y lo que queda fuera

- **Videollamada:** depende de un servicio externo; el spike de la Fase 5 decide Jitsi/JaaS o enlace externo.
- **Chat en vivo:** Reverb necesita un proceso siempre encendido; en hosting compartido no corre. Definir dónde se despliega antes de la Fase 5.
- **Subidas:** PHP por defecto limita archivos a ~2 MB; subir `upload_max_filesize` y `post_max_size` en el servidor.
- **Esquema probado en MariaDB 10.11 y en MySQL 8.0.46** (el servidor local real; ver `docs/DATABASE.md`).
- **Fuera de esta versión:** banco de preguntas aleatorio, certificados, pagos, notificaciones push, app móvil, co-ponentes, directores por grupo, exportar a CSV/PDF, XP e insignias (el mapa de misiones muestra progreso, sin puntos).
