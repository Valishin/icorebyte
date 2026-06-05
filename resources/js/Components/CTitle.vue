<script setup lang="ts">
  import { useInView } from '@/composables/useInView'
  import type { ColorType } from '@/types'
  import { icons } from '@assets/icons'
  import { computed } from 'vue'
  import CButton from './CButton.vue'

  interface ButtonConfig {
    title: string
    color?: ColorType
    icon?: keyof typeof icons
    type?: 'primary' | 'secondary' | 'tertiary'
    nativeType?: 'submit' | 'button' | 'reset'
    disabled?: boolean
    /** Si se pasa, el botón actúa como enlace */
    href?: string
    target?: '_blank' | '_self'
  }

  const props = defineProps<{
    overline?: string
    title: string
    description?: string
    color?: 'primary' | 'secondary' | 'gradient'
    /** Botón CTA opcional bajo la descripción */
    button?: ButtonConfig
  }>()

  const emit = defineEmits<{ 'button-click': [] }>()

  type CButtonBindProps = Omit<ButtonConfig, 'href' | 'target'>

  // Tipo de retorno explícito para que TS no infiera la unión con {}
  // y sepa que `title` siempre está presente en el v-bind.
  const buttonProps = computed((): CButtonBindProps => {
    if (!props.button) return { title: '' }  // rama muerta: v-if lo bloquea
    const { href: _h, target: _t, ...rest } = props.button
    return rest
  })

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

      <div v-if="props.button" class="c-title__wrapper-button">
        <a
          v-if="props.button.href"
          :href="props.button.href"
          :target="props.button.target ?? '_self'"
          :rel="props.button.target === '_blank' ? 'noopener noreferrer' : undefined"
          class="c-title__button-link"
        >
          <CButton v-bind="buttonProps" @click="emit('button-click')" />
        </a>
        <CButton v-else v-bind="buttonProps" @click="emit('button-click')" />
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

      &.color-primary {
        color: var(--color-primary);
      }
      &.color-secondary {
        color: var(--color-secondary);
      }
      &.color-gradient {
        background: var(--color-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
    }

    // ── Estado inicial: invisible ──────────────────────────
    &__wrapper-overline,
    &__wrapper-title,
    &__wrapper-description,
    &__wrapper-button {
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

      &__wrapper-button {
        opacity: 1;
        transform: none;
        transition-delay: 0.3s;
        padding-top: 46px;
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

    &__wrapper-button {
      padding-top: 8px;
    }

    &__button-link {
      display: inline-block;
      text-decoration: none;
    }
  }
</style>
