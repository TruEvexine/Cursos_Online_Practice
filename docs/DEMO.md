# Guion de la demo (5 minutos)

**Antes:** `php artisan app:seed-demo` (con `DEMO_MODE=true`, `DEMO_PASSWORD` y `DEMO_EMAIL_BASE`) y abre `RUN.txt`: ahí está la tabla de cuentas. La contraseña de todas es `DEMO_PASSWORD`. Usa dos ventanas, una normal y una privada.

Cuentas que se usan (el correo es `tu+gg-<etiqueta>@dominio`):
- **ponente1** (Laura, Estadística): ponente con una entrega por calificar.
- **alumno02** (Ana Sofía): tiene entregas sin calificar y pendientes.
- **alumno01** (Diego): casi todo calificado, sirve para ver notas y promedio.
- **alumno05** (Jorge): curso en cero.
- **director**: lectura de todo.

## Guion

1. **Portada (30 s).**
   - Abre `/` sin sesión.
   - Muestra el mensaje central, los cursos abiertos con portada, las funciones "Disponible" y "Próximamente", los planes de ejemplo y el simulador de Escuela (250 alumnos y 5 ponentes dan $4,250).
2. **Registro y catálogo (45 s).**
   - Entra al catálogo y abre un curso de pago.
   - Muestra que un menor de 18 años no puede registrarse (mensaje claro).
   - Crea una cuenta de mayor de 18 años e inscríbete: **el pago es simulado, rotulado "modo prueba"**.
3. **Alumno (90 s).** Entra como `alumno02`:
   - "Mis cursos" con la próxima entrega.
   - El mapa de misiones: temas completados, uno disponible y los siguientes bloqueados; intenta abrir uno bloqueado (403 con el motivo).
   - Abre un tema, lee el contenido y entrega una tarea con texto o archivo.
   - En la ventana privada, entra como `alumno01` y muestra "Mis calificaciones": nota, retroalimentación y promedio ponderado.
4. **Ponente (90 s).** Entra como `ponente1`:
   - El inicio muestra las entregas por calificar.
   - Abre la entrega de Ana Sofía y califícala con retroalimentación, y devuelve otra para que la corrija.
   - Vuelve a la ventana del alumno: la nota ya aparece.
   - Muestra el constructor (bloques, temas en Markdown, tareas y materiales) y la **vista previa como alumno**.
5. **Director y seguridad (45 s).**
   - Entra como el director: puede ver los cursos pero no hay botones para cambiar nada (un POST directo devuelve 403).
   - Cierra con: roles y permisos en el servidor, archivos privados, bitácora de calificaciones, límite de intentos y 116 pruebas automatizadas.

## Si algo falla en vivo
- Sin cursos en el catálogo: falta aplicar `database/changes/002_catalogo.sql`.
- No llega el correo de verificación: falta `MAIL_FROM_ADDRESS` (o el dominio en Resend).
- Quedó sucio el estado: `php artisan app:seed-demo --reset` recrea los tres cursos.
