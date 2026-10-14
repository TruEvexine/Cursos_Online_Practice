# Cursos el Mai — instrucciones para Claude Code

Plataforma de cursos en línea (Laravel 13 + MySQL local). Roles: `super_admin`, `director`, `ponente`, `alumno`.
Lee antes de trabajar: `docs/PLAN.md` (fases, permisos, reglas), `docs/DATABASE.md` (esquema), `docs/DESIGN.md` (diseño).

## Cómo trabajar

- **Ponytail siempre:** el cambio más pequeño que resuelva todo el problema; sin abstracciones, opciones ni dependencias que nadie pidió.
  Respuestas cortas que terminan con lo que se omitió o no se verificó, y el riesgo. Lógica no trivial (desbloqueo, calificación, permisos, dinero, seguridad) lleva su prueba.
- Una **fase** de `docs/PLAN.md` a la vez, en su propia rama. Al empezar: mini-especificación (qué entra, qué no) con `spec-driven-development` y `planning-and-task-breakdown`, y se confirma con el dueño del proyecto.
- Al terminar una fase: pruebas en verde, casillas de `docs/PLAN.md` marcadas, resumen de 3 líneas.
- Si algo de los documentos no coincide con la realidad o falta una decisión, **pregunta**; no inventes.
- No agregues paquetes de Composer/npm sin pedir permiso y decir para qué. No uses Livewire, Inertia, React, Vue ni Docker.

## Base de datos (reglas duras)

- `database/schema.sql` es la fuente de verdad. **No uses migraciones** (`php artisan migrate` está prohibido) ni crees tablas desde código.
- Un cambio de esquema = archivo nuevo `database/changes/NNN_descripcion.sql` + actualizar `schema.sql` + avisar para que el dueño lo aplique a mano.
- Conexión solo como `cursos_app` (sin DDL, nunca root). Credenciales **solo** en `.env`; `.env` nunca se sube al repo.
- Modelos Eloquent con `$fillable` explícito (nada de `$guarded = []`). Usuarios, cursos y avisos con `SoftDeletes`.
- Pruebas contra `cursos_el_mai_test` con `DatabaseTransactions` (no `RefreshDatabase`, que necesita migraciones).
- Fechas en UTC; mostrar en `America/Mexico_City`.

## Comandos

```bash
composer install && npm install          # primera vez
php artisan serve                        # app
npm run dev                              # Vite
php artisan reverb:start                 # solo desde la Fase 5 (chat en vivo)
php artisan test                         # pruebas
php artisan app:create-super-admin       # crea el super admin por consola (se programa en la Fase 0)
```

Usar el ejecutor de pruebas que traiga el esqueleto de Laravel 13; revisa `composer.json` antes de asumir cuál es.

## Código

- Convenciones de Laravel (controladores delgados, Form Requests para validar, Policies para permisos, componentes Blade). Identificadores en inglés; textos de interfaz en español (es-MX, trato de "tú"), escritos directamente en las vistas.
- **Permisos:** toda acción pasa por una Policy o Gate (propiedad del curso, inscripción activa, rol). El rol nunca viene del request. Ocultar un botón no es un permiso. Ver tabla en `docs/PLAN.md`, sección 3.
- **Desbloqueo de misiones:** reglas en `docs/PLAN.md`, sección 4. Se calcula en el servidor; una pantalla bloqueada responde 403.
- Contenido de usuario (temas, avisos): Markdown con `Str::markdown($texto, ['html_input' => 'strip', 'allow_unsafe_links' => false])`.

## Seguridad (lista de obligatorios; revisar antes de cerrar cada fase)

- [ ] Consultas con Eloquent/parámetros; **nunca** concatenar variables en SQL.
- [ ] En Blade, `{{ }}` siempre; `{!! !!}` solo con salida ya segura (p. ej. el Markdown renderizado sin HTML).
- [ ] CSRF en todo formulario/petición que modifique datos; peticiones del chat incluidas.
- [ ] Sin registro público; `is_active = 0` no puede iniciar sesión; límite de intentos de login; mensajes de error iguales para correo inexistente y clave incorrecta.
- [ ] Cookies de sesión `HttpOnly`, `SameSite=Lax`, `Secure` en producción; regenerar sesión al iniciar sesión.
- [ ] Subidas: lista blanca de tipos (pdf, docx, pptx, xlsx, png, jpg, webp; **sin** html, svg, zip, ejecutables), tamaño máximo, nombre aleatorio, almacenamiento **privado**, descarga por un controlador que autoriza. Nunca usar el nombre original como ruta.
- [ ] Chat: texto plano, máx. 1000 caracteres, límite de ritmo por usuario, canal privado autorizado (inscrito, dueño del curso, director/super admin en lectura).
- [ ] Enlaces externos: solo `https://`; `rel="noopener noreferrer"`.
- [ ] Sin datos sensibles en logs ni en la bitácora (nunca contraseñas ni tokens).
- [ ] Acciones de calificar, quitar alumno y cambiar roles se registran en `audit_logs`.

## Skills (en `.claude/skills/`, fijadas en `docs/PLAN.md`, sección 7)

`ponytail` (siempre) · `security-and-hardening` (auth, subidas, chat, permisos, revisión final) · `spec-driven-development` y `planning-and-task-breakdown` (inicio de fase) ·
`ui-ux-pro-max` y `design-motion-principles` (interfaz y movimiento; si chocan con `docs/DESIGN.md`, gana `docs/DESIGN.md`) · `taste-skill` solo para la página pública.
No instales otras skills ni ejecutes scripts de instalación de los repos de skills.

## Diseño (resumen; detalle en `docs/DESIGN.md`)

Claro por defecto con tema oscuro opcional; azul `#1F3A8A` para acciones, ámbar solo para logros; Source Serif 4 + Source Sans 3 autohospedadas;
WCAG 2.2 AA; `prefers-reduced-motion` respetado; tablas que se vuelven tarjetas en móvil; sin degradados morados ni efectos de cristal.

## Límites

- No toques `database/00_database_and_user.sql` (contiene un marcador de clave; el dueño lo ejecuta).
- No subas archivos de usuarios, `.env`, volcados de BD ni claves al repositorio.
- Antes de cualquier acción destructiva (borrar datos, `git reset --hard`, sobrescribir archivos), pregunta.
