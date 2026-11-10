<script setup lang="ts">
  import { icons } from '@assets/icons'
  import CIcon from './CIcon.vue'

  defineProps<{
    title      : string
    type      ?: 'primary' | 'secondary' | 'tertiary'
    nativeType?: 'submit' | 'button' | 'reset'
    icon      ?: keyof typeof icons
    disabled  ?: boolean
    loading   ?: boolean
  }>()

  const emit = defineEmits<{ click: [] }>()
</script>

<template>
  <button
    class="c-button"
    :class="[
      `c-button--${type ?? 'primary'}`,
      { 'c-button--disabled': disabled || loading }
    ]"
    :type="nativeType ?? 'button'"
    :disabled="disabled || loading"
    @click="emit('click')"
  >
    <div v-if="icon" class="c-button__icon">
      <CIcon :icon="icon" />
    </div>
    <span class="c-button__title">{{ loading ? 'Enviando...' : title }}</span>
  </button>
</template>

<style lang="scss" scoped>
  .c-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 12px 24px;
    border: 1px solid transparent;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition:
      background-color 0.22s ease,
      color            0.22s ease,
      border-color     0.22s ease,
      transform        0.18s ease;

    &:hover { transform: translateY(-1px); }
    &:active { transform: translateY(0); }

    // ── Primary: fondo sólido, hover → secundario ─────────
    &--primary {
      background-color: var(--color-primary);
      color: var(--color-white);
      border-color: var(--color-primary);

      &:hover {
        background-color: var(--color-secondary, var(--color-primary));
        border-color: var(--color-secondary, var(--color-primary));
      }
    }

    // ── Secondary: outline, hover → relleno ───────────────
    &--secondary {
      background-color: transparent;
      color: var(--color-primary);
      border-color: var(--color-primary);

      &:hover {
        background-color: var(--color-primary);
        color: var(--color-white);
      }
    }

    // ── Tertiary: sin fondo, solo texto ───────────────────
    &--tertiary {
      background-color: transparent;
      color: var(--color-gray-dark);
      border-color: transparent;

      &:hover { color: var(--color-white); }

      .theme-light &:hover { color: var(--color-black); }
    }

    // ── Disabled ──────────────────────────────────────────
    &--disabled {
      opacity: 0.45;
      cursor: not-allowed;
      pointer-events: none;
      transform: none !important;
    }

    &__icon {
      display: flex;
      align-items: center;
    }
  }
</style>
