<script setup lang="ts">
  import { useToast } from '@/composables/useToast'
  import { usePage } from '@inertiajs/vue3'
  import { computed, watch } from 'vue'

  const { visible, type, message, barKey, show, close } = useToast()

  const page = usePage<{ flash: { success?: string; error?: string } }>()
  const flash = computed(() => page.props.flash)

  watch(flash, (val) => {
    if (val?.success) show('success', val.success)
    else if (val?.error) show('error', val.error)
  }, { deep: true })
</script>

<template>
  <Transition name="toast">
    <div v-if="visible" class="c-toast" :class="`c-toast--${type}`">
      <div class="c-toast__body">
        <span class="c-toast__icon">{{ type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ' }}</span>
        <p class="c-toast__message">{{ message }}</p>
        <button class="c-toast__close" @click="close" aria-label="Cerrar">✕</button>
      </div>
      <div class="c-toast__bar" :key="barKey"></div>
    </div>
  </Transition>
</template>

<style lang="scss" scoped>
  .c-toast {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    min-width: 320px;
    max-width: 480px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);

    &--success {
      background: var(--color-success-dark);
      border: 1px solid var(--color-success);
      .c-toast__icon { color: var(--color-success); }
      .c-toast__bar  { background: var(--color-success); }
    }

    &--error {
      background: var(--color-error-dark);
      border: 1px solid var(--color-error);
      .c-toast__icon { color: var(--color-error); }
      .c-toast__bar  { background: var(--color-error); }
    }

    &--info {
      background: var(--color-info-dark);
      border: 1px solid var(--color-info);
      .c-toast__icon { color: var(--color-info); }
      .c-toast__bar  { background: var(--color-info); }
    }

    &__body {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 1rem 1.25rem;
    }

    &__icon {
      font-size: 1.1rem;
      font-weight: 700;
      flex-shrink: 0;
    }

    &__message {
      flex: 1;
      color: var(--color-text);
      font-size: 0.9375rem;
      margin: 0;
      line-height: 1.4;
    }

    &__close {
      background: none;
      border: none;
      color: var(--color-gray-dark);
      cursor: pointer;
      font-size: 0.875rem;
      padding: 0;
      flex-shrink: 0;
      transition: color 0.2s;
      &:hover { color: var(--color-text); }
    }

    &__bar {
      height: 3px;
      width: 100%;
      animation: toast-progress 3s linear forwards;
    }
  }

  @keyframes toast-progress {
    from { width: 100%; }
    to   { width: 0%; }
  }

  .toast-enter-active,
  .toast-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
  }

  .toast-enter-from,
  .toast-leave-to {
    transform: translateX(-50%) translateY(1rem);
    opacity: 0;
  }

  .toast-enter-to,
  .toast-leave-from {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
  }
</style>
