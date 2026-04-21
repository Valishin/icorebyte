<script setup lang="ts">
  import type { ColorType } from '@/types'
  import { icons } from '@assets/icons'

  const props = defineProps<{
    icon: keyof typeof icons
    color?: ColorType
    size?: string
    isWrapper?: boolean
  }>()
</script>
<template>
  <div
    v-if="props.icon"
    class="c-card__wrapper-icon"
    :style="{
      ...(props.isWrapper
        ? {
            background: 'var(--color-primary-dark)',
            padding: '8px',
            border: '1px solid var(--color-primary)',
            borderRadius: '8px'
          }
        : {})
    }"
  >
    <component
      class="c-card__icon"
      :is="icons[props.icon]"
      :style="{
        ...(props.color ? { color: `var(--color-${props.color})` } : {}),
        '--icon-size': props.size
      }"
      aria-hidden="true"
    />
  </div>
</template>
<style lang="scss" scoped>
  svg {
    width: var(--icon-size, 24px);
    height: var(--icon-size, 24px);
  }
</style>
