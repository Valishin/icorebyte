<script setup lang="ts">
  import CCtaBanner from '@/Components/CCtaBanner.vue'
  import CFaqAccordion from '@/Components/CFaqAccordion.vue'
  import CProcessSteps from '@/Components/CProcessSteps.vue'
  import CServiceCard from '@/Components/CServiceCard.vue'
  import CTitle from '@/Components/CTitle.vue'
  import CTrustBar from '@/Components/CTrustBar.vue'
  import PublicLayout from '@/Layouts/PublicLayout.vue'
  import { services } from '@/data/services'
  import { Head, useForm } from '@inertiajs/vue3'
  import { onMounted, onUnmounted, ref } from 'vue'
  import { registerParallax, type ParallaxEntry } from '@/composables/useParallaxLoop'
  import { images } from '@/constants/images'

  // ── Parallax hero (IntersectionObserver + scrollY) ────────
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
      const y = heroCurY | 0
      heroBgInnerRef.value.style.transform = `translate3d(0,${y}px,0)`
      if (Math.abs(heroCurY - heroTargetY) > 0.1) return true
      heroCurY = heroTargetY
      return false
    }
  }

  const startHeroParallax = () => {
    if (!unregisterHeroParallax) unregisterHeroParallax = registerParallax(heroEntry)
  }
  const stopHeroParallax = () => {
    unregisterHeroParallax?.()
    unregisterHeroParallax = null
  }

  onMounted(() => {
    if (heroBgInnerRef.value) heroBgInnerRef.value.style.backgroundImage = `url(${images.heroSlide3})`
    heroObserver = new IntersectionObserver(
      (entries) => entries.forEach(e => e.isIntersecting ? startHeroParallax() : stopHeroParallax()),
      { threshold: 0 }
    )
    if (heroSectionRef.value) heroObserver.observe(heroSectionRef.value)
  })
  onUnmounted(() => { heroObserver?.disconnect(); unregisterHeroParallax?.() })

  // ── Iconos de fondo flotantes (IT) — solo laterales ───────
  interface BgIcon { d: string; x: number; y: number; size: number; opacity: number; delay: number; dur: number }

  const itIcons: BgIcon[] = [
    // Lateral izquierdo
    { d: 'M4 5h16v10H4V5zm-1 10h18v2H3v-2z',                                                    x:  1, y:  5, size: 56, opacity: 0.45, delay: 0.0, dur: 8  },
    { d: 'M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm5 16v1',           x:  6, y: 16, size: 42, opacity: 0.38, delay: 1.4, dur: 11 },
    { d: 'M12 20h.01M2 8.82a15 15 0 0120 0M5 12.86A10 10 0 0119 12.86M8 16.82a5 5 0 018 0',     x:  2, y: 27, size: 50, opacity: 0.35, delay: 3.1, dur:  7 },
    { d: 'M9 3h6v3h3v3h3v6h-3v3h-3v3H9v-3H6v-3H3V9h3V6h3V3zm0 6h6v6H9V9z',                    x:  7, y: 40, size: 64, opacity: 0.33, delay: 2.5, dur:  9 },
    { d: 'M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z',                                           x:  0, y: 53, size: 44, opacity: 0.38, delay: 4.2, dur: 12 },
    { d: 'M16 18l6-6-6-6M8 6L2 12l6 6',                                                          x:  5, y: 65, size: 46, opacity: 0.40, delay: 5.3, dur: 10 },
    { d: 'M2 4h20v13H2V4zm0 13 3 4h14l3-4M9 20h6',                                              x:  2, y: 77, size: 48, opacity: 0.35, delay: 0.3, dur: 14 },
    { d: 'M12 2a7 7 0 00-7 7v5a7 7 0 0014 0V9a7 7 0 00-7-7zm0 0v7',                             x:  6, y: 90, size: 40, opacity: 0.35, delay: 1.9, dur:  6 },
    // Lateral derecho
    { d: 'M4 5h16v10H4V5zm-1 10h18v2H3v-2z',                                                    x: 90, y:  8, size: 54, opacity: 0.42, delay: 3.7, dur:  8 },
    { d: 'M9 3h6v3h3v3h3v6h-3v3h-3v3H9v-3H6v-3H3V9h3V6h3V3zm0 6h6v6H9V9z',                    x: 93, y: 20, size: 36, opacity: 0.33, delay: 1.9, dur: 11 },
    { d: 'M2 4h20v13H2V4zm0 13 3 4h14l3-4M9 20h6',                                              x: 88, y: 32, size: 52, opacity: 0.38, delay: 6.0, dur:  7 },
    { d: 'M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z',                                           x: 95, y: 44, size: 44, opacity: 0.35, delay: 2.2, dur: 15 },
    { d: 'M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm5 16v1',           x: 91, y: 56, size: 38, opacity: 0.40, delay: 4.8, dur:  9 },
    { d: 'M16 18l6-6-6-6M8 6L2 12l6 6',                                                          x: 94, y: 68, size: 42, opacity: 0.33, delay: 0.6, dur: 13 },
    { d: 'M12 20h.01M2 8.82a15 15 0 0120 0M5 12.86A10 10 0 0119 12.86M8 16.82a5 5 0 018 0',     x: 89, y: 80, size: 46, opacity: 0.38, delay: 2.9, dur:  6 },
    { d: 'M12 2a7 7 0 00-7 7v5a7 7 0 0014 0V9a7 7 0 00-7-7zm0 0v7',                             x: 96, y: 93, size: 48, opacity: 0.35, delay: 1.2, dur: 10 },
  ]

  // ── Modal de presupuesto ──────────────────────────────────
  const contactOpen = ref(false)
  const contactSent = ref(false)
  const contactForm = useForm({
    name:    '',
    email:   '',
    phone:   '',
    service: 'Reparación de ordenadores',
    message: ''
  })
  const sendContact = () => {
    contactForm.post(route('contact.send'), {
      preserveScroll: true,
      onSuccess: () => { contactSent.value = true; contactForm.reset() }
    })
  }
  const openModal  = () => { contactOpen.value = true;  document.body.style.overflow = 'hidden' }
  const closeModal = () => { contactOpen.value = false; document.body.style.overflow = ''; contactSent.value = false }

  const contactServices = [
    'Reparación de ordenadores',
    'Reparación de portátiles',
    'Reparación de móviles',
    'Reparación de tablets',
    'Reparación de consolas',
    'Reparación de impresoras',
    'Reparación de electrónica',
    'Servicio a domicilio',
    'Otros',
  ]

  // ── Modal de empresas ─────────────────────────────────────
  const businessOpen = ref(false)
  const businessSent = ref(false)
  const businessForm = useForm({
    name:    '',
    email:   '',
    phone:   '',
    service: 'Soporte IT Empresa',
    message: ''
  })
  const sendBusiness = () => {
    businessForm.post(route('contact.send'), {
      preserveScroll: true,
      onSuccess: () => { businessSent.value = true; businessForm.reset() }
    })
  }
  const openBusiness  = () => { businessOpen.value = true;  document.body.style.overflow = 'hidden' }
  const closeBusiness = () => { businessOpen.value = false; document.body.style.overflow = ''; businessSent.value = false }

  const businessServiceOptions = [
    'Mantenimiento preventivo',
    'Soporte ante incidencias',
    'Instalación / configuración de red',
    'Gestión de puestos de trabajo',
    'Consulta plan personalizado',
  ]

  // ── FAQ ───────────────────────────────────────────────────
  const faqItems = [
    {
      question: '¿El diagnóstico tiene coste?',
      answer: 'El diagnóstico se incluye en el precio de la reparación. Si finalmente no se acepta el presupuesto, se cobrará únicamente el diagnóstico, cuyo coste depende de la complejidad del caso.',
    },
    {
      question: '¿Qué garantía tienen las reparaciones?',
      answer: 'Todas nuestras reparaciones incluyen garantía, excepto la instalación de software. El tiempo de garantía varía según el tipo de reparación, consúltanos para tu caso concreto.',
    },
    {
      question: '¿Cuánto tarda una reparación?',
      answer: 'El tiempo depende de la complejidad de la reparación. Siempre te damos un tiempo aproximado antes de empezar. También puede variar si hay que esperar a que llegue alguna pieza.',
    },
    {
      question: '¿Usáis piezas originales?',
      answer: 'Utilizamos piezas de calidad contrastada. Siempre te informamos del tipo de pieza disponible (original, compatible de alta calidad) y del precio antes de proceder, para que puedas decidir con toda la información.',
    },
    {
      question: '¿Tratan mis datos con seguridad?',
      answer: 'Sí. Tratamos los datos de tus dispositivos con total confidencialidad y solo accedemos a ellos cuando es estrictamente necesario para realizar la reparación. Puedes consultar nuestra Política de Privacidad para más información.',
    },
    {
      question: '¿Ofrecéis servicio a domicilio?',
      answer: 'Sí, ofrecemos servicio a domicilio en Sant Feliu de Guíxols y las zonas cercanas: S\'Agaró, Castell-Platja d\'Aro, Santa Cristina d\'Aro, Llagostera, Palamós y Tossa de Mar. Contacta con nosotros para consultar disponibilidad.',
    },
    {
      question: '¿Qué formas de pago aceptáis?',
      answer: 'Aceptamos Bizum, efectivo, tarjeta y transferencia bancaria.',
    },
    {
      question: '¿Puedo traer el dispositivo sin cita previa?',
      answer: 'No hace falta cita previa. Puedes traer tu dispositivo directamente durante nuestro horario de atención: lunes a viernes 10:00–13:30 y 17:00–20:00, sábados 10:00–13:00.',
    },
  ]

  // ── Trust bar ─────────────────────────────────────────────
  const trustItems = [
    { icon: 'IconSupport'     as const, label: 'Valoraciones 5 estrellas en Google' },
    { icon: 'IconDesktop'     as const, label: 'Garantía en reparaciones' },
    { icon: 'IconElectronic'  as const, label: 'Reparación a nivel de placa' },
    { icon: 'IconLaptop'      as const, label: 'Servicio a domicilio' },
    { icon: 'IconMaintenance' as const, label: 'Presupuesto sin compromiso' },
  ]

  // ── JSON-LD ───────────────────────────────────────────────
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
        openingHoursSpecification: [
          {
            '@type': 'OpeningHoursSpecification',
            dayOfWeek: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            opens: '10:00',
            closes: '13:30',
          },
          {
            '@type': 'OpeningHoursSpecification',
            dayOfWeek: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            opens: '17:00',
            closes: '20:00',
          },
          {
            '@type': 'OpeningHoursSpecification',
            dayOfWeek: ['Saturday'],
            opens: '10:00',
            closes: '13:00',
          },
        ],
        telephone: '+34 678 758 210',
      },
      {
        '@type': 'ItemList',
        name: 'Servicios de iCoreByte',
        itemListElement: services.map((s, i) => ({
          '@type': 'ListItem',
          position: i + 1,
          name: s.title,
          url: `https://icorebyte.com/servicios#${s.anchor}`,
        })),
      },
      {
        '@type': 'FAQPage',
        mainEntity: faqItems.map(item => ({
          '@type': 'Question',
          name: item.question,
          acceptedAnswer: {
            '@type': 'Answer',
            text: item.answer,
          },
        })),
      },
    ],
  }
</script>

<template>
  <Head>
    <title>Reparación de móviles, ordenadores y consolas en Sant Feliu de Guíxols | iCOREBYTE</title>
    <meta name="description" content="Reparación profesional de móviles, portátiles, PCs, consolas y tablets en Sant Feliu de Guíxols. Diagnóstico previo, garantía incluida y presupuesto sin compromiso." />
    <link rel="canonical" href="https://icorebyte.com/servicios" />
    <meta property="og:title" content="Reparación de móviles, ordenadores y consolas en Sant Feliu de Guíxols | iCOREBYTE" />
    <meta property="og:description" content="Reparación profesional de móviles, portátiles, PCs, consolas y tablets en Sant Feliu de Guíxols. Diagnóstico previo, garantía incluida y presupuesto sin compromiso." />
    <meta property="og:url" content="https://icorebyte.com/servicios" />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Reparación de móviles, ordenadores y consolas en Sant Feliu de Guíxols | iCOREBYTE" />
    <meta name="twitter:description" content="Reparación profesional de móviles, portátiles, PCs, consolas y tablets en Sant Feliu de Guíxols." />
    <component :is="'script'" type="application/ld+json" v-html="JSON.stringify(jsonLd)" />
  </Head>

  <PublicLayout>
    <div class="s-page">

      <!-- ── Iconos de fondo flotantes ─────────────────────── -->
      <div class="s-page-bg" aria-hidden="true">
        <svg
          v-for="(icon, i) in itIcons" :key="i"
          class="s-page-bg__icon"
          :style="{ left: icon.x + '%', top: icon.y + '%', width: icon.size + 'px', height: icon.size + 'px', opacity: icon.opacity, animationDelay: icon.delay + 's', animationDuration: icon.dur + 's' }"
          viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"
        ><path :d="icon.d" /></svg>
      </div>

      <!-- ── Hero ──────────────────────────────────────────── -->
      <section ref="heroSectionRef" class="s-hero">
        <div class="s-hero__bg" aria-hidden="true">
          <div ref="heroBgInnerRef" class="s-hero__bg-inner" />
        </div>
        <div class="s-hero__inner o-container">
          <div class="s-hero__content">
          <p class="s-hero__overline">// SERVICIOS</p>
          <h1 class="s-hero__title">
            Reparación de móviles, ordenadores y consolas en Sant Feliu de Guíxols
          </h1>
          <p class="s-hero__subtitle">
            Diagnóstico profesional, reparación garantizada y presupuesto sin compromiso.
            Tu dispositivo en manos expertas.
          </p>
          <div class="s-hero__ctas">
            <button class="s-hero__cta s-hero__cta--primary" @click="openModal">
              Pedir presupuesto →
            </button>
            <a
              href="https://wa.me/34678758210"
              class="s-hero__cta s-hero__cta--ghost"
              target="_blank"
              rel="noopener noreferrer"
            >
              WhatsApp
            </a>
          </div>
          </div>
        </div>
      </section>

      <!-- ── Barra de confianza ─────────────────────────────── -->
      <CTrustBar :items="trustItems" />

      <!-- ── Catálogo de servicios ──────────────────────────── -->
      <section class="s-catalog">
        <CTitle
          overline="// CATÁLOGO"
          title="Qué reparamos"
          description="Desde la reparación más sencilla hasta la microsoldadura avanzada, cubrimos todo tipo de dispositivos."
          color="primary"
        />
        <div class="s-catalog__inner o-container">
          <div class="s-catalog__grid">
            <div
              v-for="service in services"
              :key="service.slug"
              :id="service.anchor"
              class="s-catalog__item"
              style="scroll-margin-top: 100px"
            >
              <CServiceCard
                :title="service.title"
                :description="service.shortDesc"
                :image="service.image"
                :items="service.tasks"
              />
            </div>
          </div>
          <p class="s-catalog__note">
            Si no se acepta el presupuesto, se cobrará únicamente el diagnóstico (dependiendo de la complejidad).
          </p>
        </div>
      </section>

      <!-- ── Cómo trabajamos ────────────────────────────────── -->
      <section class="s-process">
        <CTitle
          overline="// CÓMO TRABAJAMOS"
          title="Tu reparación paso a paso"
          description="Un proceso claro y transparente para que siempre sepas qué está pasando con tu dispositivo."
          color="primary"
        />
        <CProcessSteps />
      </section>

      <!-- ── Bloque empresas ────────────────────────────────── -->
      <section class="s-business">
        <div class="s-business__inner o-container">
          <div class="s-business__content">
            <CTitle
              overline="// PARA EMPRESAS"
              title="Soporte IT para tu negocio"
              description="Planes de mantenimiento a medida para pymes, freelancers y comercios de la Costa Brava."
              color="primary"
            />
            <ul class="s-business__list">
              <li class="s-business__point">
                <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                SLA garantizado
              </li>
              <li class="s-business__point">
                <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Soporte remoto y presencial
              </li>
              <li class="s-business__point">
                <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Gestión de puestos de trabajo
              </li>
              <li class="s-business__point">
                <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Instalación y configuración de redes
              </li>
              <li class="s-business__point">
                <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Sustitución y actualización de equipos
              </li>
            </ul>
            <button class="s-business__cta" @click="openBusiness">
              Solicitar plan para mi empresa →
            </button>
          </div>
        </div>
      </section>

      <!-- ── FAQ ───────────────────────────────────────────── -->
      <section class="s-faq">
        <CTitle
          overline="// FAQ"
          title="Preguntas frecuentes"
          color="primary"
        />
        <CFaqAccordion :items="faqItems" />
      </section>

      <!-- ── Zona de servicio ───────────────────────────────── -->
      <section class="s-zone">
        <div class="s-zone__inner o-container">
          <div class="s-zone__content">
            <h2 class="s-zone__title">Zona de servicio y horario</h2>
            <div class="s-zone__cols">
              <div class="s-zone__col">
                <h3 class="s-zone__col-title">Atendemos en</h3>
                <ul class="s-zone__list">
                  <li>Sant Feliu de Guíxols</li>
                  <li>S'Agaró</li>
                  <li>Castell-Platja d'Aro</li>
                  <li>Santa Cristina d'Aro</li>
                  <li>Llagostera</li>
                  <li>Palamós</li>
                  <li>Tossa de Mar</li>
                </ul>
              </div>
              <div class="s-zone__col">
                <h3 class="s-zone__col-title">Horario</h3>
                <ul class="s-zone__list">
                  <li><strong>Lunes a viernes:</strong> 10:00 – 13:30 y 17:00 – 20:00</li>
                  <li><strong>Sábados:</strong> 10:00 – 13:00</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── CTA final ──────────────────────────────────────── -->
      <CCtaBanner
        title="¿Listo para recuperar tu dispositivo?"
        subtitle="Pídenos presupuesto ahora, es gratis y sin compromiso."
        cta-label="Contactar ahora →"
        cta-href="#"
        @click.prevent="openModal"
      />

    </div>
  </PublicLayout>

  <!-- ── Modal de presupuesto ───────────────────────────────── -->
  <Teleport to="body">
    <Transition name="s-modal">
      <div v-if="contactOpen" class="s-modal-overlay" @click.self="closeModal">
        <div class="s-modal" role="dialog" aria-modal="true" aria-label="Formulario de presupuesto">

          <button class="s-modal__close" @click="closeModal" aria-label="Cerrar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </button>

          <div v-if="!contactSent" class="s-modal__body">
            <p class="s-modal__overline">// PRESUPUESTO</p>
            <h3 class="s-modal__title">Pide tu presupuesto</h3>
            <p class="s-modal__desc">Cuéntanos qué necesitas y te enviamos un presupuesto sin compromiso en menos de 24 horas.</p>

            <form @submit.prevent="sendContact" novalidate class="s-modal__form">
              <div class="s-modal__row">
                <div class="s-modal__field">
                  <label class="s-modal__label">Nombre</label>
                  <input v-model="contactForm.name" type="text" placeholder="Tu nombre" class="s-modal__input" />
                  <p v-if="contactForm.errors.name" class="s-modal__error">{{ contactForm.errors.name }}</p>
                </div>
                <div class="s-modal__field">
                  <label class="s-modal__label">Email</label>
                  <input v-model="contactForm.email" type="email" placeholder="tu@email.com" class="s-modal__input" />
                  <p v-if="contactForm.errors.email" class="s-modal__error">{{ contactForm.errors.email }}</p>
                </div>
              </div>

              <div class="s-modal__row">
                <div class="s-modal__field">
                  <label class="s-modal__label">Teléfono (opcional)</label>
                  <input v-model="contactForm.phone" type="tel" placeholder="+34 XXX XXX XXX" class="s-modal__input" />
                </div>
                <div class="s-modal__field">
                  <label class="s-modal__label">Servicio</label>
                  <div class="s-modal__select-wrapper">
                    <select
                      v-model="contactForm.service"
                      class="s-modal__input s-modal__input--select"
                    >
                      <option v-for="s in contactServices" :key="s" :value="s">{{ s }}</option>
                    </select>
                    <svg class="s-modal__select-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </div>
              </div>

              <div class="s-modal__field">
                <label class="s-modal__label">Mensaje</label>
                <textarea v-model="contactForm.message" placeholder="Describe el problema o lo que necesitas..." rows="4" class="s-modal__input s-modal__input--textarea"></textarea>
                <p v-if="contactForm.errors.message" class="s-modal__error">{{ contactForm.errors.message }}</p>
              </div>

              <p class="s-modal__privacy">
                Al enviar aceptas el tratamiento de tus datos para gestionar tu consulta.
                <a href="/politica-de-privacidad" class="s-modal__privacy-link" target="_blank">Política de Privacidad</a>.
              </p>

              <button type="submit" class="s-modal__submit" :disabled="contactForm.processing">
                {{ contactForm.processing ? 'Enviando…' : 'Enviar mensaje' }}
              </button>
            </form>
          </div>

          <div v-else class="s-modal__success">
            <svg viewBox="0 0 48 48" fill="none" class="s-modal__success-icon">
              <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
              <path d="M14 24l7 7 13-14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h3 class="s-modal__title">¡Mensaje enviado!</h3>
            <p class="s-modal__desc">Te contactamos en menos de 24h. ¡Hasta pronto!</p>
            <button class="s-modal__submit" @click="closeModal">Cerrar</button>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- ── Modal de empresas ──────────────────────────────────── -->
  <Teleport to="body">
    <Transition name="s-modal">
      <div v-if="businessOpen" class="s-modal-overlay" @click.self="closeBusiness">
        <div class="s-modal" role="dialog" aria-modal="true" aria-label="Formulario soporte para empresas">

          <button class="s-modal__close" @click="closeBusiness" aria-label="Cerrar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </button>

          <div v-if="!businessSent" class="s-modal__body">
            <p class="s-modal__overline">// SOPORTE EMPRESAS</p>
            <h3 class="s-modal__title">Plan para tu empresa</h3>
            <p class="s-modal__desc">Cuéntanos las necesidades de tu empresa y te preparamos una propuesta personalizada.</p>

            <form @submit.prevent="sendBusiness" novalidate class="s-modal__form">
              <div class="s-modal__row">
                <div class="s-modal__field">
                  <label class="s-modal__label">Nombre / Empresa</label>
                  <input v-model="businessForm.name" type="text" placeholder="Tu nombre o empresa" class="s-modal__input" />
                  <p v-if="businessForm.errors.name" class="s-modal__error">{{ businessForm.errors.name }}</p>
                </div>
                <div class="s-modal__field">
                  <label class="s-modal__label">Email</label>
                  <input v-model="businessForm.email" type="email" placeholder="email@empresa.com" class="s-modal__input" />
                  <p v-if="businessForm.errors.email" class="s-modal__error">{{ businessForm.errors.email }}</p>
                </div>
              </div>

              <div class="s-modal__row">
                <div class="s-modal__field">
                  <label class="s-modal__label">Teléfono</label>
                  <input v-model="businessForm.phone" type="tel" placeholder="+34 XXX XXX XXX" class="s-modal__input" />
                </div>
                <div class="s-modal__field">
                  <label class="s-modal__label">Tipo de soporte</label>
                  <div class="s-modal__select-wrapper">
                    <select
                      v-model="businessForm.service"
                      class="s-modal__input s-modal__input--select"
                    >
                      <option v-for="opt in businessServiceOptions" :key="opt" :value="opt">{{ opt }}</option>
                    </select>
                    <svg class="s-modal__select-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </div>
              </div>

              <div class="s-modal__field">
                <label class="s-modal__label">Descripción</label>
                <textarea
                  v-model="businessForm.message"
                  placeholder="Describe tu empresa: número de equipos, infraestructura actual, servicios requeridos..."
                  rows="4"
                  class="s-modal__input s-modal__input--textarea"
                ></textarea>
                <p v-if="businessForm.errors.message" class="s-modal__error">{{ businessForm.errors.message }}</p>
              </div>

              <p class="s-modal__privacy">
                Al enviar aceptas el tratamiento de tus datos para gestionar tu consulta.
                <a href="/politica-de-privacidad" class="s-modal__privacy-link" target="_blank">Política de Privacidad</a>.
              </p>

              <button type="submit" class="s-modal__submit" :disabled="businessForm.processing">
                {{ businessForm.processing ? 'Enviando…' : 'Solicitar plan' }}
              </button>
            </form>
          </div>

          <div v-else class="s-modal__success">
            <svg viewBox="0 0 48 48" fill="none" class="s-modal__success-icon">
              <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
              <path d="M14 24l7 7 13-14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h3 class="s-modal__title">¡Solicitud enviada!</h3>
            <p class="s-modal__desc">Te contactamos en breve con una propuesta personalizada.</p>
            <button class="s-modal__submit" @click="closeBusiness">Cerrar</button>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style lang="scss" scoped>
  // ── Wrapper de página ─────────────────────────────────────
  .s-page {
    position: relative;
    overflow: hidden;
  }

  // ── Iconos de fondo ───────────────────────────────────────
  @keyframes s-float {
    0%   { transform: translate(0px,   0px)  rotate(0deg);    }
    20%  { transform: translate(5px,  -12px) rotate(2deg);    }
    45%  { transform: translate(-4px,  -6px) rotate(-1.5deg); }
    65%  { transform: translate(6px,   8px)  rotate(2.5deg);  }
    85%  { transform: translate(-3px,  3px)  rotate(-1deg);   }
    100% { transform: translate(0px,   0px)  rotate(0deg);    }
  }

  .s-page-bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;

    &__icon {
      position: absolute;
      color: #89c2ff;
      filter: drop-shadow(0 0 10px rgba(89, 160, 255, 0.65));
      animation: s-float 7s ease-in-out infinite;

      @media (prefers-reduced-motion: reduce) {
        animation: none;
      }
    }
  }

  // ── Sections need z-index above bg ───────────────────────
  .s-hero,
  .s-catalog,
  .s-process,
  .s-business,
  .s-faq,
  .s-zone {
    position: relative;
    z-index: 1;
  }

  // ── Hero ──────────────────────────────────────────────────
  .s-hero {
    padding: 72px 0 56px;
    position: relative;
    overflow: hidden;

    @include from-md {
      padding: 96px 0 72px;
    }

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
      gap: 0;
    }

    &__content {
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
      max-width: 760px;
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
      align-items: center;
    }

    &__cta {
      text-decoration: none;
      padding: 12px 28px;
      border-radius: 999px;
      font-size: 0.9rem;
      font-weight: 700;
      transition: filter 0.2s ease, border-color 0.2s ease, color 0.2s ease;
      cursor: pointer;

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

        &:hover {
          border-color: rgba(255, 255, 255, 0.75);
          color: #fff;
        }

      }

      &:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 3px;
      }
    }
  }

  // ── Catálogo ──────────────────────────────────────────────
  .s-catalog {
    padding-bottom: 80px;

    &__grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 24px;

      @include from-sm {
        grid-template-columns: repeat(2, 1fr);
      }

      @include from-md {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    &__note {
      margin-top: 20px;
      font-size: 0.82rem;
      color: var(--color-gray-dark);
      text-align: center;
      padding: 10px 16px;
      border: 1px solid var(--color-border-subtle);
      border-radius: 8px;
      max-width: 560px;
      margin-inline: auto;
    }
  }

  // ── Proceso ───────────────────────────────────────────────
  .s-process {
    // CProcessSteps handles its own bottom padding
  }

  // ── Empresas ──────────────────────────────────────────────
  .s-business {
    padding-bottom: 80px;

    &__content {
      max-width: 680px;
      margin-inline: auto;
      text-align: center;
    }

    &__list {
      list-style: none;
      padding: 0;
      margin: 0 0 36px;
      display: inline-flex;
      flex-direction: column;
      gap: 12px;
      text-align: left;
    }

    &__point {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.95rem;
      color: var(--color-gray);

      svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: var(--color-primary);
      }
    }

    &__cta {
      display: inline-flex;
      align-items: center;
      padding: 12px 28px;
      border-radius: 999px;
      border: none;
      background: var(--color-primary);
      color: #fff;
      font-weight: 700;
      font-size: 0.9rem;
      cursor: pointer;
      transition: filter 0.2s ease;
      margin-top: 8px;

      &:hover { filter: brightness(1.15); }

      &:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 3px;
      }
    }
  }

  // ── FAQ ───────────────────────────────────────────────────
  .s-faq {
    // CFaqAccordion handles padding
  }

  // ── Zona ──────────────────────────────────────────────────
  .s-zone {
    padding: 64px 0 80px;
    border-top: 1px solid var(--color-border-subtle);

    &__title {
      font-size: clamp(1.4rem, 3vw, 1.8rem);
      font-weight: 700;
      color: var(--color-gray);
      margin: 0 0 36px;
      text-align: center;
    }

    &__cols {
      display: grid;
      grid-template-columns: 1fr;
      gap: 40px;
      max-width: 640px;
      margin-inline: auto;

      @include from-sm {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    &__col-title {
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--color-primary);
      margin: 0 0 16px;
    }

    &__list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 10px;

      li {
        font-size: 0.9rem;
        color: var(--color-gray-dark);
        line-height: 1.5;

        strong { color: var(--color-gray); }
      }
    }
  }

  // ── Modal overlay ─────────────────────────────────────────
  .s-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9000;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }

  .s-modal {
    position: relative;
    width: 100%;
    max-width: 540px;
    max-height: 90dvh;
    overflow-y: auto;
    background: var(--color-primary-dark);
    border: 1px solid var(--color-gray-dark);
    border-radius: 20px;
    padding: 40px 36px 36px;

    @media (max-width: 480px) {
      padding: 32px 20px 24px;
    }

    &__close {
      position: absolute;
      top: 16px;
      right: 16px;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: transparent;
      border: 1px solid var(--color-gray-dark);
      border-radius: 50%;
      cursor: pointer;
      color: var(--color-gray-dark);
      transition: color 0.2s, border-color 0.2s;

      svg { width: 14px; height: 14px; }

      &:hover { color: var(--color-white); border-color: var(--color-white); }
    }

    &__overline {
      font-family: 'Courier New', monospace;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      color: var(--color-primary);
      margin-bottom: 8px;
    }

    &__title {
      font-size: 1.6rem;
      font-weight: 800;
      color: var(--color-white);
      margin: 0 0 10px;
    }

    &__desc {
      font-size: 0.9rem;
      color: var(--color-gray-dark);
      line-height: 1.6;
      margin: 0 0 28px;
    }

    &__form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    &__row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;

      @media (max-width: 480px) {
        grid-template-columns: 1fr;
      }
    }

    &__field {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    &__label {
      font-size: 0.72rem;
      font-weight: 600;
      color: var(--color-gray);
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }

    &__input {
      width: 100%;
      background: var(--color-black);
      border: 1px solid var(--color-gray-dark);
      border-radius: 10px;
      padding: 12px 14px;
      color: var(--color-white);
      font-size: 0.95rem;
      transition: border-color 0.2s;

      &::placeholder { color: var(--color-gray-dark); }
      &:focus { outline: none; border-color: var(--color-primary); }

      &--textarea { resize: none; }

      &--select {
        appearance: none;
        cursor: pointer;
      }

      option {
        background: var(--color-black);
        color: var(--color-white);
      }
    }

    &__select-wrapper {
      position: relative;
    }

    &__select-arrow {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      width: 1rem;
      height: 1rem;
      color: var(--color-gray-dark);
      pointer-events: none;
    }

    &__error {
      color: #f87171;
      font-size: 0.75rem;
      margin: 0;
    }

    &__privacy {
      font-size: 0.75rem;
      color: var(--color-gray-dark);
      line-height: 1.5;
      margin: 0;
    }

    &__privacy-link {
      color: var(--color-primary);
      text-decoration: none;
      &:hover { text-decoration: underline; }
    }

    &__submit {
      margin-top: 4px;
      width: 100%;
      padding: 14px 24px;
      border-radius: 10px;
      border: none;
      background: var(--color-primary);
      color: #fff;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: filter 0.2s ease;

      &:hover:not(:disabled) { filter: brightness(1.15); }
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

  // ── Transición modal ──────────────────────────────────────
  .s-modal-enter-active,
  .s-modal-leave-active {
    transition: opacity 0.25s ease;
    .s-modal { transition: transform 0.25s ease, opacity 0.25s ease; }
  }
  .s-modal-enter-from,
  .s-modal-leave-to {
    opacity: 0;
    .s-modal { transform: translateY(16px); opacity: 0; }
  }
</style>
