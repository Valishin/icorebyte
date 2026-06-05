<script setup lang="ts">
  import { useDevice } from '@/composables/useDevice'
  import { useLoaderState } from '@/composables/useLoaderState'
  import { useLogoMorph } from '@/composables/useLogoMorph'
  import Logo from '@assets/logos/logo.svg'
  import gsap from 'gsap'
  import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
  import { useScroll } from '../composables/useScroll'

  const props = defineProps<{
    bgImages?: string[] // slider de imágenes (recomendado)
    bgImage?: string // imagen única — fallback si no se pasa bgImages
    bgImageMobile?: string // imagen móvil alternativa
  }>()

  const { isMobile } = useDevice()
  const { scrollToNextSection } = useScroll()
  const { loaderReady } = useLoaderState()
  const { heroRef, logoFixedEl, handleLogoClick } = useLogoMorph()

  // ── Slider ────────────────────────────────────────────────
  // Si hay bgImages usa el array; si no, usa bgImage como slide único
  const slides = computed<string[]>(() => {
    if (props.bgImages?.length) return props.bgImages
    const single = isMobile.value ? (props.bgImageMobile ?? props.bgImage) : props.bgImage
    return single ? [single] : []
  })

  const activeIndex = ref(0)
  const slideEls = ref<HTMLElement[]>([])
  const hasSlider = computed(() => slides.value.length > 1)
  let sliderInterval = 0

  const goToSlide = (index: number) => {
    const prev = slideEls.value[activeIndex.value]
    const next = slideEls.value[index]
    if (!prev || !next || prev === next) return

    gsap.to(prev, { opacity: 0, duration: 1.2, ease: 'power2.inOut' })
    gsap.to(next, { opacity: 1, duration: 1.2, ease: 'power2.inOut' })
    activeIndex.value = index
  }

  const nextSlide = () => {
    const next = (activeIndex.value + 1) % slides.value.length
    goToSlide(next)
  }

  // ── Parallax (directo al DOM, sin reactividad Vue) ────────
  const PARALLAX_SPEED = 0.25
  let rafParallax = 0

  const onParallaxScroll = () => {
    cancelAnimationFrame(rafParallax)
    rafParallax = requestAnimationFrame(() => {
      const pos = `center ${window.scrollY * PARALLAX_SPEED}px`
      slideEls.value.forEach((el) => {
        el.style.backgroundPosition = pos
      })
    })
  }

  // ── Refs para animaciones GSAP ───────────────────────────
  const overlineRef = ref<HTMLElement>()
  const subtitleRef = ref<HTMLElement>()
  const arrowRef = ref<HTMLElement>()
  let floatTween: gsap.core.Tween | null = null
  let bounceTween: gsap.core.Tween | null = null

  const playEnterAnimations = () => {
    if (!overlineRef.value || !subtitleRef.value) return

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      gsap.set([overlineRef.value, subtitleRef.value], { opacity: 1, y: 0 })
      return
    }
    const tl = gsap.timeline()
    tl.fromTo(
      overlineRef.value,
      { opacity: 0, y: 8 },
      { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out' }
    )
    tl.fromTo(
      subtitleRef.value,
      { opacity: 0, y: 14 },
      { opacity: 1, y: 0, duration: 0.7, ease: 'power2.out' },
      '-=0.3'
    )
    tl.call(() => {
      floatTween = gsap.to(overlineRef.value, {
        y: 2,
        duration: 1.6,
        ease: 'sine.inOut',
        yoyo: true,
        repeat: -1
      })
    })
    if (arrowRef.value) {
      bounceTween = gsap.to(arrowRef.value, {
        y: 4,
        duration: 0.7,
        ease: 'sine.inOut',
        yoyo: true,
        repeat: -1
      })
    }
  }

  // ── Lifecycle ─────────────────────────────────────────────
  onMounted(() => {
    // Fijar imágenes en cada slide UNA sola vez (no Vue reactivity)
    slides.value.forEach((src, i) => {
      const el = slideEls.value[i]
      if (!el) return
      el.style.backgroundImage = `url(${src})`
      el.style.backgroundPosition = 'center top'
      el.style.opacity = i === 0 ? '1' : '0'
    })

    // Arrancar slider automático si hay más de 1 imagen
    if (hasSlider.value) {
      sliderInterval = window.setInterval(nextSlide, 5000)
    }

    // Parallax
    if (slides.value.length) {
      window.addEventListener('scroll', onParallaxScroll, { passive: true })
    }

    // Animaciones de texto
    if (loaderReady.value) {
      playEnterAnimations()
    } else {
      const stop = watch(loaderReady, (ready) => {
        if (ready) {
          playEnterAnimations()
          stop()
        }
      })
    }
  })

  onUnmounted(() => {
    clearInterval(sliderInterval)
    floatTween?.kill()
    bounceTween?.kill()
    window.removeEventListener('scroll', onParallaxScroll)
    cancelAnimationFrame(rafParallax)
  })
</script>

<template>
  <div ref="heroRef" class="c-hero">
    <!-- Slides de fondo — uno por imagen -->
    <div
      v-for="(src, i) in slides"
      :key="src"
      :ref="
        (el) => {
          if (el) slideEls[i] = el as HTMLElement
        }
      "
      class="c-hero__bg"
      aria-hidden="true"
    />

    <!-- Dots del slider -->
    <div v-if="hasSlider" class="c-hero__dots" aria-hidden="true">
      <button
        v-for="(_, i) in slides"
        :key="i"
        class="c-hero__dot"
        :class="{ 'is-active': i === activeIndex }"
        @click="goToSlide(i)"
      />
    </div>

    <div class="c-hero__inner">
      <div class="c-hero__container o-container">
        <div class="c-hero__col o-col-8@md o-col-push-2@md o-col-6@sm o-col-push-1@sm o-col-4@xs">
          <div class="c-hero__overline">
            <h2 ref="overlineRef" class="c-hero__overline-text o-font-display-caption">
              Reparación de teléfonos, ordenadores y tablets · Desarrollo web para empresas
            </h2>
          </div>

          <div class="c-hero__title">
            <h1>
              <span class="sr-only"
                >Servicio técnico informático en Sant Feliu de Guixols — iCoreByte</span
              >
              <!-- Desktop: visibility:hidden reserva espacio (logo real es position:fixed) -->
              <!-- Mobile: visible, logo estático en hero -->
              <div class="c-hero__logo-placeholder" aria-hidden="true">
                <component data-hero-logo class="c-hero__svg" :is="Logo" />
              </div>
            </h1>
          </div>

          <div class="c-hero__subtitle">
            <p ref="subtitleRef" class="c-hero__subtitle-text o-font-display-3">
              Tu servicio técnico de confianza en Sant Feliu de Guixols. Reparamos iPhone,
              portátiles, tablets e impresoras. También creamos webs, tiendas online y soluciones
              para empresas de la Costa Brava.
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
            <span ref="arrowRef" class="c-hero__explore-arrow" aria-hidden="true">↓</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Logo fijo: solo desktop — en mobile el header lo gestiona -->
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

    // ── Slides de fondo ────────────────────────────────────
    &__bg {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center !important;
      z-index: 0;
      will-change: opacity;

      // Overlay oscuro para legibilidad del texto
      &::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);

        .theme-light & {
          background: rgba(255, 255, 255, 0.5);
        }
      }
    }

    // ── Dots del slider ────────────────────────────────────
    &__dots {
      position: absolute;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 8px;
      z-index: 2;
    }

    &__dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      border: none;
      background: rgba(255, 255, 255, 0.4);
      cursor: pointer;
      padding: 0;
      transition:
        background 0.3s ease,
        transform 0.3s ease;

      &.is-active {
        background: var(--color-white);
        transform: scale(1.3);
      }

      &:hover {
        background: rgba(255, 255, 255, 0.7);
      }
    }

    // ── Layout interior ────────────────────────────────────
    &__inner {
      position: relative;
      z-index: 1;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding-top: 0;
    }

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
      border: 1px solid var(--color-white);
      padding: 5px 10px;
      border-radius: 9999px;
      opacity: 0; // GSAP anima a 1
    }

    &__title {
      h1 {
        margin: 0;
      }

      padding-bottom: 36px; // móvil

      @include from-sm {
        padding-bottom: 80px;
      }
      @include from-md {
        padding-bottom: 140px;
      }
    }

    // Mobile: visible / Desktop: reserva espacio (logo real es position:fixed)
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
    }

    &__logo-fixed {
      display: block;
      text-decoration: none;
    }

    &__subtitle {
      padding-bottom: 24px;
    }

    &__subtitle-text {
      color: var(--color-gray);
      opacity: 0; // GSAP anima a 1
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
    }
  }
</style>
