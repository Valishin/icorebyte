# Despliegue del chat en Hostinger — guía paso a paso

> Guía para el primer despliegue completo. Para actualizaciones posteriores, solo son necesarios los pasos marcados con ♻️.

---

## 0. Requisitos previos

| Requisito | Versión mínima |
|-----------|---------------|
| PHP | 8.4 |
| MySQL | 8.0 |
| Node.js | 20 LTS |
| Composer | 2.x |
| pnpm | 9.x |

Extensiones PHP necesarias: `pdo_mysql`, `gd`, `fileinfo`, `mbstring`, `openssl`, `bcmath`, `ctype`, `tokenizer`, `xml`, `curl`.

---

## 1. Variables de entorno `.env`

Copia `.env.example` como `.env` y configura estas variables:

### Aplicación

```dotenv
APP_NAME="iCoreByte"
APP_ENV=production
APP_KEY=              # generar con: php artisan key:generate
APP_DEBUG=false
APP_URL=https://icorebyte.com
```

### Base de datos (MySQL en Hostinger)

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1    # o el host interno de Hostinger
DB_PORT=3306
DB_DATABASE=nombre_bbdd
DB_USERNAME=usuario_bbdd
DB_PASSWORD=contraseña_bbdd
```

### Correo (Hostinger SMTP)

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=info@icorebyte.com
MAIL_PASSWORD=contraseña_correo
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@icorebyte.com
MAIL_FROM_NAME="iCoreByte"
```

### Chat — variables nuevas

```dotenv
# Telegram (obligatorio si CHAT_TELEGRAM_ENABLED=true)
CHAT_TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=123456789:AAFxxxxx   # Token del bot de BotFather
TELEGRAM_CHAT_ID=-100xxxxxxxxx          # ID del grupo/canal donde llegan los avisos
TELEGRAM_WEBHOOK_SECRET=secreto_largo_aleatorio  # min. 32 caracteres aleatorios

# Widget (opcional — anula los valores por defecto del código)
CHAT_AGENT_NAME="iCoreByte"
CHAT_AGENT_PHONE="+34 678 758 210"
CHAT_NO_REPLY_MINUTES=5
CHAT_TIMEZONE=Europe/Madrid

# WhatsApp (Vite — se incrusta en el JS de producción)
VITE_WHATSAPP_URL=https://wa.me/34678758210

# Diagnóstico de proxy — SOLO durante la configuración inicial; vaciar después
CHAT_DIAGNOSE_TOKEN=   # dejar vacío → ruta proxy-info invisible

# Proxies de confianza (IPs o rangos CIDR separados por coma)
# Configurar DESPUÉS de leer /api/chat/proxy-info (ver sección 7)
TRUSTED_PROXIES=
```

---

## 2. Instalación y compilación ♻️

```bash
composer install --no-dev --optimize-autoloader
pnpm install --frozen-lockfile
pnpm run build
```

---

## 3. Migraciones y assets ♻️

```bash
php artisan migrate --force
php artisan storage:link
php artisan filament:assets
php artisan optimize
```

> `storage:link` crea el enlace simbólico `public/storage → storage/app/public`. Solo necesario en el primer despliegue o si se recrea el directorio.

---

## 4. Cron de Laravel (Hostinger cPanel)

En **cPanel → Tareas programadas (Cron Jobs)**, añade:

```
* * * * * /usr/local/bin/php /home/usuario/public_html/artisan schedule:run >> /dev/null 2>&1
```

Ajusta la ruta a `artisan` según tu estructura de directorios en Hostinger.

Esto activa:
- `chat:no-reply-warning` cada minuto
- `chat:purge` diariamente a las 03:00 (limpieza RGPD 90 días)
- `chat:cleanup-telegram-updates` diariamente

---

## 5. Crear el agente y configurar 2FA

```bash
php artisan chat:create-agent
```

El comando pide nombre, correo y contraseña de forma interactiva.

Después de crear el agente:
1. Entra en `https://icorebyte.com/admin` con las credenciales creadas.
2. Ve a **Perfil → Autenticación de dos factores** y activa 2FA (app TOTP, p. ej. Google Authenticator o Aegis).
3. Guarda los códigos de recuperación en un lugar seguro.

---

## 6. Configurar el webhook de Telegram

### 6a. Registrar el webhook

```bash
php artisan chat:telegram-setup
```

El comando registra el webhook en `https://icorebyte.com/api/chat/telegram/webhook` con el `TELEGRAM_WEBHOOK_SECRET` configurado.

### 6b. Verificar que funciona

```bash
php artisan chat:telegram-test
```

Deberías recibir un mensaje de prueba en el grupo/canal de Telegram configurado.

---

## 7. Diagnóstico de proxy y configuración de `TRUSTED_PROXIES`

Hostinger usa un proxy inverso (Cloudflare o proxy propio). Sin configurar `TRUSTED_PROXIES`, la IP registrada en el chat será la del proxy, no la del visitante.

### 7a. Activar el endpoint de diagnóstico (temporal)

En `.env`, añade un secreto aleatorio:

```dotenv
CHAT_DIAGNOSE_TOKEN=secreto_temporal_32chars_minimo
```

Ejecuta `php artisan optimize` para aplicar el cambio.

### 7b. Leer la IP detectada

```bash
curl -s -H "X-Diagnose-Token: secreto_temporal_32chars_minimo" \
  https://icorebyte.com/api/chat/proxy-info | python3 -m json.tool
```

La respuesta muestra:
- `ip_from_request`: IP que ve Laravel (debería ser la del proxy)
- `x_forwarded_for`: cadena de IPs del header `X-Forwarded-For`
- `trusted_proxies`: lo que está configurado ahora

### 7c. Configurar `TRUSTED_PROXIES`

Añade en `.env` el CIDR o IP del proxy de Hostinger que aparece en `ip_from_request`:

```dotenv
TRUSTED_PROXIES=1.2.3.4/32   # o el rango CIDR de Cloudflare / Hostinger
```

> Si Hostinger usa Cloudflare, los rangos actuales están en [cloudflare.com/ips](https://www.cloudflare.com/ips/).

### 7d. Desactivar el endpoint de diagnóstico

```dotenv
CHAT_DIAGNOSE_TOKEN=
```

```bash
php artisan optimize
```

Verifica que `https://icorebyte.com/api/chat/proxy-info` devuelve `404`.

---

## 8. Pruebas de humo en producción

Realiza estas comprobaciones manualmente después del despliegue:

- [ ] `https://icorebyte.com` carga correctamente.
- [ ] El widget de chat aparece en la esquina inferior derecha.
- [ ] Iniciar una conversación (nombre obligatorio, consentimiento) → el bot de Telegram recibe el aviso.
- [ ] Responder desde Telegram → el mensaje aparece en el chat del visitante.
- [ ] Adjuntar una imagen → se muestra en el chat y llega a Telegram.
- [ ] Enviar un mensaje de texto largo (>500 chars) → se recorta a 2000 chars.
- [ ] Campo honeypot: enviar el formulario con el campo `website` relleno → HTTP 422 (comprobar con DevTools).
- [ ] Si hay correo configurado: esperar el aviso de no-respuesta (`CHAT_NO_REPLY_MINUTES`) → llega el email de reanudación → el enlace abre el chat con la conversación.
- [ ] Reutilizar el enlace de reanudación → página de error amigable (410).
- [ ] Acceder a `/admin` → redirige al login de Filament.
- [ ] `https://icorebyte.com/api/chat/proxy-info` → `404`.
- [ ] Comprobar en los logs (`storage/logs/laravel.log`) que no hay errores inesperados.

---

## 9. Plan de vuelta atrás

Si algo falla después del despliegue:

### Opción A — Revertir el código (no hay migraciones que deshacer)

```bash
git checkout <commit_anterior>
composer install --no-dev --optimize-autoloader
pnpm run build
php artisan optimize
```

### Opción B — Deshacer la última migración (solo si se ejecutó una nueva)

```bash
php artisan migrate:rollback --step=1
```

> Las migraciones del chat añaden columnas nullable o crean tablas nuevas: el rollback es seguro y no destruye datos existentes.

### Opción C — Deshabilitar el chat sin revertir

Añade en `.env`:

```dotenv
CHAT_ENABLED=false   # si has implementado esta guardia en el frontend
```

O elimina el componente `<ChatFab>` del layout principal temporalmente.

### Restaurar desde backup

Hostinger hace backups automáticos diarios. Desde cPanel → **Backups** puedes restaurar la base de datos al estado anterior al despliegue.

---

## Resumen de variables `.env` nuevas

| Variable | Obligatoria | Descripción |
|----------|------------|-------------|
| `CHAT_TELEGRAM_ENABLED` | Si se usa Telegram | `true` para activar notificaciones |
| `TELEGRAM_BOT_TOKEN` | Si Telegram activo | Token del bot (BotFather) |
| `TELEGRAM_CHAT_ID` | Si Telegram activo | ID del grupo/canal destino |
| `TELEGRAM_WEBHOOK_SECRET` | Si Telegram activo | Secreto de validación del webhook |
| `CHAT_AGENT_NAME` | No | Nombre del agente visible al visitante |
| `CHAT_AGENT_PHONE` | No | Teléfono del agente |
| `CHAT_NO_REPLY_MINUTES` | No | Minutos hasta aviso de no-respuesta (defecto: 5) |
| `CHAT_TIMEZONE` | No | Zona horaria del horario del chat (defecto: Europe/Madrid) |
| `VITE_WHATSAPP_URL` | No | URL de WhatsApp para el botón del widget |
| `TRUSTED_PROXIES` | Sí (producción) | IPs/CIDRs del proxy inverso |
| `CHAT_DIAGNOSE_TOKEN` | Temporal | Solo durante configuración de proxy; vaciar después |
