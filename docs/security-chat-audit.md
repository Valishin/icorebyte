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

### 🟡 MEDIO — CORS: `/api/chat` respondía con `Access-Control-Allow-Origin: *`

**Archivo:** `config/cors.php` (creado)

El proyecto no tenía `config/cors.php`. El framework aplica por defecto `'allowed_origins' => ['*']` a todas las rutas `api/*`. Resultado: cualquier origen podía llamar a la API de chat desde JS de terceros.

**Impacto:** Un script en evil.com podía crear conversaciones falsas, leer mensajes de cualquier conversación cuyo token obtuviera (p.ej. robado de localStorage), y agotar los rate-limits de visitantes legítimos. Con CORS wildcard y `supports_credentials: false`, las cookies no viajan —pero los tokens Bearer sí.

**Arreglo:** Crear `config/cors.php` con `'allowed_origins' => [env('APP_URL')]`. Solo el dominio propio puede hacer llamadas cross-origin.

**Test:** `SecurityXssAdminCorsTest::test_cors_rejects_request_from_unknown_origin` y `test_cors_allows_request_from_app_url`.

---

## TABLA DE COBERTURA

| # | Área auditada | Hallazgo | Estado | Test que lo demuestra |
|---|---------------|----------|--------|----------------------|
| 1 | IDOR — token por conversación | Sin problemas — middleware valida token + uuid | ✅ Confirmado seguro | `SecurityAttackTest::test_idor_visitor_token_of_conv_a_cannot_read_conv_b`, `test_idor_wrong_token_cannot_post_message_to_conversation`, `TokenAuthTest` (5 tests) |
| 2 | XSS — widget, Filament, Telegram, email | Sin problemas — Vue `{{ }}`, Blade `{{ }}`, `htmlspecialchars()` en Telegram | ✅ Confirmado seguro | `SecurityXssAdminCorsTest` (4 tests: message body, visitor name, Telegram dispatch x2, email x2) |
| 3 | Webhook Telegram — secret, chat_id, callback_query | Sin problemas — `hash_equals` del secret; filtra chat_id en message y callback_query | ✅ Confirmado seguro | `TelegramWebhookTest::test_missing_secret_returns_200_silently`, `test_wrong_secret_returns_200_silently`, `test_message_from_wrong_chat_id_is_ignored`, `test_callback_query_from_wrong_chat_id_is_ignored` |
| 4 | Panel /admin — is_agent, 2FA obligatoria | Sin problemas — `canAccessPanel` → `is_agent`; Filament bloquea con 403 | ✅ Confirmado seguro | `SecurityXssAdminCorsTest::test_non_agent_user_cannot_access_admin_panel`, `test_non_agent_user_cannot_access_filament_livewire_endpoint` |
| 5 | Rate limiting / CDN proxy (X-Forwarded-For falso) | Sin problemas en tests — `TRUSTED_PROXIES` vacío = ignora XFF | ✅ Confirmado seguro (ver nota) | `TrustProxiesTest::test_spoofed_x_forwarded_for_does_not_change_rate_limit_key`, `test_rate_limit_key_uses_remote_addr_not_forwarded_for` |
| 6 | Open redirect (page_url) | **🔴 ALTO — CORREGIDO** — `page_url` externo redirigía con token en fragmento | ✅ Corregido | `SecurityAttackTest::test_resume_redirect_with_external_page_url_goes_to_home`, `test_resume_redirect_with_own_domain_page_url_is_allowed` |
| 7 | GDPR purge — adjuntos borrados | **🟠 MEDIO-ALTO — CORREGIDO** — ruta `'uuid.jpg'` no encontraba `'chat/attachments/uuid.jpg'` | ✅ Corregido | `SecurityAttackTest::test_purge_deletes_attachment_file_from_disk`, `PurgeOldConversationsTest::test_purge_removes_attachment_files` |
| 8 | CORS (`/api/chat` con `*`) | **🟡 MEDIO — CORREGIDO** — framework default aplicaba wildcard | ✅ Corregido | `SecurityXssAdminCorsTest::test_cors_rejects_request_from_unknown_origin`, `test_cors_allows_request_from_app_url` |

> **Nota sobre rate limiting en producción:** Los tests usan REMOTE_ADDR = 127.0.0.1 (sin CDN), por lo que los tests pasan. En producción con Hostinger hcdn, `$request->ip()` = IP del CDN → todos los visitantes comparten el mismo bucket. Ver sección siguiente.

---

## CDN DE HOSTINGER (hcdn) — TRUSTED_PROXIES

### Hallazgos de la investigación

Hostinger no publica documentación oficial sobre qué cabecera usa su CDN (hcdn) para la IP real del cliente, ni rangos de IP oficiales. Las observaciones de la comunidad y el comportamiento típico de hcdn (basado en LiteSpeed y Nginx) sugieren `X-Forwarded-For`, pero no está garantizado.

**Para determinarlo con certeza en producción:** usar el endpoint de diagnóstico del propio proyecto:

```
GET /api/chat/proxy-info
X-Diagnose-Token: <CHAT_DIAGNOSE_TOKEN de .env>
```

Este endpoint devuelve todas las cabeceras recibidas, incluidas `X-Forwarded-For`, `X-Real-IP` y `CF-Connecting-IP` si las hubiera.

### Opciones de configuración (de más segura a menos)

#### Opción A — IP/CIDR fijo del CDN (más segura, difícil de mantener)
```env
TRUSTED_PROXIES=185.230.xxx.xxx/24
TRUSTED_HEADERS=FORWARDED|X_FORWARDED_FOR|X_FORWARDED_HOST|X_FORWARDED_PORT|X_FORWARDED_PROTO
```
- **Ventaja:** Solo confía en proxies conocidos. Un atacante externo no puede spoofear la IP.
- **Riesgo:** Si Hostinger rota las IPs del edge (fra-edge7, mad-edge2, etc.), la IP "de confianza" deja de coincidir con `REMOTE_ADDR` → `$request->ip()` vuelve a ser la IP del CDN → rate limiting ciego.
- **Cómo obtenerla:** Leer REMOTE_ADDR en `/api/chat/proxy-info` desde producción.

#### Opción B — Wildcard `*` (más simple, riesgo controlado)
```env
TRUSTED_PROXIES=*
TRUSTED_HEADERS=X_FORWARDED_FOR
```
- **Comportamiento:** Laravel confía en CUALQUIER `X-Forwarded-For`, incluido uno falsificado por un atacante directo.
- **Riesgo real:** Un atacante que llega directamente al servidor (saltándose el CDN) puede poner cualquier IP en `X-Forwarded-For` y bypassear el rate limit completamente.
- **Cuándo es aceptable:** Si y solo si el servidor **no es accesible públicamente** en los puertos 80/443 sin pasar por el CDN. Con Hostinger Shared/Cloud, el origen suele ser accesible directamente — hay que verificarlo.

**Verificación:** `curl -H "X-Forwarded-For: 1.2.3.4" https://icorebyte.com/api/chat/proxy-info | grep remote_addr`. Si `remote_addr` es la IP del CDN (no 1.2.3.4), el servidor NO filtra XFF antes de llegar a Laravel → `TRUSTED_PROXIES=*` es arriesgado.

#### Opción C — Sin TRUSTED_PROXIES (estado actual en producción)
- `$request->ip()` = IP del CDN → rate limiting ciego (todos comparten bucket)
- XFF falso ignorado (no peor que el estado actual en cuanto a seguridad de rate limit)
- **Recomendación:** Peor que las opciones A y B para rate limiting; igual de seguro frente a spoofing.

### Recomendación

1. Ejecutar `/api/chat/proxy-info` en producción con tráfico real para identificar REMOTE_ADDR y qué cabecera lleva la IP del visitante.
2. Si REMOTE_ADDR es siempre la misma IP del CDN: usar **Opción A** con ese CIDR.
3. Si REMOTE_ADDR varía (edge IPs rotan): usar **Opción B** solo si el servidor no es accesible sin CDN. Verificar cerrando el puerto 80/443 al tráfico no-CDN en el firewall de Hostinger.
4. Si no es posible restringir el acceso directo: documentar el rate limiting ciego como riesgo aceptado (un atacante puede vaciar el límite diario de chat.create pero no spoofear la IP de otro visitante para hacer más daño).

---

## Cambios en producción requeridos tras el fix

```bash
# Tras el commit, en el servidor:
php artisan optimize:clear
php artisan optimize

# CORS: config/cors.php ya está publicado — no requiere cambios en .env.

# Rate limiting / CDN (ver sección TRUSTED_PROXIES):
# Una vez identificada la IP del CDN con proxy-info:
TRUSTED_PROXIES=<ip-del-cdn-de-hostinger>
TRUSTED_HEADERS=X_FORWARDED_FOR
```
