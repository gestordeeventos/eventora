# Eventora (gestor-eventos) — Instalación en otro equipo

Guía para copiar el proyecto, instalar dependencias y usar **Google OAuth** + **correo Gmail** en **local**.

---

## 1. Qué copiar al otro PC

### Opción A — Git (recomendada)

```bash
git clone https://github.com/gestordeeventos/eventora.git gestor-eventos
cd gestor-eventos
```

### Opción B — USB / carpeta comprimida

Copia la carpeta **excepto** (ocupan mucho y se regeneran):

- `vendor/`
- `node_modules/`
- `.env` (secretos; crea uno nuevo en el otro PC)
- `public/build/` (se regenera con `npm run build`)
- `storage/logs/*.log`

**Sí incluye:** todo el código, `composer.lock`, `package-lock.json`, `storage/certs/cacert.pem` (SSL para Google en Windows).

---

## 2. Programas a instalar

| Programa | Versión mínima | Para qué |
|----------|----------------|----------|
| **PHP** | 8.3 | Laravel |
| **Composer** | 2.x | Paquetes PHP |
| **Node.js** | 20 LTS | Vite / CSS / JS |
| **Git** | cualquiera | Clonar repo (opcional) |

### Extensiones PHP necesarias

`pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd`, `zip`

En Windows (PowerShell como administrador, si usas winget):

```powershell
winget install PHP.PHP.8.3
winget install Composer.Composer
winget install OpenJS.NodeJS.LTS
winget install Git.Git
```

Reinicia la terminal después de instalar.

Comprueba:

```bash
php -v          # debe ser 8.3.x
composer -V
node -v
npm -v
```

---

## 3. Instalar el proyecto (comandos)

Abre terminal en la carpeta del proyecto:

### Windows (Git Bash o PowerShell)

```bash
cd ruta/a/gestor-eventos

composer install
npm install
npm run build

copy .env.example .env
php artisan key:generate
php artisan storage:link
php artisan config:clear
```

### macOS / Linux

```bash
cd ruta/a/gestor-eventos

composer install
npm install
npm run build

cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan config:clear
```

---

## 4. Configurar `.env` (local)

Edita `.env` con estos valores para **desarrollo local**:

```env
APP_NAME=Eventora
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# Supabase (mismo proyecto que ya tienes)
VITE_SUPABASE_URL=https://nmwgpwqbyvcljomovyyo.supabase.co
VITE_SUPABASE_ANON_KEY=tu_anon_key_de_supabase

# Contraseña de BD: Supabase → Settings → Database
SUPABASE_DB_PASSWORD=tu_password

DB_CONNECTION=pgsql
# Si ves "Unknown host" con db.xxx.supabase.co (DNS/red escolar), usa Session pooler:
# SUPABASE_POOLER_HOST=aws-1-us-west-1.pooler.supabase.com
# DB_USERNAME=postgres.TU_REF_DEL_PROYECTO
# (Host y usuario exactos: Supabase → Connect → Session pooler)

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=false

# Gmail (contraseña de aplicación de 16 caracteres)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu@gmail.com
MAIL_PASSWORD=xxxxxxxxxxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu@gmail.com
MAIL_FROM_NAME=Eventora

# Google OAuth
GOOGLE_CLIENT_ID=tu_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxx
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```

> **Render vs local:** En Render usas `DB_URL` o `SUPABASE_POOLER_HOST`. En local **no** los pongas; Laravel conecta a `db.TUREF.supabase.co` solo con `VITE_SUPABASE_URL` + `SUPABASE_DB_PASSWORD`.

---

## 5. Base de datos (Supabase)

Si el proyecto Supabase **ya tiene tablas**, no hace falta volver a ejecutar el SQL.

Si es **base nueva**, en Supabase → **SQL Editor**, en este orden:

1. `database/supabase_schema.sql`
2. `database/supabase_portadas_update.sql`
3. `database/supabase_avatar_update.sql`
4. `database/supabase_features_update.sql`
5. `database/supabase_carrito_update.sql` (carrito de compras)

Opcional (datos de prueba):

```bash
php artisan db:seed
```

Cuentas por defecto del seeder:

| Rol | Correo | Contraseña |
|-----|--------|------------|
| Admin | `admin@gestoreventos.com` | `Admin123!` |
| Cliente | `cliente@gestoreventos.com` | `Cliente123!` |

---

## 6. Google OAuth (login con Google)

1. [Google Cloud Console](https://console.cloud.google.com/) → proyecto (o crea uno).
2. **APIs y servicios** → **Credenciales** → **Crear credenciales** → **ID de cliente OAuth**.
3. Tipo: **Aplicación web**.
4. **URIs de redirección autorizados:**
   ```
   http://127.0.0.1:8000/auth/google/callback
   ```
5. **Orígenes autorizados de JavaScript:**
   ```
   http://127.0.0.1:8000
   ```
6. Copia **ID de cliente** y **Secreto** → `.env` (`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`).
7. Pantalla de consentimiento OAuth: modo **Prueba** y agrega tu Gmail como usuario de prueba.

---

## 7. SSL en Windows (error cURL 60 con Google)

Si al entrar con Google sale error SSL:

1. Descarga: https://curl.se/ca/cacert.pem  
2. Guárdalo como: `storage/certs/cacert.pem`  
3. En `.env` (opcional):
   ```env
   SSL_CA_BUNDLE=storage/certs/cacert.pem
   ```

El proyecto ya usa ese archivo si existe en `storage/certs/`.

---

## 8. Gmail (correos al comprar)

1. Cuenta Google → **Seguridad** → **Verificación en 2 pasos** (activada).
2. **Contraseñas de aplicaciones** → crear “Eventora”.
3. Copia las 16 letras **sin espacios** → `MAIL_PASSWORD` en `.env`.
4. `MAIL_USERNAME` y `MAIL_FROM_ADDRESS` = el mismo Gmail.

Prueba local:

```bash
php artisan config:clear
php artisan serve
```

Haz una compra de prueba; el correo debe llegar al email del cliente registrado.

Para ver correos sin Gmail: `MAIL_MAILER=log` y revisa `storage/logs/laravel.log`.

---

## 9. Arrancar la aplicación

```bash
php artisan serve
```

Abre: **http://127.0.0.1:8000**

Desarrollo con recarga de assets (opcional, segunda terminal):

```bash
npm run dev
```

---

## 10. Resumen de comandos (copiar y pegar)

```bash
cd gestor-eventos
composer install
npm install
npm run build
cp .env.example .env    # Windows: copy .env.example .env
php artisan key:generate
php artisan storage:link
php artisan config:clear
php artisan serve
```

Luego configura `.env` (Supabase, Google, Gmail) y recarga.

---

## 11. Problemas frecuentes

| Problema | Solución |
|----------|----------|
| `composer` no reconocido | Instala Composer y reinicia terminal |
| `Unknown host` / `could not translate host name "db.*.supabase.co"` | Tu red/DNS no resuelve Supabase directo. En `.env` agrega `SUPABASE_POOLER_HOST` y `DB_USERNAME` (Supabase → Connect → **Session pooler**). Ejecuta `php artisan config:clear`. |
| Google SSL cURL 60 | Pon `storage/certs/cacert.pem` |
| Google redirect mismatch | URI exacta: `http://127.0.0.1:8000/auth/google/callback` |
| Correo no llega | Contraseña de aplicación, no la normal de Gmail |
| CSS roto | `npm run build` |
| Imágenes 404 | `php artisan storage:link` |

---

## 12. Diferencia local vs Render

| | Local | Render |
|--|-------|--------|
| URL | `http://127.0.0.1:8000` | `https://eventora-xxxx.onrender.com` |
| Supabase | Conexión directa **o** session pooler si falla el DNS (`SUPABASE_POOLER_HOST`) | Session pooler (`DB_URL` o pooler host) |
| Gmail SMTP | Sí funciona | Suele estar bloqueado |
| Google OAuth | URI con `127.0.0.1:8000` | URI con dominio Render |
