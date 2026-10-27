# Presentación de Gaining Grades

Guía breve para mostrar la app en línea. El guion completo, con tiempos, está en `docs/DEMO.md`.

**Dirección:** https://gaining-grades.onrender.com (o `https://gaining-grades.rogelioemmanuel.com` si ya quedó el dominio)

## Antes de la presentación (15 min antes)

1. **Despierta el servicio.** El plan gratis se duerme tras 15 min sin tráfico y tarda cerca de 1 minuto en volver. Abre `/up` y espera a que responda 200.
2. **Comprueba el catálogo.** Abre `/` y el catálogo. Si no aparecen cursos, faltan los datos demo (ver "Si algo falla").
3. **Prepara dos ventanas:** una normal y una privada, para entrar con dos cuentas a la vez.
4. **Ten a la mano** la contraseña de demo (`DEMO_PASSWORD`) y la lista de cuentas de abajo.

## Cuentas de demo

El correo es `<tu correo con +etiqueta>@<dominio>`. La contraseña de todas es la de `DEMO_PASSWORD`.

| Cuenta | Rol | Para mostrar |
|---|---|---|
| `ponente1` | Ponente | Entregas por calificar, calificar, vista previa |
| `alumno02` | Alumno | Entregas pendientes, mapa de misiones |
| `alumno01` | Alumno | Calificaciones y promedio |
| `director` | Director | Solo lectura de los cursos |

## Recorrido (5 a 6 minutos)

1. **Portada** (`/`, sin sesión): qué es la plataforma, cursos y planes.
2. **Alumno** (ventana normal, `alumno02`): "Mis cursos", el mapa de misiones y una entrega. Intenta abrir una misión bloqueada: debe responder con el motivo.
3. **Ponente** (ventana privada, `ponente1`): entregas por calificar. Califica una y devuelve otra para corregir.
4. **Vuelve al alumno:** la nota ya aparece. En la ventana privada, entra como `alumno01` y muestra calificaciones y promedio.
5. **Director:** puede ver todo, pero no tiene botones para cambiar nada.
6. **Cierre:** permisos en el servidor, archivos privados, bitácora de calificaciones y las pruebas automatizadas.

## Qué no mostrar en vivo

- **El registro de cuentas nuevas.** Depende del correo de verificación, que solo llega cuando el dominio de Resend está verificado. Si lo muestras, hazlo antes y ten una cuenta ya creada.
- **El chat en vivo.** No está desplegado: Reverb no corre en Render.
- **La parte de pagos.** Es simulada; si el profesor pregunta, dilo tal cual.

## Si algo falla

| Síntoma | Causa probable | Qué hacer |
|---|---|---|
| Error 500 | Base sin datos, o `DB_PASSWORD` incorrecta | Revisa **Logs** en Render |
| Catálogo vacío | Falta `002_catalogo.sql` o `app:seed-demo` | Revisa `docs/DEPLOY.md`, paso 6 |
| Estado sucio o cursos desordenados | Pruebas previas | `php artisan app:seed-demo --reset` (desde tu máquina, con `~/gg-prod.env`) |
| No responde al inicio | Servicio dormido | Espera 1 minuto y recarga |

## Antes de confiar en esta guía

- No la ensayé contra producción. Haz una pasada completa el día antes.
- Confirma que la rama que se despliega es la que tiene el diseño actual. En `docs/PENDIENTE.md` se habla de `feature/diseno-v2` sin mezclar en `main`.
