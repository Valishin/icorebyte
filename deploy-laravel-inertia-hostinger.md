# 🚀 Despliegue Laravel + Vue + Inertia en Hostinger Shared Hosting

> Guía basada en despliegue real de icorebyte.com — Mayo 2026

---

## 📋 Requisitos previos

- Proyecto Laravel + Vue + Inertia funcionando en local
- Cuenta en Hostinger con **Shared Hosting** (Premium o superior)
- Base de datos MySQL creada en hPanel
- Repositorio en GitHub/GitLab
- Acceso SSH activado en hPanel

---

## 🗂️ Estructura de archivos en el servidor

En Hostinger Shared Hosting la estructura es:

```
/home/u863791557/
└── domains/
    └── tudominio.com/
        ├── public_html/        ← solo el contenido de /public de Laravel
        │   ├── index.php       ← modificado con rutas absolutas
        │   ├── .htaccess
        │   ├── favicon.ico
        │   ├── robots.txt
        │   └── build/          ← assets generados por Vite
        │
        └── laravel/            ← todo el proyecto Laravel aquí
            ├── app/
            ├── bootstrap/
            ├── config/
            ├── database/
            ├── resources/
            ├── routes/
            ├── storage/
            ├── vendor/
            ├── .env
            └── ...
```

---

## 🔑 Paso 1 — Conectarse por SSH

En hPanel → **Avanzado → SSH Access**, copia tus credenciales.

```bash
ssh u863791557@tudominio.com -p 65002
```

---

## 🐘 Paso 2 — Configurar PHP 8.4 en el CLI

Hostinger usa PHP 8.1 por defecto en el CLI aunque el panel tenga otra versión.

**2.1 — Cambia la versión PHP en hPanel:**

hPanel → Hosting → tudominio.com → **PHP Configuration** → selecciona **PHP 8.3 o 8.4**

**2.2 — Configura el CLI por SSH:**

```bash
# Verifica las versiones disponibles
ls /opt/alt/php*/usr/bin/php

# Añade PHP 8.4 al PATH
echo 'export PATH=/opt/alt/php84/usr/bin:$PATH' >> ~/.bashrc
echo 'export PATH=/opt/alt/php84/usr/bin:$PATH' >> ~/.bash_profile
echo "alias php='/opt/alt/php84/usr/bin/php'" >> ~/.bashrc
echo "alias php='/opt/alt/php84/usr/bin/php'" >> ~/.bash_profile
source ~/.bashrc

# Verifica que usa 8.4
php -v
```

> ⚠️ Si `php -v` sigue mostrando 8.1, usa siempre la ruta completa:
> `/opt/alt/php84/usr/bin/php artisan ...`

---

## 📦 Paso 3 — Clonar el repositorio e instalar dependencias

```bash
# Ve al directorio del dominio
cd ~/domains/tudominio.com/

# Clona el proyecto fuera de public_html
git clone https://github.com/tuusuario/tu-repo.git laravel
cd laravel

# Instala dependencias PHP (sin dev)
composer install --optimize-autoloader --no-dev
```

---

## ⚙️ Paso 4 — Configurar el .env

```bash
cp .env.example .env
php artisan key:generate
nano .env
```

Rellena con tus datos reales:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u863791557_nombrebd
DB_USERNAME=u863791557_usuario
DB_PASSWORD=tu_contraseña
```

> ⚠️ Asegúrate de que las líneas **no tengan `#` delante** — es un error común del .env.example.

Guarda con `Ctrl+X` → `Y` → `Enter`.

---

## 🗄️ Paso 5 — Ejecutar migraciones

```bash
php artisan migrate --force
```

---

## 🌐 Paso 6 — Copiar /public a public_html

```bash
cp -r ~/domains/tudominio.com/laravel/public/. ~/domains/tudominio.com/public_html/
```

---

## 📝 Paso 7 — Modificar index.php

```bash
nano ~/domains/tudominio.com/public_html/index.php
```

Cambia las rutas relativas por rutas absolutas apuntando a tu carpeta Laravel:

```php
// ANTES:
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// DESPUÉS:
require __DIR__.'/../laravel/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel/bootstrap/app.php';
```

---

## 🏗️ Paso 8 — Subir el build de Vite

Hostinger Shared Hosting **no tiene Node.js**, así que el build se hace en local.

**En tu máquina local:**

```bash
npm run build
```

**Sube la carpeta build al servidor:**

```bash
scp -P 65002 -r ./public/build u863791557@tudominio.com:~/domains/tudominio.com/laravel/public/
```

**Cópialo a public_html en el servidor:**

```bash
cp -r ~/domains/tudominio.com/laravel/public/build ~/domains/tudominio.com/public_html/
```

---

## 🔗 Paso 9 — Storage link

```bash
ln -s ~/domains/tudominio.com/laravel/storage/app/public ~/domains/tudominio.com/public_html/storage
```

---

## 📁 Paso 10 — Crear carpetas de storage y permisos

```bash
cd ~/domains/tudominio.com/laravel

mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/logs

chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

---

## ⚡ Paso 11 — Optimizar para producción

```bash
php artisan optimize:clear
php artisan optimize
```

---

## 🔒 Paso 12 — Activar SSL

En hPanel → **SSL** → activa **Let's Encrypt** gratuito para tudominio.com.

---

## ✅ Checklist final

| | Tarea |
|---|---|
| ☐ | PHP 8.4 configurado en hPanel y en el CLI |
| ☐ | Proyecto clonado en `~/domains/tudominio.com/laravel/` |
| ☐ | `.env` configurado sin `#` en las líneas de DB |
| ☐ | `APP_DEBUG=false` en producción |
| ☐ | Migraciones ejecutadas |
| ☐ | Contenido de `/public` copiado a `public_html/` |
| ☐ | `public_html/index.php` con rutas absolutas corregidas |
| ☐ | Carpeta `build/` en `public_html/` |
| ☐ | `storage:link` creado |
| ☐ | Carpetas de storage creadas con permisos 775 |
| ☐ | `php artisan optimize` ejecutado |
| ☐ | SSL activado |

---

## 🔄 Flujo para futuros deploys

Cada vez que hagas cambios en el proyecto:

**En local:**
```bash
npm run build
scp -P 65002 -r ./public/build u863791557@tudominio.com:~/domains/tudominio.com/laravel/public/
```

**En el servidor (SSH):**
```bash
cd ~/domains/tudominio.com/laravel
git pull
composer install --optimize-autoloader --no-dev  # si cambiaron dependencias
cp -r public/build/. ~/domains/tudominio.com/public_html/build/
php artisan optimize:clear
php artisan optimize
php artisan migrate --force  # solo si hay migraciones nuevas
```

---

## 🐛 Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| `php version 8.1 does not satisfy ^8.3` | CLI usa PHP 8.1 | Configurar PATH con `/opt/alt/php84/usr/bin` |
| `Access denied for user 'root'` | Líneas DB comentadas en .env | Quitar los `#` de DB_HOST, DB_DATABASE, etc. |
| `Please provide a valid cache path` | Carpetas de storage no existen | `mkdir -p storage/framework/views` y permisos 775 |
| Página en blanco / 500 | Cualquier error con debug=false | Activar `APP_DEBUG=true` temporalmente para ver el error |
| Assets de Vue no cargan | Carpeta `build/` no está en `public_html` | Copiar `public/build/` a `public_html/build/` |

---

*Generado tras despliegue real en Hostinger Shared Hosting — Mayo 2026*
