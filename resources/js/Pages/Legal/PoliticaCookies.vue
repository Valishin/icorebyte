<script setup lang="ts">
  import CLegalBody from '@/Components/CLegalBody.vue'
  import CLegalHero from '@/Components/CLegalHero.vue'
  import PublicLayout from '@/Layouts/PublicLayout.vue'

  const toc = [
    { id: 'que-son',     label: '1. ¿Qué son las cookies?' },
    { id: 'cuales-usa',  label: '2. Cookies que usamos' },
    { id: 'terceros',    label: '3. Cookies de terceros' },
    { id: 'gestion',     label: '4. Gestión y desactivación' },
    { id: 'actualizacion', label: '5. Actualización' },
  ]

  const cookies = [
    {
      name: 'laravel_session',
      provider: 'icorebyte.com',
      purpose: 'Mantiene la sesión del usuario activa durante la navegación. Imprescindible para el funcionamiento del formulario de contacto y la seguridad de la web.',
      type: 'necessary',
      typeLabel: 'Técnica',
      duration: 'Sesión (se elimina al cerrar el navegador)',
    },
    {
      name: 'XSRF-TOKEN',
      provider: 'icorebyte.com',
      purpose: 'Cookie de seguridad que protege contra ataques de tipo CSRF (Cross-Site Request Forgery). Obligatoria para el correcto funcionamiento del formulario de contacto.',
      type: 'necessary',
      typeLabel: 'Técnica',
      duration: 'Sesión (se elimina al cerrar el navegador)',
    },
  ]

  const localStorage = [
    {
      name: 'icorebyte-theme',
      provider: 'icorebyte.com',
      purpose: 'Guarda tu preferencia de tema visual (modo claro u oscuro) para que se mantenga entre visitas. No contiene datos personales ni se envía a ningún servidor.',
      type: 'preference',
      typeLabel: 'Preferencia',
      duration: 'Persistente (hasta que se borre manualmente)',
    },
  ]
</script>

<template>
  <PublicLayout>
    <CLegalHero title="Política de Cookies" last-updated="Mayo 2025" />

    <CLegalBody :toc="toc">

      <section id="que-son" class="p-legal__section">
        <h2 class="p-legal__section-title">1. ¿Qué son las cookies?</h2>
        <p>Las cookies son pequeños archivos de texto que los sitios web almacenan en el navegador del usuario cuando este los visita. Su función principal es recordar información sobre la visita —como preferencias o el estado de sesión— para facilitar la navegación y mejorar la experiencia de uso.</p>
        <p>Existen distintos tipos de cookies según su finalidad: técnicas (necesarias para que el sitio funcione), de preferencias, analíticas y publicitarias. En este Sitio Web <strong>únicamente se utilizan cookies técnicas estrictamente necesarias</strong>, por lo que no es preciso obtener tu consentimiento previo para su instalación, tal como establece el artículo 22.2 de la <strong>LSSICE</strong>.</p>
      </section>

      <section id="cuales-usa" class="p-legal__section">
        <h2 class="p-legal__section-title">2. Cookies que utilizamos</h2>
        <p>A continuación se detalla la relación completa de cookies y almacenamiento local utilizado en este Sitio Web:</p>

        <h3 style="font-size: 0.9375rem; font-weight: 600; color: var(--color-text-muted); margin: 1.75rem 0 0.5rem;">Cookies de sesión (técnicas)</h3>
        <div class="p-legal__table-wrap">
          <table class="p-legal__table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Proveedor</th>
                <th>Tipo</th>
                <th>Duración</th>
                <th>Finalidad</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="cookie in cookies" :key="cookie.name">
                <td><code>{{ cookie.name }}</code></td>
                <td>{{ cookie.provider }}</td>
                <td>
                  <span class="p-legal__badge" :class="`p-legal__badge--${cookie.type}`">
                    {{ cookie.typeLabel }}
                  </span>
                </td>
                <td>{{ cookie.duration }}</td>
                <td>{{ cookie.purpose }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <h3 style="font-size: 0.9375rem; font-weight: 600; color: var(--color-text-muted); margin: 1.75rem 0 0.5rem;">Almacenamiento local (localStorage)</h3>
        <p>El siguiente elemento <strong>no es una cookie</strong> propiamente dicha, sino datos almacenados en el <code>localStorage</code> de tu navegador. A diferencia de las cookies, no se envía al servidor en cada petición y solo persiste en tu dispositivo.</p>
        <div class="p-legal__table-wrap">
          <table class="p-legal__table">
            <thead>
              <tr>
                <th>Clave</th>
                <th>Proveedor</th>
                <th>Tipo</th>
                <th>Duración</th>
                <th>Finalidad</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in localStorage" :key="item.name">
                <td><code>{{ item.name }}</code></td>
                <td>{{ item.provider }}</td>
                <td>
                  <span class="p-legal__badge" :class="`p-legal__badge--${item.type}`">
                    {{ item.typeLabel }}
                  </span>
                </td>
                <td>{{ item.duration }}</td>
                <td>{{ item.purpose }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-legal__info-box" style="margin-top: 1.5rem;">
          <p>Este Sitio Web <strong>no utiliza cookies analíticas, publicitarias ni de seguimiento</strong> de ningún tipo. No se comparte ningún dato de navegación con terceros.</p>
        </div>
      </section>

      <section id="terceros" class="p-legal__section">
        <h2 class="p-legal__section-title">3. Cookies de terceros</h2>
        <p>iCoreByte <strong>no instala cookies de terceros</strong> en este Sitio Web. No se integran servicios de analítica (como Google Analytics), redes sociales, publicidad programática ni ningún otro servicio externo que requiera el uso de cookies propias o de terceros más allá de las estrictamente necesarias descritas en la sección anterior.</p>
      </section>

      <section id="gestion" class="p-legal__section">
        <h2 class="p-legal__section-title">4. Gestión y desactivación de cookies</h2>
        <p>Dado que las cookies utilizadas son estrictamente técnicas y necesarias para el funcionamiento básico del Sitio Web, <strong>no es posible desactivarlas individualmente desde este sitio</strong> sin afectar a su funcionamiento.</p>
        <p>No obstante, puedes configurar tu navegador para bloquear o eliminar cookies en cualquier momento. Ten en cuenta que esto puede impedir el correcto funcionamiento del formulario de contacto. A continuación encontrarás los enlaces de configuración de los principales navegadores:</p>
        <ul class="p-legal__list">
          <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener noreferrer" class="p-legal__inline-link">Google Chrome</a></li>
          <li><a href="https://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-sitios-web-rastrear-preferencias" target="_blank" rel="noopener noreferrer" class="p-legal__inline-link">Mozilla Firefox</a></li>
          <li><a href="https://support.apple.com/es-es/guide/safari/sfri11471/mac" target="_blank" rel="noopener noreferrer" class="p-legal__inline-link">Apple Safari</a></li>
          <li><a href="https://support.microsoft.com/es-es/microsoft-edge/eliminar-las-cookies-en-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener noreferrer" class="p-legal__inline-link">Microsoft Edge</a></li>
        </ul>
        <p>Para eliminar el elemento de <code>localStorage</code>, puedes hacerlo desde las herramientas de desarrollo de tu navegador (pestaña <em>Aplicación → Almacenamiento local</em>) o borrando los datos de navegación del sitio.</p>
      </section>

      <section id="actualizacion" class="p-legal__section">
        <h2 class="p-legal__section-title">5. Actualización de la política</h2>
        <p>iCoreByte se reserva el derecho a modificar esta Política de Cookies para adaptarla a cambios legislativos, técnicos o de los servicios ofrecidos. Cualquier modificación será publicada en esta página con la fecha de actualización correspondiente.</p>
        <p>Para cualquier consulta relacionada con el uso de cookies, puedes contactar con nosotros en <a href="mailto:info@icorebyte.com" class="p-legal__inline-link">info@icorebyte.com</a>.</p>
      </section>

    </CLegalBody>
  </PublicLayout>
</template>
