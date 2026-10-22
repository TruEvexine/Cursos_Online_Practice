# Skills del proyecto

Lista oficial y criterios: `docs/PLAN.md`, sección 7. Copiadas a mano desde sus repos y auditadas el 2026-10-13:
sin hooks, sin instaladores (`npx`, `pip`, plugins), sin llamadas de red ocultas.
Para actualizar una skill: clonar en `/tmp`, revisar el diff contra el commit fijado y copiar a mano.

| Carpeta | Origen @ commit | Notas de la auditoría |
|---|---|---|
| `ponytail` | DietrichGebert/ponytail @ `2961b52` | Solo el SKILL.md. Se omitieron los hooks del plugin (inyectan reglas en cada prompt y escriben en `~/.claude`). |
| `security-and-hardening`, `spec-driven-development`, `planning-and-task-breakdown` | addyosmani/agent-skills @ `aeac41d` | Checklists compartidos en `.claude/references/`. |
| `ui-ux-pro-max` | nextlevelbuilder/ui-ux-pro-max-skill @ `7246805` | Solo el core: Python stdlib y búsqueda en CSV locales, sin red. Sin tests ni el kit `stack/`. |
| `design-motion-principles` | kylezantos/design-motion-principles @ `4a9ca87` | Solo markdown y HTML, sin scripts. |
| `impeccable` | pbakaus/impeccable @ `69c34b7` (copiada el 2026-10-22) | Carpeta `.claude/skills/impeccable/` completa por decisión del dueño. **No ejecutar `scripts/`**: el lanzador `scripts/impeccable` descarga un binario de GitHub releases en la primera ejecución. Se usan solo `SKILL.md` y `reference/*.md`. |
| `taste-skill` | Leonxlnx/taste-skill @ `a60a36d` | Solo la página pública. `scripts/inspect.mjs` abre un Chrome local; `scripts/assets.mjs` consulta las APIs públicas de imágenes de Openverse, Wikimedia y el Art Institute of Chicago. |

Si una skill de diseño choca con `docs/DESIGN.md`, gana `docs/DESIGN.md`.
