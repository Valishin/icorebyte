<script setup lang="ts">
  import CFeatureGrid from '@/Components/CFeatureGrid.vue'
  import CImage from '@/Components/CImage.vue'
  import CTabShowcase from '@/Components/CTabShowcase.vue'
  import CTitle from '@/Components/CTitle.vue'
  import PublicLayout from '@/Layouts/PublicLayout.vue'
  import { images } from '@/constants/images'
  import { useForm } from '@inertiajs/vue3'
  import { ref } from 'vue'

  type Sector = 'it' | 'electricista'
  const sector = ref<Sector>('it')

  // ── Iconos de fondo (página completa) ─────────────────────
  interface BgIcon { d: string; x: number; y: number; size: number; opacity: number; delay: number; dur: number }

  const itIcons: BgIcon[] = [
    { d: 'M4 5h16v10H4V5zm-1 10h18v2H3v-2z',                                                    x:  3, y:  3, size: 56, opacity: 0.45, delay: 0.0, dur: 8  },
    { d: 'M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm5 16v1',           x: 88, y:  8, size: 42, opacity: 0.38, delay: 1.4, dur: 11 },
    { d: 'M12 20h.01M2 8.82a15 15 0 0120 0M5 12.86A10 10 0 0119 12.86M8 16.82a5 5 0 018 0',     x: 20, y: 16, size: 38, opacity: 0.35, delay: 3.1, dur:  7 },
    { d: 'M2 4h20v13H2V4zm0 13 3 4h14l3-4M9 20h6',                                              x: 74, y: 22, size: 50, opacity: 0.40, delay: 0.7, dur: 13 },
    { d: 'M9 3h6v3h3v3h3v6h-3v3h-3v3H9v-3H6v-3H3V9h3V6h3V3zm0 6h6v6H9V9z',                    x:  5, y: 32, size: 64, opacity: 0.33, delay: 2.5, dur:  9 },
    { d: 'M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z',                                           x: 90, y: 38, size: 44, opacity: 0.38, delay: 4.2, dur: 12 },
    { d: 'M12 2a7 7 0 00-7 7v5a7 7 0 0014 0V9a7 7 0 00-7-7zm0 0v7',                             x: 38, y: 44, size: 40, opacity: 0.35, delay: 1.0, dur:  6 },
    { d: 'M16 18l6-6-6-6M8 6L2 12l6 6',                                                          x: 62, y: 50, size: 46, opacity: 0.40, delay: 5.3, dur: 10 },
    { d: 'M4 5h16v10H4V5zm-1 10h18v2H3v-2z',                                                    x:  7, y: 58, size: 48, opacity: 0.35, delay: 0.3, dur: 14 },
    { d: 'M12 20h.01M2 8.82a15 15 0 0120 0M5 12.86A10 10 0 0119 12.86M8 16.82a5 5 0 018 0',     x: 82, y: 64, size: 54, opacity: 0.42, delay: 3.7, dur:  8 },
    { d: 'M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm5 16v1',           x: 25, y: 72, size: 36, opacity: 0.33, delay: 1.9, dur: 11 },
    { d: 'M9 3h6v3h3v3h3v6h-3v3h-3v3H9v-3H6v-3H3V9h3V6h3V3zm0 6h6v6H9V9z',                    x: 70, y: 78, size: 52, opacity: 0.38, delay: 6.0, dur:  7 },
    { d: 'M2 4h20v13H2V4zm0 13 3 4h14l3-4M9 20h6',                                              x: 44, y: 83, size: 44, opacity: 0.35, delay: 2.2, dur: 15 },
    { d: 'M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z',                                           x: 55, y: 89, size: 38, opacity: 0.40, delay: 4.8, dur:  9 },
    { d: 'M16 18l6-6-6-6M8 6L2 12l6 6',                                                          x: 14, y: 93, size: 42, opacity: 0.33, delay: 0.6, dur: 13 },
    { d: 'M12 2a7 7 0 00-7 7v5a7 7 0 0014 0V9a7 7 0 00-7-7zm0 0v7',                             x: 86, y: 95, size: 46, opacity: 0.38, delay: 2.9, dur:  6 },
  ]

  const electricIcons: BgIcon[] = [
    { d: 'M13 2L3 14h9l-1 8 10-12h-9z',                                                          x:  3, y:  3, size: 56, opacity: 0.45, delay: 0.0, dur:  7 },
    { d: 'M9 21h6M12 3a7 7 0 017 7c0 2.38-1.19 4.47-3 5.74V17H8v-1.26A7 7 0 015 10a7 7 0 017-7z', x: 88, y:  8, size: 44, opacity: 0.38, delay: 2.3, dur: 11 },
    { d: 'M6 7h3M15 7h3M12 2v5m0 10v5M6 12h12a3 3 0 01-3 3H9a3 3 0 01-3-3z',                   x: 20, y: 16, size: 40, opacity: 0.35, delay: 4.1, dur:  9 },
    { d: 'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91A6 6 0 0114.7 6.3z', x: 74, y: 22, size: 50, opacity: 0.40, delay: 1.0, dur: 14 },
    { d: 'M13 2L3 14h9l-1 8 10-12h-9z',                                                          x:  5, y: 32, size: 68, opacity: 0.35, delay: 5.5, dur:  8 },
    { d: 'M22 12h-4l-3 9L9 3l-3 9H2',                                                            x: 90, y: 38, size: 46, opacity: 0.40, delay: 0.8, dur: 12 },
    { d: 'M2 9a2 2 0 012-2h14a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9zm20 1v4M7 12v.01M11 12v.01', x: 38, y: 44, size: 52, opacity: 0.38, delay: 3.3, dur:  6 },
    { d: 'M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48 2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48 2.83-2.83', x: 62, y: 50, size: 44, opacity: 0.35, delay: 1.7, dur: 10 },
    { d: 'M9 21h6M12 3a7 7 0 017 7c0 2.38-1.19 4.47-3 5.74V17H8v-1.26A7 7 0 015 10a7 7 0 017-7z', x:  7, y: 58, size: 48, opacity: 0.42, delay: 6.2, dur: 13 },
    { d: 'M13 2L3 14h9l-1 8 10-12h-9z',                                                          x: 82, y: 64, size: 42, opacity: 0.38, delay: 2.0, dur:  7 },
    { d: 'M6 7h3M15 7h3M12 2v5m0 10v5M6 12h12a3 3 0 01-3 3H9a3 3 0 01-3-3z',                   x: 25, y: 72, size: 38, opacity: 0.33, delay: 4.6, dur: 15 },
    { d: 'M22 12h-4l-3 9L9 3l-3 9H2',                                                            x: 70, y: 78, size: 54, opacity: 0.40, delay: 0.4, dur:  9 },
    { d: 'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91A6 6 0 0114.7 6.3z', x: 44, y: 83, size: 46, opacity: 0.38, delay: 3.8, dur: 11 },
    { d: 'M2 9a2 2 0 012-2h14a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9zm20 1v4M7 12v.01M11 12v.01', x: 55, y: 89, size: 40, opacity: 0.35, delay: 1.2, dur:  8 },
    { d: 'M13 2L3 14h9l-1 8 10-12h-9z',                                                          x: 14, y: 93, size: 36, opacity: 0.40, delay: 5.0, dur: 12 },
    { d: 'M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48 2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48 2.83-2.83', x: 86, y: 95, size: 48, opacity: 0.38, delay: 2.6, dur:  6 },
  ]

  // ── Modal de contacto ──────────────────────────────────────
  const contactOpen = ref(false)
  const contactSent = ref(false)

  const contactForm = useForm({
    name: '',
    email: '',
    phone: '',
    service: 'iCB SAT Informática',
    message: ''
  })

  const sendContact = () => {
    contactForm.post(route('contact.send'), {
      preserveScroll: true,
      onSuccess: () => {
        contactSent.value = true
        contactForm.reset()
      }
    })
  }

  const openModal  = () => { contactOpen.value = true;  document.body.style.overflow = 'hidden' }
  const closeModal = () => { contactOpen.value = false; document.body.style.overflow = '';      contactSent.value = false }

  const sectors = {
    it: {
      label: 'iCB SAT Informática',
      image: images.icbsatIT,
      imageAlt: 'iCB SAT para talleres de informática',
      title: 'Gestiona tu <span>servicio técnico</span> de informática',
      description:
        'Gestiona tu servicio técnico de informática de forma sencilla y organizada. Controla reparaciones, dispositivos, clientes, diagnósticos y estados de cada intervención desde una única plataforma. Mantén todo bajo control y ofrece a tus clientes un seguimiento profesional de sus reparaciones.',
      features: ['SATs y reparaciones', 'Dispositivos y diagnósticos', 'Firma digital del cliente'],
    },
    electricista: {
      label: 'iCB SAT Electricista',
      image: images.icbsatElectricista,
      imageAlt: 'iCB SAT para electricistas',
      title: 'Organiza tus <span>trabajos eléctricos</span> sin esfuerzo',
      description:
        'Organiza y controla todos tus trabajos eléctricos desde una única plataforma. Gestiona clientes, avisos de avería, instalaciones, órdenes de trabajo, presupuestos y seguimiento de servicios. Ten toda la información de tus intervenciones centralizada y dedica menos tiempo a la gestión administrativa.',
      features: ['Órdenes e intervenciones', 'Avisos y presupuestos', 'Calendario de visitas'],
    },
  }

  const current = () => sectors[sector.value]
</script>

<template>
  <PublicLayout>
  <div class="icb-page">

    <!-- ── Iconos de fondo (página completa) ─────────────────── -->
    <div class="icb-page-bg" :class="{ 'icb-page-bg--elec': sector === 'electricista' }" aria-hidden="true">
      <Transition name="icb-bg" mode="out-in">
        <div v-if="sector === 'it'" key="bg-it" class="icb-page-bg__layer">
          <svg
            v-for="(icon, i) in itIcons" :key="i"
            class="icb-page-bg__icon"
            :style="{ left: icon.x + '%', top: icon.y + '%', width: icon.size + 'px', height: icon.size + 'px', opacity: icon.opacity, animationDelay: icon.delay + 's', animationDuration: icon.dur + 's' }"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"
          ><path :d="icon.d" /></svg>
        </div>
        <div v-else key="bg-elec" class="icb-page-bg__layer">
          <svg
            v-for="(icon, i) in electricIcons" :key="i"
            class="icb-page-bg__icon"
            :style="{ left: icon.x + '%', top: icon.y + '%', width: icon.size + 'px', height: icon.size + 'px', opacity: icon.opacity, animationDelay: icon.delay + 's', animationDuration: icon.dur + 's' }"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"
          ><path :d="icon.d" /></svg>
        </div>
      </Transition>
    </div>

    <!-- ── Hero con selector de sector ─────────────────────── -->
    <section class="icb-showcase">
      <div class="icb-showcase__inner o-container">

        <!-- Columna texto -->
        <div class="icb-showcase__content">
          <p class="icb-showcase__overline">// SOFTWARE DE GESTIÓN</p>

          <!-- Selector de sector -->
          <div class="icb-showcase__tabs" role="group" aria-label="Seleccionar sector">
            <button
              v-for="(s, key) in sectors"
              :key="key"
              class="icb-showcase__tab"
              :class="{ 'is-active': sector === key }"
              @click="sector = key"
            >
              {{ s.label }}
            </button>
          </div>

          <Transition name="icb-fade" mode="out-in">
            <div :key="sector" class="icb-showcase__text">
              <h2
                class="icb-showcase__title"
                v-html="current().title"
              />
              <p class="icb-showcase__description">{{ current().description }}</p>

              <ul class="icb-showcase__features">
                <li
                  v-for="f in current().features"
                  :key="f"
                  class="icb-showcase__feature"
                >
                  {{ f }}
                </li>
              </ul>
            </div>
          </Transition>

          <div class="icb-showcase__ctas">
            <button class="icb-showcase__cta icb-showcase__cta--primary" @click="openModal">
              Contáctanos →
            </button>
          </div>
        </div>

        <!-- Columna imagen -->
        <div class="icb-showcase__media">
          <Transition name="icb-fade" mode="out-in">
            <CImage
              :key="sector"
              :src="current().image"
              :alt="current().imageAlt"
              class="icb-showcase__image"
              :lazy="false"
            />
          </Transition>
        </div>

      </div>
    </section>


    <CTabShowcase
      overline="// Interfaz de la App"
      title="Diseñada para ser usada"
      description="Interfaz limpia e intuitiva. Tu equipo aprende a usarla en minutos, sea cual sea el sector."
      title-color="primary"
      :tabs="[
        {
          label: 'Dashboard',
          title: 'Vista general en tiempo real',
          description:
            'Panel principal con KPIs del negocio: órdenes abiertas, en curso, finalizadas hoy y pendientes de entrega. Gráficas semanales de ingresos y actividad reciente de un vistazo.',
          image: images.icbsatDashboard,
          url: 'app.icbsat.io/dashboard'
        },
        {
          label: 'Nueva Orden',
          title: 'Abre una orden en segundos',
          description:
            'Formulario guiado para registrar el trabajo, el problema, los materiales y obtener la firma digital del cliente antes de empezar. Válido para reparaciones, instalaciones o cualquier intervención técnica.',
          image: images.icbsatIT,
          url: 'app.icbsat.io/orden/nueva'
        },
        {
          label: 'Clientes',
          title: 'Ficha completa de cada cliente',
          description:
            'Historial de órdenes, equipos o instalaciones atendidas, datos de contacto y notas internas. Busca cualquier cliente en milisegundos por nombre, email o teléfono.',
          image: images.icbsatClientes,
          url: 'app.icbsat.io/clientes'
        },
        {
          label: 'Analítica',
          title: 'Métricas que importan',
          description:
            'Tiempo medio de resolución, ingresos por período, órdenes por operario y tasa de éxito. Todo en gráficas claras para tomar decisiones informadas.',
          image: images.icbsatAnaliticas,
          url: 'app.icbsat.io/analitica'
        },
        {
          label: 'Usuarios y Permisos',
          title: 'Control total de accesos',
          description:
            'Define qué puede ver y hacer cada usuario. Roles de administrador, técnico y recepción con accesos granulares a cada sección de la plataforma.',
          image: images.icbsatUsuarios,
          url: 'app.icbsat.io/usuarios'
        }
      ]"
    />

    <CFeatureGrid
      overline="// Características"
      title="Todo lo que necesita tu empresa de servicios"
      description="Pensado para cualquier empresa técnica que gestione órdenes de trabajo, clientes y operarios. Sin funciones innecesarias, con todo lo esencial."
      title-color="primary"
      :features="[
        {
          icon: 'IconMaintenance',
          title: 'Gestión de Órdenes',
          description:
            'Crea, asigna y sigue el estado de cada orden de trabajo en tiempo real. Historial completo por cliente y tipo de intervención.'
        },
        {
          icon: 'IconSupport',
          iconColor: 'primary',
          title: 'Base de Clientes',
          description:
            'Ficha completa de cada cliente con historial de trabajos, equipos o instalaciones atendidas y datos de contacto centralizados.'
        },
        {
          icon: 'IconSoftware',
          iconColor: 'secondary',
          title: 'Permisos por Rol',
          description:
            'Define qué puede ver y hacer cada usuario. Roles de administrador, técnico y recepción con accesos granulares.'
        },
        {
          icon: 'IconSupport',
          iconColor: 'secondary',
          title: 'Gestión de Operarios',
          description:
            'Asigna órdenes a técnicos o instaladores, controla la carga de trabajo y mide el rendimiento individual de cada miembro del equipo.'
        },
        {
          icon: 'IconEmail',
          iconColor: 'primary',
          title: 'Formulario de Entrada',
          description:
            'Recoge el problema o trabajo a realizar, materiales, fotos y la firma digital del cliente al abrir cualquier orden.'
        },
        {
          icon: 'IconDesktop',
          iconColor: 'primary',
          title: 'Analítica y Reportes',
          description:
            'Dashboard con métricas clave: órdenes por estado, tiempos medios de resolución, ingresos y rendimiento del equipo.'
        }
      ]"
    />

    <!-- ── Planes y Precios ───────────────────────────────── -->
    <section class="icb-pricing">
      <CTitle
        overline="// Planes y Precios"
        title="Sin sorpresas. Sin complicaciones."
        description="Contacta con nosotros y te asesoramos sin compromiso."
        color="primary"
      />
      <div class="icb-pricing__inner o-container">

        <div class="icb-pricing__card">
          <div class="icb-pricing__card-header">
            <span class="icb-pricing__plan-name">Plan iCB SAT</span>
            <p class="icb-pricing__plan-desc">Todo lo que necesitas para gestionar tu servicio técnico.</p>
          </div>

          <div class="icb-pricing__price-wrap">
            <span class="icb-pricing__from">desde</span>
            <span class="icb-pricing__price">24,99€</span>
            <span class="icb-pricing__suffix">/mes</span>
          </div>

          <ul class="icb-pricing__features">
            <li class="icb-pricing__feature">
              <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Gestión de órdenes y clientes
            </li>
            <li class="icb-pricing__feature">
              <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Operarios y permisos por rol
            </li>
            <li class="icb-pricing__feature">
              <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Firma digital del cliente
            </li>
            <li class="icb-pricing__feature">
              <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Analítica y reportes
            </li>
            <li class="icb-pricing__feature">
              <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Soporte incluido
            </li>
          </ul>

          <button class="icb-pricing__cta" @click="openModal">
            Contáctanos →
          </button>
        </div>
      </div>
    </section>

    <!-- ── Modal de contacto ───────────────────────────────── -->
    <Teleport to="body">
      <Transition name="icb-modal">
        <div v-if="contactOpen" class="icb-modal-overlay" @click.self="closeModal">
          <div class="icb-modal" role="dialog" aria-modal="true" aria-label="Formulario de contacto iCB SAT">

            <button class="icb-modal__close" @click="closeModal" aria-label="Cerrar">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>

            <div v-if="!contactSent" class="icb-modal__body">
              <p class="icb-modal__overline">// iCB SAT</p>
              <h3 class="icb-modal__title">¿Hablamos?</h3>
              <p class="icb-modal__desc">Cuéntanos tu sector y número de técnicos y te preparamos una propuesta.</p>

              <form @submit.prevent="sendContact" novalidate class="icb-modal__form">
                <!-- Selector de sector -->
                <div class="icb-modal__sector-group" role="group" aria-label="Sector">
                  <button
                    type="button"
                    class="icb-modal__sector-btn"
                    :class="{ 'is-active': contactForm.service === 'iCB SAT Informática' }"
                    @click="contactForm.service = 'iCB SAT Informática'"
                  >
                    iCB SAT Informática
                  </button>
                  <button
                    type="button"
                    class="icb-modal__sector-btn"
                    :class="{ 'is-active': contactForm.service === 'iCB SAT Electricista' }"
                    @click="contactForm.service = 'iCB SAT Electricista'"
                  >
                    iCB SAT Electricista
                  </button>
                </div>

                <div class="icb-modal__row">
                  <div class="icb-modal__field">
                    <label class="icb-modal__label">Nombre</label>
                    <input v-model="contactForm.name" type="text" placeholder="Tu nombre" class="icb-modal__input" />
                    <p v-if="contactForm.errors.name" class="icb-modal__error">{{ contactForm.errors.name }}</p>
                  </div>
                  <div class="icb-modal__field">
                    <label class="icb-modal__label">Email</label>
                    <input v-model="contactForm.email" type="email" placeholder="tu@email.com" class="icb-modal__input" />
                    <p v-if="contactForm.errors.email" class="icb-modal__error">{{ contactForm.errors.email }}</p>
                  </div>
                </div>

                <div class="icb-modal__field">
                  <label class="icb-modal__label">Teléfono (opcional)</label>
                  <input v-model="contactForm.phone" type="tel" placeholder="+34 XXX XXX XXX" class="icb-modal__input" />
                </div>

                <div class="icb-modal__field">
                  <label class="icb-modal__label">Mensaje</label>
                  <textarea v-model="contactForm.message" placeholder="Cuéntanos tu caso: sector, nº de técnicos, necesidades..." rows="4" class="icb-modal__input icb-modal__input--textarea"></textarea>
                  <p v-if="contactForm.errors.message" class="icb-modal__error">{{ contactForm.errors.message }}</p>
                </div>

                <p class="icb-modal__privacy">
                  Al enviar aceptas el tratamiento de tus datos para gestionar tu consulta.
                  <a href="/politica-de-privacidad" class="icb-modal__privacy-link" target="_blank">Política de Privacidad</a>.
                </p>

                <button
                  type="submit"
                  class="icb-modal__submit"
                  :disabled="contactForm.processing"
                >
                  {{ contactForm.processing ? 'Enviando…' : 'Enviar mensaje' }}
                </button>
              </form>
            </div>

            <div v-else class="icb-modal__success">
              <svg viewBox="0 0 48 48" fill="none" class="icb-modal__success-icon">
                <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
                <path d="M14 24l7 7 13-14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <h3 class="icb-modal__title">¡Mensaje enviado!</h3>
              <p class="icb-modal__desc">Te contactamos en menos de 24h. ¡Hasta pronto!</p>
              <button class="icb-modal__submit" @click="closeModal">Cerrar</button>
            </div>

          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
  </PublicLayout>
</template>

<style lang="scss" scoped>
  // ── Hero showcase ──────────────────────────────────────────
  .icb-showcase {
    padding: 80px 0;

    &__inner {
      display: grid;
      grid-template-columns: 1fr;
      gap: 48px;
      align-items: center;

      @include from-md {
        grid-template-columns: 1fr 1.35fr;
        gap: 64px;
      }
    }

    &__overline {
      font-family: 'Courier New', monospace;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--color-primary);
      margin-bottom: 20px;
    }

    // ── Selector de sector ──────────────────────────────────
    &__tabs {
      display: inline-flex;
      gap: 8px;
      background: var(--color-black);
      border: 1px solid var(--color-gray-dark);
      border-radius: 999px;
      padding: 4px;
      margin-bottom: 32px;
      flex-wrap: wrap;
    }

    &__tab {
      padding: 8px 20px;
      border-radius: 999px;
      border: none;
      font-size: 0.82rem;
      font-weight: 600;
      cursor: pointer;
      background: transparent;
      color: var(--color-gray-dark);
      transition:
        background 0.25s ease,
        color 0.25s ease;

      &.is-active {
        background: var(--color-primary);
        color: var(--color-white);

        .theme-light & {
          color: var(--color-black);
        }
      }

      &:not(.is-active):hover {
        color: var(--color-white);
      }
    }

    // ── Contenido reactivo ──────────────────────────────────
    &__text {
      // Bloque para Transition
    }

    &__title {
      font-size: clamp(2rem, 5vw, 3.2rem);
      font-weight: 800;
      line-height: 1.3;
      color: var(--color-white);
      margin: 0 0 24px;

      :deep(span) {
        color: var(--color-secondary);
      }
    }

    &__description {
      font-size: 1rem;
      line-height: 1.7;
      color: var(--color-gray-dark);
      margin-bottom: 28px;
      max-width: 480px;
    }

    &__features {
      list-style: none;
      padding: 0;
      margin: 0 0 36px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px 20px;
    }

    &__feature {
      font-size: 0.82rem;
      color: var(--color-gray-dark);
      display: flex;
      align-items: center;
      gap: 6px;

      &::before {
        content: '✓';
        color: var(--color-primary);
        font-weight: 700;
      }
    }

    &__ctas {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    &__cta {
      text-decoration: none;
      padding: 10px 24px;
      border-radius: 999px;
      font-size: 0.9rem;
      font-weight: 600;
      transition:
        background 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease;

      &--primary {
        background: var(--color-primary);
        color: var(--color-white);

        &:hover { filter: brightness(1.15); }

        .theme-light & { color: var(--color-black); }
      }

      &--ghost {
        background: transparent;
        border: 1px solid var(--color-gray-dark);
        color: var(--color-gray-dark);

        &:hover {
          border-color: var(--color-white);
          color: var(--color-white);
        }
      }
    }

    // ── Imagen ──────────────────────────────────────────────
    &__media {
      border-radius: 16px;
      overflow: hidden;
      box-shadow:
        0 24px 80px rgba(0, 0, 0, 0.4),
        0 0 0 1px rgba(255, 255, 255, 0.06);
    }

    &__image {
      width: 100%;
      height: auto;
      display: block;
    }
  }

  // ── Transición de contenido ───────────────────────────────
  .icb-fade-enter-active,
  .icb-fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
  }
  .icb-fade-enter-from {
    opacity: 0;
    transform: translateY(8px);
  }
  .icb-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
  }

  // ── Precio único ──────────────────────────────────────────
  .icb-pricing {
    padding: 0 0 80px;

    &__inner {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      gap: 0;
    }

    &__card {
      width: 100%;
      max-width: 440px;
      background: var(--color-black);
      border: 1px solid var(--color-primary-dark);
      border-radius: 20px;
      padding: 40px 36px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0;
      box-shadow: 0 0 0 1px rgba(var(--color-primary-rgb, 0,107,223), 0.15), 0 24px 64px rgba(0,0,0,0.3);
    }

    &__card-header {
      margin-bottom: 28px;
      text-align: center;
    }

    &__plan-name {
      display: block;
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--color-white);
      margin-bottom: 8px;
    }

    &__plan-desc {
      font-size: 0.85rem;
      color: var(--color-gray-dark);
      margin: 0;
    }

    &__price-wrap {
      display: flex;
      align-items: baseline;
      gap: 6px;
      margin-bottom: 32px;
      padding-bottom: 32px;
      border-bottom: 1px solid rgba(255,255,255,0.06);
      width: 100%;
      justify-content: center;
    }

    &__from {
      font-size: 0.85rem;
      color: var(--color-gray-dark);
      align-self: center;
    }

    &__price {
      font-size: clamp(2.4rem, 6vw, 3.2rem);
      font-weight: 800;
      color: var(--color-white);
      line-height: 1;
    }

    &__suffix {
      font-size: 0.85rem;
      color: var(--color-gray-dark);
    }

    &__features {
      list-style: none;
      padding: 0;
      margin: 0 0 36px;
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 14px;
      text-align: left;
    }

    &__feature {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.9rem;
      color: var(--color-white);

      svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: var(--color-primary);
      }
    }

    &__cta {
      width: 100%;
      padding: 14px 32px;
      border-radius: 999px;
      border: none;
      background: var(--color-primary);
      color: var(--color-white);
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: filter 0.2s ease, transform 0.15s ease;

      .theme-light & { color: var(--color-black); }

      &:hover {
        filter: brightness(1.15);
        transform: translateY(-1px);
      }
    }
  }

  // ── Modal overlay ──────────────────────────────────────────
  .icb-modal-overlay {
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

  .icb-modal {
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

    &__sector-group {
      display: inline-flex;
      gap: 4px;
      width: 100%;
      background: var(--color-black);
      border: 1px solid var(--color-gray-dark);
      border-radius: 999px;
      padding: 4px;
    }

    &__sector-btn {
      flex: 1;
      padding: 8px 16px;
      border-radius: 999px;
      border: none;
      background: transparent;
      color: var(--color-gray-dark);
      font-size: 0.82rem;
      font-weight: 600;
      cursor: pointer;
      text-align: center;
      white-space: nowrap;
      transition: background 0.25s ease, color 0.25s ease;

      &.is-active {
        background: var(--color-primary);
        color: #fff;
      }

      &:not(.is-active):hover {
        color: var(--color-primary);
      }
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

      &--textarea {
        resize: none;
      }
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
      color: var(--color-white);
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: filter 0.2s ease;

      .theme-light & { color: var(--color-black); }
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

  // ── Transición modal ───────────────────────────────────────
  .icb-modal-enter-active,
  .icb-modal-leave-active {
    transition: opacity 0.25s ease;
    .icb-modal { transition: transform 0.25s ease, opacity 0.25s ease; }
  }
  .icb-modal-enter-from,
  .icb-modal-leave-to {
    opacity: 0;
    .icb-modal { transform: translateY(16px); opacity: 0; }
  }

  // ── Fondo de página con iconos ────────────────────────────
  @keyframes icb-float {
    0%   { transform: translate(0px,   0px)  rotate(0deg);   }
    20%  { transform: translate(5px,  -12px) rotate(2deg);   }
    45%  { transform: translate(-4px,  -6px) rotate(-1.5deg);}
    65%  { transform: translate(6px,   8px)  rotate(2.5deg); }
    85%  { transform: translate(-3px,  3px)  rotate(-1deg);  }
    100% { transform: translate(0px,   0px)  rotate(0deg);   }
  }

  .icb-page {
    position: relative;
    overflow: hidden;
  }

  .icb-page-bg {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    pointer-events: none;
    z-index: 0;

    &__layer {
      position: absolute;
      inset: 0;
    }

    &__icon {
      position: absolute;
      color: #89c2ff;
      filter: drop-shadow(0 0 10px rgba(89, 160, 255, 0.65));
      animation: icb-float 7s ease-in-out infinite;

      @media (prefers-reduced-motion: reduce) {
        animation: none;
      }
    }

    &--elec &__icon {
      color: #fcd34d;
      filter: drop-shadow(0 0 10px rgba(252, 211, 77, 0.6));
    }
  }

  // ── Transición entre sets de iconos ─────────────────────
  .icb-bg-enter-active,
  .icb-bg-leave-active { transition: opacity 0.5s ease; }
  .icb-bg-enter-from,
  .icb-bg-leave-to     { opacity: 0; }

  // Sections need z-index above the bg layer
  .icb-showcase,
  .icb-pricing {
    position: relative;
    z-index: 1;
  }
</style>
