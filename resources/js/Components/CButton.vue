<script setup lang="ts">
  import { ColorType } from '@/types'
  import { icons } from '@assets/icons'
  import CIcon from './CIcon.vue'

  const props = defineProps<{
    title: string
    type?: 'primary' | 'secondary' | 'tertiary'
    nativeType?: 'submit' | 'button' | 'reset'
    icon?: keyof typeof icons
    color?: ColorType
    disabled?: boolean
    loading?: boolean
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
      'c-button--gradient': props.color === 'gradient',
      'c-button--disabled': props.disabled || props.loading
    }"
    :type="props.nativeType ?? 'button'"
    :disabled="props.disabled || props.loading"
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
    <div class="c-button__title">{{ props.loading ? 'Enviando...' : props.title }}</div>
    <CIcon v-if="!props.loading" :icon="'IconLink'" />
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

    .theme-light & {
      color: var(--color-black);
    }

    &:hover {
      background-color: var(--color-secondary);

      .theme-light & {
        opacity: 0.9;
        color: var(--color-white);
      }
    }

    &--disabled {
      opacity: 0.5;
      cursor: not-allowed;
      pointer-events: none;
    }

    &__icon {
      display: flex;
      align-items: center;
    }

    &--gradient {
      background: var(--color-primary);
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
