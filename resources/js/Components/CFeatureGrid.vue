<script setup lang="ts">
  import { useInView } from '@/composables/useInView'
  import { icons } from '@assets/icons'
  import CIcon from './CIcon.vue'
  import CTitle from './CTitle.vue'

  export interface FeatureItem {
    icon       : keyof typeof icons
    iconColor ?: 'primary' | 'secondary' | 'white'
    title      : string
    description: string
  }

  defineProps<{
    // CTitle
    overline   ?: string
    title       : string
    description?: string
    titleColor ?: 'primary' | 'secondary' | 'gradient'

    // Grid de features
    features    : FeatureItem[]
  }>()

  const { el: gridRef, isVisible } = useInView(0.1)
</script>

<template>
  <div class="c-feature-grid">
    <!-- Cabecera reutilizando CTitle -->
    <CTitle
      :overline="overline"
      :title="title"
      :description="description"
      :color="titleColor"
    />

    <!-- Grid de tarjetas -->
    <div class="c-feature-grid__inner o-container">
      <ul
        ref="gridRef"
        class="c-feature-grid__list"
        :class="{ 'is-visible': isVisible }"
      >
        <li
          v-for="(item, i) in features"
          :key="item.title"
          class="c-feature-grid__item"
          :style="{ '--delay': `${i * 0.07}s` }"
        >
          <!-- Icono con fondo redondeado -->
          <div class="c-feature-grid__icon-wrap">
            <CIcon
              :icon="item.icon"
              :color="item.iconColor ?? 'primary'"
              size="22px"
              :is-wrapper="true"
            />
          </div>

          <h3 class="c-feature-grid__item-title">{{ item.title }}</h3>
          <p  class="c-feature-grid__item-desc">{{ item.description }}</p>
        </li>
      </ul>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-feature-grid {
    padding-bottom: 80px;

    &__inner {
      padding-top: 0;
    }

    // ── Grid responsive ───────────────────────────────────
    &__list {
      list-style: none;
      margin: 0;
      padding: 0;
      display: grid;
      grid-template-columns: 1fr;
      gap: 40px 32px;

      @include from-sm {
        grid-template-columns: repeat(2, 1fr);
      }

      @include from-md {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    // ── Tarjeta individual ────────────────────────────────
    &__item {
      padding: 28px;
      border: 1px solid var(--color-primary-dark);
      border-radius: 12px;
      background-color: var(--color-black);
      opacity: 0;
      transform: translateY(20px);
      transition:
        opacity 0.5s ease var(--delay, 0s),
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
    }

    // ── Icono ─────────────────────────────────────────────
    &__icon-wrap {
      margin-bottom: 20px;

      // Sobreescribir el tamaño del wrapper heredado de CIcon
      :deep(.c-card__wrapper-icon) {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 12px;
      }
    }

    // ── Textos ────────────────────────────────────────────
    &__item-title {
      font-size: 1rem;
      font-weight: 700;
      color: var(--color-white);
      margin: 0 0 10px;
    }

    &__item-desc {
      font-size: 0.9rem;
      line-height: 1.65;
      color: var(--color-gray-dark);
      margin: 0;
    }
  }
</style>
