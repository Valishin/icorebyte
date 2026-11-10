<script setup lang="ts">
  import { nextTick, onMounted, ref } from 'vue'

  withDefaults(defineProps<{
    src      : string
    alt     ?: string
    objectFit?: 'cover' | 'contain' | 'fill' | 'scale-down' | 'none'
    lazy    ?: boolean
  }>(), {
    alt      : '',
    objectFit: 'cover',
    lazy     : true,
  })

  const loaded = ref(false)
  const imgEl  = ref<HTMLImageElement>()

  // Las imágenes cacheadas no disparan @load — comprobar .complete en mount
  onMounted(() => {
    nextTick(() => {
      if (imgEl.value?.complete) loaded.value = true
    })
  })
</script>

<template>
  <div class="c-image">
    <Transition name="c-image-fade">
      <div v-if="!loaded" class="c-image__skeleton" aria-hidden="true" />
    </Transition>
    <img
      ref="imgEl"
      class="c-image__img"
      :src="src"
      :alt="alt"
      :loading="lazy ? 'lazy' : 'eager'"
      :style="{ objectFit }"
      @load="loaded = true"
      @error="loaded = true"
    />
  </div>
</template>

<style lang="scss" scoped>
  .c-image {
    position: relative;
    overflow: hidden;
    display: block;
    width: 100%;
    height: 100%;

    // ── Skeleton shimmer ──────────────────────────────────
    &__skeleton {
      position: absolute;
      inset: 0;
      z-index: 1;
      background: var(--color-primary-dark);
      border-radius: inherit;

      &::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(
          90deg,
          transparent       0%,
          rgba(255,255,255,0.07) 50%,
          transparent       100%
        );
        transform: translateX(-100%);
        animation: c-img-shimmer 1.6s ease-in-out infinite;
      }
    }

    // ── Imagen — siempre visible; el skeleton la tapa mientras carga ──
    &__img {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  // ── Transición de salida del skeleton ─────────────────
  .c-image-fade-leave-active { transition: opacity 0.3s ease; }
  .c-image-fade-leave-to     { opacity: 0; }

  @keyframes c-img-shimmer {
    to { transform: translateX(100%); }
  }
</style>
