<script setup lang="ts">
  import { Link } from '@inertiajs/vue3'

  defineProps<{
    title: string
    lastUpdated?: string
  }>()
</script>

<template>
  <section class="c-legal-hero">
    <div class="c-legal-hero__inner o-container">
      <nav class="c-legal-hero__breadcrumb" aria-label="Breadcrumb">
        <Link href="/" class="c-legal-hero__breadcrumb-link">Inicio</Link>
        <span class="c-legal-hero__breadcrumb-sep" aria-hidden="true">›</span>
        <span class="c-legal-hero__breadcrumb-current">{{ title }}</span>
      </nav>
      <h1 class="c-legal-hero__title">{{ title }}</h1>
      <p v-if="lastUpdated" class="c-legal-hero__meta">
        Última actualización: <time>{{ lastUpdated }}</time>
      </p>
    </div>
  </section>
</template>

<style lang="scss" scoped>
  .c-legal-hero {
    border-bottom: 1px solid var(--color-border-subtle);
    padding: 3rem 0 2.5rem;

    @include from-md {
      padding: 4rem 0 3rem;
    }

    &__breadcrumb {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      margin-bottom: 1.25rem;
    }

    &__breadcrumb-link {
      font-size: 0.8125rem;
      color: var(--color-text-muted);
      text-decoration: none;
      transition: color 0.2s;

      &:hover {
        color: var(--color-primary);
      }
    }

    &__breadcrumb-sep {
      font-size: 0.8125rem;
      color: var(--color-text-subtle);
    }

    &__breadcrumb-current {
      font-size: 0.8125rem;
      color: var(--color-primary);
    }

    &__title {
      font-size: clamp(1.75rem, 4vw, 2.5rem);
      font-weight: 700;
      color: var(--color-white);
      margin: 0 0 0.75rem;
      line-height: 1.2;
    }

    &__meta {
      font-size: 0.875rem;
      color: var(--color-text-subtle);
      margin: 0;

      time {
        color: var(--color-text-muted);
      }
    }
  }
</style>
