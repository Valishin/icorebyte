<script setup lang="ts">
  import { ColorType } from '@/types'
  import { icons } from '@assets/icons'
  import CIcon from './CIcon.vue'

  const props = defineProps<{
    title: string
    type?: 'primary' | 'secondary' | 'tertiary'
    icon?: keyof typeof icons
    color?: ColorType
  }>()

  const emit = defineEmits<{
    click: []
  }>()

  const handleClick = () => {
    emit('click')
  }
</script>
<template>
  <button
    class="c-button"
    :class="{
      'c-button--gradient': props.color === 'gradient'
    }"
    @click="handleClick"
    :style="{
      ...(props.color && props.color !== 'gradient'
        ? { backgroundColor: `var(--color-${props.color})` }
        : {})
    }"
  >
    <div v-if="props.icon" class="c-button__icon">
      <CIcon :icon="props.icon" />
    </div>
    <div class="c-button__title">{{ props.title }}</div>
    <CIcon :icon="'IconLink'" />
  </button>
</template>
<style lang="scss" scoped>
  .c-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    color: var(--color-white);
    cursor: pointer;
    transition: all 0.3s ease;

    &:hover {
      background-color: var(--color-primary-dark);
    }

    &__icon {
      display: flex;
      align-items: center;
    }

    &--gradient {
      background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
      position: relative;

      &::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(
          135deg,
          var(--color-primary-dark),
          var(--color-secondary-dark, var(--color-secondary))
        );
        opacity: 0;
        transition: opacity 0.3s ease;
        border-radius: 8px;
        z-index: -1;
      }

      &:hover::before {
        opacity: 1;
      }
    }
  }
</style>
