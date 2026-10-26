# Fuente de las imágenes

Ninguna imagen viene de sitios de terceros ni tiene licencia ajena.

| Imagen | Fuente |
|---|---|
| Portadas de curso (`resources/views/components/course-cover.blade.php`) | Generadas por código (SVG propio); color y forma según el id del curso. |
| `public/img/portada/constructor.webp`, `alumnos.webp`, `temario.webp`, `tema.webp` | Capturas de la propia app con los datos de `php artisan app:seed-demo` (personas y cursos ficticios), a 1280 px, diseño v2. |
| `public/favicon.svg` | Dibujo propio (un nodo del mapa de misiones). |

Cómo regenerar las capturas: sembrar la demo en `cursos_el_mai_test` dentro de una transacción revertida, renderizar las pantallas y capturarlas a 1280 px (el procedimiento está en el historial de `docs/PENDIENTE.md`).
