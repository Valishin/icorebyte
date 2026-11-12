<script setup lang="ts">
  defineProps<{
    title: string
    subtitle?: string
    ctaLabel: string
    ctaHref: string
  }>()
</script>

<template>
  <div class="c-cta-banner">
    <div class="c-cta-banner__inner o-container">
      <h2 class="c-cta-banner__title">{{ title }}</h2>
      <p v-if="subtitle" class="c-cta-banner__subtitle">{{ subtitle }}</p>
      <a :href="ctaHref" class="c-cta-banner__cta">{{ ctaLabel }}</a>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-cta-banner {
    background: linear-gradient(135deg, var(--color-primary) 0%, #0047a8 100%);
    padding: 72px 0;

    &__inner {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      gap: 20px;
    }

    &__title {
      font-size: clamp(1.6rem, 4vw, 2.4rem);
      font-weight: 800;
      color: #fff;
      margin: 0;
      line-height: 1.2;
    }

    &__subtitle {
      font-size: 1rem;
      color: rgba(255, 255, 255, 0.82);
      margin: 0;
      max-width: 520px;
      line-height: 1.6;
    }

    &__cta {
      display: inline-flex;
      align-items: center;
      padding: 14px 32px;
      border-radius: 999px;
      background: #fff;
      color: var(--color-primary);
      font-weight: 700;
      font-size: 0.95rem;
      text-decoration: none;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      margin-top: 8px;

      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      }

      &:focus-visible {
        outline: 3px solid #fff;
        outline-offset: 3px;
      }
    }
  }
</style>
