<script setup lang="ts">
  import { usePage } from '@inertiajs/vue3'
  import { computed, ref, watch } from 'vue'

  const page = usePage<{ flash: { success?: string; error?: string } }>()

  const visible = ref(false)
  const type    = ref<'success' | 'error'>('success')
  const message = ref('')
  const barKey  = ref(0)
  let timer: ReturnType<typeof setTimeout>

  const flash = computed(() => page.props.flash)

  watch(flash, (val) => {
    if (val?.success) show('success', val.success)
    else if (val?.error) show('error', val.error)
  }, { deep: true })

  const show = (t: 'success' | 'error', msg: string) => {
    clearTimeout(timer)
    type.value    = t
    message.value = msg
    visible.value = true
    barKey.value++
    timer = setTimeout(() => { visible.value = false }, 3000)
  }

  const close = () => {
    clearTimeout(timer)
    visible.value = false
  }
</script>

<template>
  <Transition name="toast">
    <div v-if="visible" class="c-toast" :class="`c-toast--${type}`">
      <div class="c-toast__body">
        <span class="c-toast__icon">{{ type === 'success' ? '✓' : '✕' }}</span>
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
      background: #0f1a0f;
      border: 1px solid #22c55e;

      .c-toast__icon { color: #22c55e; }
      .c-toast__bar  { background: #22c55e; }
    }

    &--error {
      background: #1a0f0f;
      border: 1px solid #ef4444;

      .c-toast__icon { color: #ef4444; }
      .c-toast__bar  { background: #ef4444; }
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
      color: var(--color-white);
      font-size: 0.9375rem;
      margin: 0;
      line-height: 1.4;
    }

    &__close {
      background: none;
      border: none;
      color: rgba(255, 255, 255, 0.4);
      cursor: pointer;
      font-size: 0.875rem;
      padding: 0;
      flex-shrink: 0;
      transition: color 0.2s;

      &:hover { color: var(--color-white); }
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
