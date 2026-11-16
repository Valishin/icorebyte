# Despliegue del chat en Hostinger — guía paso a paso

> El sitio **ya está en producción**. Esta guía describe el primer despliegue del módulo de chat
> sobre la instalación existente. Los pasos de creación de proyecto, generación de APP_KEY y
> `cp .env.example .env` **no se ejecutan**; el `.env` ya existe y no debe tocarse salvo para
> AÑADIR las variables nuevas de la sección §1.
>
> El despliegue se integra con `deploy.sh` y `deploy-laravel-inertia-hostinger.md` existentes.
> Los pasos marcados con ♻️ forman parte del ciclo de actualización normal.

---

## Requisitos del servidor

| Requisito | Versión mínima |
|-----------|---------------|
| PHP | 8.4 |
| MySQL | 8.0 **o MariaDB 10.6+** |
| Extensiones PHP | `pdo_mysql`, `gd`, `fileinfo`, `mbstring`, `openssl`, `bcmath` |

> **MariaDB**: todas las migraciones del chat son compatibles con MariaDB 10.6+.
> Se usan solo `varchar`, `bigint`, `timestamp`, `tinyint` y `enum` — ningún tipo
> exclusivo de MySQL 8 ni sintaxis incompatible con MariaDB.

---

## 1. Variables nuevas en `.env` de producción

**No toques `APP_KEY`**. Solo añade las líneas que falten al `.env` existente:

```dotenv
# ── Identidad ────────────────────────────────────────────────────────────────
APP_NAME="iCOREBYTE"
MAIL_FROM_NAME="iCOREBYTE"

# ── Cola y caché (recomendado para hosting compartido) ────────────────────────
# La cola 'database' funciona sin Redis. Los jobs (Telegram, emails de
# notificación) se procesan una vez por minuto mediante queue:work
# --stop-when-empty, que el scheduler lanza automáticamente.
QUEUE_CONNECTION=database
CACHE_STORE=database

# ── Chat ─────────────────────────────────────────────────────────────────────
CHAT_ENABLED=true            # false → widget oculto, /api/chat/* 503, inbox oculto
                             # (se lee en tiempo de ejecución vía prop de Inertia;
                             #  NO requiere recompilar el build — solo optimize)
CHAT_AGENT_NAME="Alex · iCOREBYTE"
CHAT_AGENT_PHONE="+34 678 758 210"
CHAT_NO_REPLY_MINUTES=5
CHAT_TIMEZONE=Europe/Madrid
VITE_WHATSAPP_URL=https://wa.me/34678758210  # necesario en local al compilar

# ── Telegram ─────────────────────────────────────────────────────────────────
CHAT_TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=          # token del bot (de BotFather)
TELEGRAM_CHAT_ID=            # tu chat privado con el bot — ver §6
TELEGRAM_WEBHOOK_SECRET=     # secreto aleatorio min. 32 chars; genera con:
                             #   openssl rand -hex 32

# ── Correo (SMTP Hostinger) ───────────────────────────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=info@icorebyte.com
MAIL_PASSWORD=               # contraseña del buzón de Hostinger
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@icorebyte.com

# ── Diagnóstico de proxy (temporal; vaciar después de §8) ─────────────────────
CHAT_DIAGNOSE_TOKEN=         # secreto temporal — vaciar tras configurar TRUSTED_PROXIES

# ── Proxies de confianza (configurar tras §8) ─────────────────────────────────
TRUSTED_PROXIES=             # IPs/CIDRs separados por coma; puede quedarse vacío
```

---

## 2. Build en local y subida de assets ♻️

El build **se genera en local** (el servidor Hostinger no tiene Node). La variable
`VITE_WHATSAPP_URL` debe estar en tu `.env` local antes de compilar.
`CHAT_ENABLED` **no** necesita estar en el build — se lee en tiempo de ejecución
vía prop compartida de Inertia; basta con `$PHP artisan optimize` para que el cambio
surta efecto.

```bash
pnpm run build          # genera public/build/
```

El script `deploy.sh` sube el build y ejecuta el resto automáticamente:

```bash
pnpm run deploy         # o: ./deploy.sh
```

> `deploy.sh` detecta si `composer.lock` o `database/migrations/` cambiaron y ejecuta
> `composer install --no-dev` y `php artisan migrate --force` según corresponda.
> También ejecuta `filament:assets` y `optimize` al final.

Si quieres ejecutar los pasos manualmente (SSH):

```bash
export PHP=/opt/alt/php84/usr/bin/php
cd ~/domains/icorebyte.com/icorebyte

git pull
$PHP artisan migrate --force
$PHP artisan optimize:clear
$PHP artisan optimize
$PHP artisan filament:assets
```

> **Ruta real del PHP 8.4**: si `/opt/alt/php84/usr/bin/php` no existe, compruébala con
> `ls /opt/alt/php*/usr/bin/php`. La ruta está también hardcodeada en `deploy.sh`
> (variable `PHP`).

---

## 3. Exportar la base de datos ANTES de migrar

> Siempre haz backup antes de ejecutar migraciones en producción.

**Opción A — phpMyAdmin** (hPanel → Bases de datos → phpMyAdmin → Exportar → Quick → Go)

**Opción B — línea de comandos** (SSH):

```bash
mysqldump -u USUARIO -p NOMBRE_BBDD > backup_$(date +%Y%m%d_%H%M%S).sql
```

Sustituye `USUARIO` y `NOMBRE_BBDD` por los valores de tu `.env`.

---

## 4. Migraciones del chat

Las migraciones nuevas son (en orden):

| Archivo | Qué hace |
|---------|----------|
| `2026_11_11_171414_create_chat_conversations_table` | Tabla principal de conversaciones |
| `2026_11_11_171415_create_chat_messages_table` | Mensajes |
| `2026_11_11_181914_create_telegram_updates_table` | Idempotencia de updates Telegram |
| `2026_11_11_182720_create_telegram_outbound_table` | Registro de mensajes enviados a Telegram |
| `2026_11_11_183013_create_settings_table` | Tabla de configuración del panel |
| `2026_11_11_183014_add_locale_no_reply_to_chat_conversations` | Columnas `locale` y `no_reply_warned_at` |
| `2026_11_11_193532_add_is_agent_to_users_table` | Flag `is_agent` en users |
| `2026_11_11_193533_create_quick_replies_table` | Respuestas rápidas |
| `2026_11_11_195722_add_app_authentication_to_users_table` | Columnas 2FA (Filament) |
| `2026_11_12_000001_add_notification_fields_to_chat_conversations` | Email y timestamp de notificación |
| `2026_11_12_000002_replace_cipher_with_nonce_on_chat_conversations` | Elimina cipher; añade nonce y resume_token |

**Rollback** (deshace las 11 migraciones del chat en orden inverso):

```bash
$PHP artisan migrate:rollback --step=11
```

Todas son reversibles (usan `dropColumn`, `dropTable`). El rollback no borra datos
de tablas no relacionadas con el chat.

---

## 5. Cron en hPanel (Hostinger)

hPanel usa **hPanel → Hosting → Avanzado → Cron Jobs** (no cPanel).

Añade esta tarea:

```
* * * * *   /opt/alt/php84/usr/bin/php /home/u863791557/domains/icorebyte.com/icorebyte/artisan schedule:run >> /dev/null 2>&1
```

Adapta la ruta del PHP y del proyecto a las tuyas (ver §2 para obtener la ruta real).

**Tareas que activa el scheduler** (una vez configurado el cron):

| Tarea | Frecuencia | Qué hace |
|-------|-----------|----------|
| `chat:no-reply-warning` | Cada minuto | Envía aviso por Telegram cuando hay mensajes sin respuesta |
| `queue:work --stop-when-empty --max-time=50` | Cada minuto | Procesa jobs de cola (Telegram, emails de notificación) |
| `chat:cleanup-telegram-updates` | Diario | Limpia registros de idempotencia Telegram > 7 días |
| `chat:purge` | Diario 03:00 | Elimina conversaciones sin actividad en 90 días (RGPD) |

> En hosting compartido **no hay `queue:work` en background**. El scheduler lanza
> `queue:work --stop-when-empty --tries=3 --timeout=55 --max-time=50` una vez por
> minuto. `--max-time=50` fuerza la salida a los 50 s aunque haya jobs nuevos,
> evitando acumulación de procesos. `withoutOverlapping()` impide que se solapen
> dos instancias. Los jobs son: `SendTelegramMessageJob` y
> `SendConversationNotificationJob` (email de reanudación). Latencia máxima: ~1 min.

---

## 6. Crear el agente y configurar 2FA

```bash
$PHP artisan chat:create-agent
```

Tras crear el agente:
1. Entra en `https://icorebyte.com/admin`.
2. Ve a **Perfil → Autenticación de dos factores** y activa 2FA (app TOTP).
3. Guarda los códigos de recuperación en un gestor de contraseñas.

---

## 7. Configurar el webhook de Telegram

### 7a. Registrar el webhook

```bash
$PHP artisan chat:telegram-setup
```

### 7b. Verificar

```bash
$PHP artisan chat:telegram-test
```

Deberías recibir un mensaje de prueba en tu chat privado con el bot.

---

## 8. Obtener tu `TELEGRAM_CHAT_ID`

`TELEGRAM_CHAT_ID` es el ID de tu chat privado con el bot (número **positivo**).

1. Envía cualquier mensaje al bot en Telegram.
2. Desde tu ordenador:
   ```bash
   curl -s "https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/getUpdates" | python3 -m json.tool
   ```
3. Busca `"chat": {"id": <número>}` en la respuesta. Ese número es tu `TELEGRAM_CHAT_ID`.

> Si la respuesta está vacía, desactiva el webhook antes de llamar a `getUpdates`:
> ```bash
> curl -s "https://api.telegram.org/bot<TOKEN>/deleteWebhook"
> ```
> Luego vuelve a enviar un mensaje y llama a `getUpdates`. Registra el webhook
> de nuevo con `chat:telegram-setup` cuando hayas obtenido el ID.

---

## 9. Diagnóstico de proxy y `TRUSTED_PROXIES`

Hostinger puede usar un proxy inverso o Cloudflare. Sin `TRUSTED_PROXIES` la IP
registrada en el chat sería la del proxy, no la del visitante.

### 9a. Activar el endpoint (temporal)

Añade en `.env`:
```dotenv
CHAT_DIAGNOSE_TOKEN=secreto_temporal_aleatorio_32chars
```
Ejecuta `$PHP artisan optimize` para aplicar.

### 9b. Lanzar desde tu ordenador

```bash
curl -s -H "X-Diagnose-Token: secreto_temporal_aleatorio_32chars" \
  https://icorebyte.com/api/chat/proxy-info | python3 -m json.tool
```

Obtendrás algo como:
```json
{
  "remote_addr":      "...",
  "x_forwarded_for":  "...",
  "cf_connecting_ip": "...",
  "laravel_ip":       "...",
  "trusted_proxies":  "(ninguno)"
}
```

Envía esa salida para decidir:

- Si `laravel_ip` **coincide con tu IP pública** (compruébala con `curl ifconfig.me`),
  el servidor ve directamente al cliente sin proxy y `TRUSTED_PROXIES` puede quedarse vacío.
- Si `laravel_ip` es una IP interna o de Cloudflare, configura `TRUSTED_PROXIES` con
  el CIDR de ese proxy. Los rangos de Cloudflare están en [cloudflare.com/ips](https://www.cloudflare.com/ips/).

### 9c. Desactivar el endpoint

```dotenv
CHAT_DIAGNOSE_TOKEN=
```
```bash
$PHP artisan optimize
```
Verifica que `https://icorebyte.com/api/chat/proxy-info` devuelve `404`.

---

## 10. Pruebas de humo en producción

- [ ] `https://icorebyte.com` carga correctamente.
- [ ] `/sat-it` redirige a `/icb-sat` (redirección de compatibilidad).
- [ ] El widget de chat aparece en la esquina inferior derecha.
- [ ] Iniciar conversación (nombre + consentimiento) → bot de Telegram recibe el aviso.
- [ ] Responder desde Telegram → el mensaje aparece en el chat del visitante.
- [ ] Adjuntar una imagen → se muestra en el chat y llega a Telegram.
- [ ] Acceder a `/admin` → redirige al login de Filament.
- [ ] Crear un agente nuevo con `chat:create-agent` → forzar activación de 2FA desde el perfil antes de acceder a la bandeja.
- [ ] Dejar un mensaje sin responder **más de `CHAT_NO_REPLY_MINUTES` minutos** con un correo válido → el agente recibe el aviso por Telegram y el visitante recibe el email de reanudación. Usar el enlace → se abre el chat con la conversación.
- [ ] Verificar que el cron funciona: `$PHP artisan schedule:list` debe mostrar las 4 tareas; esperar el siguiente minuto y comprobar que llega el aviso de no-respuesta real.
- [ ] Probar en un dispositivo iOS real y en un dispositivo Android real (layout, apertura del chat, envío de mensajes).
- [ ] `https://icorebyte.com/api/chat/proxy-info` → `404` (token vaciado en §9c).
- [ ] Revisar `storage/logs/laravel.log` — sin errores inesperados.

---

## 11. Interruptor de emergencia (`CHAT_ENABLED`)

Si necesitas desactivar el chat sin revertir código:

```dotenv
CHAT_ENABLED=false
```
```bash
$PHP artisan optimize
```

Efectos inmediatos:
- El widget **no se monta** en el frontend: `app.js` lee el prop `chat_enabled` que Inertia inyecta en el `data-page` del DOM — **sin recompilar**.
- Todas las rutas `/api/chat/*` devuelven `503` excepto el webhook de Telegram, que siempre recibe `200`.
- La bandeja de chat desaparece del menú de Filament.

Para restaurar: `CHAT_ENABLED=true` + `$PHP artisan optimize`.

---

## 12. Plan de vuelta atrás

### Opción A — Revertir migraciones del chat

```bash
$PHP artisan migrate:rollback --step=11
```

(Ver §4 para la lista completa. Todas son reversibles y no afectan datos fuera del chat.)

### Opción B — Desactivar el chat sin revertir

Usar el interruptor de §11. Es la opción más rápida.

### Opción C — Revertir el código

```bash
git checkout <commit_anterior_al_chat>
# en local:
pnpm run build
# subir build y ejecutar en servidor:
./deploy.sh
```

### Restaurar base de datos desde backup

hPanel → Bases de datos → phpMyAdmin → Importar el `.sql` del backup (§3),
o desde SSH:

```bash
mysql -u USUARIO -p NOMBRE_BBDD < backup_YYYYMMDD_HHMMSS.sql
```
