# Auditoría de seguridad — Chat en vivo
Fecha: 2026-11-16 | Entorno analizado: local (producción: icorebyte.com / Hostinger hcdn)

---

## Resumen ejecutivo

El sistema tiene un diseño de seguridad sólido: IDOR bien cerrado con middleware, tokens de alta entropía almacenados hasheados, 2FA obligatoria en el panel, escape HTML correcto en widget y panel, y webhook de Telegram con `hash_equals`. Se encontraron dos hallazgos explotables (uno alto, uno medio-alto) y varios de menor gravedad.

---

## HALLAZGOS

### 🔴 ALTO — Open redirect: token de reanudación enviado a dominio externo

**Archivo:** `routes/web.php:84`
**Explotación:**
1. El atacante crea una conversación con `page_url: https://evil.com/page`
2. Espera a recibir la notificación por email
3. Hace clic en el enlace de reanudación
4. El servidor redirige a `https://evil.com/page#chat=uuid.newToken`
5. JavaScript en evil.com puede leer `window.location.hash` y robar el nuevo token de visitante

`page_url` se valida como `'url'` pero no se restringe al dominio propio. El nuevo token `newRawToken` aparece en el fragmento, que el server de evil.com no ve, pero cualquier JS de esa página sí.

**Arreglo:** Validar que `page_url` comience con `config('app.url')` antes de redirigir. Si no, redirigir a `/`.

---

### 🟠 MEDIO-ALTO — GDPR: adjuntos NO se borran en el purge

**Archivos:** `app/Console/Commands/Chat/PurgeOldConversations.php:87` y `app/Http/Controllers/Chat/MessageController.php:75`

`ImageProcessor::process()` devuelve sólo el nombre de archivo (`uuid.jpg`). `MessageController` lo guarda en `attachment_path` como `uuid.jpg`. Pero los archivos se guardan en disco en `chat/attachments/uuid.jpg`. El purge hace:

```php
Storage::disk('private')->delete($msg->attachment_path); // 'uuid.jpg'
// ⬆ intenta borrar en la raíz del disco privado → archivo NO encontrado → silencio
```

Los archivos de fotos enviadas por visitantes nunca se borran tras 90 días. Los tests no detectan esto porque crean los mensajes con `attachment_path = 'chat/attachments/test.jpg'` manualmente (incoherente con la producción).

**Arreglo:** Corregir la ruta en el purge o almacenar la ruta completa relativa desde el principio.

---

### 🟡 MEDIO — Rate limiting ciego con CDN

**Archivo:** `app/Providers/AppServiceProvider.php:39-53` + `bootstrap/app.php:28-34`

Todos los rate limiters usan `$request->ip()`. Cuando `TRUSTED_PROXIES` está vacío (valor por defecto en producción), `$request->ip()` = `REMOTE_ADDR` = IP del CDN de Hostinger. Resultado: **todos los visitantes comparten el mismo bucket de rate limit**. Un atacante puede vaciar el límite diario de chat.create (20/día) afectando a visitantes legítimos, o el CDN puede falsear los contadores.

Hostinger hcdn no publica rangos IP oficiales. Opciones:
- `TRUSTED_PROXIES=*` confía en cualquier X-Forwarded-For (spoofeable desde fuera del CDN → peor)
- `TRUSTED_PROXIES=<IP-CDN>` confía solo en la IP del CDN (hay que identificarla con `/api/chat/proxy-info`)
- Usar `X-Real-IP` si Hostinger la envía (verificar con proxy-info)

**Arreglo:** Usar `proxy-info` para identificar la IP/cabecera del CDN, luego configurar `TRUSTED_PROXIES` con ese rango o IP.

---

### 🟡 MEDIO — `sender` en `ChatMessage::$fillable`

**Archivo:** `app/Models/ChatMessage.php:12`

`sender` está en `$fillable`. Aunque `MessageController::store()` lo fuerza a `'visitor'` y la API nunca lee `sender` del request, el campo fillable permite que cualquier llamada a `create()` o `fill()` con datos externos pueda sobrescribir el sender. Riesgo actual bajo (el controlador no pasa el request al `create`), pero es un riesgo latente en refactorizaciones.

**Arreglo:** Eliminar `sender` de `$fillable` y usar `forceFill()` en los puntos internos donde se asigna.

---

### 🟢 BAJO — Nonce de reanudación en query string (logs de acceso)

**Archivo:** `app/Mail/Chat/ConversationNotificationMailable.php:33`

El nonce viaja en la query string de la URL firmada (`?nonce=xyz&expires=...&signature=...`). Cualquier log de acceso del servidor (Hostinger, CDN, proxies intermedios) lo registra. Si alguien lee los logs antes de que el visitante use el enlace, podría consumirlo primero. Mitigado por uso único y expiración de 7 días.

**Arreglo:** Ninguno práctico para un enlace GET por email. Documentar como riesgo aceptado. Reducir la expiración si se quiere minimizar la ventana.

---

### 🟢 BAJO — Race condition en idempotencia del webhook

**Archivo:** `app/Http/Controllers/Chat/TelegramController.php:45-48`

```php
if (TelegramUpdate::where('update_id', $updateId)->exists()) {
    return response('', 200);
}
TelegramUpdate::create(['update_id' => $updateId]);
```

No hay transacción ni índice UNIQUE. Con dos requests simultáneos del mismo `update_id`, ambos pueden pasar el check antes de que alguno inserte. Resultado: mensaje duplicado. Telegram puede reenviar el mismo update_id en reintentos.

**Arreglo:** Índice UNIQUE en `telegram_updates.update_id` + capturar `QueryException` en el insert.

---

## LO QUE ESTÁ BIEN ✅

| Área | Estado |
|------|--------|
| IDOR conversaciones | ✅ Middleware valida token + uuid en TODAS las rutas |
| Entropía del token | ✅ `bin2hex(random_bytes(32))` = 256 bits |
| Almacenamiento del token | ✅ Solo hash SHA-256 en BD |
| Comparación | ✅ `hash_equals` en middleware y resume route |
| Invalidación del nonce | ✅ Se pone a null tras primer uso |
| Token en fragmento (#) | ✅ Nunca llega al servidor, nunca en logs |
| Referrer-Policy: no-referrer | ✅ En la respuesta del resume |
| Mass assignment sender | ✅ Controller hardcodea `'visitor'`, no lee de request |
| XSS widget | ✅ `{{ }}` en Vue (auto-escape) |
| XSS panel Filament | ✅ `{{ }}` en Blade (auto-escape) |
| XSS Telegram | ✅ `htmlspecialchars()` en todos los campos de visitante |
| XSS email | ✅ `{{ $name }}` con Blade y `e()` en PHP |
| Webhook secret | ✅ `hash_equals` del X-Telegram-Bot-Api-Secret-Token |
| Filtro chat_id webhook | ✅ Filtra message y callback_query |
| Panel is_agent | ✅ `canAccessPanel` → `$this->is_agent` |
| 2FA obligatoria | ✅ `multiFactorAuthentication(isRequired: true)` |
| Path traversal adjuntos | ✅ Regex de ruta + `Str::contains` check |
| Signed URL adjuntos | ✅ Expiración 60 min, firma HMAC |
| Adjunto ligado a conversación | ✅ Indirectamente (token valida acceso a mensajes) |
| Honeypot bot | ✅ campo `website` con `max:0` |
| proxy-info desactivado | ✅ 404 con `CHAT_DIAGNOSE_TOKEN` vacío |
| APP_DEBUG production | ✅ Falso por defecto (`env('APP_DEBUG', false)`) |
| GDPR purge cutoff | ✅ Usa max(last_visitor, last_agent, created_at) correctamente |
| Token y nonce ocultos en BD | ✅ `$hidden` en modelo |

---

## Cambios en producción requeridos tras el fix

```bash
# Tras el commit, en el servidor:
php artisan optimize:clear
php artisan optimize

# Si se configura TRUSTED_PROXIES (tras identificar IP del CDN con proxy-info):
# En .env de producción:
TRUSTED_PROXIES=<ip-del-cdn-de-hostinger>
```
