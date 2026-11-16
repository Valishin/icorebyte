<script setup lang="ts">
  import { useInView } from '@/composables/useInView'

  interface Step {
    number: string
    title: string
    description: string
  }

  withDefaults(defineProps<{ steps?: Step[] }>(), {
    steps: () => [
      {
        number: '01',
        title: 'Diagnóstico',
        description: 'Analizamos tu dispositivo y detectamos el problema con precisión.',
      },
      {
        number: '02',
        title: 'Presupuesto sin compromiso',
        description: 'Te informamos del coste y tiempo estimado antes de empezar. Sin sorpresas.',
      },
      {
        number: '03',
        title: 'Reparación',
        description: 'Nuestros técnicos realizan la reparación con piezas de calidad.',
      },
      {
        number: '04',
        title: 'Entrega con garantía',
        description: 'Recibes tu dispositivo listo para usar, con garantía incluida.',
      },
    ],
  })

  const { el, isVisible } = useInView(0.1)
</script>

<template>
  <div class="c-process-steps">
    <div class="c-process-steps__inner o-container">
      <ol ref="el" class="c-process-steps__list" :class="{ 'is-visible': isVisible }">
        <li
          v-for="(step, i) in steps"
          :key="step.number"
          class="c-process-steps__item"
          :style="{ '--delay': `${i * 0.1}s` }"
        >
          <div class="c-process-steps__number">{{ step.number }}</div>
          <div class="c-process-steps__content">
            <h3 class="c-process-steps__title">{{ step.title }}</h3>
            <p class="c-process-steps__desc">{{ step.description }}</p>
          </div>
        </li>
      </ol>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-process-steps {
    padding-bottom: 80px;

    &__list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: grid;
      grid-template-columns: 1fr;
      gap: 32px;

      @include from-sm {
        grid-template-columns: repeat(2, 1fr);
      }

      @include from-md {
        grid-template-columns: repeat(4, 1fr);
        gap: 0;
      }
    }

    &__item {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 16px;
      position: relative;
      opacity: 0;
      transform: translateY(24px);
      transition: opacity 0.5s ease, transform 0.5s ease;
      transition-delay: var(--delay, 0s);

      @media (prefers-reduced-motion: reduce) {
        opacity: 1;
        transform: none;
        transition: none;
      }

      // Línea conectora entre pasos en desktop
      @include from-md {
        padding-right: 32px;

        &:not(:last-child)::after {
          content: '';
          position: absolute;
          top: 22px;
          right: 0;
          width: 32px;
          height: 2px;
          background: linear-gradient(90deg, var(--color-primary), transparent);
        }

        &:first-child {
          padding-left: 32px;

          &::before {
            content: '';
            position: absolute;
            top: 22px;
            left: 0;
            width: 32px;
            height: 2px;
            background: linear-gradient(270deg, var(--color-primary), transparent);
          }
        }
      }

      .is-visible & {
        opacity: 1;
        transform: none;
      }
    }

    &__number {
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--color-primary);
      line-height: 1;
      font-variant-numeric: tabular-nums;
      opacity: 0.9;
    }

    &__content {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    &__title {
      font-size: 1rem;
      font-weight: 700;
      color: var(--color-gray);
      margin: 0;
    }

    &__desc {
      font-size: 0.875rem;
      color: var(--color-gray-dark);
      margin: 0;
      line-height: 1.6;
    }
  }
</style>
