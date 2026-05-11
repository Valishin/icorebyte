<script setup lang="ts">
  import CLegalBody from '@/Components/CLegalBody.vue'
  import CLegalHero from '@/Components/CLegalHero.vue'
  import PublicLayout from '@/Layouts/PublicLayout.vue'

  const toc = [
    { id: 'responsable',  label: '1. Responsable del tratamiento' },
    { id: 'datos',        label: '2. Datos que recabamos' },
    { id: 'finalidad',    label: '3. Finalidad y base jurídica' },
    { id: 'conservacion', label: '4. Conservación de datos' },
    { id: 'destinatarios',label: '5. Destinatarios' },
    { id: 'derechos',     label: '6. Tus derechos' },
    { id: 'menores',      label: '7. Menores de edad' },
    { id: 'cambios',      label: '8. Cambios en la política' },
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
              <dd>Consentimiento del interesado (art. 6.1.a RGPD), otorgado mediante el envío voluntario del formulario.</dd>
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

      <section id="destinatarios" class="p-legal__section">
        <h2 class="p-legal__section-title">5. Destinatarios y transferencias internacionales</h2>
        <p>iCoreByte <strong>no cede ni vende tus datos personales a terceros</strong> con fines comerciales ni de ninguna otra índole.</p>
        <p>Para el envío de correos electrónicos de respuesta a tus consultas, iCoreByte utiliza un proveedor de servicios de correo electrónico que actúa como encargado del tratamiento, con acceso limitado y estrictamente necesario para la prestación del servicio, bajo las garantías contractuales exigidas por el RGPD.</p>
        <p>No se realizan transferencias internacionales de datos fuera del Espacio Económico Europeo.</p>
      </section>

      <section id="derechos" class="p-legal__section">
        <h2 class="p-legal__section-title">6. Tus derechos</h2>
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
        <h2 class="p-legal__section-title">7. Menores de edad</h2>
        <p>Los servicios del Sitio Web no están dirigidos a menores de <strong>14 años</strong>. iCoreByte no recaba conscientemente datos personales de menores. Si tienes conocimiento de que un menor ha facilitado datos sin el consentimiento de sus tutores legales, te rogamos que nos lo comuniques a <a href="mailto:info@icorebyte.com" class="p-legal__inline-link">info@icorebyte.com</a> para proceder a su eliminación inmediata.</p>
      </section>

      <section id="cambios" class="p-legal__section">
        <h2 class="p-legal__section-title">8. Cambios en la política de privacidad</h2>
        <p>iCoreByte se reserva el derecho a modificar la presente Política de Privacidad para adaptarla a novedades legislativas, jurisprudenciales o de práctica empresarial. Cualquier modificación será publicada en esta misma página con la fecha de actualización correspondiente.</p>
        <p>Te recomendamos revisar esta página periódicamente para estar informado de posibles cambios.</p>
      </section>

    </CLegalBody>
  </PublicLayout>
</template>
