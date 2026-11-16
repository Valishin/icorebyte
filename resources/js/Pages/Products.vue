<script setup lang="ts">
  import CCtaBanner from '@/Components/CCtaBanner.vue'
  import CFaqAccordion from '@/Components/CFaqAccordion.vue'
  import CIcon from '@/Components/CIcon.vue'
  import CTitle from '@/Components/CTitle.vue'
  import CTrustBar from '@/Components/CTrustBar.vue'
  import PublicLayout from '@/Layouts/PublicLayout.vue'
  import { productCategories, refurbishedEnabled } from '@/data/products'
  import { images } from '@/constants/images'
  import { Head } from '@inertiajs/vue3'
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
    { question: '¿Realizáis envíos?',                     answer: 'TODO: confirmar política de envíos.' },
    { question: '¿Recogéis equipos viejos para reciclar?',answer: 'TODO: confirmar política de reciclaje / punto limpio.' },
    { question: '¿Hacéis montajes a medida?',             answer: 'Sí. Puedes traernos los componentes o comprarlos en nuestra tienda y nos encargamos del montaje, cableado y configuración inicial.' },
  ]

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
              <a
                :href="`${WA_BASE}${cat.ctaMessage}`"
                class="p-categories__card-cta"
                target="_blank"
                rel="noopener noreferrer"
              >Consultar por WhatsApp</a>
            </article>
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
        <div class="o-container">
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
      </section>

      <!-- ── Empresas ───────────────────────────────────────── -->
      <section class="p-business">
        <div class="o-container">
          <div class="p-business__box">
            <div class="p-business__text">
              <p class="p-business__overline">// EMPRESAS</p>
              <h2 class="p-business__title">Equipamiento para tu oficina</h2>
              <p class="p-business__desc">
                Suministramos y configuramos equipos para empresas y autónomos: PCs, portátiles,
                impresoras, consumibles y periféricos. Presupuesto personalizado, factura con
                desglose de IVA y suministro continuo de consumibles.
              </p>
              <ul class="p-business__list">
                <li>Presupuestos para equipar oficinas completas</li>
                <li>Factura a empresa con IVA desglosado</li>
                <li>Suministro recurrente de consumibles</li>
                <li>Soporte técnico incluido con plan de mantenimiento</li>
              </ul>
            </div>
            <div class="p-business__ctas">
              <a href="/contacto" class="p-business__cta p-business__cta--primary">Solicitar presupuesto</a>
              <a
                :href="WA_BASE + 'Hola%2C%20necesito%20equipamiento%20para%20mi%20empresa'"
                class="p-business__cta p-business__cta--ghost"
                target="_blank"
                rel="noopener noreferrer"
              >Consultar por WhatsApp</a>
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

      &-cta {
        display: inline-block;
        margin-top: auto;
        padding: 8px 16px;
        background: var(--color-primary);
        color: #fff;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        text-align: center;
        transition: filter 0.2s;

        &:hover { filter: brightness(1.15); }
      }
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
    padding-bottom: 72px;

    &__grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 24px;
      margin-top: 48px;

      @include from-sm { grid-template-columns: repeat(3, 1fr); }
    }

    &__card {
      background: var(--color-black);
      border: 1px solid var(--color-primary-dark);
      border-radius: 12px;
      padding: 28px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      transition: border-color 0.2s;

      &:hover { border-color: var(--color-primary); }

      &-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--color-primary);
        margin: 0;
      }

      &-desc {
        font-size: 0.875rem;
        color: var(--color-gray-dark);
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
      color: var(--color-primary);
      text-decoration: none;

      &:hover { text-decoration: underline; }
    }
  }

  // ── Empresas ───────────────────────────────────────────────
  .p-business {
    padding: 72px 0;
    background: var(--color-primary-dark);

    &__box {
      display: flex;
      flex-direction: column;
      gap: 32px;

      @include from-md { flex-direction: row; align-items: flex-start; justify-content: space-between; gap: 64px; }
    }

    &__overline {
      font-family: 'Courier New', monospace;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--color-primary);
      margin: 0 0 12px;
    }

    &__title {
      font-size: clamp(1.4rem, 3vw, 2rem);
      font-weight: 800;
      color: var(--color-white);
      margin: 0 0 16px;
    }

    &__desc {
      font-size: 0.95rem;
      color: var(--color-gray-dark);
      line-height: 1.7;
      margin: 0 0 16px;
    }

    &__list {
      padding-left: 1.2rem;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 6px;

      li {
        font-size: 0.875rem;
        color: var(--color-gray-dark);
        line-height: 1.5;
      }
    }

    &__ctas {
      display: flex;
      flex-direction: column;
      gap: 12px;
      flex-shrink: 0;

      @include from-sm { flex-direction: row; }
      @include from-md { flex-direction: column; align-items: flex-start; }
    }

    &__cta {
      padding: 12px 28px;
      border-radius: 999px;
      font-size: 0.9rem;
      font-weight: 700;
      text-decoration: none;
      white-space: nowrap;
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
