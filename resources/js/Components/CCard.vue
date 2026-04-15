<script setup lang="ts">
  import { icons } from '@assets/icons'

  const props = defineProps<{
    logo?: string
    overline?: string
    title: string
    content: string
    image: string
    iconInImage?: keyof typeof icons
  }>()
</script>
<template>
  <div class="c-card">
    <div class="c-card__inner">
      <div class="c-card__wrapper-image">
        <img :src="props.image" alt="" class="c-card__image" />
        <div v-if="props.iconInImage" class="c-card__wrapper-icon">
          <component :is="icons[props.iconInImage]" class="c-card__icon" aria-hidden="true" />
        </div>
      </div>
      <div class="c-card__wrapper-content">
        <h2 class="c-card__title">{{ props.title }}</h2>
        <p class="c-card__overline">{{ props.overline }}</p>
        <p class="c-card__content">{{ props.content }}</p>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .c-card {
    &__inner {
      border: 1px solid var(--color-gray-dark);
      border-radius: 8px;
      background-color: var(--color-white);
      overflow: hidden;
      transition: border-color 0.3s ease;
    }

    &__inner:hover {
      border-color: var(--color-secondary);
    }

    &__inner:hover &__image {
      transform: scale(1.09);
    }

    &__title {
      font-size: 1.5rem;
      margin-bottom: 10px;
      color: var(--color-gray);
    }
    &__overline {
      font-size: 0.875rem;
      color: var(--color-gray-dark);
    }
    &__content {
      font-size: 1rem;
      color: var(--color-gray-dark);
      margin-bottom: 10px;
    }
    &__wrapper-image {
      width: 100%;
      height: 200px;
      overflow: hidden;
      position: relative;
    }
    &__image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transform: scale(1.01);
      transition: all 0.5s ease;
    }
    &__wrapper-content {
      padding: 20px;
      background-color: var(--color-black);
    }
    &__wrapper-icon {
      position: absolute;
      bottom: 10px;
      left: 10px;
      padding: 10px;
      border: 1px solid var(--color-gray-dark);
      border-radius: 8px;
    }
    &__icon {
      width: 24px;
      height: 24px;
    }
  }
</style>
