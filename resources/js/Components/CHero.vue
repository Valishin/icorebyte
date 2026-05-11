<script setup lang="ts">
  import { useDevice } from '@/composables/useDevice'
  import { useHeroLogoState } from '@/composables/useHeroLogoState'
  import { useInView } from '@/composables/useInView'
  import { useLoaderState } from '@/composables/useLoaderState'
  import { images } from '@/constants/images'
  import Logo from '@assets/logos/logo.svg'
  import { onMounted, onUnmounted, ref, watch } from 'vue'
  import { useScroll } from '../composables/useScroll'
  import CCard from './CCard.vue'

  const { isMobile } = useDevice()
  const { scrollToNextSection } = useScroll()
  const { el: cardsRef, isVisible: cardsVisible } = useInView(0.1)
  const { loaderReady } = useLoaderState()
  const { heroManagesLogo } = useHeroLogoState()

  // ── Fixed logo morph on scroll ──────────────────────────
  const heroRef       = ref<HTMLElement>()
  const placeholderRef = ref<HTMLElement>()

  // Style applied to the position:fixed logo element
  const fixedLogoStyle = ref<Record<string, string>>({ opacity: '0', pointerEvents: 'none' })

  let initRect    = { left: 0, top: 0, width: 0, height: 0 }
  let headRect    = { left: 0, top: 0, width: 0 }
  let triggerDist = 350
  let measured    = false
  let entranceDone = false

  const easeOutQuart = (t: number) => 1 - Math.pow(1 - t, 4)
  const lerp  = (a: number, b: number, t: number) => a + (b - a) * t
  const clamp = (v: number, lo: number, hi: number) => Math.min(hi, Math.max(lo, v))

  /** Apply transform + opacity to the fixed logo, no transition by default (smooth scroll). */
  const applyStyle = (eased: number, opacity = 1, transition = '') => {
    const x  = lerp(initRect.left, headRect.left, eased)
    // initRect.top is an absolute page position; at scroll=0 it equals viewport Y.
    // headRect.top is always a viewport Y (header is position:fixed).
    const y  = lerp(initRect.top,  headRect.top,  eased)
    const sc = lerp(1, headRect.width / initRect.width, eased)

    const style: Record<string, string> = {
      position        : 'fixed',
      top             : '0',
      left            : '0',
      width           : `${initRect.width}px`,
      transformOrigin : 'top left',
      willChange      : 'transform',
      pointerEvents   : 'auto',
      zIndex          : '101',
      transform       : `translate(${x}px, ${y}px) scale(${sc})`,
      opacity         : String(opacity),
      cursor          : 'pointer',
    }
    if (transition) style.transition = transition

    fixedLogoStyle.value = style
  }

  const measurePositions = () => {
    const placeholder = placeholderRef.value
    // Read header logo even while visibility:hidden — getBoundingClientRect still works
    const headerEl = (
      document.querySelector('.c-header__logo--mobile') ??
      document.querySelector('.c-header__logo')
    ) as HTMLElement | null

    if (!placeholder || !headerEl) return

    const pRect = placeholder.getBoundingClientRect()
    const hRect = headerEl.getBoundingClientRect()
    const scrollY = window.scrollY

    initRect = {
      left   : pRect.left,
      top    : pRect.top + scrollY,   // convert to absolute page Y (animation starts at scrollY=0)
      width  : pRect.width,
      height : pRect.height,
    }
    headRect = {
      left  : hRect.left,
      top   : hRect.top,              // always viewport-relative (header is fixed)
      width : hRect.width,
    }

    triggerDist = heroRef.value
      ? Math.min(500, heroRef.value.offsetHeight * 0.5)
      : 350

    measured = true

    // Place logo at correct position for the current scroll (invisible)
    const rawP  = clamp(scrollY / triggerDist, 0, 1)
    const eased = easeOutQuart(rawP)
    applyStyle(eased, 0)

    // Fade in after two rAFs so the initial paint with opacity:0 lands first
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        applyStyle(eased, 1, 'opacity 0.8s ease-out')
        setTimeout(() => { entranceDone = true }, 900)
      })
    })
  }

  let rafId = 0

  const onScrollMorph = () => {
    cancelAnimationFrame(rafId)
    rafId = requestAnimationFrame(() => {
      if (!measured) return
      const scrollY = window.scrollY
      const rawP    = clamp(scrollY / triggerDist, 0, 1)
      const eased   = easeOutQuart(rawP)
      // During entrance the opacity is still climbing; after entrance always 1
      const opacity = entranceDone ? 1 : parseFloat(fixedLogoStyle.value.opacity ?? '1')
      applyStyle(eased, opacity)
    })
  }

  const handleLogoClick = () => {
    const el = document.querySelector('#inicio')
    if (el) el.scrollIntoView({ behavior: 'smooth' })
  }

  onMounted(() => {
    // Hide the header logo immediately — we own it now.
    // visibility:hidden keeps layout so we can still measure its rect.
    heroManagesLogo.value = true

    // Measure once the loader overlay is gone
    if (loaderReady.value) {
      setTimeout(measurePositions, 50)
    } else {
      const stop = watch(loaderReady, (ready) => {
        if (ready) { setTimeout(measurePositions, 50); stop() }
      })
    }

    window.addEventListener('scroll', onScrollMorph, { passive: true })
    window.addEventListener('resize', measurePositions)
  })

  onUnmounted(() => {
    heroManagesLogo.value = false   // restore header logo on other pages
    window.removeEventListener('scroll', onScrollMorph)
    window.removeEventListener('resize', measurePositions)
    cancelAnimationFrame(rafId)
  })
</script>

<template>
  <div ref="heroRef" class="c-hero">
    <div class="c-hero__inner" :class="{ 'is-ready': loaderReady }">
      <div class="c-hero__container o-container">
        <div class="c-hero__col o-col-8@md o-col-push-2@md o-col-6@sm o-col-push-1@sm o-col-4@xs">
          <div class="c-hero__overline">
            <p class="c-hero__overline-text o-font-display-caption">
              Servicios IT profesionales en tu zona
            </p>
          </div>
          <div class="c-hero__title">
            <h1>
              <span class="sr-only">iCoreByte</span>
              <!-- Invisible placeholder — keeps layout space for the SVG -->
              <div ref="placeholderRef" class="c-hero__logo-placeholder" aria-hidden="true">
                <component class="c-hero__svg" :is="Logo" />
              </div>
            </h1>
          </div>
          <div class="c-hero__subtitle">
            <p class="c-hero__subtitle-text o-font-display-3">
              Tu centro integral de soluciones informáticas. Reparación, venta y desarrollo
              tecnológico al más alto nivel.
            </p>
          </div>
        </div>
        <div
          ref="cardsRef"
          class="c-hero__wrapper-cards"
          :class="{ 'is-visible': cardsVisible }"
        >
          <CCard
            :image="images.imagenRepara"
            title="Reparación de equipos"
            content="Reparamos todo tipo de dispositivos electrónicos con eficiencia."
          />
          <CCard
            :image="images.imagenTienda"
            title="Nuestra tienda"
            content="Ofrecemos una amplia gama de productos de alta calidad."
          />
          <CCard
            :image="images.imagenDesarrollo"
            title="Desarrollo"
            content="Desarrollamos soluciones tecnológicas innovadoras para tu negocio."
          />
        </div>
        <button
          v-if="!isMobile"
          class="c-hero__explore"
          type="button"
          @click="scrollToNextSection('.c-hero')"
          aria-label="Explorar y hacer scroll"
        >
          <span class="c-hero__explore-text">Explorar</span>
          <span class="c-hero__explore-arrow" aria-hidden="true">↓</span>
        </button>
      </div>
    </div>

    <!-- Single fixed logo that travels from hero → header on scroll -->
    <a
      class="c-hero__logo-fixed"
      :style="fixedLogoStyle"
      href="#inicio"
      aria-label="iCoreByte — ir al inicio"
      @click.prevent="handleLogoClick"
    >
      <component class="c-hero__svg" :is="Logo" />
    </a>
  </div>
</template>

<style lang="scss" scoped>
  .c-hero {
    &__inner {
      height: 100%;
      text-align: center;

      @include from-sm { padding-top: 100px; }
      @include from-md { padding-top: 0px; }
    }

    &__overline {
      padding-bottom: 40px;
    }

    &__overline-text {
      display: inline-block;
      color: var(--color-white);
      margin-bottom: 1rem;
      border: 1px solid var(--color-gray-dark);
      padding: 5px 10px;
      border-radius: 9999px;
      opacity: 0;

      .is-ready & {
        animation:
          c-hero-overline-enter 0.5s ease-out both,
          c-hero-overline-float 3.2s ease-in-out 0.5s infinite;
      }

      @media (prefers-reduced-motion: reduce) {
        opacity: 1 !important;
        animation: none !important;
      }
    }

    &__title {
      padding-bottom: 20px;

      h1 {
        margin: 0;
      }
    }

    // Invisible spacer — keeps layout space for the fixed logo
    &__logo-placeholder {
      visibility: hidden;
    }

    &__svg {
      position: relative;
      left: 50%;
      transform: translateX(calc(-50% - 10px));
      display: block;

      @media (prefers-reduced-motion: reduce) {
        transform: translateX(-50%);
      }
    }

    // The actual visible logo (position:fixed, styled via JS)
    &__logo-fixed {
      display: block;
      text-decoration: none;
      // All positioning/opacity/transform via :style binding
    }

    &__subtitle {
      padding-bottom: 24px;
    }

    &__subtitle-text {
      color: var(--color-gray-dark);
      opacity: 0;

      .is-ready & {
        animation: c-hero-subtitle-enter 0.7s ease-out 0.55s both;
      }

      @media (prefers-reduced-motion: reduce) {
        opacity: 1 !important;
        animation: none !important;
      }
    }

    &__explore {
      margin-top: 24px;
      display: inline-flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      border: 0;
      background: transparent;
      color: var(--color-white);
      cursor: pointer;
      opacity: 0.85;
      transition: opacity 0.3s ease;

      &:hover { opacity: 1; }
    }

    &__explore-text {
      font-size: 0.9rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    &__explore-arrow {
      font-size: 1rem;
      line-height: 1;
      animation: c-hero-arrow-bounce 1.4s ease-in-out infinite;
    }

    &__wrapper-cards {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
      justify-content: center;
      margin-top: 40px;

      @include from-sm {
        grid-template-columns: repeat(3, 1fr);
      }

      :deep(.c-card) {
        opacity: 0;
        transform: translateY(24px);
        transition: opacity 0.5s ease, transform 0.5s ease;

        @media (prefers-reduced-motion: reduce) {
          opacity: 1;
          transform: none;
          transition: none;
        }
      }

      &.is-visible {
        :deep(.c-card:nth-child(1)) { opacity: 1; transform: none; transition-delay: 0s; }
        :deep(.c-card:nth-child(2)) { opacity: 1; transform: none; transition-delay: 0.12s; }
        :deep(.c-card:nth-child(3)) { opacity: 1; transform: none; transition-delay: 0.24s; }
      }
    }
  }

  @keyframes c-hero-overline-enter {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  @keyframes c-hero-arrow-bounce {
    0%, 100% { transform: translateY(-2px); }
    50%       { transform: translateY(4px); }
  }

  @keyframes c-hero-overline-float {
    0%, 100% { transform: translateY(-1px); }
    50%       { transform: translateY(2px); }
  }

  @keyframes c-hero-subtitle-enter {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
  }
</style>
