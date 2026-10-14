# Diseño — Cursos el Mai

Objetivo: moderno, responsivo y **formal/educativo**. Claro por defecto (se estudia mejor de día), con tema oscuro opcional.
Diferente de tu práctica anterior (oscuro con brillos): aquí manda la legibilidad y la calma; la "gracia" vive en un solo lugar, el **mapa de misiones**.

Referencias de sensación (solo para orientarse, sin copiar): IBM (formal y claro), Notion (lectura limpia), Linear (tablas y paneles densos pero ordenados).

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
  --canvas: #F6F7F9;   --surface: #FFFFFF;   --surface-sunken: #EEF1F5;
  --border: #E2E6EC;   --border-strong: #64748B;   /* campos de formulario: 4.76:1 */
  /* Texto */
  --ink: #0F172A;      /* 17.9:1 sobre surface */
  --ink-muted: #475569;/* 7.6:1 */
  /* Marca */
  --primary: #1F3A8A;  --primary-hover: #1E40AF;  --primary-soft: #E8EDFB;   /* texto blanco sobre primary: 10.3:1 */
  --accent: #F59E0B;   --accent-ink: #92400E;     /* relleno ámbar con texto --ink: 8.3:1; texto ámbar usa --accent-ink: 7.1:1 */
  /* Estado (texto sobre surface) */
  --success: #15803D;  --success-ink: #166534;  --success-soft: #E7F5EC;  /* badge: success-ink sobre soft 6.3:1 */
  --warning: #B45309;  --warning-soft: #FDF1DC;  --warning-ink: #92400E;
  --danger:  #B91C1C;  --danger-soft:  #FBEAEA;
  --info:    #0369A1;
  --focus: #1F3A8A;    /* anillo 2 px + 2 px de separación */
}
:root[data-theme="dark"] {
  --canvas: #0B1220;   --surface: #111A2E;   --surface-sunken: #0E1626;
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
- Escala (≈1.2): 14 · 16 (base) · 20 · 24 · 30 · 36 px. Interlineado 1.6 en lectura, 1.35 en títulos. Números de tablas con `tabular-nums`.
- Espaciado base de 4 px (4, 8, 12, 16, 24, 32, 48, 64). Radios: campos 6 px, tarjetas 10 px, píldoras 999 px. Sombra solo en menús y modales.
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
