# Despliegue en Render (plan gratis)

Guía para publicar Gaining Grades en Render, con MySQL en Aiven, archivos en Cloudflare R2 y correo con Resend. Todo en plan gratis.

Las marcas **(sin comprobar)** son pasos que no se probaron desde el repositorio: revísalos en el panel. El `Dockerfile` no se construyó aún (no hay Docker en la máquina de desarrollo).

Límites de Render Free verificados el 2026-10-26 en <https://render.com/docs/free>.

## Antes de empezar: dónde va cada cosa

| Lugar | Qué va ahí | Se sube al repo |
|---|---|---|
| `.env` (tu máquina) | Configuración de **desarrollo** (tu MySQL local `cursos_el_mai`). No lo borres. | No |
| Variables de Render (**Environment**) | Todo lo que usa la app **desplegada**: base, R2, Resend, `APP_KEY`, etc. | No |
| `~/gg-prod.env` (tu máquina, fuera del repo) | Solo para correr `artisan` desde tu máquina contra producción (paso 5). | No |
| `~/aiven-ca.pem` (tu máquina, fuera del repo) | Certificado de Aiven. Su contenido va a `DB_CA_CERT` en Render. | No |

El `.env` nunca llega a Render: `.dockerignore` lo excluye.

## Qué implica el plan gratis

- **Se duerme a los 15 min sin tráfico** y tarda cerca de 1 minuto en despertar. Para la demo, abre la app antes o usa un ping externo cada 5–10 min.
- **El disco se borra** en cada reinicio o redespliegue. Por eso los archivos van a R2 y la sesión va en cookie cifrada.
- **No hay MySQL en Render.** Se usa Aiven.
- **No hay shell.** Los comandos de `artisan` se corren desde tu máquina (paso 5).
- **Correo:** Render bloquea SMTP; el correo sale por la API de Resend.
- Render puede reiniciar el servicio en cualquier momento.

## Orden

1. Clave de la app (en tu máquina)
2. Aiven (base de datos)
3. Cloudflare R2 (archivos)
4. Resend (correo)
5. Blueprint en Render (aquí se pegan los valores de los pasos 1 a 4)
6. Super admin y demo (desde tu máquina)
7. Dominio propio (opcional)
8. Respaldos

## 1. Clave de la app

```bash
php artisan key:generate --show
```

Copia el resultado (empieza con `base64:`). Va a Render como `APP_KEY`.

## 2. Base de datos en Aiven

1. En aiven.io, crea cuenta y pulsa **Create service → MySQL → plan Free**. Elige una región cercana.
2. En el servicio, **Overview → Connection information**, anota:
   - **Host** → `DB_HOST`
   - **Port** → `DB_PORT`
   - **Database** → `DB_DATABASE` (será `defaultdb`)
   - **User** → `avnadmin` (solo para cargar el esquema)
3. En la misma pantalla, **CA certificate → Download**. Guárdalo como `~/aiven-ca.pem`.
4. Carga el esquema desde la carpeta del proyecto. Pedirá la clave de `avnadmin`:
   ```bash
   cd ~/IdeaProjects/Cursos_Online_Practice
   mysql --ssl-ca=$HOME/aiven-ca.pem -h <host> -P <puerto> -u avnadmin -p defaultdb < database/schema.sql
   mysql --ssl-ca=$HOME/aiven-ca.pem -h <host> -P <puerto> -u avnadmin -p defaultdb < database/changes/002_catalogo.sql
   ```
   Antes, revisa si `schema.sql` ya incluye el cambio 001: `grep -n -i comodin database/schema.sql`. Si no aparece, carga también `database/changes/001_comodines.sql`. **(sin comprobar)**
5. Crea el usuario de la app. Elige tú una clave larga y aleatoria, y ejecuta en la consola SQL de Aiven o con `mysql`:
   ```sql
   CREATE USER 'cursos_app'@'%' IDENTIFIED BY '<clave-larga>';
   GRANT SELECT, INSERT, UPDATE, DELETE ON defaultdb.* TO 'cursos_app'@'%';
   ```
   **(sin comprobar que Aiven deje crear usuarios desde `avnadmin`)**

| Variable en Render | Valor |
|---|---|
| `DB_HOST` | Host de Aiven |
| `DB_PORT` | Puerto de Aiven |
| `DB_DATABASE` | `defaultdb` |
| `DB_USERNAME` | `cursos_app` (ya viene en el Blueprint) |
| `DB_PASSWORD` | La clave de `cursos_app` |
| `DB_CA_CERT` | Todo el contenido de `~/aiven-ca.pem`, con las líneas `BEGIN` y `END` **(sin comprobar que Render acepte saltos de línea)** |

## 3. Archivos en Cloudflare R2

1. En dash.cloudflare.com, **R2 → Create bucket**: `gaining-grades`, sin acceso público. **(sin comprobar si pide tarjeta)**
2. **R2 → Manage API tokens → Create API token**, con permiso **Object Read & Write** solo sobre ese bucket. Copia el *Access Key ID* y el *Secret Access Key*; se muestran una sola vez.
3. Copia la URL del endpoint S3: `https://<id-de-cuenta>.r2.cloudflarestorage.com`.

| Variable en Render | Valor |
|---|---|
| `AWS_BUCKET` | `gaining-grades` |
| `AWS_ENDPOINT` | La URL del endpoint |
| `AWS_ACCESS_KEY_ID` | El Access Key ID |
| `AWS_SECRET_ACCESS_KEY` | El Secret |

`AWS_DEFAULT_REGION=auto` y `AWS_USE_PATH_STYLE_ENDPOINT=true` ya vienen en el Blueprint.

## 4. Correo con Resend

1. En resend.com, **Domains → Add Domain** con `correo.<tu-dominio>`. Resend recomienda un subdominio.
2. En **Cloudflare → DNS**, crea los registros que Resend muestra (MX, SPF y DKIM), todos en **DNS only**. Usa los valores exactos de Resend; la verificación puede tardar hasta 72 h.
3. **API Keys → Create API Key** con permiso de envío.

| Variable en Render | Valor |
|---|---|
| `RESEND_API_KEY` | La llave de Resend |
| `MAIL_FROM_ADDRESS` | `no-responder@correo.<tu-dominio>` (del dominio verificado) |

Hasta que el dominio esté verificado, la app arranca pero los correos no salen.

## 5. Blueprint en Render

1. Haz commit y push de la rama que vayas a publicar. El repo necesita `Dockerfile`, `render.yaml` y `start-container.sh`.
2. En dashboard.render.com, **New → Blueprint**. Conecta GitHub, elige el repositorio y la rama. Render lee `render.yaml`.
3. Te pedirá las variables sin valor. Llénalas:

   | Variable | Dónde se obtiene |
   |---|---|
   | `APP_KEY` | Paso 1 |
   | `APP_URL` | `https://gaining-grades.onrender.com`. Si Render asigna otro nombre, cámbialo en **Environment** y redespliega |
   | `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_PASSWORD`, `DB_CA_CERT` | Paso 2 |
   | `AWS_BUCKET`, `AWS_ENDPOINT`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY` | Paso 3 |
   | `RESEND_API_KEY`, `MAIL_FROM_ADDRESS` | Paso 4 |
   | `SITE_CONTACT_EMAIL`, `SITE_WHATSAPP` | Tus datos de contacto |

   Si Render no te deja crear el Blueprint, crea un **Web Service → Docker** a mano, plan Free, **Health Check Path** `/up`, y pega las variables de `render.yaml`.
4. Pulsa **Apply**. El primer build tarda varios minutos. Sigue la pestaña **Logs**. Si falla, lo más probable es la ruta del Caddyfile (el script prueba `/etc/frankenphp/Caddyfile` y luego `/etc/caddy/Caddyfile`) o la imagen `dunglas/frankenphp`.
5. Cuando esté **Live**, abre `https://<tu-url>/up`. Debe responder 200.

Cada cambio en **Environment** redespliega el servicio solo.

## 6. Super admin y demo (desde tu máquina)

Crea `~/gg-prod.env` **fuera del repo**. Son los mismos valores de producción, para que `artisan` apunte a Aiven y R2:

```bash
APP_ENV=production
APP_KEY=<la misma de Render>
DB_CONNECTION=mysql
DB_HOST=<host de Aiven>
DB_PORT=<puerto>
DB_DATABASE=defaultdb
DB_USERNAME=cursos_app
DB_PASSWORD=<clave de cursos_app>
MYSQL_ATTR_SSL_CA=/home/<tu-usuario>/aiven-ca.pem
AWS_BUCKET=gaining-grades
AWS_ENDPOINT=<endpoint de R2>
AWS_ACCESS_KEY_ID=<de R2>
AWS_SECRET_ACCESS_KEY=<de R2>
AWS_DEFAULT_REGION=auto
AWS_USE_PATH_STYLE_ENDPOINT=true
MAIL_MAILER=resend
RESEND_API_KEY=<de Resend>
MAIL_FROM_ADDRESS=no-responder@correo.<tu-dominio>
```

Luego, en tu terminal:

```bash
cd ~/IdeaProjects/Cursos_Online_Practice
set -a; . ~/gg-prod.env; set +a
php artisan app:create-super-admin
```

Te pedirá correo y clave; no quedan en ningún archivo.

**Demo** (opcional): agrega a `~/gg-prod.env` `DEMO_MODE=true`, `DEMO_PASSWORD=<una clave>` y `DEMO_EMAIL_BASE=<tu correo>`, y ejecuta `php artisan app:seed-demo`. Es idempotente; con `--reset` recrea los tres cursos demo. Las cuentas usan plus-addressing (`tu+gg-alumno01@…`), así que las invitaciones te llegan a ti. Al terminar, pon `DEMO_MODE=false` en Render y borra `DEMO_*` de `~/gg-prod.env`.

## 7. Dominio propio (opcional)

1. En el servicio, **Settings → Custom Domains → Add** y escribe `gaining-grades.tudominio.com`.
2. Crea en Cloudflare el `CNAME` que muestra Render, en **DNS only (nube gris)**. **(sin comprobar si el plan gratis pide pasos extra)**
3. El certificado se emite solo. Cambia `APP_URL` en Render y redespliega.
4. Con el proxy de Cloudflare activado, `X-Forwarded-For` traería la IP de Cloudflare y el límite de login se compartiría entre todos: déjalo en DNS only.

## 8. Respaldos

- **Base:** antes de cada cambio de esquema, guarda un volcado fuera del repo:
  ```bash
  mysqldump --ssl-ca=$HOME/aiven-ca.pem -h <host> -P <puerto> -u avnadmin -p --single-transaction --no-tablespaces defaultdb > ~/respaldos/gg-$(date +%F).sql
  ```
- **Archivos:** viven en R2, que no hace respaldo propio. Para una copia, `rclone sync` hacia tu máquina.

## 9. Costos a vigilar

Todo cabe en planes gratis, con límites: 750 horas de instancia, ancho de banda y minutos de build de Render (si se agotan sin tarjeta, el servicio se suspende o los builds se desactivan), 1 GB en Aiven, 10 GB en R2 y el plan de Resend. No anoto cifras porque cambian; revisa las páginas de precios.

## 10. Cuando cambie el esquema

Los cambios llegan como `database/changes/NNN_*.sql`. Se aplican **a mano**, con respaldo previo, conectándote como `avnadmin` igual que en el paso 2. La app nunca migra.
