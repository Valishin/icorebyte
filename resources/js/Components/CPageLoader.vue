<script setup lang="ts">
  import { useLoaderState } from '@/composables/useLoaderState'
  import { router } from '@inertiajs/vue3'
  import Logo from '@assets/logos/logo.svg'
  import { onMounted, onUnmounted, ref } from 'vue'

  const { markReady } = useLoaderState()

  // ── Loader inicial ────────────────────────────────────────
  const initialVisible = ref(true)

  // ── Barra de progreso (navegaciones Inertia) ──────────────
  const barVisible = ref(false)
  const barProgress = ref(0)
  const barDone = ref(false)

  let removeStart: () => void
  let removeProgress: () => void
  let removeFinish: () => void

  onMounted(() => {
    // Oculta el overlay inicial tras la animación de entrada
    setTimeout(() => {
      initialVisible.value = false
    }, 900)

    // Señal para useInView: espera a que el fade-out del overlay termine (900 + 400ms)
    setTimeout(() => {
      markReady()
    }, 1300)

    // Eventos del router Inertia
    removeStart = router.on('start', () => {
      barDone.value = false
      barProgress.value = 0
      barVisible.value = true
    })

    removeProgress = router.on('progress', (e) => {
      const pct = (e as any).detail?.progress?.percentage
      if (pct) barProgress.value = Math.min(pct, 90) // nunca llega al 100 solo
    })

    removeFinish = router.on('finish', () => {
      barProgress.value = 100
      barDone.value = true
      setTimeout(() => {
        barVisible.value = false
        barProgress.value = 0
        barDone.value = false
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
  <Transition name="loader-fade">
    <div v-if="initialVisible" class="c-page-loader">
      <div class="c-page-loader__logo">
        <component :is="Logo" class="c-page-loader__svg" />
      </div>
      <div class="c-page-loader__bar" />
    </div>
  </Transition>

  <!-- Barra de progreso en navegaciones -->
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
    background: var(--color-black);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2rem;

    &__logo {
      animation: loader-logo-in 0.5s ease-out both;
    }

    &__svg {
      width: 160px;

      @include from-sm {
        width: 220px;
      }
    }

    &__bar {
      width: 48px;
      height: 2px;
      background: var(--color-border-subtle);
      border-radius: 99px;
      overflow: hidden;
      position: relative;

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

  // ── Transición salida overlay ─────────────────────────────
  .loader-fade-leave-active {
    transition: opacity 0.4s ease;
  }
  .loader-fade-leave-to {
    opacity: 0;
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
