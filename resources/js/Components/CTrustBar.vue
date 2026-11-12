<script setup lang="ts">
  import { icons } from '@assets/icons'
  import CIcon from './CIcon.vue'

  defineProps<{
    items: { icon: keyof typeof icons; label: string }[]
  }>()
</script>

<template>
  <div class="c-trust-bar">
    <div class="c-trust-bar__inner o-container">
      <ul class="c-trust-bar__list">
        <li v-for="item in items" :key="item.label" class="c-trust-bar__item">
          <CIcon :icon="item.icon" size="20px" color="primary" :is-wrapper="false" />
          <span class="c-trust-bar__label">{{ item.label }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-trust-bar {
    border-top: 1px solid var(--color-border-subtle);
    border-bottom: 1px solid var(--color-border-subtle);
    padding: 20px 0;

    &__list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px 32px;
    }

    &__item {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    &__label {
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--color-gray);
      white-space: nowrap;
    }
  }
</style>
