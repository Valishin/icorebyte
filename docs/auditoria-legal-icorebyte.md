# Auditoría Legal y Técnica — iCoreByte
**Fecha:** Noviembre 2026  
**Ámbito:** icorebyte.com — web corporativa sin tienda online  
**Normativa aplicada:** RGPD (UE) 2016/679 · LOPDGDD 3/2018 · LSSI-CE 34/2002 · Guía cookies AEPD · RDL 1/2007 (parcial)

> ⚠️ **Aviso:** Esta auditoría es de carácter técnico-documental. No sustituye una revisión jurídica profesional ni garantiza el cumplimiento normativo completo. Se recomienda validación por abogado especializado en protección de datos antes de publicar cambios relevantes.

---

## 1. RESUMEN EJECUTIVO

### Estado general
La web presenta una base legal razonablemente estructurada, con documentos legales completos en su mayor parte y sin servicios de seguimiento (Google Analytics, Meta Pixel, GTM). Sin embargo, existe un **incumplimiento significativo** derivado del uso de Google Maps, cuya integración real no está reflejada en ninguno de los documentos legales, y un **incumplimiento crítico** por ausencia de NIF en el Aviso Legal.

### Conteo de hallazgos

| Nivel    | Cantidad |
|----------|----------|
| CRÍTICO  | 1        |
| ALTO     | 3        |
| MEDIO    | 3        |
| BAJO     | 3        |
| CORRECTO | 14       |

### Riesgos principales
1. **NIF ausente** — Incumplimiento directo del art. 10.1.b LSSI-CE. Sancionable por la AEPD o la Secretaría de Estado de Telecomunicaciones.
2. **Google Maps sin declarar** — La API de Google Maps se carga en la página de inicio y transmite datos a Google LLC. Ni la Política de Cookies ni la de Privacidad lo mencionan, contradiciendo lo declarado en ambas.
3. **Formularios sin aviso de privacidad en el punto de recogida** — El art. 13 RGPD obliga a informar en el momento de la recogida de datos, no solo mediante un enlace en el footer.

---

## 2. AUDITORÍA POR DOCUMENTO

### A. Aviso Legal (`/aviso-legal`)

| Requisito (art. 10 LSSI-CE) | Estado | Observación |
|-----------------------------|--------|-------------|
| Nombre/denominación | ✅ CORRECTO | "iCoreByte" |
| NIF | ❌ CRÍTICO | **Ausente**. Obligatorio por art. 10.1.b LSSI-CE |
| Domicilio | ✅ CORRECTO | Carretera de Palamós 57, Sant Feliu de Guíxols |
| Teléfono | ✅ CORRECTO | +34 678 758 210 |
| Correo electrónico | ✅ CORRECTO | info@icorebyte.com |
| Comunicación directa y efectiva | ✅ CORRECTO | Teléfono + email presentes |
| Información registral | ✅ N/A | Autónomo: no obligatorio |
| Actividad | ✅ CORRECTO | Descrita correctamente |
| Condiciones de uso | ✅ CORRECTO | Incluidas |
| Propiedad intelectual | ✅ CORRECTO | Incluida |
| Responsabilidad | ✅ CORRECTO | Incluida |
| Enlaces externos | ✅ CORRECTO | Incluidos |
| Privacidad/Cookies (remisión) | ✅ CORRECTO | Con enlaces |
| Legislación aplicable y jurisdicción | ✅ CORRECTO | Girona, legislación española |

**Hallazgo AV-01 — CRÍTICO:** El Aviso Legal no incluye el NIF del titular. El art. 10.1.b de la Ley 34/2002 (LSSI-CE) exige que el prestador de servicios de la sociedad de la información ponga a disposición del destinatario "su número de identificación fiscal". Su ausencia supone un incumplimiento directo y sancionable.

---

### B. Política de Privacidad (`/politica-de-privacidad`)

| Requisito (art. 13 RGPD) | Estado | Observación |
|--------------------------|--------|-------------|
| Identidad del responsable | ✅ CORRECTO | iCoreByte |
| NIF del responsable | ⚠️ BAJO | No incluido. No obligatorio por el RGPD pero es buena práctica |
| Domicilio y contacto | ✅ CORRECTO | Incluidos |
| Finalidades del tratamiento | ✅ CORRECTO | Gestión de consultas y presupuestos |
| Base jurídica | ⚠️ MEDIO | Declarada como "consentimiento (art. 6.1.a)" para el formulario de contacto/presupuesto. Para solicitudes de presupuesto, el art. 6.1.b (medidas precontractuales) es más adecuado y sólido jurídicamente |
| Categorías de datos | ✅ CORRECTO | Nombre, email, teléfono, servicio, mensaje |
| Destinatarios | ⚠️ MEDIO | Menciona "un proveedor de correo electrónico" sin identificarlo. Recomendable identificar al encargado del tratamiento |
| Transferencias internacionales | ❌ ALTO | Declara "no se realizan transferencias internacionales" pero Google Maps JS API transmite datos a Google LLC. Debe rectificarse |
| Plazos de conservación | ✅ CORRECTO | 3 años. Justificado con referencia al Código Civil |
| Derechos del interesado | ✅ CORRECTO | Acceso, rectificación, supresión, oposición, limitación, portabilidad, retirada del consentimiento |
| Procedimiento de ejercicio | ✅ CORRECTO | Email info@icorebyte.com con copia DNI |
| Reclamación ante AEPD | ✅ CORRECTO | Incluido con enlace a aepd.es |
| Menores de edad | ✅ CORRECTO | 14 años (correcto según LOPDGDD art. 7) |
| Google Maps como encargado | ❌ ALTO | No mencionado. Google procesa datos de usuarios al cargar el mapa |
| Aviso en punto de recogida (formularios) | ❌ ALTO | El art. 13 RGPD exige información en el momento de la recogida, no solo en la política |

---

### C. Política de Cookies (`/politica-de-cookies`)

| Requisito | Estado | Observación |
|-----------|--------|-------------|
| Definición y clasificación de cookies | ✅ CORRECTO | Explicación clara y correcta |
| `laravel_session` declarada | ✅ CORRECTO | Proveedor, tipo, duración, finalidad |
| `XSRF-TOKEN` declarada | ✅ CORRECTO | Proveedor, tipo, duración, finalidad |
| `icorebyte-theme` (localStorage) declarada | ✅ CORRECTO | Correctamente distinguida de cookies |
| `icorebyte-cookies-accepted` (localStorage) | ❌ MEDIO | **No declarada**. CCookieBanner.vue almacena esta clave en localStorage y no aparece en la política |
| Cookies de Google Maps | ❌ ALTO | **No declaradas**. Google Maps JS API instala cookies propias (NID, CONSENT, etc.) que no figuran en la política |
| Declaración "no instala cookies de terceros" | ❌ ALTO | **Incorrecta**. Google Maps es un servicio de terceros que instala cookies |
| Instrucciones para gestionar cookies | ✅ CORRECTO | Con enlaces a los cuatro navegadores principales |
| Instrucciones para gestionar localStorage | ✅ CORRECTO | Incluidas |
| Información para modificar/retirar consentimiento | ✅ CORRECTO | Via navegador o herramientas de desarrollo |

---

## 3. AUDITORÍA TÉCNICA

### 3.1 Scripts y servicios externos detectados

| Servicio | Archivo | Transmite datos | Declarado |
|----------|---------|-----------------|-----------|
| Google Maps JS API (`@fawmi/vue-google-maps`) | `app.js`, `CContact.vue` | ✅ Sí — IP, navegador, posición a Google LLC | ❌ No |
| Bunny Fonts (`fonts.bunny.net`) | `app.blade.php` | Mínimo — alternativa GDPR-friendly de Google Fonts. No establece cookies. | ✅ N/A (no requiere declaración) |
| **Google Analytics** | — | ❌ No presente | ✅ N/A |
| **Google Tag Manager** | — | ❌ No presente | ✅ N/A |
| **Meta Pixel** | — | ❌ No presente | ✅ N/A |
| **reCAPTCHA** | — | ❌ No presente | ✅ N/A |
| GSAP | `package.json` | ❌ No — librería client-side | ✅ N/A |
| Swiper | `package.json` | ❌ No — librería client-side | ✅ N/A |

### 3.2 Cookies y almacenamiento local detectados

| Nombre | Tipo | Proveedor | Declarado en política |
|--------|------|-----------|----------------------|
| `laravel_session` | Cookie técnica | icorebyte.com | ✅ Sí |
| `XSRF-TOKEN` | Cookie técnica | icorebyte.com | ✅ Sí |
| `icorebyte-theme` | localStorage | icorebyte.com | ✅ Sí |
| `icorebyte-cookies-accepted` | localStorage | icorebyte.com | ❌ No |
| Cookies Google Maps (NID, CONSENT, etc.) | Cookies terceros | Google LLC | ❌ No |

### 3.3 Formularios

**Formulario 1 — Contacto general (`CContact.vue`, página `/`)**

| Verificación | Estado |
|-------------|--------|
| Datos solicitados | Nombre, email, teléfono (opcional), servicio, mensaje |
| ¿Necesidad de todos los datos? | ✅ Proporcional a la finalidad |
| Aviso de privacidad en el punto de recogida | ❌ AUSENTE |
| Enlace a Política de Privacidad | ❌ AUSENTE |
| Base jurídica informada en el formulario | ❌ AUSENTE |
| Casillas premarcadas | ✅ No existen |
| Throttle/protección anti-spam | ✅ Middleware `throttle:contact` activo |
| Envío a servicios externos | ⚠️ Proveedor de correo [PENDIENTE DE CONFIRMAR en producción] |

**Formulario 2 — Contacto iCB SAT (`IcbSat.vue`, modal en `/icb-sat`)**

| Verificación | Estado |
|-------------|--------|
| Datos solicitados | Nombre, email, teléfono (opcional), sector, mensaje |
| Aviso de privacidad en el punto de recogida | ❌ AUSENTE |
| Enlace a Política de Privacidad | ❌ AUSENTE |

### 3.4 Google Maps — análisis detallado

**Situación técnica:** `@fawmi/vue-google-maps` se registra como plugin global en `app.js` y carga la API key desde `VITE_GOOGLE_MAPS_API_KEY`. El componente `GMapMap` se renderiza en la sección de contacto de la página de inicio.

**Implicaciones:**
- Al cargar la sección de contacto, el navegador realiza peticiones a `maps.googleapis.com` y `maps.gstatic.com`
- Google Maps instala cookies propias en el dispositivo del usuario (NID, CONSENT, 1P_JAR, entre otras) sin que el usuario haya prestado consentimiento informado para ello
- Se transmite la IP del usuario y datos del navegador a Google LLC
- Google LLC puede tener servidores fuera del EEE, aunque bajo el EU-US Data Privacy Framework (julio 2023) las transferencias a Google se consideran adecuadas
- La Política de Privacidad declara "no se realizan transferencias internacionales" — incorrecto

**Opciones de corrección (ordenadas por preferencia legal):**
1. **Reemplazar Google Maps por OpenStreetMap/Leaflet** — solución sin cookies de terceros, host europeo, sin transferencias
2. **Implementar carga diferida con consentimiento** — el mapa solo carga si el usuario acepta cookies de terceros
3. **Mantener Maps y actualizar documentación** — mínima opción aceptable: actualizar PP y PC para declarar Google Maps, implementar banner con opción de rechazar, bloquear Maps hasta consentimiento

### 3.5 Banner de cookies (`CCookieBanner.vue`)

El banner actual:
- ✅ Informa que solo se usan cookies técnicas
- ✅ Enlaza a la Política de Cookies
- ✅ Solo aparece si no existe la clave `icorebyte-cookies-accepted` en localStorage
- ❌ Solo tiene botón "Entendido" — sin "Rechazar" ni "Configurar"
- ❌ No permite rechazo de cookies (aunque para cookies estrictamente técnicas esto sería admisible, la presencia de Google Maps lo hace insuficiente)

**Nota AEPD:** Para cookies estrictamente técnicas/necesarias, la Guía de cookies de la AEPD no exige banner con accept/reject. Sin embargo, cuando existen cookies de terceros (Google Maps), sí se requiere mecanismo de consentimiento previo y posibilidad de rechazo equivalente al de aceptación.

---

## 4. MATRIZ DE CUMPLIMIENTO

| ID | Hallazgo | Nivel | Normativa | Archivo afectado | Propuesta |
|----|----------|-------|-----------|-----------------|-----------|
| AV-01 | NIF del titular ausente en Aviso Legal | CRÍTICO | Art. 10.1.b LSSI-CE | `AvisoLegal.vue` | Añadir NIF [PENDIENTE] |
| PP-01 | Google Maps no mencionado como encargado/destinatario | ALTO | Art. 13.1.e, 28 RGPD | `PoliticaPrivacidad.vue` | Añadir sección sobre Google Maps como encargado del tratamiento |
| PP-02 | Declaración de transferencias internacionales incorrecta | ALTO | Art. 13.1.f, 46 RGPD | `PoliticaPrivacidad.vue` | Corregir: Google Maps puede implicar transferencias; referenciar EU-US DPF |
| FM-01 | Formularios sin aviso de privacidad en punto de recogida | ALTO | Art. 13 RGPD | `CContact.vue`, `IcbSat.vue` | Añadir enlace a PP + texto mínimo de información bajo los formularios |
| PC-01 | Cookies de Google Maps no declaradas | ALTO | Art. 22.2 LSSI-CE, Guía AEPD cookies | `PoliticaCookies.vue` | Añadir tabla con cookies de Google Maps |
| PC-02 | `icorebyte-cookies-accepted` no declarado en política | MEDIO | Art. 22.2 LSSI-CE | `PoliticaCookies.vue` | Añadir a la tabla de localStorage |
| PP-03 | Base jurídica del formulario: consentimiento vs. precontractual | MEDIO | Art. 6.1.a vs. 6.1.b RGPD | `PoliticaPrivacidad.vue` | Cambiar a art. 6.1.b para solicitudes de presupuesto; mantener 6.1.a para contacto general |
| PP-04 | Encargado del tratamiento (correo) sin identificar | MEDIO | Art. 13.1.e, 28 RGPD | `PoliticaPrivacidad.vue` | Identificar proveedor [PENDIENTE] o indicar categoría genérica con garantías |
| CK-01 | CCookieBanner sin opción de rechazo explícita | MEDIO | Guía AEPD cookies (2023) | `CCookieBanner.vue` | Añadir botón "Rechazar" mientras Google Maps esté activo |
| AV-02 | NIF ausente también de Política de Privacidad | BAJO | Buena práctica AEPD | `PoliticaPrivacidad.vue` | Añadir NIF del responsable [PENDIENTE] |
| TK-01 | API key Google Maps expuesta en bundle JS | BAJO | Buena práctica seguridad | `app.js`, `.env` | Restringir la API key por HTTP referrer en Google Cloud Console |
| SV-01 | Proveedor email producción no identificado | BAJO | Art. 28 RGPD (registro encargados) | `.env` / documentación interna | Confirmar proveedor e incluir en PP |

---

## 5. CORRECCIONES IMPLEMENTADAS

### 5.1 Política de Privacidad — correcciones aplicadas

**Cambios realizados:**
- Sección 3: Cambio de base jurídica a art. 6.1.b (medidas precontractuales) para solicitudes de presupuesto, manteniendo referencia al consentimiento para consultas generales
- Sección 5: Añadida Google LLC / Google Maps como encargado del tratamiento con referencia al EU-US Data Privacy Framework
- Sección 5: Corregida la declaración de transferencias internacionales
- Añadido [PENDIENTE DE CONFIRMAR: NIF del responsable] en sección 1

### 5.2 Política de Cookies — correcciones aplicadas

**Cambios realizados:**
- Añadida `icorebyte-cookies-accepted` a la tabla de localStorage
- Añadida nueva sección de cookies de terceros con tabla de cookies de Google Maps
- Corregida la declaración de "no instala cookies de terceros"
- Añadida nota sobre Google Maps y consentimiento

### 5.3 Aviso Legal — correcciones aplicadas

**Cambios realizados:**
- Añadido `[PENDIENTE DE CONFIRMAR: NIF del titular]` en la ficha de datos identificativos

### 5.4 Formularios — correcciones aplicadas

**Cambios realizados:**
- Añadido texto de aviso de privacidad en `CContact.vue` (formulario home)
- Añadido texto de aviso de privacidad en `IcbSat.vue` (modal de contacto)

---

## 6. LIMITACIONES DE ESTA AUDITORÍA

- No se ha podido acceder al entorno de producción real para verificar las cookies que efectivamente instala Google Maps en ese contexto
- El proveedor de correo de producción es desconocido (`MAIL_MAILER=log` en desarrollo)
- No se han verificado configuraciones del servidor web (cabeceras HTTP de seguridad, HTTPS, etc.)
- No se ha revisado el panel de Google Cloud Console para verificar las restricciones de la API key
- Esta auditoría no constituye asesoramiento jurídico profesional

---

*Auditoría realizada con acceso al código fuente del repositorio. Fecha: Noviembre 2026.*
