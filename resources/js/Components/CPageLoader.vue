<script setup lang="ts">
  import { useHeroLogoState } from '@/composables/useHeroLogoState'
  import { useLoaderState } from '@/composables/useLoaderState'
  import { router } from '@inertiajs/vue3'
  import Logo from '@assets/logos/logo.svg'
  import { onMounted, onUnmounted, ref } from 'vue'

  const { markReady } = useLoaderState()
  const { loaderMorphedToHero, loaderIsActive } = useHeroLogoState()

  const isMobile = () => window.innerWidth < 768

  // ── Loader inicial ────────────────────────────────────────
  const initialVisible = ref(true)
  const homeExiting    = ref(false)   // true durante la secuencia de salida home
  const bgFading       = ref(false)   // fondo negro desapareciendo
  const logoMorphStyle = ref<Record<string, string>>({})
  const loaderLogoRef  = ref<HTMLElement>()

  // ── Barra de progreso (navegaciones Inertia) ──────────────
  const barVisible  = ref(false)
  const barProgress = ref(0)
  const barDone     = ref(false)

  let removeStart: () => void
  let removeProgress: () => void
  let removeFinish: () => void

  // ── Salida simple (mobile o páginas no-home) ──────────────
  const startSimpleExit = () => {
    setTimeout(() => { initialVisible.value = false }, 900)
    setTimeout(() => { loaderIsActive.value = false; markReady() }, 1300)
  }

  // ── Salida especial en home (solo desktop) ────────────────
  const startHomeExit = () => {
    homeExiting.value = true  // oculta la barra
    bgFading.value    = true  // fondo empieza a desvanecerse ya

    const loaderLogo  = loaderLogoRef.value
    // Usamos data-hero-logo para no depender de clases CSS scoped
    const heroSvg     = document.querySelector('[data-hero-logo]') as HTMLElement | null

    if (!loaderLogo || !heroSvg) {
      // Fallback: fade estándar si no encontramos los elementos
      setTimeout(() => { initialVisible.value = false; markReady() }, 450)
      return
    }

    // ── Paso 1: cancelar la animación CSS del logo del loader y preparar la transición.
    //   NO se incluye transform todavía — la transición necesita ver el estado inicial
    //   antes de que llegue el estado final (si se setean en el mismo tick, el browser
    //   no tiene referencia de dónde partir y el elemento salta directamente al final).
    logoMorphStyle.value = {
      animation       : 'none',
      transformOrigin : 'top left',
      transition      : 'transform 0.52s cubic-bezier(0.4, 0, 0.2, 1)',
    }

    // ── Paso 2: en el siguiente frame el browser ya ha pintado el estado sin transform.
    //   Ahora medimos y aplicamos el transform final → la transición se dispara correctamente.
    requestAnimationFrame(() => {
      const from = loaderLogo.getBoundingClientRect()
      const to   = heroSvg.getBoundingClientRect()

      // Si el SVG del hero no tiene dimensiones aún, dejamos sc=1 (sin escala)
      const sc = to.width > 0 ? to.width / from.width : 1
      const dx = to.left - from.left
      const dy = to.top  - from.top

      logoMorphStyle.value = {
        animation       : 'none',
        transformOrigin : 'top left',
        transition      : 'transform 0.52s cubic-bezier(0.4, 0, 0.2, 1)',
        transform       : `translate(${dx}px, ${dy}px) scale(${sc})`,
      }

      // Al terminar la transición: todos los cambios en el mismo tick síncrono.
      // Vue agrupa los tres en un solo render → overlay desaparece Y hero logo aparece
      // en el mismo frame, sin ningún solapamiento visible.
      setTimeout(() => {
        loaderMorphedToHero.value = true  // CHero aparece sin fade-in
        loaderIsActive.value      = false  // overlay ha terminado
        markReady()                        // dispara el watch en useLogoMorph
        initialVisible.value      = false  // elimina el overlay en el mismo render
      }, 560)  // ligeramente más que los 0.52s de transición
    })
  }

  onMounted(() => {
    // Señalamos que el overlay está activo: useLogoMorph esperará antes de mostrarse
    loaderIsActive.value = true

    if (window.location.pathname === '/' && !isMobile()) {
      // Home en desktop: salida con morph del logo al hero
      setTimeout(startHomeExit, 900)
    } else {
      // Mobile o páginas no-home: fade estándar sin logo
      startSimpleExit()
    }

    // Eventos del router Inertia
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
    removeStart?.()
    removeProgress?.()
    removeFinish?.()
  })
</script>

<template>
  <!-- Overlay carga inicial -->
  <!--
    En home usamos 'loader-instant' (sin transición) porque el fondo ya se fue
    con CSS y el logo ya está en la posición del hero; quitar el div es imperceptible.
    En otras páginas usamos 'loader-fade' (opacity 0.4s) como antes.
  -->
  <Transition :name="homeExiting ? 'loader-instant' : 'loader-fade'">
    <div v-if="initialVisible" class="c-page-loader">
      <!-- Capa de fondo — se desvanece independientemente del logo -->
      <div class="c-page-loader__bg" :class="{ 'is-fading': bgFading }" />

      <!-- Logo — solo en desktop, anima hacia el hero en home -->
      <div ref="loaderLogoRef" class="c-page-loader__logo" :style="logoMorphStyle">
        <component :is="Logo" class="c-page-loader__svg" />
      </div>

      <!-- Barra de carga — siempre visible en mobile, se oculta antes del morph en desktop -->
      <div v-if="!homeExiting" class="c-page-loader__bar" />
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
  // ── Overlay inicial ───────────────────────────────────────
  .c-page-loader {
    position: fixed;
    inset: 0;
    z-index: 999;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2rem;
    // Sin background propio — lo gestiona __bg para poder desvanecerlo por separado
    background: transparent;
    pointer-events: none;

    &__bg {
      position: absolute;
      inset: 0;
      background: var(--color-black);
      transition: opacity 0.45s ease;
      z-index: 0;

      &.is-fading {
        opacity: 0;
      }
    }

    &__logo {
      position: relative;
      z-index: 1;
      animation: loader-logo-in 0.5s ease-out both;
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

      &::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, var(--color-primary), var(--color-secondary, var(--color-primary)));
        border-radius: 99px;
        animation: loader-pulse 0.9s ease-in-out infinite alternate;
      }
    }
  }

  // ── Transiciones de salida ────────────────────────────────
  // Páginas normales: fade del overlay completo
  .loader-fade-leave-active {
    transition: opacity 0.4s ease;
  }
  .loader-fade-leave-to {
    opacity: 0;
  }

  // Home: el fondo y el logo ya se fueron con CSS; quitamos el div sin transición
  .loader-instant-leave-active {
    transition: none;
  }

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
      transition:
        width 0.15s ease,
        opacity 0.4s ease 0.1s;
      opacity: 0;
    }
  }

  // ── Keyframes ─────────────────────────────────────────────
  @keyframes loader-logo-in {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes loader-pulse {
    from { transform: scaleX(0.3); transform-origin: left; }
    to   { transform: scaleX(1);   transform-origin: left; }
  }
</style>
