<script setup lang="ts">
  import { useInView } from '@/composables/useInView'

  const props = defineProps<{
    overline?: string
    title: string
    description?: string
    color?: 'primary' | 'secondary' | 'gradient'
  }>()

  const { el, isVisible } = useInView(0.15)
</script>

<template>
  <div class="c-title">
    <div
      ref="el"
      class="c-title__inner"
      :class="[props.color ? `color-${props.color}` : '', { 'is-visible': isVisible }]"
    >
      <div class="c-title__wrapper-overline" v-if="props.overline">
        <p class="c-title__overline o-font-display-section">
          {{ props.overline }}
        </p>
      </div>
      <div class="c-title__wrapper-title">
        <h2 class="c-title__title o-font-display-2">
          {{ props.title }}
        </h2>
      </div>
      <div class="c-title__wrapper-description" v-if="props.description">
        <p class="c-title__description o-font-display-body">
          {{ props.description }}
        </p>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-title {
    padding-top: 60px;

    @include from-sm {
      padding-top: 120px;
    }

    &__inner {
      text-align: center;
      padding-bottom: 68px;

      &.color-primary  { color: var(--color-primary); }
      &.color-secondary { color: var(--color-secondary); }
      &.color-gradient {
        background: var(--color-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
    }

    // ── Estado inicial: invisible ──────────────────────────
    &__wrapper-overline,
    &__wrapper-title,
    &__wrapper-description {
      opacity: 0;
      transform: translateY(20px);
      transition:
        opacity 0.55s ease,
        transform 0.55s ease;

      // Respeta la preferencia del usuario
      @media (prefers-reduced-motion: reduce) {
        opacity: 1;
        transform: none;
        transition: none;
      }
    }

    // ── Estado visible: stagger por elemento ──────────────
    .is-visible & {
      &__wrapper-overline {
        opacity: 1;
        transform: none;
        transition-delay: 0s;
      }

      &__wrapper-title {
        opacity: 1;
        transform: none;
        transition-delay: 0.1s;
      }

      &__wrapper-description {
        opacity: 1;
        transform: none;
        transition-delay: 0.2s;
      }
    }

    // ── Estilos de contenido ──────────────────────────────
    &__wrapper-overline {
      padding-bottom: 24px;
    }

    &__overline {
      margin-bottom: 0.5rem;
      text-transform: uppercase;
      position: relative;

      &::after {
        content: '';
        position: relative;
        display: block;
        width: 40px;
        bottom: -8px;
        height: 2px;
        margin: 0 auto;

        .color-gradient & {
          background: var(--color-gradient);
        }
      }

      .color-gradient & {
        background: var(--color-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
    }

    &__title {
      margin-bottom: 1rem;
    }

    &__description {
      color: var(--color-white);
      background: var(--color-white);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
  }
</style>
