<script setup lang="ts">
  import { ref } from 'vue'
  import CImage from './CImage.vue'
  import CTitle from './CTitle.vue'

  export interface TabShowcaseTitlePart {
    text  : string
    color?: 'primary' | 'secondary' | 'white'
  }

  export interface TabItem {
    label      : string   // etiqueta del tab
    title      : string   // título de la sección (izquierda)
    description: string   // descripción (izquierda)
    image      : string   // captura/imagen (derecha)
    imageAlt  ?: string
    url        ?: string  // URL que aparece en la barra del browser
  }

  const props = defineProps<{
    // Cabecera (CTitle)
    overline   ?: string
    title       : string
    description?: string
    titleColor ?: 'primary' | 'secondary' | 'gradient'

    // Tabs
    tabs: TabItem[]
  }>()

  const activeIndex = ref(0)
  const active      = () => props.tabs[activeIndex.value]

  const select = (i: number) => {
    if (i === activeIndex.value) return
    activeIndex.value = i
  }
</script>

<template>
  <div class="c-tab-showcase">

    <!-- ── Cabecera ───────────────────────────────────────── -->
    <CTitle
      :overline="overline"
      :title="title"
      :description="description"
      :color="titleColor"
    />

    <div class="c-tab-showcase__inner o-container">

      <!-- ── Tabs ──────────────────────────────────────────── -->
      <nav class="c-tab-showcase__tabs" role="tablist">
        <button
          v-for="(tab, i) in tabs"
          :key="tab.label"
          role="tab"
          :aria-selected="i === activeIndex"
          class="c-tab-showcase__tab"
          :class="{ 'is-active': i === activeIndex }"
          @click="select(i)"
        >
          {{ tab.label }}
        </button>
      </nav>

      <!-- ── Contenido animado ──────────────────────────────── -->
      <Transition name="tab" mode="out-in">
        <div
          :key="activeIndex"
          class="c-tab-showcase__content"
          role="tabpanel"
        >
          <!-- Izquierda: texto -->
          <div class="c-tab-showcase__text">
            <h3 class="c-tab-showcase__section-title">{{ active().title }}</h3>
            <p  class="c-tab-showcase__section-desc">{{ active().description }}</p>
          </div>

          <!-- Derecha: browser mockup + imagen -->
          <div class="c-tab-showcase__media">
            <div class="c-tab-showcase__browser">
              <div class="c-tab-showcase__browser-bar">
                <div class="c-tab-showcase__dots">
                  <span class="c-tab-showcase__dot c-tab-showcase__dot--red"    />
                  <span class="c-tab-showcase__dot c-tab-showcase__dot--yellow" />
                  <span class="c-tab-showcase__dot c-tab-showcase__dot--green"  />
                </div>
                <span v-if="active().url" class="c-tab-showcase__url">
                  {{ active().url }}
                </span>
              </div>
              <div class="c-tab-showcase__browser-body">
                <CImage
                  :src="active().image"
                  :alt="active().imageAlt ?? active().title"
                  class="c-tab-showcase__image"
                />
              </div>
            </div>
          </div>
        </div>
      </Transition>

    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-tab-showcase {
    padding-bottom: 80px;

    &__inner {
      display: flex;
      flex-direction: column;
      gap: 40px;
    }

    // ── Tabs ──────────────────────────────────────────────
    &__tabs {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: center;
    }

    &__tab {
      padding: 8px 20px;
      border-radius: 999px;
      border: 1px solid transparent;
      background: transparent;
      color: var(--color-gray-dark);
      font-size: 0.85rem;
      font-weight: 500;
      cursor: pointer;
      transition:
        color      0.2s ease,
        border-color 0.2s ease,
        background   0.2s ease;
      white-space: nowrap;

      &:hover:not(.is-active) {
        color: var(--color-white);
      }

      &.is-active {
        border-color: var(--color-primary);
        background: rgba(var(--color-primary-rgb, 59 130 246) / 0.08);
        color: var(--color-white);
      }
    }

    // ── Contenido ─────────────────────────────────────────
    &__content {
      display: grid;
      grid-template-columns: 1fr;
      gap: 32px;
      align-items: center;

      @include from-md {
        grid-template-columns: 1fr 1.6fr;
        gap: 56px;
      }
    }

    // ── Texto izquierda ───────────────────────────────────
    &__text {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    &__section-title {
      font-size: clamp(1.3rem, 3vw, 1.8rem);
      font-weight: 700;
      color: var(--color-white);
      margin: 0;
      line-height: 1.2;
    }

    &__section-desc {
      font-size: 0.95rem;
      line-height: 1.7;
      color: var(--color-gray-dark);
      margin: 0;
    }

    // ── Browser mockup ────────────────────────────────────
    &__browser {
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: #0d0f14;
      box-shadow:
        0 24px 80px rgba(0, 0, 0, 0.45),
        0 0 0 1px rgba(255, 255, 255, 0.04);
    }

    &__browser-bar {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 16px;
      background: #161820;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    &__dots {
      display: flex;
      gap: 6px;
      flex-shrink: 0;
    }

    &__dot {
      width: 11px;
      height: 11px;
      border-radius: 50%;

      &--red    { background: #ff5f57; }
      &--yellow { background: #febc2e; }
      &--green  { background: #28c840; }
    }

    &__url {
      font-size: 0.7rem;
      color: rgba(255, 255, 255, 0.3);
      letter-spacing: 0.02em;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    &__browser-body {
      line-height: 0; // evita espacio extra bajo la imagen
    }

    &__image {
      width: 100%;
      display: block;
      object-fit: cover;
    }
  }

  // ── Transición entre tabs ──────────────────────────────
  .tab-enter-active,
  .tab-leave-active {
    transition:
      opacity   0.25s ease,
      transform 0.25s ease;
  }

  .tab-enter-from {
    opacity: 0;
    transform: translateY(12px);
  }

  .tab-leave-to {
    opacity: 0;
    transform: translateY(-8px);
  }
</style>
