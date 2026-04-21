<script setup lang="ts">
  import { ColorType } from '@/types'
  import { icons } from '@assets/icons'
  import CIcon from './CIcon.vue'

  const props = defineProps<{
    icon: keyof typeof icons
    title: string
    description: string
    iconColor?: ColorType
    iconSize?: string
  }>()
</script>
<template>
  <div class="c-icon">
    <div class="c-icon__inner">
      <CIcon :icon="props.icon" :color="iconColor" :size="iconSize" :is-wrapper="true" />
      <div class="c-icon__wrapper-content">
        <h3 class="c-icon__title">{{ props.title }}</h3>
        <p class="c-icon__description">{{ props.description }}</p>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .c-icon {
    &__inner {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding-bottom: 10px;
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
