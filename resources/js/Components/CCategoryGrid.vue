<script setup lang="ts">
  import { icons } from '@assets/icons'
  import CIcon from './CIcon.vue'

  const props = defineProps<{
    title: string
    items: Array<{
      icon: keyof typeof icons
      title: string
      enlace: string
    }>
  }>()
</script>
<template>
  <div class="c-category-grid">
    <h2 class="c-category-grid__title o-font-display-h3">{{ props.title }}</h2>
    <ul>
      <li v-for="item in props.items" :key="item.title">
        <div class="c-category-grid__wrapper">
          <CIcon :icon="item.icon" :color="'primary'" />
          <p class="c-category-grid__item-title">{{ item.title }}</p>
        </div>
      </li>
    </ul>
  </div>
</template>
<style lang="scss" scoped>
  .c-category-grid {
    ul {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 1rem;
      list-style: none;
      padding: 0;
    }
    li {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      padding: 10px 15px;
      border: 1px solid var(--color-primary);
      border-radius: 8px;
      background-color: var(--color-black);
    }
    a {
      color: var(--color-primary);
      text-decoration: none;
    }
    &__wrapper {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    &__title {
      padding-bottom: 20px;
      color: var(--color-gray-dark);
      text-transform: uppercase;
    }
    &__item-title {
      color: var(--color-white);
    }
  }
</style>
