<script setup lang="ts">
  import { Link } from '@inertiajs/vue3'
  import { ref, onMounted } from 'vue'
  import CButton from './CButton.vue'

  const STORAGE_KEY = 'icorebyte-cookies-accepted'
  const visible = ref(false)

  onMounted(() => {
    if (!localStorage.getItem(STORAGE_KEY)) {
      visible.value = true
    }
  })

  const accept = () => {
    localStorage.setItem(STORAGE_KEY, 'true')
    visible.value = false
  }
</script>

<template>
  <Transition name="cookie-banner">
    <div v-if="visible" class="c-cookie-banner" role="dialog" aria-label="Aviso de cookies">
      <div class="c-cookie-banner__inner o-container">
        <p class="c-cookie-banner__text">
          Usamos cookies técnicas propias, estrictamente necesarias para el funcionamiento del sitio.
          No utilizamos cookies analíticas ni publicitarias.
          <Link href="/politica-de-cookies" class="c-cookie-banner__link">Más información</Link>
        </p>
        <CButton title="Entendido" color="gradient" class="c-cookie-banner__btn" @click="accept" />
      </div>
    </div>
  </Transition>
</template>

<style lang="scss" scoped>
  .c-cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 200;
    background: var(--color-primary-dark);
    border-top: 1px solid var(--color-border-subtle);
    box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.3);

    .theme-light & {
      box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.1);
    }

    &__inner {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      padding-top: 1.25rem;
      padding-bottom: 1.25rem;

      @include from-sm {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
      }
    }

    &__text {
      font-size: 0.875rem;
      color: var(--color-text-muted);
      line-height: 1.6;
      margin: 0;
    }

    &__link {
      color: var(--color-primary);
      text-decoration: none;
      white-space: nowrap;

      &:hover {
        text-decoration: underline;
      }
    }

    &__btn {
      flex-shrink: 0;
      width: 100%;
      justify-content: center;

      @include from-sm {
        width: auto;
        justify-content: flex-start;
      }
    }
  }

  .cookie-banner-enter-active,
  .cookie-banner-leave-active {
    transition:
      transform 0.3s ease,
      opacity 0.3s ease;
  }

  .cookie-banner-enter-from,
  .cookie-banner-leave-to {
    transform: translateY(100%);
    opacity: 0;
  }
</style>
