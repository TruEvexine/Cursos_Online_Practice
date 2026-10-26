# Despliegue en Railway

Guía corta para publicar Gaining Grades en Railway, en un subdominio tuyo (p. ej. `gaining-grades.tudominio.com`).
En producción la base es el **MySQL de Railway**; lo "local" de `CLAUDE.md` aplica al desarrollo.
Las marcas **(sin comprobar)** son pasos que no pude probar desde aquí: revísalos en el panel.

Revisado contra la documentación de Railway y de Railpack el 2026-10-24.

## Lo que ya hace el repositorio

- **`start-container.sh`** reemplaza el arranque de Railpack para Laravel. Al iniciar:
  - crea las carpetas de `storage/`;
  - ejecuta `optimize:clear` y `optimize` (cachés de configuración, eventos, rutas y vistas);
  - arranca FrankenPHP (Caddy).
  - **Nunca migra.** Railpack migra por defecto; además se pone `RAILPACK_SKIP_MIGRATIONS=true` como segunda barrera.
- **`composer.json`** declara las extensiones que usa la app, y Railpack las instala:
  - `gd`: fotos de perfil a WebP;
  - `intl`: tamaños de archivo;
  - `fileinfo`: tipo real de las subidas;
  - `mbstring`;
  - `pdo_mysql`.

  Las demás (`ctype`, `curl`, `dom`, `openssl`, `tokenizer`, `xml`…) ya vienen en la lista base de Railpack para Laravel.
- **Producción sin correo real:** con `APP_ENV=production` y `MAIL_MAILER=log`, la app no arranca. Esto incluye `php artisan`.
- **IP real del visitante:** se toma de `X-Forwarded-For` cuando `TRUSTED_PROXIES` está definido. Es la IP que usa el límite de intentos de login.
- **Cabeceras de seguridad:** `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` y `X-Frame-Options`. En producción, además, la CSP y HSTS que ya existían desde la Fase 1.

> Nota: Railpack sirve PHP con **FrankenPHP** (Caddy con PHP embebido), no con php-fpm. Para nosotros no cambia nada.

## 1. Proyecto y servicio de la app

1. En Railway: **New Project → Deploy from GitHub repo** y elige este repositorio y la rama que vayas a publicar.
2. En el servicio, **Settings**:
   - **Custom Build Command:** `npm run build`. Es lo que indica la guía de Laravel de Railway, para compilar CSS y JS. **(sin comprobar si Railpack ya lo hace solo)**
   - **Pre-deploy Command:** `php artisan config:cache && php artisan route:cache && php artisan view:cache`
     - El pre-deploy corre en **otro contenedor, sin el volumen**, y sus archivos **no se conservan**, así que no sirve para dejar cachés.
     - Sirve como **revisión**: si la configuración está mal (p. ej. `MAIL_MAILER=log`), el despliegue se detiene antes de reemplazar la versión anterior.
     - Las cachés reales las genera `start-container.sh` al arrancar.
     - **No pongas `migrate` aquí.**

## 2. Base de datos MySQL

1. **New → Database → MySQL** en el mismo proyecto.
2. Activa temporalmente su acceso público (TCP proxy) para cargar el esquema desde tu máquina. Railway te muestra el host y el puerto públicos en la pestaña **Connect** o **Variables** del servicio MySQL.
3. Carga el esquema como `root`, solo esta vez. La base de Railway suele llamarse `railway`; confírmalo en `MYSQLDATABASE`.
   ```bash
   mysql -h <host-publico> -P <puerto> -u root -p railway < database/schema.sql
   ```
4. Crea el usuario de la aplicación **sin permisos de DDL**. Usa una clave larga y aleatoria; no la guardes en el repo.
   ```sql
   CREATE USER 'cursos_app'@'%' IDENTIFIED BY '<clave-larga>';
   GRANT SELECT, INSERT, UPDATE, DELETE ON railway.* TO 'cursos_app'@'%';
   ```
5. Si no lo necesitas para respaldos, vuelve a quitar el acceso público.

## 3. Variables del servicio de la app

Copia `docs/railway-variables.example` en **Variables** (el "Raw Editor" acepta el archivo) y completa:

- `APP_KEY`: genérala en tu máquina con `php artisan key:generate --show`.
- `APP_URL`: tu subdominio con `https://`.
- `DB_*`: usa las variables de referencia del servicio MySQL (`${{MySQL.MYSQLHOST}}`…). `DB_USERNAME=cursos_app` y su clave.
  **(sin comprobar: confirma en el servicio MySQL el nombre exacto del servicio y de sus variables)**
- `TRUSTED_PROXIES=*`: solo si la app **no** tiene TCP proxy ni otro acceso directo. Así la única forma de llegar es el proxy HTTP de Railway, y nadie puede falsificar `X-Forwarded-For`.
- `MAIL_*`: ver el paso 6.
- `SITE_CONTACT_EMAIL` y `SITE_WHATSAPP`.
- `RAILPACK_SKIP_MIGRATIONS=true`.
- `CADDY_SERVER_EXTRA_DIRECTIVES="request_body { max_size 12MB }"`:
  - el `php.ini` de Railpack trae `upload_max_filesize = 0` y `post_max_size = 0` (sin límite);
  - la app valida 10 MB, pero sin este tope cualquiera podría mandar envíos enormes.

  **(sin comprobar en un despliegue real)**

## 4. Volumen (subidas y sesiones)

- **Mount path:** `/app/storage`. Ahí viven:
  - los materiales y fotos (`storage/app/private`);
  - las sesiones (`storage/framework/sessions`);
  - la caché de archivos.

  Los logs van a `stderr` (`LOG_CHANNEL=stderr`).
- La primera vez el volumen llega vacío; `start-container.sh` crea las carpetas.
- **Límites de Railway:**
  - un volumen por servicio;
  - **sin réplicas** con volumen;
  - cada redespliegue tiene una pausa breve porque el volumen no se comparte entre dos despliegues.
- Si aparecen errores de permisos, agrega la variable `RAILWAY_RUN_UID=0`, como indica Railway para imágenes que no corren como root. **(sin comprobar)**

## 5. Dominio propio (subdominio)

1. En el servicio de la app: **Settings → Public Networking → + Custom Domain** y escribe `gaining-grades.tudominio.com`.
2. Railway te muestra **dos registros, y los dos son obligatorios**: un `CNAME` hacia un destino `*.up.railway.app` y un `TXT` de verificación. Créalos **exactamente** como aparecen.
   - Sin el TXT, el dominio responde 404 aunque el CNAME ya resuelva.
3. El certificado (Let's Encrypt) se emite solo, normalmente en menos de una hora. Los cambios de DNS pueden tardar hasta 72 h.
4. **Si tu DNS está en Cloudflare:**
   - Railway pide **proxy activado** y SSL/TLS en **Full**, no Full (Strict).
   - Con el proxy de Cloudflare, `X-Forwarded-For` trae la IP de Cloudflare en lugar de la del visitante, y el límite de intentos de login se compartiría entre todos.
   - Para este subdominio conviene **DNS only (nube gris)**, si Railway lo acepta en tu caso. **(sin comprobar)**

## 6. Correo transaccional con Resend (API HTTPS)

Railway bloquea el SMTP saliente en Free, Trial y Hobby, así que la app envía por la **API de Resend** (paquete `resend/resend-php`, que usa el transporte `resend` de Laravel).

1. En [resend.com](https://resend.com): **Domains → Add Domain**. Resend recomienda enviar desde un **subdominio**, p. ej. `correo.<tu dominio>`. En los registros de abajo, `send` y `resend._domainkey` llevan entonces el sufijo del subdominio.
2. En **Cloudflare → DNS**, crea los registros que Resend muestra en la página del dominio (copia los valores de ahí; los de abajo son la forma, no los valores exactos):

   | Tipo | Nombre | Contenido | Prioridad | Proxy |
   |---|---|---|---|---|
   | MX | `send` | `feedback-smtp.<región>.amazonses.com` (el que te dé Resend) | 10 | DNS only |
   | TXT | `send` | `"v=spf1 include:amazonses.com ~all"` (SPF) | — | DNS only |
   | TXT | `resend._domainkey` | `p=…` (DKIM, la llave que te dé Resend) | — | DNS only |
   | TXT | `_dmarc` | `v=DMARC1; p=none;` (recomendado; empieza en `none`) | — | DNS only |

   - Escribe solo el nombre corto (`send`, no `send.tudominio.com`). Cloudflare completa el dominio.
   - Si la prioridad 10 ya está ocupada, usa 20 o 30.
   - Resend tiene un botón **"Sign in to Cloudflare"** que crea los registros solo.
   - La verificación puede tardar hasta 72 h (normalmente mucho menos).
3. Crea una **API key** en Resend con permiso de envío.
4. Variables en Railway:
   - `MAIL_MAILER=resend`
   - `RESEND_API_KEY=<la llave>`
   - `MAIL_FROM_ADDRESS=no-responder@correo.<tu dominio>` (del dominio verificado)
   - `MAIL_FROM_NAME="Gaining Grades"`

Con `MAIL_MAILER=log` la app no arranca en producción (a propósito).

## 7. Crear el super admin en producción

Con el servicio ya arriba, abre una terminal en el contenedor con la CLI de Railway y ejecuta el comando interactivo. Pide correo y clave; no quedan en ningún archivo.
```bash
railway ssh            # desde la carpeta del proyecto, ya enlazada con `railway link`
php artisan app:create-super-admin
```
**(sin comprobar: el nombre y las opciones de `railway ssh` en tu versión de la CLI)**

## 7b. Datos de la DEMO

1. Variables:
   - `DEMO_MODE=true`;
   - `DEMO_PASSWORD=<una clave>` (no hay valor por defecto);
   - `DEMO_EMAIL_BASE=<tu correo>`.

   Las cuentas usan plus-addressing (`tu+gg-alumno01@…`), así que las invitaciones y verificaciones te llegan a ti.
2. Aplica antes `database/changes/002_catalogo.sql`, para que los cursos aparezcan en el catálogo. Sin él, se siembran pero sin precio.
3. `php artisan app:seed-demo`:
   - es idempotente;
   - con `--reset`, borra y recrea los tres cursos demo.

   Nunca crea ni cambia al super admin.
4. Para el registro abierto: `SITE_OPEN_REGISTRATION=true`.
5. Al terminar la demostración: `DEMO_MODE=false`.

## 8. Respaldo manual antes de cada despliegue

Con el acceso público de MySQL activo, guarda el respaldo **fuera del repositorio** (los `.sql` están ignorados, pero no los dejes en la carpeta del proyecto):
```bash
mysqldump -h <host-publico> -P <puerto> -u root -p --single-transaction --no-tablespaces railway > ~/respaldos/gg-$(date +%F).sql
```
Los archivos subidos viven en el volumen: Railway ofrece respaldos manuales y automáticos de volúmenes. Actívalos.

## 9. Costos a vigilar

En Railway se paga por uso: CPU y memoria de la app y del MySQL, GB del volumen y tráfico de salida, sobre la cuota del plan.
No anoto cifras porque cambian; revisa la página de precios de Railway y pon un **límite de gasto** en la configuración del proyecto.
También el plan del proveedor de correo.

## 10. Cuando cambie el esquema

Los cambios llegan como `database/changes/NNN_*.sql`. Se aplican **a mano**, con respaldo previo, conectándote como `root` igual que en el paso 2. La app nunca migra.
