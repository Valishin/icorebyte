<script setup lang="ts">
  import { useHeroLogoState } from '@/composables/useHeroLogoState'
  import { useLoaderState } from '@/composables/useLoaderState'
  import { router } from '@inertiajs/vue3'
  import Logo from '@assets/logos/logo.svg'
  import gsap from 'gsap'
  import { onMounted, onUnmounted, ref } from 'vue'

  const { markReady } = useLoaderState()
  const { loaderMorphedToHero, loaderIsActive } = useHeroLogoState()

  const isMobile = () => window.innerWidth < 768

  // ── Estado del overlay ────────────────────────────────────
  const initialVisible = ref(true)
  const homeExiting    = ref(false)
  const loaderLogoRef  = ref<HTMLElement>()
  const loaderBgRef    = ref<HTMLElement>()
  const loaderBarRef   = ref<HTMLElement>()

  // ── Barra de progreso (navegaciones Inertia) ──────────────
  const barVisible  = ref(false)
  const barProgress = ref(0)
  const barDone     = ref(false)

  let removeStart   : () => void
  let removeProgress: () => void
  let removeFinish  : () => void
  let barPulseTween : gsap.core.Tween | null = null

  // ── Animación de entrada del logo ─────────────────────────
  const animateLogoIn = () => {
    if (!loaderLogoRef.value) return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      gsap.set(loaderLogoRef.value, { opacity: 1, y: 0 })
      return
    }
    gsap.fromTo(
      loaderLogoRef.value,
      { opacity: 0, y: 10 },
      { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out' }
    )
  }

  // ── Animación de la barra de carga ────────────────────────
  const animateBarIn = () => {
    if (!loaderBarRef.value) return
    const fill = loaderBarRef.value.querySelector('.c-page-loader__bar-fill') as HTMLElement
    if (!fill) return
    gsap.set(fill, { scaleX: 0.3, transformOrigin: 'left' })
    barPulseTween = gsap.to(fill, {
      scaleX: 1, duration: 0.9, ease: 'sine.inOut',
      yoyo: true, repeat: -1,
    })
  }

  // ── Salida simple (mobile o páginas no-home) ──────────────
  const startSimpleExit = () => {
    setTimeout(() => { initialVisible.value = false }, 900)
    setTimeout(() => { loaderIsActive.value = false; markReady() }, 1300)
  }

  // ── Salida con morph del logo al hero (desktop home) ──────
  const startHomeExit = () => {
    homeExiting.value = true
    barPulseTween?.kill()

    const loaderLogo = loaderLogoRef.value
    const heroSvg    = document.querySelector('[data-hero-logo]') as HTMLElement | null

    if (!loaderLogo || !heroSvg) {
      startSimpleExit()
      return
    }

    const from = loaderLogo.getBoundingClientRect()
    const to   = heroSvg.getBoundingClientRect()
    const sc   = to.width > 0 ? to.width / from.width : 1
    const dx   = to.left - from.left
    const dy   = to.top  - from.top

    // Fondo: fade-out independiente
    gsap.to(loaderBgRef.value!, {
      opacity: 0, duration: 0.45, ease: 'power1.inOut',
    })

    // Logo: morph hacia la posición del hero
    gsap.to(loaderLogo, {
      x: dx, y: dy, scale: sc,
      duration: 0.52,
      ease: 'power2.inOut',
      transformOrigin: 'top left',
      onComplete: () => {
        loaderMorphedToHero.value = true
        loaderIsActive.value      = false
        markReady()
        initialVisible.value      = false
      },
    })
  }

  // ── Lifecycle ─────────────────────────────────────────────
  onMounted(() => {
    loaderIsActive.value = true

    animateLogoIn()
    animateBarIn()

    if (window.location.pathname === '/' && !isMobile()) {
      setTimeout(startHomeExit, 900)
    } else {
      startSimpleExit()
    }

    removeStart = router.on('start', () => {
      barDone.value     = false
      barProgress.value = 0
      barVisible.value  = true
    })
    removeProgress = router.on('progress', (e) => {
      const pct = (e as any).detail?.progress?.percentage
      if (pct) barProgress.value = Math.min(pct, 90)
    })
    removeFinish = router.on('finish', () => {
      barProgress.value = 100
      barDone.value     = true
      setTimeout(() => {
        barVisible.value  = false
        barProgress.value = 0
        barDone.value     = false
      }, 400)
    })
  })

  onUnmounted(() => {
    barPulseTween?.kill()
    removeStart?.()
    removeProgress?.()
    removeFinish?.()
  })
</script>

<template>
  <Transition :name="homeExiting ? 'loader-instant' : 'loader-fade'">
    <div v-if="initialVisible" class="c-page-loader">
      <div ref="loaderBgRef" class="c-page-loader__bg" />

      <div ref="loaderLogoRef" class="c-page-loader__logo">
        <component :is="Logo" class="c-page-loader__svg" />
      </div>

      <div v-if="!homeExiting" ref="loaderBarRef" class="c-page-loader__bar">
        <span class="c-page-loader__bar-fill" />
      </div>
    </div>
  </Transition>

  <!-- Barra de progreso en navegaciones Inertia -->
  <div
    v-if="barVisible"
    class="c-progress-bar"
    :class="{ 'c-progress-bar--done': barDone }"
    :style="{ width: `${barProgress}%` }"
  />
</template>

<style lang="scss" scoped>
  .c-page-loader {
    position: fixed;
    inset: 0;
    z-index: 999;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2rem;
    background: transparent;
    pointer-events: none;

    &__bg {
      position: absolute;
      inset: 0;
      background: var(--color-black);
      z-index: 0;
    }

    &__logo {
      position: relative;
      z-index: 1;
      opacity: 0; // GSAP anima a 1
    }

    &__svg {
      display: block;
      width: 160px;

      @include from-sm {
        width: 220px;
      }
    }

    &__bar {
      position: relative;
      z-index: 1;
      width: 48px;
      height: 2px;
      background: var(--color-border-subtle);
      border-radius: 99px;
      overflow: hidden;
    }

    &__bar-fill {
      display: block;
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, var(--color-primary), var(--color-secondary, var(--color-primary)));
      border-radius: 99px;
      transform-origin: left;
    }
  }

  // Páginas normales: fade del overlay
  .loader-fade-leave-active { transition: opacity 0.4s ease; }
  .loader-fade-leave-to     { opacity: 0; }

  // Home desktop: overlay ya vacío, quitar sin transición
  .loader-instant-leave-active { transition: none; }

  // ── Barra de progreso Inertia ─────────────────────────────
  .c-progress-bar {
    position: fixed;
    top: 0;
    left: 0;
    height: 2px;
    z-index: 1000;
    background: linear-gradient(90deg, var(--color-primary), var(--color-secondary, var(--color-primary)));
    transition: width 0.3s ease;
    border-radius: 0 2px 2px 0;
    box-shadow: 0 0 8px var(--color-primary);

    &--done {
      transition: width 0.15s ease, opacity 0.4s ease 0.1s;
      opacity: 0;
    }
  }
</style>
