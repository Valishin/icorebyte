<script setup lang="ts">
  import CLegalBody from '@/Components/CLegalBody.vue'
  import CLegalHero from '@/Components/CLegalHero.vue'
  import PublicLayout from '@/Layouts/PublicLayout.vue'

  const toc = [
    { id: 'titular',        label: '1. Datos del titular' },
    { id: 'objeto',         label: '2. Objeto y ámbito' },
    { id: 'uso',            label: '3. Condiciones de uso' },
    { id: 'propiedad',      label: '4. Propiedad intelectual' },
    { id: 'responsabilidad',label: '5. Responsabilidad' },
    { id: 'enlaces',        label: '6. Enlaces externos' },
    { id: 'privacidad',     label: '7. Privacidad y cookies' },
    { id: 'legislacion',    label: '8. Legislación aplicable' },
  ]
</script>

<template>
  <PublicLayout>
    <CLegalHero title="Aviso Legal" last-updated="Mayo 2025" />

    <CLegalBody :toc="toc">

      <section id="titular" class="p-legal__section">
        <h2 class="p-legal__section-title">1. Datos identificativos del titular</h2>
        <p>En cumplimiento del artículo 10 de la <strong>Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y del Comercio Electrónico (LSSICE)</strong>, se informa que el titular del sitio web <strong>icorebyte.com</strong> es:</p>
        <div class="p-legal__data-card">
          <dl class="p-legal__data-list">
            <div class="p-legal__data-row">
              <dt>Denominación</dt>
              <dd>iCoreByte</dd>
            </div>
            <div class="p-legal__data-row">
              <dt>Actividad</dt>
              <dd>Reparación de equipos informáticos, venta de productos tecnológicos y desarrollo web</dd>
            </div>
            <div class="p-legal__data-row">
              <dt>Domicilio</dt>
              <dd>Carretera de Palamós 57 local, Sant Feliu de Guíxols, Girona, 17220</dd>
            </div>
            <div class="p-legal__data-row">
              <dt>Teléfono</dt>
              <dd>+34 678 758 210</dd>
            </div>
            <div class="p-legal__data-row">
              <dt>Correo electrónico</dt>
              <dd><a href="mailto:info@icorebyte.com" class="p-legal__inline-link">info@icorebyte.com</a></dd>
            </div>
          </dl>
        </div>
      </section>

      <section id="objeto" class="p-legal__section">
        <h2 class="p-legal__section-title">2. Objeto y ámbito de aplicación</h2>
        <p>El presente Aviso Legal regula el acceso y el uso del sitio web <strong>icorebyte.com</strong> (en adelante, «el Sitio Web»), cuya finalidad es informar sobre los servicios y productos ofrecidos por iCoreByte, así como facilitar el contacto con sus clientes y usuarios.</p>
        <p>El acceso al Sitio Web implica la aceptación plena y sin reservas de todas y cada una de las disposiciones incluidas en este Aviso Legal, en la versión publicada en el momento del acceso.</p>
      </section>

      <section id="uso" class="p-legal__section">
        <h2 class="p-legal__section-title">3. Condiciones de uso</h2>
        <p>El usuario se compromete a hacer un uso adecuado de los contenidos y servicios del Sitio Web, y en particular a no emplearlos para:</p>
        <ul class="p-legal__list">
          <li>Incurrir en actividades ilícitas, ilegales o contrarias a la buena fe y al orden público.</li>
          <li>Difundir contenidos de carácter racista, xenófobo, pornográfico o atentatorio contra los derechos humanos.</li>
          <li>Introducir o difundir virus informáticos o sistemas que puedan provocar daños en los sistemas de iCoreByte o de terceros.</li>
          <li>Intentar acceder, utilizar o manipular los datos de iCoreByte, terceros proveedores u otros usuarios.</li>
          <li>Reproducir, copiar o distribuir los contenidos sin autorización previa y expresa de iCoreByte.</li>
        </ul>
        <p>iCoreByte se reserva el derecho a denegar o retirar el acceso al Sitio Web, en cualquier momento y sin necesidad de preaviso, a aquellos usuarios que incumplan estas condiciones.</p>
      </section>

      <section id="propiedad" class="p-legal__section">
        <h2 class="p-legal__section-title">4. Propiedad intelectual e industrial</h2>
        <p>Todos los contenidos del Sitio Web —incluyendo textos, fotografías, gráficos, imágenes, iconos, tecnología, software, enlaces y demás contenidos audiovisuales— así como su diseño gráfico y códigos fuente, son propiedad de iCoreByte o de terceros que han autorizado su uso.</p>
        <p>Las marcas, nombres comerciales o signos distintivos son titularidad de iCoreByte o de terceras empresas. Cualquier uso no autorizado constituirá una violación de los derechos de propiedad intelectual o industrial.</p>
      </section>

      <section id="responsabilidad" class="p-legal__section">
        <h2 class="p-legal__section-title">5. Limitación de responsabilidad</h2>
        <p>iCoreByte no garantiza la inexistencia de interrupciones o errores en el acceso al Sitio Web, aunque desarrollará sus mejores esfuerzos para evitarlos o subsanarlos. Tampoco se responsabiliza de los daños derivados de:</p>
        <ul class="p-legal__list">
          <li>La no disponibilidad o funcionamiento del Sitio Web y/o sus servicios.</li>
          <li>La falta de utilidad o validez de los contenidos para necesidades concretas del usuario.</li>
          <li>Los errores o defectos en los contenidos.</li>
          <li>La existencia de virus u otros elementos lesivos en los contenidos.</li>
        </ul>
      </section>

      <section id="enlaces" class="p-legal__section">
        <h2 class="p-legal__section-title">6. Enlaces a sitios de terceros</h2>
        <p>El Sitio Web puede contener enlaces a sitios web de terceros. iCoreByte no asume responsabilidad alguna por los contenidos de dichos sitios, que tienen exclusivamente carácter informativo.</p>
        <p>En caso de detectar que un enlace externo vulnera la legislación vigente o el presente Aviso Legal, procederá a su eliminación de forma inmediata.</p>
      </section>

      <section id="privacidad" class="p-legal__section">
        <h2 class="p-legal__section-title">7. Privacidad y cookies</h2>
        <p>Para acceder a información sobre el tratamiento de sus datos personales, puede consultar nuestra <a href="/politica-de-privacidad" class="p-legal__inline-link">Política de Privacidad</a>.</p>
        <p>Para información sobre el uso de cookies en este Sitio Web, puede consultar nuestra <a href="/politica-de-cookies" class="p-legal__inline-link">Política de Cookies</a>.</p>
      </section>

      <section id="legislacion" class="p-legal__section">
        <h2 class="p-legal__section-title">8. Legislación aplicable y jurisdicción</h2>
        <p>Las presentes condiciones se rigen por la legislación española. Para la resolución de cualquier controversia, iCoreByte y el usuario se someten a los Juzgados y Tribunales de <strong>Girona</strong>, salvo que la ley disponga otro fuero imperativo.</p>
        <p>Normativa de referencia:</p>
        <ul class="p-legal__list">
          <li><strong>LSSICE:</strong> Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información.</li>
          <li><strong>RGPD:</strong> Reglamento (UE) 2016/679 del Parlamento Europeo, de 27 de abril de 2016.</li>
          <li><strong>LOPDGDD:</strong> Ley Orgánica 3/2018, de 5 de diciembre, de Protección de Datos Personales.</li>
          <li><strong>LPI:</strong> Real Decreto Legislativo 1/1996, de 12 de abril, de Propiedad Intelectual.</li>
        </ul>
      </section>

    </CLegalBody>
  </PublicLayout>
</template>
