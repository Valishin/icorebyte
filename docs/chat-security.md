# Chat widget — documentación técnica de seguridad

> Para la información orientada al usuario, véase la sección §5 de la Política de Privacidad.
> Este documento describe las decisiones técnicas de seguridad del sistema de chat.

---

## Tokens de sesión del visitante

- El servidor genera `bin2hex(random_bytes(32))` → 64 hex → 256 bits de entropía.
- En la respuesta `201 Created` el token en claro se devuelve **una sola vez** al cliente.
- En BD se guarda únicamente `hash('sha256', $rawToken)`. El token en claro no es recuperable desde el servidor.
- La comparación se hace siempre con `hash_equals()` para evitar ataques de timing.
- El cliente lo almacena en `localStorage` y lo envía en la cabecera `Authorization: Bearer <token>`.

## IP del visitante

- Se guarda `hash('sha256', $ip)`. La dirección IP en texto plano **nunca** se persiste.

## Flujo de reanudación por correo (Fase 5)

1. Se genera `$plainNonce = bin2hex(random_bytes(32))`.
2. En BD: `resume_nonce_hash = hash('sha256', $plainNonce)` (el nonce en claro no se almacena).
3. El email contiene `URL::temporarySignedRoute('chat.resume', 7 días, {uuid, nonce})`.
4. Al abrirlo, el servidor valida firma + caducidad + hash del nonce → invalida el nonce (lo pone a `null`).
5. Genera un nuevo token: `$newRawToken = bin2hex(random_bytes(32))`, almacena `hash('sha256', $newRawToken)` en `resume_token_hash`.
6. Redirige a `{page_url}#chat={uuid}.{newRawToken}` con:
   - `Referrer-Policy: no-referrer` — el fragment no viaja en Referer.
   - `Cache-Control: no-store` — no queda en caché ni en historial de proxy.
7. El fragment (`#…`) nunca llega al servidor; el widget lo lee en JS, lo guarda en localStorage y lo limpia con `history.replaceState`.

### Qué ocurre si se reutiliza el enlace o está caducado

- El servidor responde `410 Gone` con una página amigable (`resources/views/chat/resume-error.blade.php`).
- Nunca se revela si el enlace fue usado o caducó.

## Dos dispositivos activos simultáneamente

- El dispositivo original (A) usa `visitor_token` (hash en columna `visitor_token`).
- El dispositivo B usa `resume_token_hash` (hash en columna `resume_token_hash`).
- El middleware acepta cualquiera de los dos con `hash_equals()`.
- Para soportar más de dos dispositivos, habría que crear una tabla `chat_visitor_tokens` (N hashes por conversación). No implementado actualmente.

## Adjuntos

- El tipo MIME se valida con `finfo` (nunca por extensión).
- La imagen se re-codifica con GD para eliminar metadatos EXIF/GPS.
- HEIC excluido (GD no lo soporta).
- Se sirve únicamente con URL firmada temporal desde disco privado (nunca accesible directamente).

## Anti-spam / honeypot

- El formulario incluye un campo oculto `website`. Si llega no vacío, la petición se rechaza silenciosamente (HTTP 422).

## Proxy y dirección IP real

- `TrustProxies` **nunca** usa `'*'`. Configurable con la variable `TRUSTED_PROXIES` (p. ej., `10.0.0.0/8`).
- Por defecto (vacío), no se confía en ningún proxy.

## Diagnóstico de proxy

- Ruta `GET /api/chat/proxy-info` con cabecera `X-Diagnose-Token: <CHAT_DIAGNOSE_TOKEN>`.
- Devuelve `404` si `CHAT_DIAGNOSE_TOKEN` está vacío **o** incorrecto (misma respuesta para no revelar la existencia de la ruta).
- Comparación con `hash_equals()`.
- Vaciar `CHAT_DIAGNOSE_TOKEN` en `.env` una vez configurado `TRUSTED_PROXIES`.

## Webhook de Telegram

- El token secreto del webhook se valida con `hash_equals()`.
- El endpoint siempre devuelve `200` para no delatar errores internos a Telegram.
- Los `update_id` se persisten en `telegram_updates` para garantizar idempotencia.

## Cabeceras de seguridad HTTP

Configuradas en `app/Http/Middleware/SecurityHeaders.php`:

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
```

La respuesta de reanudación añade adicionalmente `Referrer-Policy: no-referrer` y `Cache-Control: no-store`.

## CSRF

- Las rutas `/api/chat/*` están excluidas de la verificación CSRF (usan autenticación por token Bearer).
- El resto de rutas web mantienen la protección CSRF estándar de Laravel.

## PHPStan

- Nivel 5, PHP 8.4. Ejecutar con:
  ```bash
  composer phpstan
  # o directamente:
  ./vendor/bin/phpstan analyse --memory-limit=512M
  ```
