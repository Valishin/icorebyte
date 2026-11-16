<script setup lang="ts">
  import CLegalBody from '@/Components/CLegalBody.vue'
  import CLegalHero from '@/Components/CLegalHero.vue'
  import PublicLayout from '@/Layouts/PublicLayout.vue'

  const toc = [
    { id: 'responsable',  label: '1. Responsable del tratamiento' },
    { id: 'datos',        label: '2. Datos que recabamos' },
    { id: 'finalidad',    label: '3. Finalidad y base jurídica' },
    { id: 'conservacion', label: '4. Conservación de datos' },
    { id: 'chat',         label: '5. Widget de chat en vivo' },
    { id: 'destinatarios',label: '6. Destinatarios' },
    { id: 'derechos',     label: '7. Tus derechos' },
    { id: 'menores',      label: '8. Menores de edad' },
    { id: 'cambios',      label: '9. Cambios en la política' },
  ]
</script>

<template>
  <PublicLayout>
    <CLegalHero title="Política de Privacidad" last-updated="Mayo 2025" />

    <CLegalBody :toc="toc">

      <section id="responsable" class="p-legal__section">
        <h2 class="p-legal__section-title">1. Responsable del tratamiento</h2>
        <p>De conformidad con el <strong>Reglamento (UE) 2016/679 (RGPD)</strong> y la <strong>Ley Orgánica 3/2018 (LOPDGDD)</strong>, el responsable del tratamiento de tus datos personales es:</p>
        <div class="p-legal__data-card">
          <dl class="p-legal__data-list">
            <div class="p-legal__data-row">
              <dt>Responsable</dt>
              <dd>iCoreByte</dd>
            </div>
            <div class="p-legal__data-row">
              <dt>NIF</dt>
              <dd>49298273G</dd>
            </div>
            <div class="p-legal__data-row">
              <dt>Domicilio</dt>
              <dd>Carretera de Palamós 57 local, Sant Feliu de Guíxols, Girona, 17220</dd>
            </div>
            <div class="p-legal__data-row">
              <dt>Contacto</dt>
              <dd><a href="mailto:info@icorebyte.com" class="p-legal__inline-link">info@icorebyte.com</a></dd>
            </div>
          </dl>
        </div>
      </section>

      <section id="datos" class="p-legal__section">
        <h2 class="p-legal__section-title">2. Datos que recabamos</h2>
        <p>iCoreByte únicamente recaba los datos personales que el usuario facilita de forma voluntaria a través del <strong>formulario de contacto</strong> disponible en el Sitio Web:</p>
        <ul class="p-legal__list">
          <li><strong>Nombre</strong> — para identificarte y dirigirnos a ti de forma personalizada.</li>
          <li><strong>Correo electrónico</strong> — para responder a tu consulta o presupuesto.</li>
          <li><strong>Teléfono</strong> — para contactarte si así lo solicitas (campo opcional).</li>
          <li><strong>Servicio de interés</strong> — para orientar correctamente tu solicitud.</li>
          <li><strong>Mensaje</strong> — con el detalle de tu consulta o proyecto.</li>
        </ul>
        <p>No se recaban datos de navegación, de perfilado ni se utilizan tecnologías de seguimiento con fines publicitarios. La preferencia de tema visual (modo claro/oscuro) se almacena exclusivamente en tu dispositivo mediante <code>localStorage</code> y no es accesible por iCoreByte.</p>
      </section>

      <section id="finalidad" class="p-legal__section">
        <h2 class="p-legal__section-title">3. Finalidad del tratamiento y base jurídica</h2>
        <p>Los datos facilitados a través del formulario de contacto se tratan con la siguiente finalidad y base legitimadora:</p>
        <div class="p-legal__data-card">
          <dl class="p-legal__data-list">
            <div class="p-legal__data-row">
              <dt>Finalidad</dt>
              <dd>Gestionar y responder a consultas, solicitudes de presupuesto y peticiones de información sobre los servicios de iCoreByte.</dd>
            </div>
            <div class="p-legal__data-row">
              <dt>Base jurídica</dt>
              <dd>Ejecución de medidas precontractuales a petición del interesado (art. 6.1.b RGPD), cuando la solicitud está orientada a obtener un presupuesto o contratar un servicio. Para consultas de carácter informativo general, el consentimiento del interesado (art. 6.1.a RGPD), otorgado mediante el envío voluntario del formulario.</dd>
            </div>
          </dl>
        </div>
        <p>Los datos no serán utilizados para ninguna otra finalidad distinta a la indicada sin informarte previamente y, en su caso, obtener tu consentimiento.</p>
      </section>

      <section id="conservacion" class="p-legal__section">
        <h2 class="p-legal__section-title">4. Plazo de conservación</h2>
        <p>Los datos personales se conservarán durante el tiempo necesario para dar respuesta a tu consulta y, posteriormente, durante el plazo de prescripción de las acciones legales que pudieran derivarse de la relación establecida (con carácter general, <strong>3 años</strong> conforme al Código Civil).</p>
        <p>Una vez transcurrido dicho plazo, los datos serán eliminados de forma segura o, en su caso, anonimizados.</p>
      </section>

      <section id="chat" class="p-legal__section">
        <h2 class="p-legal__section-title">5. Chat en tiempo real</h2>
        <p>El Sitio Web dispone de un chat en tiempo real para que puedas consultar con el equipo de iCoreByte sin necesidad de crear una cuenta. Usarlo es completamente voluntario.</p>

        <h3 class="p-legal__subsection-title">¿Qué datos recogemos y para qué?</h3>
        <ul class="p-legal__list">
          <li><strong>Nombre</strong> (obligatorio) — para dirigirnos a ti durante la conversación.</li>
          <li><strong>Teléfono</strong> (opcional) — si deseas que te contactemos por otro medio.</li>
          <li><strong>Correo electrónico</strong> (opcional) — para enviarte un enlace que te permita retomar la conversación si se interrumpe o si necesitas continuarla desde otro dispositivo.</li>
          <li><strong>Mensajes e imágenes</strong> — el contenido de la conversación, necesario para gestionarla y responderte. Las imágenes que compartas no son accesibles públicamente.</li>
          <li><strong>Dirección de la página</strong> desde la que iniciaste el chat, para entender el contexto de tu consulta.</li>
          <li><strong>Fecha y hora</strong> de los mensajes y de la última actividad.</li>
        </ul>
        <p>También registramos tu dirección IP de forma que no es posible recuperarla: solo guardamos una huella digital unidireccional para detectar posibles abusos, sin poder identificarte a partir de ella.</p>

        <h3 class="p-legal__subsection-title">Base jurídica</h3>
        <p>El tratamiento se basa en tu <strong>consentimiento</strong> (art. 6.1.a del RGPD), que otorgas marcando la casilla de aceptación antes de iniciar el chat. Puedes retirar ese consentimiento en cualquier momento solicitando la eliminación de tu conversación (ver más abajo).</p>

        <h3 class="p-legal__subsection-title">¿Cuánto tiempo conservamos los datos?</h3>
        <p>Todos los datos del chat —mensajes, imágenes y demás información asociada— se eliminan automáticamente cuando han transcurrido <strong>90 días desde el último mensaje</strong>. Si la conversación no llega a tener actividad, el plazo se cuenta desde que se inició.</p>
        <p>Puedes solicitar la eliminación antes de ese plazo escribiéndonos a <a href="mailto:info@icorebyte.com" class="p-legal__inline-link">info@icorebyte.com</a>.</p>

        <h3 class="p-legal__subsection-title">¿Quién tiene acceso a los datos?</h3>
        <ul class="p-legal__list">
          <li><strong>Hostinger</strong> — proveedor de alojamiento y correo electrónico con servidores en la Unión Europea. Gestiona el envío del correo de reanudación de conversación (si facilitaste tu dirección de correo) a través de su servicio SMTP.</li>
          <li><strong>Telegram</strong> — las notificaciones de nuevos mensajes del chat se envían internamente al equipo de iCoreByte a través de un bot de Telegram. Telegram es una empresa con sede fuera del Espacio Económico Europeo; los datos transmitidos se limitan al contenido del mensaje y al identificador de la conversación. Esta transferencia está cubierta por las cláusulas contractuales estándar aplicables. Más información: <a href="https://telegram.org/privacy" target="_blank" rel="noopener noreferrer" class="p-legal__inline-link">telegram.org/privacy</a>.</li>
        </ul>
        <p>iCoreByte no vende ni cede estos datos a terceros con fines comerciales.</p>

        <h3 class="p-legal__subsection-title">Tus derechos sobre los datos del chat</h3>
        <p>Puedes ejercer tus derechos de acceso, rectificación, supresión, oposición, limitación y portabilidad escribiendo a <a href="mailto:info@icorebyte.com" class="p-legal__inline-link">info@icorebyte.com</a>. Para solicitar la eliminación de una conversación concreta, indícanos la fecha aproximada o el correo con el que iniciaste el chat. Atenderemos tu solicitud en el plazo máximo de un mes desde su recepción, prorrogable en los casos previstos por el RGPD.</p>
        <p>Si consideras que el tratamiento no cumple la normativa, tienes derecho a reclamar ante la <strong>Agencia Española de Protección de Datos (AEPD)</strong> en <a href="https://www.aepd.es" target="_blank" rel="noopener noreferrer" class="p-legal__inline-link">www.aepd.es</a>.</p>
      </section>

      <section id="destinatarios" class="p-legal__section">
        <h2 class="p-legal__section-title">6. Destinatarios y transferencias internacionales</h2>
        <p>iCoreByte <strong>no cede ni vende tus datos personales a terceros</strong> con fines comerciales ni de ninguna otra índole.</p>
        <p>Para la prestación de los servicios del Sitio Web, iCoreByte puede contar con los siguientes encargados del tratamiento, que actúan bajo las garantías contractuales exigidas por el RGPD (art. 28):</p>
        <ul class="p-legal__list">
          <li><strong>Hostinger (servidor de correo propio)</strong> — proveedor de alojamiento y correo electrónico, con servidores ubicados en la Unión Europea. Se utiliza para enviar respuestas a tus consultas a través de <code>info@icorebyte.com</code>. Acceso limitado y estrictamente necesario. Más información en <a href="https://www.hostinger.es/politica-de-privacidad" target="_blank" rel="noopener noreferrer" class="p-legal__inline-link">hostinger.es/politica-de-privacidad</a>.</li>
          <li><strong>OpenStreetMap Foundation (openstreetmap.org)</strong> — utilizado para mostrar el mapa de localización del establecimiento en la sección de contacto. El mapa se carga mediante un iframe estático desde <code>openstreetmap.org</code>. OpenStreetMap no instala cookies propias ni realiza seguimiento de usuarios. Los datos de uso de los servidores de tile están sujetos a la <a href="https://wiki.osmfoundation.org/wiki/Privacy_Policy" target="_blank" rel="noopener noreferrer" class="p-legal__inline-link">Política de Privacidad de OSMF</a>. OSMF es una entidad sin ánimo de lucro con sede en el Reino Unido; las transferencias se realizan al amparo del Reglamento UK GDPR, equivalente al RGPD europeo.</li>
        </ul>
        <p>iCoreByte no realiza transferencias internacionales de datos fuera del Espacio Económico Europeo, salvo lo indicado respecto a OpenStreetMap Foundation (Reino Unido, con nivel de protección equivalente al RGPD).</p>
      </section>

      <section id="derechos" class="p-legal__section">
        <h2 class="p-legal__section-title">7. Tus derechos</h2>
        <p>Como interesado, puedes ejercer en cualquier momento los siguientes derechos reconocidos por el RGPD y la LOPDGDD:</p>
        <ul class="p-legal__list">
          <li><strong>Acceso:</strong> obtener confirmación sobre si tratamos tus datos y, en su caso, acceder a ellos.</li>
          <li><strong>Rectificación:</strong> solicitar la corrección de datos inexactos o incompletos.</li>
          <li><strong>Supresión («derecho al olvido»):</strong> solicitar la eliminación de tus datos cuando ya no sean necesarios.</li>
          <li><strong>Oposición:</strong> oponerte al tratamiento de tus datos en determinadas circunstancias.</li>
          <li><strong>Limitación:</strong> solicitar la suspensión del tratamiento en los casos previstos por la ley.</li>
          <li><strong>Portabilidad:</strong> recibir tus datos en un formato estructurado y de uso común.</li>
          <li><strong>Retirada del consentimiento:</strong> en cualquier momento, sin que ello afecte a la licitud del tratamiento previo.</li>
        </ul>
        <p>Para ejercer cualquiera de estos derechos, puedes dirigirte a iCoreByte mediante correo electrónico a <a href="mailto:info@icorebyte.com" class="p-legal__inline-link">info@icorebyte.com</a>, indicando el derecho que deseas ejercer y adjuntando una copia de tu documento de identidad.</p>
        <div class="p-legal__info-box">
          <p>Si consideras que el tratamiento de tus datos no cumple la normativa vigente, tienes derecho a presentar una reclamación ante la <strong>Agencia Española de Protección de Datos (AEPD)</strong> en <a href="https://www.aepd.es" target="_blank" rel="noopener noreferrer" class="p-legal__inline-link">www.aepd.es</a>.</p>
        </div>
      </section>

      <section id="menores" class="p-legal__section">
        <h2 class="p-legal__section-title">8. Menores de edad</h2>
        <p>Los servicios del Sitio Web no están dirigidos a menores de <strong>14 años</strong>. iCoreByte no recaba conscientemente datos personales de menores. Si tienes conocimiento de que un menor ha facilitado datos sin el consentimiento de sus tutores legales, te rogamos que nos lo comuniques a <a href="mailto:info@icorebyte.com" class="p-legal__inline-link">info@icorebyte.com</a> para proceder a su eliminación inmediata.</p>
      </section>

      <section id="cambios" class="p-legal__section">
        <h2 class="p-legal__section-title">9. Cambios en la política de privacidad</h2>
        <p>iCoreByte se reserva el derecho a modificar la presente Política de Privacidad para adaptarla a novedades legislativas, jurisprudenciales o de práctica empresarial. Cualquier modificación será publicada en esta misma página con la fecha de actualización correspondiente.</p>
        <p>Te recomendamos revisar esta página periódicamente para estar informado de posibles cambios.</p>
      </section>

    </CLegalBody>
  </PublicLayout>
</template>
