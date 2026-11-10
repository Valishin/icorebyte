<script setup lang="ts">
  import { useInView } from '@/composables/useInView'
  import CButton from './CButton.vue'
  import CTitle from './CTitle.vue'

  export interface PricingPlan {
    name        : string
    description : string
    price       : string      // "49€" o "Personalizado"
    priceSuffix?: string      // "/mes"
    badge      ?: string      // "⚡ Más popular" — si se pasa muestra el badge
    highlighted?: boolean     // card elevada visualmente
    features    : string[]
    cta: {
      label   : string
      href   ?: string
      variant?: 'primary' | 'ghost'  // default: ghost
    }
  }

  defineProps<{
    // CTitle
    overline   ?: string
    title       : string
    description?: string
    titleColor ?: 'primary' | 'secondary' | 'gradient'

    // Planes
    plans: PricingPlan[]
  }>()

  const { el, isVisible } = useInView(0.1)
</script>

<template>
  <div class="c-pricing">
    <CTitle
      :overline="overline"
      :title="title"
      :description="description"
      :color="titleColor"
    />

    <div class="c-pricing__inner o-container">
      <ul
        ref="el"
        class="c-pricing__grid"
        :class="{ 'is-visible': isVisible }"
      >
        <li
          v-for="(plan, i) in plans"
          :key="plan.name"
          class="c-pricing__card"
          :class="{ 'c-pricing__card--highlighted': plan.highlighted }"
          :style="{ '--delay': `${i * 0.1}s` }"
        >
          <!-- Badge "Más popular" -->
          <div v-if="plan.badge" class="c-pricing__badge-wrap">
            <span class="c-pricing__badge">{{ plan.badge }}</span>
          </div>

          <!-- Cabecera -->
          <div class="c-pricing__card-header">
            <h3 class="c-pricing__plan-name">{{ plan.name }}</h3>
            <p  class="c-pricing__plan-desc">{{ plan.description }}</p>
          </div>

          <!-- Precio -->
          <div class="c-pricing__price-wrap">
            <span class="c-pricing__price">{{ plan.price }}</span>
            <span v-if="plan.priceSuffix" class="c-pricing__price-suffix">
              {{ plan.priceSuffix }}
            </span>
          </div>

          <!-- Features -->
          <ul class="c-pricing__features">
            <li
              v-for="feat in plan.features"
              :key="feat"
              class="c-pricing__feature"
            >
              <svg class="c-pricing__check" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M3 8l3.5 3.5L13 4.5" stroke="currentColor" stroke-width="1.8"
                      stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              {{ feat }}
            </li>
          </ul>

          <!-- CTA -->
          <div class="c-pricing__cta-wrap">
            <a :href="plan.cta.href ?? '#'" class="c-pricing__cta-link">
              <CButton
                :title="plan.cta.label"
                :type="plan.cta.variant === 'primary' ? 'primary' : 'tertiary'"
              />
            </a>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-pricing {
    padding-bottom: 80px;

    &__inner {
      padding-top: 0;
    }

    // ── Grid de planes ────────────────────────────────────
    &__grid {
      list-style: none;
      margin: 0;
      padding: 0;
      display: grid;
      grid-template-columns: 1fr;
      gap: 24px;
      align-items: start;     // los badges no desplazan el layout

      @include from-sm {
        grid-template-columns: repeat(3, 1fr);
        align-items: stretch;
      }
    }

    // ── Tarjeta ───────────────────────────────────────────
    &__card {
      position: relative;
      display: flex;
      flex-direction: column;
      padding: 32px 28px;
      border-radius: 16px;
      border: 1px solid var(--color-primary-dark);
      background: var(--color-black);
      opacity: 0;
      transform: translateY(24px);
      transition:
        opacity   0.5s ease var(--delay, 0s),
        transform 0.5s ease var(--delay, 0s);

      @media (prefers-reduced-motion: reduce) {
        opacity: 1;
        transform: none;
        transition: none;
      }

      .is-visible & {
        opacity: 1;
        transform: none;
      }

      // Card destacada — solo reserva espacio para el badge, sin estilos distintos
      &--highlighted {
        padding-top: 48px;
      }
    }

    // ── Badge ─────────────────────────────────────────────
    &__badge-wrap {
      position: absolute;
      top: -14px;
      left: 50%;
      transform: translateX(-50%);
    }

    &__badge {
      display: inline-block;
      padding: 5px 16px;
      border-radius: 999px;
      background: linear-gradient(90deg, var(--color-primary), var(--color-secondary, var(--color-primary)));
      color: var(--color-white);
      font-size: 0.75rem;
      font-weight: 600;
      white-space: nowrap;
    }

    // ── Cabecera ──────────────────────────────────────────
    &__card-header {
      margin-bottom: 20px;
    }

    &__plan-name {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--color-white);
      margin: 0 0 8px;
    }

    &__plan-desc {
      font-size: 0.85rem;
      line-height: 1.5;
      color: var(--color-gray-dark);
      margin: 0;
    }

    // ── Precio ────────────────────────────────────────────
    &__price-wrap {
      display: flex;
      align-items: baseline;
      gap: 4px;
      margin-bottom: 24px;
      padding-bottom: 24px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    &__price {
      font-size: clamp(2.2rem, 5vw, 3rem);
      font-weight: 800;
      color: var(--color-white);
      line-height: 1;
    }

    &__price-suffix {
      font-size: 0.85rem;
      color: var(--color-gray-dark);
    }

    // ── Features ──────────────────────────────────────────
    &__features {
      list-style: none;
      padding: 0;
      margin: 0 0 32px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      flex: 1; // empuja el CTA al fondo
    }

    &__feature {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-size: 0.875rem;
      color: var(--color-white);
      line-height: 1.4;
    }

    &__check {
      width: 16px;
      height: 16px;
      flex-shrink: 0;
      color: var(--color-primary);
      margin-top: 1px;
    }

    // ── CTA ───────────────────────────────────────────────
    &__cta-wrap {
      margin-top: auto;
    }

    &__cta-link {
      display: block;
      text-decoration: none;

      // CButton ocupa todo el ancho de la card
      :deep(.c-button) {
        width: 100%;
      }
    }
  }
</style>
