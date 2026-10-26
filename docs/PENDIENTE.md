# Pendiente (pausa pedida por el dueño, 2026-10-26)

Rama de trabajo: `feature/diseno-v2` (sale de `main`, que ya incluye las Fases 2 y 3). No se ha subido a GitHub ni mezclado.

## Terminado
- **Bloques 1 a 4 completos**, con las capas (a) a (e) del rediseño; pruebas 116 de 116.
- **Bloque 5:** `docs/DEMO.md` (guion de 5 minutos) escrito.
- Rama `feature/diseno-v2` subida a GitHub; **sin mezclar en `main`** (espera tu visto bueno).

## Sigue
1. **Primer despliegue en Railway** (lo haces tú con tu cuenta; los pasos están en `docs/DEPLOY.md`). No se hizo desde aquí: no tengo acceso a Railway.
2. Aplicar a mano `database/changes/002_catalogo.sql` en `cursos_el_mai`, `cursos_el_mai_test` y Railway, y fijar `MAIL_FROM_ADDRESS` en el `.env`.
3. Comodines (Fase 3, tarea 7): requieren aplicar `001_comodines.sql`.
4. Mezclar `feature/diseno-v2` en `main` con tu aprobación.

## No verificado
- `app:seed-demo` no se ha corrido contra la base real `cursos_el_mai`.
- El constructor, la bandeja y los formularios solo recibieron la escala de títulos v2; no un rediseño propio.
- No corrió el inspector de taste-skill (no hay Chrome).
- El despliegue en Railway sigue sin probarse (ver `docs/DEPLOY.md`, marcas "sin comprobar").
