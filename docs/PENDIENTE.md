# Pendiente (pausa pedida por el dueño, 2026-10-26)

Rama de trabajo: `feature/diseno-v2` (sale de `main`, que ya incluye las Fases 2 y 3). No se ha subido a GitHub ni mezclado.

## Terminado
- **Bloque 1:** `app:seed-demo` imprime la tabla de cuentas y la guarda en `RUN.txt` (sin contraseñas).
- **Bloque 2:** límite de intentos del registro, pestañas del curso en cuadrícula en móvil, error de entrega solo bajo su tarea.
- **Bloque 3:** `main` = `fase-0-1` → `fase-2` → `fase-3` (fast-forward); pruebas en verde en cada paso (31, 81 y 113). `main` y `feature/fase-3-misiones` están subidas.
- **Bloque 4, capas:** (a) tokens, (b) portada pública, (c) panel del alumno y mapa, (d) inicio del ponente. La dirección visual v2 está en `docs/DESIGN.md`.
- Pruebas: 116 de 116.

## A medias
- **Capa (d), panel de ponente:** solo el inicio y la lista de cursos están en v2. Faltan el constructor, la bandeja de entregas, los formularios (tema, tarea, curso) y las pantallas de alumnos y materiales.

## Sigue, en este orden
1. Terminar (d): llevar al ponente la misma escala de títulos y las tarjetas del resto de pantallas; capturas 375/768/1280 y oscuro antes del commit.
2. **(e) Imágenes:** las portadas SVG por código ya existen (`components/course-cover`). Falta usarlas en el catálogo (`catalog/index`, `catalog/show`) y reemplazar las 4 capturas de `public/img/portada/` por capturas nuevas con el diseño v2 (datos demo). Anotar la fuente de cada imagen (todas son propias o generadas por código).
3. **Bloque 5:** primer despliegue en Railway (`docs/DEPLOY.md`) y `docs/DEMO.md` (guion de 5 minutos).
4. Mezclar `feature/diseno-v2` en `main` solo con tu visto bueno.

## No verificado
- Con la base `cursos_el_mai` real no se ha corrido `app:seed-demo` (solo en pruebas y en `cursos_el_mai_test` con transacción revertida).
- La portada se ve bien con el catálogo vacío, pero no se probó con más de 3 cursos destacados.
- `npm run build` y las capturas se hicieron en Firefox; no se corrió el inspector de taste-skill (no hay Chrome).
- Falta aplicar `database/changes/001_comodines.sql` (comodines, Fase 3 tarea 7, pendiente).
