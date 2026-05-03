<script setup lang="ts">
  import { ColorType } from '@/types'
  import { icons } from '@assets/icons'
  import CIcon from './CIcon.vue'

  const props = defineProps<{
    icon: keyof typeof icons
    title: string
    description?: string
    iconColor?: ColorType
    iconSize?: string
    isWrapper?: boolean
  }>()
</script>
<template>
  <div class="c-place-card">
    <div class="c-place-card__inner" :class="props.isWrapper ? 'c-place-card__is-wrapper' : ''">
      <CIcon :icon="props.icon" :color="iconColor" :size="iconSize" :is-wrapper="true" />
      <div class="c-place-card__wrapper-content">
        <h3 class="c-place-card__title">{{ props.title }}</h3>
        <p class="c-place-card__description">{{ props.description }}</p>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .c-place-card {
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
    }
    &__title {
      color: var(--color-white);
    }
    &__description {
      color: var(--color-gray-dark);
    }
  }
</style>
