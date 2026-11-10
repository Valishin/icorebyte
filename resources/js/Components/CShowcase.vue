<script setup lang="ts">
  import CButton from './CButton.vue'
  import CImage from './CImage.vue'

  export interface ShowcaseFeature {
    label: string
    icon?: string
  }

  export interface ShowcaseCta {
    label: string
    href?: string
    variant?: 'primary' | 'ghost'
  }

  defineProps<{
    overline?: string
    title?: string
    titleHtml?: string // HTML enriquecido — permite tags inline como badges
    description: string
    features?: ShowcaseFeature[]
    primaryCta?: ShowcaseCta
    secondaryCta?: ShowcaseCta
    image?: string
    imageAlt?: string
  }>()
</script>

<template>
  <section class="c-showcase">
    <div class="c-showcase__inner o-container">
      <!-- ── Columna izquierda: texto ───────────────────── -->
      <div class="c-showcase__content">
        <p v-if="overline" class="c-showcase__overline">{{ overline }}</p>

        <h2 v-if="titleHtml" class="c-showcase__title" v-html="titleHtml" />
        <h2 v-else class="c-showcase__title">{{ title }}</h2>

        <p class="c-showcase__description">{{ description }}</p>

        <ul v-if="features?.length" class="c-showcase__features">
          <li v-for="f in features" :key="f.label" class="c-showcase__feature">
            <span v-if="f.icon" class="c-showcase__feature-icon">{{ f.icon }}</span>
            {{ f.label }}
          </li>
        </ul>

        <div v-if="primaryCta || secondaryCta" class="c-showcase__ctas">
          <a v-if="primaryCta" :href="primaryCta.href ?? '#'" class="c-showcase__cta-link">
            <CButton :title="primaryCta.label" type="primary" />
          </a>
          <a v-if="secondaryCta" :href="secondaryCta.href ?? '#'" class="c-showcase__cta-link">
            <CButton :title="secondaryCta.label" type="tertiary" />
          </a>
        </div>
      </div>

      <!-- ── Columna derecha: imagen ─────────────────────── -->
      <div v-if="image" class="c-showcase__media">
        <CImage :src="image" :alt="imageAlt ?? ''" class="c-showcase__image" />
      </div>
    </div>
  </section>
</template>

<style lang="scss" scoped>
  .c-showcase {
    padding: 80px 0;

    &__inner {
      display: grid;
      grid-template-columns: 1fr;
      gap: 48px;
      align-items: center;

      @include from-md {
        grid-template-columns: 1fr 1fr;
        gap: 64px;
      }
    }

    // ── Columna de texto ──────────────────────────────────
    &__overline {
      font-family: 'Courier New', monospace;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--color-primary);
      margin-bottom: 20px;
    }

    &__title {
      font-size: clamp(2rem, 5vw, 3.2rem);
      font-weight: 800;
      line-height: 1.3;
      color: var(--color-white);
      margin: 0 0 24px;

      // Partes con color (array estático)
      &-part {
        &--primary {
          color: var(--color-primary);
        }
        &--secondary {
          color: var(--color-secondary, var(--color-primary));
        }
        &--white {
          color: var(--color-white);
        }
      }

      // :deep() para que los estilos penetren en el HTML de v-html
      :deep(span) {
        color: var(--color-secondary);
      }

      :deep(.c-title__tag) {
        display: inline-block;
        vertical-align: middle;
        padding: 2px 12px;
        margin: 0 4px;
        border-radius: 999px;
        border: 1px solid var(--color-primary);
        background: var(--color-primary-dark);
        color: var(--color-primary);
        font-size: 0.42em;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        line-height: 1.9;
        position: relative;
        bottom: 0.15em;
      }
    }

    &__description {
      font-size: 1rem;
      line-height: 1.7;
      color: var(--color-gray-dark);
      margin-bottom: 32px;
      max-width: 480px;
    }

    &__features {
      list-style: none;
      padding: 0;
      margin: 0 0 36px;
      display: flex;
      flex-wrap: wrap;
      gap: 12px 24px;
    }

    &__feature {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.85rem;
      color: var(--color-gray-dark);

      &-icon {
        font-size: 1rem;
        opacity: 0.8;
      }
    }

    &__ctas {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    &__cta-link {
      text-decoration: none;
    }

    // ── Columna de imagen ─────────────────────────────────
    &__media {
      border-radius: 16px;
      overflow: hidden;
      box-shadow:
        0 24px 80px rgba(0, 0, 0, 0.4),
        0 0 0 1px rgba(255, 255, 255, 0.06);
    }

    &__image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
  }
</style>
