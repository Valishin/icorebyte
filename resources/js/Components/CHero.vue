<script setup lang="ts">
  import { useDevice } from '@/composables/useDevice'
  import { useLoaderState } from '@/composables/useLoaderState'
  import { useLogoMorph } from '@/composables/useLogoMorph'
  import Logo from '@assets/logos/logo.svg'
  import gsap from 'gsap'
  import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
  import { useScroll } from '../composables/useScroll'

  const props = defineProps<{
    bgImage?       : string
    bgImageMobile? : string
  }>()

  const { isMobile } = useDevice()
  const { scrollToNextSection } = useScroll()
  const { loaderReady } = useLoaderState()
  const { heroRef, logoFixedEl, handleLogoClick } = useLogoMorph()

  const activeBgImage = computed(() =>
    isMobile.value ? (props.bgImageMobile ?? props.bgImage) : props.bgImage
  )

  // ── Parallax ──────────────────────────────────────────────
  // backgroundPosition se aplica directo al DOM — sin pasar por reactividad Vue
  // para evitar que el browser toque backgroundImage en cada frame de scroll
  const PARALLAX_SPEED = 0.25
  const bgEl           = ref<HTMLElement>()
  let   rafParallax    = 0

  const onParallaxScroll = () => {
    cancelAnimationFrame(rafParallax)
    rafParallax = requestAnimationFrame(() => {
      if (bgEl.value) {
        bgEl.value.style.backgroundPosition = `center ${window.scrollY * PARALLAX_SPEED}px`
      }
    })
  }

  // ── Refs para las animaciones GSAP ───────────────────────
  const overlineRef  = ref<HTMLElement>()
  const subtitleRef  = ref<HTMLElement>()
  const arrowRef     = ref<HTMLElement>()

  // ── Animaciones de entrada (GSAP) ─────────────────────────
  let floatTween  : gsap.core.Tween | null = null
  let bounceTween : gsap.core.Tween | null = null

  const playEnterAnimations = () => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      gsap.set([overlineRef.value, subtitleRef.value], { opacity: 1, y: 0 })
      return
    }

    const tl = gsap.timeline()

    // Overline: sube + fade-in
    tl.fromTo(
      overlineRef.value,
      { opacity: 0, y: 8 },
      { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out' }
    )

    // Subtítulo: sube + fade-in (ligero solapamiento)
    tl.fromTo(
      subtitleRef.value,
      { opacity: 0, y: 14 },
      { opacity: 1, y: 0, duration: 0.7, ease: 'power2.out' },
      '-=0.3'
    )

    // Flote continuo del overline (loop)
    tl.call(() => {
      floatTween = gsap.to(overlineRef.value, {
        y: 2, duration: 1.6, ease: 'sine.inOut',
        yoyo: true, repeat: -1,
      })
    })

    // Bounce continuo de la flecha
    if (arrowRef.value) {
      bounceTween = gsap.to(arrowRef.value, {
        y: 4, duration: 0.7, ease: 'sine.inOut',
        yoyo: true, repeat: -1,
      })
    }
  }

  // ── Lifecycle ─────────────────────────────────────────────
  onMounted(() => {
    if (activeBgImage.value && bgEl.value) {
      // Imagen: se fija UNA vez, el browser la cachea y nunca más la toca Vue
      bgEl.value.style.backgroundImage    = `url(${activeBgImage.value})`
      bgEl.value.style.backgroundPosition = 'center top'

      if (!isMobile.value) {
        window.addEventListener('scroll', onParallaxScroll, { passive: true })
      }
    }

    // Arrancar animaciones cuando el loader termina
    if (loaderReady.value) {
      playEnterAnimations()
    } else {
      const stop = watch(loaderReady, (ready) => {
        if (ready) { playEnterAnimations(); stop() }
      })
    }
  })

  onUnmounted(() => {
    floatTween?.kill()
    bounceTween?.kill()
    window.removeEventListener('scroll', onParallaxScroll)
    cancelAnimationFrame(rafParallax)
  })
</script>

<template>
  <div ref="heroRef" class="c-hero">
    <div
      v-if="activeBgImage"
      ref="bgEl"
      class="c-hero__bg"
      aria-hidden="true"
    />

    <div class="c-hero__inner">
      <div class="c-hero__container o-container">
        <div class="c-hero__col o-col-8@md o-col-push-2@md o-col-6@sm o-col-push-1@sm o-col-4@xs">
          <div class="c-hero__overline">
            <p ref="overlineRef" class="c-hero__overline-text o-font-display-caption">
              Servicios IT profesionales en tu zona
            </p>
          </div>

          <div class="c-hero__title">
            <h1>
              <span class="sr-only">iCoreByte</span>
              <div class="c-hero__logo-placeholder" aria-hidden="true">
                <component data-hero-logo class="c-hero__svg" :is="Logo" />
              </div>
            </h1>
          </div>

          <div class="c-hero__subtitle">
            <p ref="subtitleRef" class="c-hero__subtitle-text o-font-display-3">
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
            <span ref="arrowRef" class="c-hero__explore-arrow" aria-hidden="true">↓</span>
          </button>
        </div>
      </div>
    </div>

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

    &__bg {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-repeat: no-repeat;
      z-index: 0;

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
      border: 1px solid var(--color-gray-dark);
      padding: 5px 10px;
      border-radius: 9999px;
      opacity: 0; // GSAP lo anima a 1
    }

    &__title {
      padding-bottom: 80px;
      h1 { margin: 0; }

      @include from-sm { padding-bottom: 80px; }
      @include from-md { padding-bottom: 140px; }
    }

    // Mobile: visible / Desktop: reserva espacio (logo real es position:fixed)
    &__logo-placeholder {
      visibility: visible;
      @include from-sm { visibility: hidden; }
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
      opacity: 0; // GSAP lo anima a 1
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
    }
  }
</style>
