<script setup lang="ts">
  const props = defineProps<{
    overline?: string
    title: string
    description?: string
    color?: 'primary' | 'secondary' | 'gradient'
  }>()
</script>
<template>
  <div class="c-title">
    <div class="c-title__inner" :class="props.color ? `color-${props.color}` : ''">
      <div class="c-title__wrapper-overline" v-if="props.overline">
        <p class="c-title__overline o-font-display-section">
          {{ props.overline }}
        </p>
      </div>
      <div class="c-title__wrapper-title">
        <h2 class="c-title__title o-font-display-2">
          {{ props.title }}
        </h2>
      </div>
      <div class="c-title__wrapper-description" v-if="props.description">
        <p class="c-title__description o-font-display-body">
          {{ props.description }}
        </p>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .c-title {
    padding-top: 120px;
    &__inner {
      text-align: center;
      padding-bottom: 68px;
      &.color-primary {
        color: var(--color-primary);
      }
      &.color-secondary {
        color: var(--color-secondary);
      }
      &.color-gradient {
        background: var(--color-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
    }
    &__wrapper-overline {
      padding-bottom: 24px;
    }
    &__overline {
      margin-bottom: 0.5rem;
      text-transform: uppercase;
      position: relative;

      &::after {
        content: '';
        position: relative;
        display: block;
        width: 40px;
        bottom: -8px;
        height: 2px;
        margin: 0 auto;

        .color-gradient & {
          background: var(--color-gradient);
        }
      }
      .color-gradient & {
        background: var(--color-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
    }
    &__title {
      margin-bottom: 1rem;
    }
    &__description {
      color: var(--color-white);
      background: var(--color-white);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
  }
</style>
