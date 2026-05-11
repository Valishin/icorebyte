<script setup lang="ts">
  import { useDevice } from '@/composables/useDevice'
  import { ColorType } from '@/types'
  import { icons } from '@assets/icons'
  import { ref } from 'vue'
  import CIcon from './CIcon.vue'

  const props = defineProps<{
    icon: keyof typeof icons
    title: string
    description?: string
    iconColor?: ColorType
    iconSize?: string
    isWrapper?: boolean
    class?: string
  }>()

  const { isMobile } = useDevice()
  const open = ref(false)

  const toggle = () => {
    if (isMobile.value) open.value = !open.value
  }
</script>

<template>
  <div class="c-place-card" :class="{ 'c-place-card--collapsible': isMobile }" @click="toggle">
    <div class="c-place-card__inner" :class="props.isWrapper ? 'c-place-card__is-wrapper' : ''">
      <CIcon :icon="props.icon" :color="iconColor" :size="iconSize" :is-wrapper="true" />
      <div class="c-place-card__wrapper-content">
        <h3 class="c-place-card__title">{{ props.title }}</h3>
        <Transition name="desc-reveal">
          <p v-if="!isMobile || open" class="c-place-card__description">{{ props.description }}</p>
        </Transition>
      </div>
      <span
        v-if="isMobile"
        class="c-place-card__chevron"
        :class="{ 'c-place-card__chevron--open': open }"
      >
        ›
      </span>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-place-card {
    &--collapsible {
      cursor: pointer;
      user-select: none;
    }

    &__inner {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding-bottom: 10px;

      &.c-place-card__is-wrapper {
        border: 1px solid var(--color-gray-dark);
        padding: 15px;
        border-radius: 8px;
        background-color: var(--color-primary-dark);

        .theme-light & {
          border: 0px solid transparent;
        }
      }
    }

    &__wrapper-icon {
      padding: 8px;
      border: 1px solid var(--color-primary);
      border-radius: 8px;
      background-color: var(--color-primary-dark);
    }

    &__wrapper-content {
      display: flex;
      flex-direction: column;
      flex: 1;
    }

    &__title {
      color: var(--color-white);
    }

    &__description {
      color: var(--color-gray-dark);
    }

    &__chevron {
      font-size: 1.2rem;
      color: var(--color-gray-dark);
      transition: transform 0.25s ease;
      line-height: 1;

      &--open {
        transform: rotate(90deg);
      }
    }
  }

  .desc-reveal-enter-active,
  .desc-reveal-leave-active {
    transition:
      max-height 0.3s ease,
      opacity 0.3s ease;
    overflow: hidden;
    max-height: 120px;
  }

  .desc-reveal-enter-from,
  .desc-reveal-leave-to {
    max-height: 0;
    opacity: 0;
  }
</style>
