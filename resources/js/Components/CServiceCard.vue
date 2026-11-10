<script setup lang="ts">
  import { router } from '@inertiajs/vue3'
  import { onMounted, onUnmounted, ref } from 'vue'
  import { registerParallax, type ParallaxEntry } from '../composables/useParallaxLoop'
  import CIcon from './CIcon.vue'

  const props = withDefaults(
    defineProps<{
      title       : string
      description : string
      image       : string
      alt?        : string
      items?      : string[]
      link?       : string
    }>(),
    { alt: undefined, items: undefined, link: undefined }
  )

  // ── Parallax + skeleton ───────────────────────────────────
  const SPEED  = 0.06
  const bgEl   = ref<HTMLElement>()
  const loaded = ref(false)
  let targetY  = 0, currentY = 0

  const bgEntry: ParallaxEntry = {
    updateTarget() {
      const wrap = bgEl.value?.parentElement
      if (!wrap) return
      const rect = wrap.getBoundingClientRect()
      if (rect.bottom < 0 || rect.top > window.innerHeight) return
      targetY = (rect.top + rect.height / 2 - window.innerHeight / 2) * SPEED
    },
    lerp() {
      if (!bgEl.value) return false
      currentY += (targetY - currentY) * 0.12
      bgEl.value.style.transform = `translate3d(0,${currentY | 0}px,0)`
      if (Math.abs(currentY - targetY) > 0.1) return true
      currentY = targetY
      return false
    }
  }
  let unregister: (() => void) | null = null

  onMounted(() => {
    if (bgEl.value) bgEl.value.style.backgroundImage = `url(${props.image})`

    const img = new Image()
    img.src = props.image
    if (img.complete) {
      loaded.value = true
    } else {
      const timeout = setTimeout(() => { loaded.value = true }, 2000)
      img.onload = img.onerror = () => { clearTimeout(timeout); loaded.value = true }
    }

    unregister = registerParallax(bgEntry)
  })

  onUnmounted(() => { unregister?.() })
</script>

<template>
  <div class="c-service-card">
    <div class="c-service-card__inner">
      <div class="c-service-card__wrapper-content">
        <!--
          Si hay link la card es un <a> con hover/cursor de enlace.
          Si no, un <div> normal sin semántica de enlace.
        -->
        <component
          :is="props.link ? 'a' : 'div'"
          :href="props.link ?? undefined"
          class="c-service-card__wrapper-card"
          :class="{ 'c-service-card__wrapper-card--link': props.link }"
          @click.prevent="props.link ? router.visit(props.link) : undefined"
        >
          <div class="c-service-card__box-image">
            <Transition name="c-sc-fade">
              <div v-if="!loaded" class="c-service-card__skeleton" aria-hidden="true" />
            </Transition>
            <div ref="bgEl" class="c-service-card__bg" :aria-label="props.alt ?? props.title" />
          </div>

          <div class="c-service-card__box-content">
            <div class="c-service-card__wrapper-title">
              <h3 class="c-service-card__title o-font-display-3">{{ props.title }}</h3>
              <!-- Icono de enlace solo cuando la card tiene link -->
              <CIcon v-if="props.link" :icon="'IconLink'" :color="'gray-dark'" />
            </div>

            <div class="c-service-card__wrapper-description">
              <p class="c-service-card__description o-font-display-body">
                {{ props.description }}
              </p>
            </div>

            <!-- Tags/items — solo se renderizan si se proporcionan -->
            <div v-if="props.items?.length" class="c-service-card__wrapper-items o-font-display-caption">
              <div v-for="value in props.items" :key="value" class="c-service-card__item">
                <p>{{ value }}</p>
              </div>
            </div>
          </div>
        </component>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-service-card {
    height: 100%;

    &__inner {
      height: 100%;
    }

    &__wrapper-content {
      height: 100%;
    }

    &__wrapper-card {
      background-color: var(--color-black);
      border: 2px solid var(--color-primary-dark);
      border-radius: 0.5rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
      height: 100%;
      text-decoration: none;     // por si es <a>
      color: inherit;

      &--link { cursor: pointer; }
    }

    &__box-image {
      margin-bottom: 1.5rem;
      position: relative;
      overflow: hidden;
      border-radius: 8px 8px 0 0;
      width: 100%;
      height: 200px;

      @include from-sm { height: 240px; }
    }

    // Skeleton shimmer
    &__skeleton {
      position: absolute;
      inset: 0;
      z-index: 2;
      background: var(--color-primary-dark);
      border-radius: 8px 8px 0 0;

      &::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.07), transparent);
        transform: translateX(-100%);
        animation: c-sc-shimmer 1.6s ease-in-out infinite;
      }
    }

    // Fondo con parallax — se extiende 40px arriba/abajo para absorber el desplazamiento
    &__bg {
      position: absolute;
      top: -40px;
      left: 0;
      right: 0;
      bottom: -40px;
      background-size: cover;
      background-position: center;
      border-radius: 8px 8px 0 0;
      will-change: transform;
      z-index: 1;
    }

    &__box-content {
      display: flex;
      flex-direction: column;
      flex: 1;
      color: var(--color-white);
      padding: 0 15px 15px;
      width: 100%;
    }

    &__wrapper-title {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    &__description {
      color: var(--color-gray-dark);
    }

    &__wrapper-items {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-top: 1.5rem;
    }

    &__item {
      background-color: var(--color-primary-dark);
      color: var(--color-primary);
      padding: 0.25rem 0.75rem;
      border-radius: 8px;
      border: 1px solid var(--color-primary);
      transition:
        background-color 0.3s ease,
        color 0.3s ease;

      &:hover {
        background-color: var(--color-primary);
        color: var(--color-white);

        .theme-light & { color: var(--color-black); }
      }
    }
  }

  // Fuera de .c-service-card para que Vue Transition las encuentre
  .c-sc-fade-leave-active { transition: opacity 0.4s ease; }
  .c-sc-fade-leave-to     { opacity: 0; }

  @keyframes c-sc-shimmer {
    to { transform: translateX(100%); }
  }
</style>
