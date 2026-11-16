<script setup lang="ts">
  import CCtaBanner from '@/Components/CCtaBanner.vue'
  import CFaqAccordion from '@/Components/CFaqAccordion.vue'
  import CIcon from '@/Components/CIcon.vue'
  import CTitle from '@/Components/CTitle.vue'
  import CTrustBar from '@/Components/CTrustBar.vue'
  import PublicLayout from '@/Layouts/PublicLayout.vue'
  import { productCategories, refurbishedEnabled } from '@/data/products'
  import { images } from '@/constants/images'
  import { Head, useForm } from '@inertiajs/vue3'
  import { onMounted, onUnmounted, ref } from 'vue'
  import { registerParallax, type ParallaxEntry } from '@/composables/useParallaxLoop'

  // ── Parallax hero ─────────────────────────────────────────
  const heroSectionRef = ref<HTMLElement>()
  const heroBgInnerRef = ref<HTMLElement>()
  let heroObserver: IntersectionObserver | null = null
  let unregisterHeroParallax: (() => void) | null = null
  const HERO_SPEED = 0.22
  let heroTargetY = 0, heroCurY = 0

  const heroEntry: ParallaxEntry = {
    updateTarget() { heroTargetY = window.scrollY * HERO_SPEED },
    lerp() {
      if (!heroBgInnerRef.value) return false
      heroCurY += (heroTargetY - heroCurY) * 0.12
      heroBgInnerRef.value.style.transform = `translate3d(0,${heroCurY | 0}px,0)`
      if (Math.abs(heroCurY - heroTargetY) > 0.1) return true
      heroCurY = heroTargetY
      return false
    }
  }

  const startHeroParallax = () => { if (!unregisterHeroParallax) unregisterHeroParallax = registerParallax(heroEntry) }
  const stopHeroParallax  = () => { unregisterHeroParallax?.(); unregisterHeroParallax = null }

  // ── Mapa diferido ─────────────────────────────────────────
  const mapActive = ref(false)

  onMounted(() => {
    if (heroBgInnerRef.value) heroBgInnerRef.value.style.backgroundImage = `url(${images.heroSlide5})`
    heroObserver = new IntersectionObserver(
      (entries) => entries.forEach(e => e.isIntersecting ? startHeroParallax() : stopHeroParallax()),
      { threshold: 0 }
    )
    if (heroSectionRef.value) heroObserver.observe(heroSectionRef.value)
  })
  onUnmounted(() => { heroObserver?.disconnect(); unregisterHeroParallax?.() })

  // ── Datos estáticos ───────────────────────────────────────
  const WA_BASE = 'https://wa.me/34678758210?text='
  const WA_GENERAL = WA_BASE + 'Hola%2C%20me%20gustar%C3%ADa%20consultar%20disponibilidad%20de%20productos'
  const MAPS_URL   = 'https://www.google.com/maps/dir/?api=1&destination=41.783112276338564,3.036094176676324'

  const advantages = [
    { icon: 'IconDesktop'     as const, title: 'Te lo dejamos configurado',       desc: 'Tu equipo llega listo para usar, con el software que necesitas instalado.' },
    { icon: 'IconHarddisk'    as const, title: 'Migración de datos incluida',      desc: 'Pasamos tus archivos, fotos y programas al nuevo equipo sin que pierdas nada.' },
    { icon: 'IconMaintenance' as const, title: 'Montaje e instalación',            desc: 'Montamos y configuramos los componentes que compras en nuestra tienda.' },
    { icon: 'IconSupport'     as const, title: 'Asesoramiento personalizado',      desc: 'Te ayudamos a elegir el equipo o componente que mejor se adapta a tu uso y presupuesto.' },
    { icon: 'IconShop'        as const, title: 'Garantía gestionada por nosotros', desc: 'Si algo falla, nos encargamos de la garantía directamente. Sin trámites para ti.' },
    { icon: 'IconElectronic'  as const, title: 'Servicio técnico propio',          desc: 'Tienes taller de reparación en la misma tienda. Un solo punto de contacto para todo.' },
  ]

  const pcBuilds = [
    { title: 'PC Gaming',      desc: 'Alto rendimiento para juegos a 1080p, 1440p o 4K. Configuramos la build que mejor encaja con tu juego y presupuesto.' },
    { title: 'PC Oficina',     desc: 'Equipos silenciosos, estables y eficientes para trabajo diario, ofimática y videollamadas.' },
    { title: 'PC Workstation', desc: 'Potencia para diseño 3D, edición de vídeo, renderizado o programación intensiva.' },
  ]

  const faqItems = [
    { question: '¿Puedo reservar un producto?',           answer: 'Sí, puedes consultarnos por WhatsApp o teléfono y te avisamos cuando el producto esté disponible o lo reservamos para ti.' },
    { question: '¿Qué garantía tienen los productos?',    answer: 'Todos los productos nuevos incluyen la garantía oficial del fabricante. Gestionamos cualquier incidencia de garantía directamente desde nuestra tienda.' },
    { question: '¿Qué formas de pago aceptáis?',          answer: 'Aceptamos efectivo, tarjeta, Bizum y transferencia bancaria.' },
    { question: '¿Realizáis envíos?',                     answer: 'Sí realizamos envíos, aunque el coste depende de la zona y del tipo de producto. Consúltanos antes de hacer tu pedido y te calculamos el coste de envío sin compromiso.' },
    { question: '¿Hacéis montajes a medida?',             answer: 'Sí. Puedes traernos los componentes o comprarlos en nuestra tienda y nos encargamos del montaje, cableado y configuración inicial.' },
  ]

  // ── Modal presupuesto empresas ────────────────────────────
  const modalOpen = ref(false)
  const modalSent = ref(false)
  const modalForm = useForm({ name: '', email: '', phone: '', service: 'Equipamiento para oficina', message: '' })
  const sendModal  = () => modalForm.post(route('contact.send'), { preserveScroll: true, onSuccess: () => { modalSent.value = true; modalForm.reset() } })
  const openModal  = () => { modalOpen.value = true;  document.body.style.overflow = 'hidden' }
  const closeModal = () => { modalOpen.value = false; document.body.style.overflow = ''; modalSent.value = false }

  const trustItems = [
    { icon: 'IconSupport'     as const, label: 'Valoraciones 5 estrellas en Google' },
    { icon: 'IconDesktop'     as const, label: 'Configuración incluida' },
    { icon: 'IconMaintenance' as const, label: 'Garantía gestionada por nosotros' },
    { icon: 'IconElectronic'  as const, label: 'Servicio técnico propio' },
    { icon: 'IconLaptop'      as const, label: 'Presupuesto sin compromiso' },
  ]

  const jsonLd = {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'ComputerStore',
        name: 'iCoreByte',
        url: 'https://icorebyte.com',
        address: {
          '@type': 'PostalAddress',
          streetAddress: 'Carretera de Palamós 57, local',
          addressLocality: 'Sant Feliu de Guíxols',
          addressRegion: 'Girona',
          postalCode: '17220',
          addressCountry: 'ES',
        },
        geo: {
          '@type': 'GeoCoordinates',
          latitude: 41.783112,
          longitude: 3.036094,
        },
        telephone: '+34 678 758 210',
        openingHoursSpecification: [
          { '@type': 'OpeningHoursSpecification', dayOfWeek: ['Monday','Tuesday','Wednesday','Thursday','Friday'], opens: '10:00', closes: '13:30' },
          { '@type': 'OpeningHoursSpecification', dayOfWeek: ['Monday','Tuesday','Wednesday','Thursday','Friday'], opens: '17:00', closes: '20:00' },
          { '@type': 'OpeningHoursSpecification', dayOfWeek: ['Saturday'], opens: '10:00', closes: '13:00' },
        ],
      },
      {
        '@type': 'FAQPage',
        mainEntity: faqItems.map(item => ({
          '@type': 'Question',
          name: item.question,
          acceptedAnswer: { '@type': 'Answer', text: item.answer },
        })),
      },
    ],
  }
</script>

<template>
  <Head>
    <title>Tienda de informática en Sant Feliu de Guíxols | iCOREBYTE</title>
    <meta name="description" content="Portátiles, sobremesa, componentes, accesorios, impresoras y más en nuestra tienda de Sant Feliu de Guíxols. Asesoramiento personalizado, montaje incluido y garantía gestionada." />
    <link rel="canonical" href="https://icorebyte.com/productos" />
    <meta property="og:title" content="Tienda de informática en Sant Feliu de Guíxols | iCOREBYTE" />
    <meta property="og:description" content="Portátiles, sobremesa, componentes, accesorios, impresoras y más en nuestra tienda de Sant Feliu de Guíxols." />
    <meta property="og:url" content="https://icorebyte.com/productos" />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Tienda de informática en Sant Feliu de Guíxols | iCOREBYTE" />
    <meta name="twitter:description" content="Portátiles, sobremesa, componentes, accesorios, impresoras y más en nuestra tienda de Sant Feliu de Guíxols." />
    <component :is="'script'" type="application/ld+json" v-html="JSON.stringify(jsonLd)" />
  </Head>

  <PublicLayout>
    <div class="p-products">

      <!-- ── Hero ──────────────────────────────────────────── -->
      <section ref="heroSectionRef" class="p-hero">
        <div class="p-hero__bg" aria-hidden="true">
          <div ref="heroBgInnerRef" class="p-hero__bg-inner" />
        </div>
        <div class="p-hero__inner o-container">
          <p class="p-hero__overline">// PRODUCTOS</p>
          <h1 class="p-hero__title">Tienda de informática en Sant Feliu de Guíxols</h1>
          <p class="p-hero__subtitle">
            Portátiles, sobremesa, componentes, accesorios y mucho más.
            Asesoramiento personalizado, montaje incluido y garantía gestionada por nosotros.
          </p>
          <div class="p-hero__ctas">
            <a
              :href="WA_GENERAL"
              class="p-hero__cta p-hero__cta--primary"
              target="_blank"
              rel="noopener noreferrer"
            >Consultar disponibilidad →</a>
            <a
              :href="MAPS_URL"
              class="p-hero__cta p-hero__cta--ghost"
              target="_blank"
              rel="noopener noreferrer"
            >Cómo llegar</a>
          </div>
        </div>
      </section>

      <!-- ── Trust bar ─────────────────────────────────────── -->
      <CTrustBar :items="trustItems" />

      <!-- ── Categorías ─────────────────────────────────────── -->
      <section class="p-categories">
        <div class="o-container">
          <CTitle
            overline="// CATÁLOGO"
            title="Nuestros productos"
            description="Encuentra todo lo que necesitas para tu equipo. Consúltanos la disponibilidad por WhatsApp."
            color="primary"
          />
          <div class="p-categories__grid">
            <article
              v-for="cat in productCategories.filter(c => c.enabled)"
              :key="cat.slug"
              :id="cat.slug"
              class="p-categories__card"
            >
              <div class="p-categories__card-icon">
                <CIcon :icon="cat.icon" color="primary" size="32px" />
              </div>
              <h2 class="p-categories__card-title">{{ cat.title }}</h2>
              <p class="p-categories__card-desc">{{ cat.description }}</p>
              <ul class="p-categories__card-examples">
                <li v-for="ex in cat.examples" :key="ex" class="p-categories__card-tag">{{ ex }}</li>
              </ul>
              <p v-if="cat.brands.length" class="p-categories__card-brands">
                <strong>Marcas:</strong> {{ cat.brands.join(', ') }}
              </p>
              <p v-else class="p-categories__card-brands p-categories__card-brands--todo">
                <!-- TODO confirmar marcas disponibles -->
              </p>
            </article>
          </div>
          <div class="p-categories__footer">
            <a
              :href="WA_GENERAL"
              class="p-categories__cta"
              target="_blank"
              rel="noopener noreferrer"
            >Consultar disponibilidad por WhatsApp →</a>
          </div>
        </div>
      </section>

      <!-- ── ¿No encuentras lo que buscas? ─────────────────── -->
      <section class="p-custom-order">
        <div class="o-container">
          <div class="p-custom-order__box">
            <div class="p-custom-order__text">
              <h2 class="p-custom-order__title">¿No encuentras lo que buscas?</h2>
              <p class="p-custom-order__desc">
                Lo conseguimos bajo pedido. Dinos qué necesitas y te buscamos el mejor precio.
                Plazo de entrega: <!-- TODO confirmar plazo medio de encargo -->
              </p>
            </div>
            <a
              :href="WA_BASE + 'Hola%2C%20me%20gustar%C3%ADa%20hacer%20un%20encargo%20de%20producto'"
              class="p-custom-order__cta"
              target="_blank"
              rel="noopener noreferrer"
            >Hacer un encargo →</a>
          </div>
        </div>
      </section>

      <!-- ── Ventajas de comprar en tienda ──────────────────── -->
      <section class="p-advantages">
        <div class="o-container">
          <CTitle
            overline="// POR QUÉ ELEGIRNOS"
            title="Ventajas de comprar en tienda"
            color="primary"
          />
          <div class="p-advantages__grid">
            <div v-for="adv in advantages" :key="adv.title" class="p-advantages__item">
              <CIcon :icon="adv.icon" color="primary" size="28px" is-wrapper />
              <div>
                <h3 class="p-advantages__item-title">{{ adv.title }}</h3>
                <p class="p-advantages__item-desc">{{ adv.desc }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── PCs a medida ───────────────────────────────────── -->
      <section class="p-custom-pc">
        <div class="p-custom-pc__parallax">
          <div class="p-custom-pc__parallax-bg" :style="{ backgroundImage: `url(${images.imagePcGaming})` }" aria-hidden="true" />
          <div class="p-custom-pc__parallax-overlay" aria-hidden="true" />
          <div class="p-custom-pc__parallax-content o-container">
            <CTitle
              overline="// MONTAJES"
              title="PCs a medida"
              description="Gaming, oficina o workstation. Diseñamos la build perfecta para ti."
              color="primary"
            />
            <div class="p-custom-pc__grid">
              <div v-for="build in pcBuilds" :key="build.title" class="p-custom-pc__card">
                <h3 class="p-custom-pc__card-title">{{ build.title }}</h3>
                <p class="p-custom-pc__card-desc">{{ build.desc }}</p>
                <a
                  :href="WA_BASE + 'Hola%2C%20me%20interesa%20un%20' + encodeURIComponent(build.title) + '%20a%20medida'"
                  class="p-custom-pc__card-cta"
                  target="_blank"
                  rel="noopener noreferrer"
                >Consultar →</a>
              </div>
            </div>
            <div class="p-custom-pc__footer">
              <a href="/servicios" class="p-custom-pc__link">Ver también nuestros servicios de reparación →</a>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Empresas ───────────────────────────────────────── -->
      <section class="p-business">
        <div class="o-container">
          <div class="p-business__card">
            <p class="p-business__overline">// EMPRESAS</p>
            <h2 class="p-business__title">Equipamiento para tu oficina</h2>
            <p class="p-business__desc">
              Suministramos y configuramos equipos para empresas y autónomos: PCs, portátiles,
              impresoras, consumibles y periféricos. Presupuesto personalizado, factura con
              desglose de IVA y suministro continuo de consumibles.
            </p>
            <ul class="p-business__list">
              <li>
                <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Presupuestos para equipar oficinas completas
              </li>
              <li>
                <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Factura a empresa con IVA desglosado
              </li>
              <li>
                <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Suministro recurrente de consumibles
              </li>
              <li>
                <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Soporte técnico incluido con plan de mantenimiento
              </li>
            </ul>
            <div class="p-business__ctas">
              <button class="p-business__cta p-business__cta--primary" @click="openModal">
                Solicitar presupuesto →
              </button>
              <a
                :href="WA_BASE + 'Hola%2C%20necesito%20equipamiento%20para%20mi%20empresa'"
                class="p-business__cta p-business__cta--ghost"
                target="_blank"
                rel="noopener noreferrer"
              >WhatsApp</a>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Reacondicionados (desactivado) ─────────────────── -->
      <section v-if="refurbishedEnabled" class="p-refurbished">
        <div class="o-container">
          <CTitle
            overline="// REACONDICIONADOS"
            title="Equipos reacondicionados"
            description="Equipos revisados y garantizados a precios reducidos."
            color="primary"
          />
          <!-- TODO: añadir grid de equipos reacondicionados cuando esté confirmada la disponibilidad -->
        </div>
      </section>

      <!-- ── Visítanos ──────────────────────────────────────── -->
      <section class="p-visit">
        <div class="o-container">
          <CTitle
            overline="// TIENDA FÍSICA"
            title="Visítanos en nuestra tienda"
            color="primary"
          />
          <div class="p-visit__layout">
            <div class="p-visit__info">
              <div class="p-visit__detail">
                <CIcon icon="IconLocation" color="primary" size="20px" />
                <div>
                  <strong>Dirección</strong>
                  <p>Carretera de Palamós 57, 17220 Sant Feliu de Guíxols, Girona</p>
                </div>
              </div>
              <div class="p-visit__detail">
                <CIcon icon="IconTime" color="primary" size="20px" />
                <div>
                  <strong>Horario</strong>
                  <p>Lunes a viernes: 10:00–13:30 y 17:00–20:00</p>
                  <p>Sábados: 10:00–13:00</p>
                </div>
              </div>
              <div class="p-visit__detail">
                <CIcon icon="IconPhone" color="primary" size="20px" />
                <div>
                  <strong>Teléfono / WhatsApp</strong>
                  <p>+34 678 758 210</p>
                </div>
              </div>
              <div class="p-visit__ctas">
                <a :href="MAPS_URL" class="p-visit__btn p-visit__btn--primary" target="_blank" rel="noopener noreferrer">Cómo llegar</a>
                <a :href="WA_GENERAL" class="p-visit__btn p-visit__btn--ghost" target="_blank" rel="noopener noreferrer">WhatsApp</a>
              </div>
            </div>
            <div class="p-visit__map-wrap">
              <div v-if="!mapActive" class="p-visit__map-overlay" @click="mapActive = true">
                <span class="p-visit__map-hint">Clic para ver el mapa</span>
              </div>
              <iframe
                v-if="mapActive"
                class="p-visit__map"
                src="https://www.openstreetmap.org/export/embed.html?bbox=3.030094%2C41.780112%2C3.042094%2C41.786112&layer=mapnik&marker=41.783112%2C3.036094"
                title="Ubicación de iCoreByte — Carretera de Palamós 57, Sant Feliu de Guíxols"
                loading="lazy"
                referrerpolicy="no-referrer"
              />
              <div v-else class="p-visit__map-placeholder" @click="mapActive = true" aria-label="Cargar mapa">
                <CIcon icon="IconLocation" color="primary" size="40px" />
                <p>Cargar mapa</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── FAQ ───────────────────────────────────────────── -->
      <section class="p-faq">
        <div class="o-container">
          <CTitle
            overline="// PREGUNTAS FRECUENTES"
            title="¿Tienes dudas?"
            color="primary"
          />
        </div>
        <CFaqAccordion :items="faqItems" />
      </section>

      <!-- ── CTA final ──────────────────────────────────────── -->
      <CCtaBanner
        title="¿Listo para equiparte?"
        subtitle="Visítanos en tienda o consúltanos por WhatsApp. Te asesoramos sin compromiso."
        cta-label="Consultar por WhatsApp"
        :cta-href="WA_GENERAL"
      />

    </div>
  </PublicLayout>

  <!-- ── Modal presupuesto empresas ──────────────────────────── -->
  <Teleport to="body">
    <Transition name="p-modal">
      <div v-if="modalOpen" class="p-modal-overlay" @click.self="closeModal">
        <div class="p-modal" role="dialog" aria-modal="true" aria-label="Formulario de presupuesto">

          <button class="p-modal__close" @click="closeModal" aria-label="Cerrar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </button>

          <div v-if="!modalSent" class="p-modal__body">
            <p class="p-modal__overline">// EMPRESAS</p>
            <h3 class="p-modal__title">Consultar presupuesto</h3>
            <p class="p-modal__desc">Cuéntanos qué necesitas equipar y te preparamos un presupuesto personalizado sin compromiso.</p>

            <form @submit.prevent="sendModal" novalidate class="p-modal__form">
              <div class="p-modal__row">
                <div class="p-modal__field">
                  <label class="p-modal__label">Nombre / Empresa</label>
                  <input v-model="modalForm.name" type="text" placeholder="Tu nombre o empresa" class="p-modal__input" />
                  <p v-if="modalForm.errors.name" class="p-modal__error">{{ modalForm.errors.name }}</p>
                </div>
                <div class="p-modal__field">
                  <label class="p-modal__label">Email</label>
                  <input v-model="modalForm.email" type="email" placeholder="tu@email.com" class="p-modal__input" />
                  <p v-if="modalForm.errors.email" class="p-modal__error">{{ modalForm.errors.email }}</p>
                </div>
              </div>
              <div class="p-modal__field">
                <label class="p-modal__label">Teléfono (opcional)</label>
                <input v-model="modalForm.phone" type="tel" placeholder="+34 XXX XXX XXX" class="p-modal__input" />
              </div>
              <div class="p-modal__field">
                <label class="p-modal__label">Mensaje</label>
                <textarea v-model="modalForm.message" placeholder="¿Cuántos puestos de trabajo? ¿Qué equipos necesitáis? ¿Presupuesto aproximado?" rows="4" class="p-modal__input p-modal__input--textarea"></textarea>
                <p v-if="modalForm.errors.message" class="p-modal__error">{{ modalForm.errors.message }}</p>
              </div>
              <p class="p-modal__privacy">
                Al enviar aceptas el tratamiento de tus datos para gestionar tu consulta.
                <a href="/politica-de-privacidad" class="p-modal__privacy-link" target="_blank">Política de Privacidad</a>.
              </p>
              <button type="submit" class="p-modal__submit" :disabled="modalForm.processing">
                {{ modalForm.processing ? 'Enviando…' : 'Enviar consulta' }}
              </button>
            </form>
          </div>

          <div v-else class="p-modal__success">
            <svg viewBox="0 0 48 48" fill="none" class="p-modal__success-icon">
              <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
              <path d="M14 24l7 7 13-14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h3 class="p-modal__title">¡Consulta enviada!</h3>
            <p class="p-modal__desc">Te contactamos en menos de 24h con tu presupuesto personalizado.</p>
            <button class="p-modal__submit" @click="closeModal">Cerrar</button>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style lang="scss" scoped>
  // ── Hero ───────────────────────────────────────────────────
  .p-hero {
    padding: 72px 0 56px;
    position: relative;
    overflow: hidden;

    @include from-md { padding: 96px 0 72px; }

    &__bg {
      position: absolute;
      inset: 0;
      overflow: hidden;
      z-index: 0;

      &::after {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 1;
        background: rgba(0, 0, 0, 0.2);
      }
    }

    &__bg-inner {
      position: absolute;
      top: -20%;
      left: 0;
      right: 0;
      bottom: -20%;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      will-change: transform;
    }

    &__inner {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    &__overline {
      font-family: 'Courier New', monospace;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--color-primary);
      margin: 0 0 20px;
    }

    &__title {
      font-size: clamp(1.9rem, 5vw, 3rem);
      font-weight: 800;
      line-height: 1.2;
      color: #fff;
      margin: 0 0 20px;
      max-width: 720px;
    }

    &__subtitle {
      font-size: 1.05rem;
      line-height: 1.7;
      color: rgba(255, 255, 255, 0.75);
      margin: 0 0 36px;
      max-width: 560px;
    }

    &__ctas {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
    }

    &__cta {
      text-decoration: none;
      padding: 12px 28px;
      border-radius: 999px;
      font-size: 0.9rem;
      font-weight: 700;
      transition: filter 0.2s ease, border-color 0.2s ease, color 0.2s ease;

      &--primary {
        background: var(--color-primary);
        color: #fff;
        border: none;
        &:hover { filter: brightness(1.15); }
      }

      &--ghost {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.35);
        color: rgba(255, 255, 255, 0.7);
        &:hover { border-color: rgba(255, 255, 255, 0.75); color: #fff; }
      }
    }
  }

  // ── Categorías ─────────────────────────────────────────────
  .p-categories {
    padding: 72px 0 56px;

    &__grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 24px;
      margin-top: 48px;

      @include from-sm { grid-template-columns: repeat(2, 1fr); }
      @include from-md { grid-template-columns: repeat(4, 1fr); }
    }

    &__card {
      background: var(--color-black);
      border: 1px solid var(--color-primary-dark);
      border-radius: 12px;
      padding: 24px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      scroll-margin-top: 100px;
      transition: border-color 0.2s;

      &:hover { border-color: var(--color-primary); }

      &-icon { margin-bottom: 4px; }

      &-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--color-white);
        margin: 0;
      }

      &-desc {
        font-size: 0.875rem;
        color: var(--color-gray-dark);
        line-height: 1.6;
        margin: 0;
        flex: 1;
      }

      &-examples {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        list-style: none;
        padding: 0;
        margin: 0;
      }

      &-tag {
        font-size: 0.75rem;
        background: var(--color-primary-dark);
        color: var(--color-primary);
        border: 1px solid var(--color-primary);
        border-radius: 6px;
        padding: 2px 8px;
      }

      &-brands {
        font-size: 0.75rem;
        color: var(--color-gray-dark);
        margin: 0;
        &--todo { min-height: 1em; }
      }
    }

    &__footer {
      margin-top: 40px;
      text-align: center;
    }

    &__cta {
      display: inline-flex;
      align-items: center;
      padding: 13px 32px;
      background: var(--color-primary);
      color: #fff;
      border-radius: 999px;
      font-size: 0.9rem;
      font-weight: 700;
      text-decoration: none;
      transition: filter 0.2s;

      &:hover { filter: brightness(1.15); }
    }
  }

  // ── ¿No encuentras? ────────────────────────────────────────
  .p-custom-order {
    padding-bottom: 56px;

    &__box {
      background: var(--color-primary-dark);
      border: 1px solid var(--color-primary);
      border-radius: 16px;
      padding: 40px;
      display: flex;
      flex-direction: column;
      gap: 24px;
      align-items: flex-start;

      @include from-sm { flex-direction: row; align-items: center; justify-content: space-between; }
    }

    &__title {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--color-white);
      margin: 0 0 8px;
    }

    &__desc {
      font-size: 0.9rem;
      color: var(--color-gray-dark);
      margin: 0;
    }

    &__cta {
      flex-shrink: 0;
      padding: 12px 28px;
      background: var(--color-primary);
      color: #fff;
      border-radius: 999px;
      font-weight: 700;
      font-size: 0.9rem;
      text-decoration: none;
      white-space: nowrap;
      transition: filter 0.2s;

      &:hover { filter: brightness(1.15); }
    }
  }

  // ── Ventajas ───────────────────────────────────────────────
  .p-advantages {
    padding: 72px 0;

    &__grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 32px;
      margin-top: 48px;

      @include from-sm { grid-template-columns: repeat(2, 1fr); }
      @include from-md { grid-template-columns: repeat(3, 1fr); }
    }

    &__item {
      display: flex;
      gap: 16px;
      align-items: flex-start;

      &-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--color-white);
        margin: 0 0 6px;
      }

      &-desc {
        font-size: 0.875rem;
        color: var(--color-gray-dark);
        line-height: 1.6;
        margin: 0;
      }
    }
  }

  // ── PCs a medida ───────────────────────────────────────────
  .p-custom-pc {
    // Contenedor sin padding propio — el parallax lo gestiona todo
    &__parallax {
      position: relative;
      overflow: hidden;
    }

    &__parallax-bg {
      position: absolute;
      inset: -15% 0;          // sobresale arriba/abajo para el efecto
      background-size: cover;
      background-position: center 30%;
      background-attachment: fixed;  // imagen fija: el contenido scrollea encima

      @media (max-width: 768px) {
        background-attachment: scroll; // iOS no soporta fixed bien
        inset: 0;
      }
    }

    &__parallax-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(
        to bottom,
        rgba(10,10,20,0.65) 0%,
        rgba(10,10,20,0.45) 40%,
        rgba(10,10,20,0.45) 60%,
        rgba(10,10,20,0.65) 100%
      );
    }

    &__parallax-content {
      position: relative;
      z-index: 1;
      padding: 80px 16px;
      // Always light text — overrides light-mode tokens locally
      --color-text:       #ffffff;
      --color-white:      #ffffff;
      --color-text-muted: rgba(255, 255, 255, 0.75);

      :deep(.c-title__overline),
      :deep(.c-title__title),
      :deep(.c-title__desc) {
        color: #ffffff;
        text-shadow: 0 2px 12px rgba(0,0,0,0.8), 0 1px 4px rgba(0,0,0,0.9);
      }
    }

    &__grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 24px;
      margin-top: 48px;

      @include from-sm { grid-template-columns: repeat(3, 1fr); }
    }

    &__card {
      background: rgba(0,0,0,0.55);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 12px;
      padding: 28px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      transition: border-color 0.2s, background 0.2s;

      &:hover {
        border-color: var(--color-primary);
        background: rgba(0,0,0,0.70);
      }

      &-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--color-primary);
        margin: 0;
      }

      &-desc {
        font-size: 0.875rem;
        color: rgba(255,255,255,0.75);
        line-height: 1.6;
        margin: 0;
        flex: 1;
      }

      &-cta {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--color-primary);
        text-decoration: none;

        &:hover { text-decoration: underline; }
      }
    }

    &__footer {
      margin-top: 32px;
      text-align: center;
    }

    &__link {
      font-size: 0.9rem;
      color: rgba(255,255,255,0.7);
      text-decoration: none;

      &:hover { color: #fff; }
    }
  }

  // ── Empresas ───────────────────────────────────────────────
  .p-business {
    padding: 80px 0;
    background: var(--color-primary-dark);

    &__card {
      max-width: 680px;
      margin: 0 auto;
      text-align: center;
    }

    &__overline {
      font-family: 'Courier New', monospace;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--color-primary);
      margin: 0 0 14px;
    }

    &__title {
      font-size: clamp(1.6rem, 3.5vw, 2.2rem);
      font-weight: 800;
      color: var(--color-white);
      margin: 0 0 16px;
    }

    &__desc {
      font-size: 0.95rem;
      color: var(--color-gray-dark);
      line-height: 1.75;
      margin: 0 0 28px;
      max-width: 560px;
      margin-left: auto;
      margin-right: auto;
    }

    &__list {
      list-style: none;
      padding: 0;
      margin: 0 0 36px;
      display: inline-flex;
      flex-direction: column;
      gap: 10px;
      text-align: left;

      li {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
        color: var(--color-gray-dark);
        line-height: 1.5;

        svg {
          flex-shrink: 0;
          width: 16px;
          height: 16px;
          color: var(--color-primary);
        }
      }
    }

    &__ctas {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 12px;
    }

    &__cta {
      display: inline-flex;
      align-items: center;
      padding: 13px 32px;
      border-radius: 999px;
      font-size: 0.9rem;
      font-weight: 700;
      text-decoration: none;
      white-space: nowrap;
      cursor: pointer;
      border: none;
      transition: filter 0.2s;

      &--primary {
        background: var(--color-primary);
        color: #fff;
        &:hover { filter: brightness(1.15); }
      }

      &--ghost {
        background: transparent;
        border: 1.5px solid var(--color-primary);
        color: var(--color-primary);
        &:hover { filter: brightness(1.2); }
      }
    }
  }

  // ── Modal presupuesto empresas ──────────────────────────────
  .p-modal-enter-active,
  .p-modal-leave-active { transition: opacity 0.25s ease; }
  .p-modal-enter-from,
  .p-modal-leave-to     { opacity: 0; }

  .p-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
    z-index: 9000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }

  .p-modal {
    background: var(--color-bg, #0d0d14);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    width: 100%;
    max-width: 540px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 32px;
    position: relative;

    &__close {
      position: absolute;
      top: 16px;
      right: 16px;
      background: none;
      border: none;
      cursor: pointer;
      color: var(--color-gray-dark);
      padding: 4px;
      svg { width: 20px; height: 20px; display: block; }
      &:hover { color: var(--color-white); }
    }

    &__overline {
      font-family: 'Courier New', monospace;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--color-primary);
      margin: 0 0 10px;
    }

    &__title {
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--color-white);
      margin: 0 0 8px;
    }

    &__desc {
      font-size: 0.875rem;
      color: var(--color-gray-dark);
      margin: 0 0 24px;
      line-height: 1.6;
    }

    &__form { display: flex; flex-direction: column; gap: 16px; }

    &__row {
      display: grid;
      grid-template-columns: 1fr;
      gap: 16px;
      @include from-sm { grid-template-columns: 1fr 1fr; }
    }

    &__field { display: flex; flex-direction: column; gap: 6px; }

    &__label {
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--color-gray-dark);
    }

    &__input {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 8px;
      padding: 10px 14px;
      color: var(--color-white);
      font-size: 0.9rem;
      outline: none;
      width: 100%;
      box-sizing: border-box;
      &:focus { border-color: var(--color-primary); }
      &--textarea { resize: vertical; min-height: 100px; font-family: inherit; }
    }

    &__error { font-size: 0.78rem; color: #f87171; margin: 0; }

    &__privacy {
      font-size: 0.75rem;
      color: var(--color-gray-dark);
      margin: 0;
      line-height: 1.5;
    }

    &__privacy-link { color: var(--color-primary); text-decoration: underline; }

    &__submit {
      background: var(--color-primary);
      color: #fff;
      border: none;
      border-radius: 999px;
      padding: 13px 32px;
      font-size: 0.9rem;
      font-weight: 700;
      cursor: pointer;
      transition: filter 0.2s;
      align-self: flex-start;
      &:hover { filter: brightness(1.15); }
      &:disabled { opacity: 0.6; cursor: not-allowed; }
    }

    &__success {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      gap: 16px;
      padding: 16px 0;
    }

    &__success-icon {
      width: 56px;
      height: 56px;
      color: var(--color-primary);
    }
  }

  // ── Visítanos ──────────────────────────────────────────────
  .p-visit {
    padding: 72px 0;

    &__layout {
      display: grid;
      grid-template-columns: 1fr;
      gap: 40px;
      margin-top: 48px;

      @include from-md { grid-template-columns: 1fr 1fr; align-items: start; }
    }

    &__info {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    &__detail {
      display: flex;
      gap: 14px;
      align-items: flex-start;

      strong {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--color-white);
        margin-bottom: 4px;
      }

      p {
        font-size: 0.875rem;
        color: var(--color-gray-dark);
        margin: 0;
        line-height: 1.5;
      }
    }

    &__ctas {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    &__btn {
      padding: 10px 24px;
      border-radius: 999px;
      font-size: 0.875rem;
      font-weight: 700;
      text-decoration: none;
      transition: filter 0.2s, border-color 0.2s, color 0.2s;

      &--primary {
        background: var(--color-primary);
        color: #fff;
        &:hover { filter: brightness(1.15); }
      }

      &--ghost {
        background: transparent;
        border: 1px solid var(--color-primary);
        color: var(--color-primary);
        &:hover { filter: brightness(1.2); }
      }
    }

    &__map-wrap {
      position: relative;
      border-radius: 12px;
      overflow: hidden;
      height: 340px;
      background: var(--color-primary-dark);
      cursor: pointer;
    }

    &__map-overlay {
      position: absolute;
      inset: 0;
      z-index: 2;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    &__map-hint {
      background: rgba(0, 0, 0, 0.55);
      color: #fff;
      font-size: 0.85rem;
      font-weight: 500;
      padding: 8px 16px;
      border-radius: 999px;
    }

    &__map-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 12px;
      cursor: pointer;

      p {
        font-size: 0.875rem;
        color: var(--color-gray-dark);
        margin: 0;
      }
    }

    &__map {
      width: 100%;
      height: 100%;
      border: none;
      display: block;
    }
  }

  // ── FAQ ────────────────────────────────────────────────────
  .p-faq {
    padding-top: 72px;
  }
</style>
