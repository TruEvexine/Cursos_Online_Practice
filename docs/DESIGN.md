# Diseño — Gaining Grades

Objetivo: moderno, responsivo y **formal/educativo**. Claro por defecto (se estudia mejor de día), con tema oscuro opcional.
Diferente de tu práctica anterior (oscuro con brillos): aquí manda la legibilidad y la calma; la "gracia" vive en un solo lugar, el **mapa de misiones**.

## Referencias

Solo inspiración: no se copian logos, textos, ilustraciones ni identidad. De sus colores y tipografías no tenemos datos, así que no se toman de ahí.

- **Coursera:** temario por módulos con resultados esperados, fechas límite visibles, navegación con progreso. Evitar: revisión anónima entre pares.
- **Duolingo:** temas cortos, poder repasar lo ya hecho, ver hitos y áreas por reforzar. Evitar: rankings, "vidas", rachas diarias, anuncios.
- **Domestika:** materiales descargables y lecciones bien presentadas. Nuestra ventaja: el ponente califica y deja retroalimentación visible.
- **Santander Open Academy:** flujo simple; la ficha del curso muestra fechas y requisitos.
- **Fundación Volta** (estudiagratis.fundacionvolta.com): catálogo por áreas con ícono, tarjetas con descripción corta. El certificado con QR queda fuera de esta versión.
- **Aprende.mx** (cursos.aprende.gob.mx) y **aprende.org:** tarjetas con portada, institución y fecha de inicio; "saltar al contenido"; tono institucional.

Lo que se adopta en el producto:

- Temario con resultados esperados por bloque (`blocks.description`).
- Fecha de entrega y "próxima entrega" visibles.
- Calificaciones por bloque con "por reforzar" (notas bajas).
- Retroalimentación del ponente junto a la nota.
- Tarjetas de curso con portada y fechas.

## Dirección visual v2 (2026-10-26)

La v1 se veía como el esqueleto: gris frío, tarjetas iguales y sin imágenes. La v2 toma de cada referencia una sola cosa:
- de Domestika y Aprende, el papel cálido y las portadas;
- de Coursera, la claridad del temario y las fechas;
- de Duolingo, el avance que se ve y se celebra sin juegos;
- de Volta y Santander, las tarjetas de catálogo con portada, fechas y descripción corta.

- **Paleta: papel cálido + azul institucional + ámbar solo para logros.**
  - Fondo `#F7F6F2` (papel), superficies blancas y tinta azul marino `#16213A`.
  - Las acciones siguen en `#1F3A8A` (CLAUDE.md).
  - *Por qué:* el gris frío se sentía de sistema administrativo; el papel cálido se siente de libro y de aula, sin perder el tono institucional. Todo cumple AA (ver tokens).
- **Tipografía: las mismas dos familias, con más contraste de escala.**
  - Source Serif 4 para títulos y Source Sans 3 para la interfaz y el texto.
  - Los títulos de página pasan a 36–56 px con interlineado apretado.
  - La lectura de temas sube a 18 px.
  - *Por qué:* la personalidad sale del tamaño y el ritmo, no de una tercera fuente. Siguen autohospedadas y sin peso extra.
- **Imágenes: portadas generadas por código, nunca fotos de terceros.**
  - Cada curso tiene una portada SVG propia, determinada por su id.
  - El color sale de 5 tonos oscuros de la paleta y el motivo es **el camino de nodos del mapa de misiones** (el "un motivo" de la portada pública), con el título en HTML encima.
  - Las capturas de la portada pública son de la app real con datos demo.
  - *Por qué:* da identidad a cada curso sin licencias ni descargas, y repite la única forma de la marca.
- **Forma:**
  - radio de tarjetas 14 px, de campos y botones 8 px;
  - superficies separadas por un escalón de tono y borde suave, sin sombras en reposo.
- **Movimiento:** sin cambios (sección 7). Lo único nuevo es el realce de 150 ms al pasar sobre una tarjeta de curso.

## 1. Principios

1. **Lectura primero.** Contenido de curso en columna de 65–75 caracteres, tipografía generosa.
2. **Un solo acento.** Azul institucional para acciones; ámbar solo para logros y misiones.
3. **Jerarquía por rol.** Cada rol ve primero lo que necesita hacer hoy (entregar, calificar, revisar).
4. **Movimiento con propósito.** Se anima el cambio de estado (desbloqueo, envío, mensaje nuevo); nada decora en bucle.
5. **Sin trucos genéricos:** nada de degradados morados, tarjetas de cristal ni brillos de fondo.

## 2. Tokens (variables CSS; mapear en la configuración de Tailwind)

Contrastes calculados (WCAG 2.2): texto ≥ 4.5:1, bordes de campos ≥ 3:1.

```css
:root {
  /* Superficies */
  --canvas: #F7F6F2;   --surface: #FFFFFF;   --surface-sunken: #EFEDE7;   /* v2: papel cálido */
  --border: #E4E1D8;   --border-strong: #6B6A63;   /* campos de formulario: 5.4:1 */
  /* Texto */
  --ink: #16213A;      /* 16.0:1 sobre surface; 14.8:1 sobre canvas */
  --ink-muted: #4B5263;/* 7.8:1 sobre surface; 7.2:1 sobre canvas */
  /* Marca */
  --primary: #1F3A8A;  --primary-hover: #182E6E;  --primary-soft: #E7ECF8;   /* texto blanco sobre primary: 10.3:1 */
  --accent: #F59E0B;   --accent-ink: #92400E;     /* relleno ámbar con texto --ink: 8.3:1; texto ámbar usa --accent-ink: 7.1:1 */
  /* Estado (texto sobre surface) */
  --success: #15803D;  --success-ink: #166534;  --success-soft: #E7F5EC;  /* badge: success-ink sobre soft 6.3:1 */
  --warning: #B45309;  --warning-soft: #FDF1DC;  --warning-ink: #92400E;
  --danger:  #B91C1C;  --danger-soft:  #FBEAEA;
  --info:    #0369A1;
  --focus: #1F3A8A;    /* anillo 2 px + 2 px de separación */
}
:root[data-theme="dark"] {
  --canvas: #0D1322;   --surface: #141C30;   --surface-sunken: #10172A;
  --border: #22304A;   --border-strong: #6B7A99;  /* 4.0:1 */
  --ink: #E6EAF2;      --ink-muted: #9AA7BD;       /* 14.4:1 y 7.1:1 */
  --primary: #7FA8FF;  --primary-hover: #A3C0FF;  --primary-soft: #1B2A4D;  /* texto --canvas sobre primary: 8.0:1 */
  --accent: #FBBF24;   --accent-ink: #FBBF24;
  --success: #4ADE80;  --danger: #F87171;  --focus: #7FA8FF;
}
```

El tema sigue `prefers-color-scheme` y se puede fijar desde el perfil (guardado en el navegador, no en BD).
Regla: **nunca** colocar texto ámbar sobre blanco (usar `--accent-ink`); nunca comunicar un estado solo con color (siempre icono o texto).

## 3. Tipografía y espaciado

- Títulos: **Source Serif 4**. Interfaz y cuerpo: **Source Sans 3**. Autohospedadas con `@fontsource` (sin Google Fonts: privacidad y funciona sin internet).
- Escala v2: 14 · 16 (base) · 18 (lectura de temas) · 22 · 28 · 36 · 48–56 (títulos de página y portada). Interlineado 1.65 en lectura, 1.15 en títulos grandes. Números de tablas con `tabular-nums`.
- Espaciado base de 4 px (4, 8, 12, 16, 24, 32, 48, 64). Radios v2: campos y botones 8 px, tarjetas 14 px, píldoras 999 px. Sombra solo en menús y modales.
- Contenedor de contenido: máx. 1120 px; lectura de curso: máx. 72ch.

## 4. Estructura y responsividad

- **App shell:** barra lateral (≥ 1024 px) con navegación por rol; en pantallas menores, barra superior con menú y el contenido a ancho completo. Migas de pan en todas las páginas internas.
- Puntos de corte: 640 · 768 · 1024 · 1280. Se diseña primero para móvil.
- Tablas: en < 768 px cada fila se vuelve una tarjeta con etiquetas (nada de scroll horizontal para datos clave).
- Áreas táctiles ≥ 44 px. Un solo botón primario por vista.

### Pantallas de inicio por rol

| Rol | Primera pantalla |
|---|---|
| Alumno | "Mis cursos": tarjetas con progreso, **próxima entrega** destacada y avisos recientes. |
| Ponente | Resumen: cursos, **pendientes por calificar** (conteo + enlace), próxima clase en vivo, acceso rápido a "Nuevo curso". |
| Director | Tablero: filtros por ponente/curso; tabla de alumnos (progreso, promedio, último acceso) con marca de "sin actividad en 14 días" (ícono + texto). |
| Super admin | Usuarios (buscar, rol, activo/inactivo), bitácora reciente y estado del sistema. |

## 5. Componentes (Blade components en `resources/views/components/`)

Botón (primario, secundario, fantasma, peligro, cargando) · campo de texto/área/select con etiqueta y error · selector de archivo con vista previa y límites visibles ·
tarjeta · tarjeta de curso · insignia de estado · tabla responsiva · pestañas · modal y panel lateral · aviso emergente (toast) · estado vacío (siempre con acción) ·
barra de progreso · avatar · migas · **mapa de misiones** · paso de examen (cronómetro + navegación entre preguntas) · **panel de chat**.

Íconos: Lucide o Heroicons como SVG en línea (un solo conjunto).

## 6. Mapa de misiones (la pieza distintiva)

Cada bloque es una "misión"; cada tema, un nodo en un camino vertical (en móvil) o en zigzag suave (escritorio).

| Estado | Apariencia | Comportamiento |
|---|---|---|
| Bloqueado | Nodo gris (`--surface-sunken`), candado, texto `--ink-muted` | `aria-disabled`; al tocar, mensaje claro: "Completa «Tema X» para desbloquear". |
| Disponible | Anillo `--primary`, número del tema | Enlace al tema. |
| En curso | Arco de progreso alrededor del nodo | Muestra "2 de 3 pasos". |
| Completado | Relleno `--success` con palomita | Se puede repasar. |

Encabezado de cada misión: nombre, barra de progreso, "Entrega pendiente" si aplica. Al completar una misión aparece una banda breve
("Misión completada: *Nombre*") con un único detalle ámbar (`--accent`), sin confeti ni sonido.

## 7. Movimiento (aplicar `design-motion-principles`)

- Duraciones: microinteracciones 120–180 ms; paneles y modales 200–250 ms; **desbloqueo de misión 400 ms** (una vez). Curva `cubic-bezier(.2,.8,.2,1)`.
- Solo `transform` y `opacity`. Nada en bucle, nada que se mueva mientras se lee.
- Desbloqueo: el nodo pasa de gris a anillo azul con una escala 0.96 → 1 y un anillo que se expande y se desvanece una vez.
- Chat: mensaje nuevo entra con fundido de 150 ms; no se desplaza la lista si el usuario está leyendo mensajes anteriores (botón "ir al último").
- `prefers-reduced-motion: reduce` → cambios instantáneos o fundido simple, sin escalas ni expansión.
- Acciones frecuentes (cambiar de pestaña, marcar como visto) casi sin animación.

## 8. Formularios, tablas y chat

- Etiqueta visible siempre (no solo placeholder); error junto al campo, en texto y con ícono; foco al primer error. Se conserva lo escrito si falla.
- Subidas: tipos y tamaño máximo visibles *antes* de elegir el archivo; barra de progreso; mensaje claro si se rechaza.
- Tablas: ordenar por columna, búsqueda y paginación del lado del servidor; acciones en un menú por fila.
- Calificar: lista de entregas a la izquierda, detalle y campo de nota/retroalimentación a la derecha (apilado en móvil); atajo para pasar a la siguiente entrega.
- Chat: región `aria-live="polite"`; el texto se pinta con `textContent` (nunca HTML); mensajes pendientes del propio alumno marcados "En revisión"; el ponente ve una cola de pendientes con aprobar/ocultar en un clic.
- Aviso en el reproductor de video: si el ponente apaga el chat, el panel se reemplaza por "El chat está desactivado".

## 9. Accesibilidad (WCAG 2.2 AA)

`lang="es"`; HTML semántico (`nav`, `main`, `h1`–`h3` en orden); navegación completa con teclado y enlace "Saltar al contenido";
foco siempre visible; no depender del color; textos alternativos en portadas y avatares; el cronómetro del examen se anuncia sin repetirse cada segundo
(avisos a 10, 5 y 1 minuto); diálogos con foco atrapado y cierre con Esc.

## 10. Cómo usar las skills de diseño

- `ui-ux-pro-max`: validar paleta, tipografía y patrones de tablas/gráficas/formularios antes de construir cada pantalla; si propone algo que choque con este documento, **gana este documento**.
- `design-motion-principles`: modo *create* para el mapa de misiones y modo *audit* en la Fase 7.
- `taste-skill` (opcional): solo la página pública de inicio.
