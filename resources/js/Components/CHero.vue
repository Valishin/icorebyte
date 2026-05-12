<script setup lang="ts">
  import { useDevice } from '@/composables/useDevice'
  import { useLoaderState } from '@/composables/useLoaderState'
  import { useLogoMorph } from '@/composables/useLogoMorph'
  import Logo from '@assets/logos/logo.svg'
  import { computed, onMounted, onUnmounted, ref } from 'vue'
  import { useScroll } from '../composables/useScroll'

  const props = defineProps<{
    bgImage?: string // imagen desktop — ej: images.imagenHero
    bgImageMobile?: string // imagen móvil (menor resolución) — fallback: bgImage
  }>()

  const { isMobile } = useDevice()
  const { scrollToNextSection } = useScroll()
  const { loaderReady } = useLoaderState()
  const { heroRef, logoFixedEl, handleLogoClick } = useLogoMorph()

  // Imagen activa según dispositivo — móvil usa bgImageMobile si está disponible
  const activeBgImage = computed(() =>
    isMobile.value ? (props.bgImageMobile ?? props.bgImage) : props.bgImage
  )

  // ── Parallax de fondo ─────────────────────────────────────
  // Factor 0.25 → el fondo se mueve a 1/4 de la velocidad del scroll
  // Se actualiza vía rAF para no bloquear el hilo principal
  const PARALLAX_SPEED = 0.25
  const bgParallaxY = ref(0)
  let rafParallax = 0

  const onParallaxScroll = () => {
    if (!props.bgImage) return
    cancelAnimationFrame(rafParallax)
    rafParallax = requestAnimationFrame(() => {
      bgParallaxY.value = window.scrollY * PARALLAX_SPEED
    })
  }

  onMounted(() => {
    if (activeBgImage.value && !isMobile.value) {
      window.addEventListener('scroll', onParallaxScroll, { passive: true })
    }
  })
  onUnmounted(() => {
    window.removeEventListener('scroll', onParallaxScroll)
    cancelAnimationFrame(rafParallax)
  })
</script>

<template>
  <div ref="heroRef" class="c-hero">
    <!-- Capa de fondo — visible solo si se pasa bgImage -->
    <div
      v-if="activeBgImage"
      class="c-hero__bg"
      :style="{
        backgroundImage: `url(${activeBgImage})`,
        backgroundPosition: isMobile ? 'center top' : `center ${bgParallaxY}px`
      }"
      aria-hidden="true"
    />

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
              <!-- Desktop: visibility:hidden reserva espacio (logo real es position:fixed) -->
              <!-- Mobile: visible, el logo se muestra estático en el hero -->
              <div class="c-hero__logo-placeholder" aria-hidden="true">
                <component data-hero-logo class="c-hero__svg" :is="Logo" />
              </div>
            </h1>
          </div>

          <div class="c-hero__subtitle">
            <p class="c-hero__subtitle-text o-font-display-3">
              Tu centro integral de soluciones informáticas. Reparación, venta y desarrollo
              tecnológico al más alto nivel.
            </p>
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
    </div>

    <!-- Logo fijo: solo en desktop — en mobile el header gestiona su propio logo -->
    <a
      v-if="!isMobile"
      ref="logoFixedEl"
      class="c-hero__logo-fixed"
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
    position: relative;
    height: 100vh;

    // ── Capa de fondo (imagen opcional) ───────────────────
    &__bg {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-repeat: no-repeat;
      z-index: 0;

      // Overlay oscuro para mantener la legibilidad del texto
      &::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);

        .theme-light & {
          background: rgba(255, 255, 255, 0.6);
        }
      }
    }

    &__inner {
      position: relative;
      z-index: 1;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      // Desplaza el centro visual por debajo del header fijo
      // (mismas alturas que el padding-top del layout)
      padding-top: 0px;
    }

    // El o-container es hijo directo del flexbox — necesita ancho completo
    &__container {
      width: 100%;
      text-align: center;
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
      padding-bottom: 80px;

      @include from-sm {
        padding-bottom: 0px;
      }
      h1 {
        margin: 0;
      }

      @include from-sm {
        padding-bottom: 80px;
      }

      @include from-md {
        padding-bottom: 140px;
      }
    }

    // Mobile: logo visible y estático en el hero
    // Desktop: invisible (el logo real viaja con position:fixed)
    &__logo-placeholder {
      visibility: visible;

      @include from-sm {
        visibility: hidden;
      }
    }

    &__svg {
      position: relative;
      left: 50%;
      transform: translateX(-50%);
      display: block;

      @include from-md {
        transform: translateX(calc(-50% - 10px));
      }

      @media (prefers-reduced-motion: reduce) {
        transform: translateX(-50%) !important;
      }
    }

    // El <a> del logo fijo — posición/tamaño/opacidad vienen de :style (useLogoMorph)
    &__logo-fixed {
      display: block;
      text-decoration: none;
    }

    &__subtitle {
      padding-bottom: 24px;
    }

    &__subtitle-text {
      color: var(--color-gray);
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

      &:hover {
        opacity: 1;
      }
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
  }

  @keyframes c-hero-overline-enter {
    from {
      opacity: 0;
      transform: translateY(8px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes c-hero-arrow-bounce {
    0%,
    100% {
      transform: translateY(-2px);
    }
    50% {
      transform: translateY(4px);
    }
  }

  @keyframes c-hero-overline-float {
    0%,
    100% {
      transform: translateY(-1px);
    }
    50% {
      transform: translateY(2px);
    }
  }

  @keyframes c-hero-subtitle-enter {
    from {
      opacity: 0;
      transform: translateY(14px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>
