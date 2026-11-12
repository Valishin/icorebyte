# Datos pendientes de confirmar — iCoreByte
**Documento generado:** Noviembre 2026  
**Propósito:** Lista de información que el titular debe proporcionar para completar la adecuación legal.

---

## OBLIGATORIO — Sin estos datos hay incumplimientos legales activos

### 1. NIF del titular
- **Dónde se usa:** Aviso Legal (obligatorio por art. 10.1.b LSSI-CE) y, como buena práctica, en la Política de Privacidad
- **Formato esperado:** NIF de autónomo (DNI + letra), ej. 12345678A
- **Bloquea:** hallazgos AV-01 y AV-02

---

## ALTO — Necesario para corregir incumplimientos de protección de datos

### 2. Proveedor de correo electrónico en producción
- **Dónde se usa:** Política de Privacidad (encargado del tratamiento para envío de respuestas a formularios)
- **Información necesaria:**
  - Nombre del proveedor (ej. SendGrid, Mailgun, Postmark, Gmail Workspace, servidor propio, etc.)
  - País donde procesa los datos
  - Si tiene DPA (Data Processing Agreement) firmado con iCoreByte
- **Por qué:** El art. 28 RGPD exige que el responsable del tratamiento firme un contrato de encargado con todo proveedor que trate datos en su nombre. El correo electrónico es un encargado del tratamiento.
- **Nota:** El entorno de desarrollo usa `MAIL_MAILER=log` (logs locales). En producción debe haber un proveedor real.
- **Bloquea:** hallazgos PP-04 y SV-01

---

## MEDIO — Necesario para completar la adecuación

### 3. Decisión sobre Google Maps
- **Situación:** Google Maps JS API se usa en la sección de contacto. Instala cookies de terceros y transmite datos a Google LLC, lo que contradice lo declarado en la Política de Cookies y Privacidad.
- **Opciones (el titular debe elegir):**
  - **Opción A:** Sustituir Google Maps por OpenStreetMap/Leaflet (solución sin cookies de terceros, recomendada)
  - **Opción B:** Mantener Google Maps implementando carga diferida con consentimiento previo (require actualizar el banner de cookies)
  - **Opción C:** Mantener Google Maps y actualizar toda la documentación legal para reflejar su uso (mínimamente aceptable pero con mayor carga documental)
- **Bloquea:** hallazgos PP-01, PP-02, PC-01, CK-01

### 4. Confirmación del email de contacto para ejercicio de derechos
- **Dónde se usa:** Política de Privacidad (sección derechos) y Aviso Legal
- **Información necesaria:** Confirmar que `info@icorebyte.com` es el canal oficial y operativo para atender solicitudes de derechos RGPD
- **Por qué:** Si este email no se revisa con regularidad, el responsable podría incumplir los plazos de respuesta de un mes del art. 12.3 RGPD

---

## RECOMENDABLE — Mejoras de buena práctica

### 5. Contrato de encargado del tratamiento (DPA) con Google
- **Qué es:** Google pone a disposición sus términos de tratamiento de datos en https://business.safety.google/gdpr/
- **Acción requerida:** Revisar y aceptar los términos de Google Maps Platform si aún no se han aceptado
- **Por qué:** El art. 28 RGPD exige que todo encargado del tratamiento opere bajo un contrato que incluya las garantías del Reglamento

### 6. Confirmación de que Google Maps API key está restringida
- **Dónde verificar:** Google Cloud Console → Credenciales → la API key de Maps
- **Restricción recomendada:** HTTP referrers limitados a `icorebyte.com/*`
- **Por qué:** Una key sin restricciones puede ser robada y usada fraudulentamente, generando costes inesperados

### 7. Condiciones de servicio / política de garantías (para futuro)
- **Situación:** La web menciona garantías en el FAQ del structured data (6 meses pantalla/batería, 12 meses componentes originales) pero no existe un documento formal
- **Recomendación:** Crear un documento de "Condiciones de Servicio" que incluya política de presupuestos, garantías y reclamaciones. Esto será **obligatorio** si se activa la tienda online (RDL 1/2007 arts. 60-65)
- **Urgencia actual:** Recomendable, no obligatorio para web corporativa

---

## SOLO PARA FUTURA TIENDA ONLINE / SUSCRIPCIONES SOFTWARE

Los siguientes elementos **no son necesarios ahora** pero serán obligatorios si se activa la venta online:

- Condiciones generales de contratación (art. 101 y ss. RDL 1/2007)
- Información precontractual completa (precio, gastos de envío, plazo de entrega)
- Política de devoluciones y derecho de desistimiento (14 días naturales, Directiva 2011/83/UE)
- Información sobre resolución alternativa de litigios (plataforma ODR de la UE)
- Para suscripciones SaaS: condiciones específicas de la suscripción, política de cancelación, facturación

---

*Este documento debe revisarse y completarse con el titular antes de implementar los cambios legales definitivos.*
