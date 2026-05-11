<script setup lang="ts">
  defineProps<{
    toc: { id: string; label: string }[]
  }>()
</script>

<template>
  <div class="p-legal">
    <div class="p-legal__inner o-container">

      <aside class="p-legal__toc">
        <p class="p-legal__toc-label">Contenido</p>
        <nav>
          <a
            v-for="item in toc"
            :key="item.id"
            :href="`#${item.id}`"
            class="p-legal__toc-link"
          >
            {{ item.label }}
          </a>
        </nav>
      </aside>

      <article class="p-legal__content">
        <slot />
      </article>

    </div>
  </div>
</template>

<style lang="scss">
  .p-legal {
    padding: 3rem 0 5rem;

    @include from-md {
      padding: 4rem 0 6rem;
    }

    &__inner {
      display: grid;
      grid-template-columns: 1fr;
      gap: 3rem;

      @include from-md {
        grid-template-columns: 220px 1fr;
        gap: 4rem;
        align-items: start;
      }
    }

    // Evita que el artículo desborde la celda de grid (min-width: auto por defecto)
    &__content {
      min-width: 0;
    }

    // ── TOC sidebar ───────────────────────────────────────
    &__toc {
      display: none;

      @include from-md {
        display: block;
        position: sticky;
        top: 88px;
      }
    }

    &__toc-label {
      font-size: 0.6875rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--color-text-subtle);
      margin: 0 0 0.875rem;
    }

    &__toc-link {
      display: block;
      font-size: 0.8125rem;
      color: var(--color-text-muted);
      text-decoration: none;
      padding: 0.35rem 0 0.35rem 0.75rem;
      border-left: 2px solid var(--color-border-subtle);
      transition:
        color 0.2s,
        border-color 0.2s;
      line-height: 1.4;

      &:hover {
        color: var(--color-white);
        border-left-color: var(--color-primary);
      }
    }

    // ── Secciones ─────────────────────────────────────────
    &__section {
      padding-bottom: 2.5rem;
      margin-bottom: 2.5rem;
      border-bottom: 1px solid var(--color-border-subtle);

      &:last-child {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
      }

      p {
        font-size: 0.9375rem;
        color: var(--color-text-muted);
        line-height: 1.8;
        margin: 0 0 1rem;

        &:last-child { margin-bottom: 0; }
      }
    }

    &__section-title {
      font-size: 1.125rem;
      font-weight: 700;
      color: var(--color-white);
      margin: 0 0 1.25rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid var(--color-border-subtle);

      @include from-md {
        font-size: 1.25rem;
      }
    }

    &__list {
      list-style: none;
      padding: 0;
      margin: 0.75rem 0 1rem;
      display: flex;
      flex-direction: column;
      gap: 0.625rem;

      li {
        font-size: 0.9375rem;
        color: var(--color-text-muted);
        line-height: 1.7;
        padding-left: 1.25rem;
        position: relative;

        &::before {
          content: '›';
          position: absolute;
          left: 0;
          color: var(--color-primary);
          font-weight: 700;
        }
      }
    }

    &__data-card {
      background: var(--color-primary-dark);
      border: 1px solid var(--color-border-subtle);
      border-radius: 0.75rem;
      padding: 1.25rem 1.5rem;
      margin-top: 1.25rem;

      .theme-light & {
        border-color: transparent;
        box-shadow: var(--box-shadow);
      }
    }

    &__data-list {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      margin: 0;
    }

    &__data-row {
      display: grid;
      grid-template-columns: 140px 1fr;
      gap: 0.5rem;
      font-size: 0.875rem;

      @include from-sm {
        grid-template-columns: 160px 1fr;
      }

      dt {
        font-weight: 600;
        color: var(--color-text-subtle);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding-top: 0.1rem;
      }

      dd {
        color: var(--color-text-muted);
        margin: 0;
        line-height: 1.5;
      }
    }

    // ── Tabla de cookies ──────────────────────────────────
    &__table-wrap {
      overflow-x: auto;
      margin-top: 1.25rem;
      border-radius: 0.75rem;
      border: 1px solid var(--color-border-subtle);

      .theme-light & {
        border-color: transparent;
        box-shadow: var(--box-shadow);
      }
    }

    &__table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.8125rem;

      thead tr {
        background: var(--color-primary-dark);
      }

      th {
        text-align: left;
        padding: 0.75rem 1rem;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--color-text-subtle);
        white-space: nowrap;
      }

      td {
        padding: 0.75rem 1rem;
        color: var(--color-text-muted);
        line-height: 1.5;
        border-top: 1px solid var(--color-border-subtle);
        vertical-align: top;
      }

      code {
        background: var(--color-primary-dark);
        color: var(--color-primary);
        padding: 0.15rem 0.45rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-family: monospace;
      }
    }

    // ── Badge tipo cookie ─────────────────────────────────
    &__badge {
      display: inline-block;
      padding: 0.2rem 0.6rem;
      border-radius: 99px;
      font-size: 0.6875rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;

      &--necessary {
        background: rgba(34, 197, 94, 0.12);
        color: var(--color-success);
      }

      &--preference {
        background: rgba(99, 102, 241, 0.12);
        color: var(--color-info);
      }
    }

    // ── Info box ─────────────────────────────────────────
    &__info-box {
      display: flex;
      gap: 0.75rem;
      background: var(--color-primary-dark);
      border: 1px solid var(--color-border-subtle);
      border-left: 3px solid var(--color-primary);
      border-radius: 0.5rem;
      padding: 1rem 1.25rem;
      margin-top: 1rem;

      .theme-light & {
        box-shadow: var(--box-shadow);
        border-color: transparent;
        border-left-color: var(--color-primary);
      }

      p {
        margin: 0 !important;
        font-size: 0.875rem;
      }
    }

    // ── Link inline ───────────────────────────────────────
    &__inline-link {
      color: var(--color-primary);
      text-decoration: none;
      border-bottom: 1px solid transparent;
      transition:
        color 0.2s,
        border-color 0.2s;

      &:hover {
        border-bottom-color: var(--color-primary);
      }
    }
  }
</style>
