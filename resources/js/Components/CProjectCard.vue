<script setup lang="ts">
  import { computed } from 'vue'

  const props = withDefaults(
    defineProps<{
      title       : string
      description : string
      image       : string
      category    : string
      link        : string
      tags?       : string[]
      alt?        : string
    }>(),
    {
      tags: () => [],
      alt : undefined,
    }
  )

  const isComingSoon = computed(() => !props.link || props.link === '#')
</script>

<template>
  <article class="c-project-card" :class="{ 'c-project-card--coming-soon': isComingSoon }">
    <component
      :is="isComingSoon ? 'div' : 'a'"
      v-bind="isComingSoon ? {} : { href: link, target: '_blank', rel: 'noopener noreferrer' }"
      class="c-project-card__link"
    >
      <!-- Imagen -->
      <div class="c-project-card__image-wrap">
        <img
          :src="image"
          :alt="alt ?? title"
          class="c-project-card__image"
          loading="lazy"
        />

        <div class="c-project-card__overlay">
          <span class="c-project-card__overlay-label">
            <template v-if="!isComingSoon">
              Ver proyecto
              <svg viewBox="0 0 20 20" fill="none" width="15" height="15">
                <path
                  d="M4 10h12M10 4l6 6-6 6"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </template>
            <template v-else>Próximamente</template>
          </span>
        </div>
      </div>

      <!-- Contenido -->
      <div class="c-project-card__body">
        <span class="c-project-card__category">{{ category }}</span>
        <h3 class="c-project-card__title o-font-display-3">{{ title }}</h3>
        <p class="c-project-card__description o-font-display-body">{{ description }}</p>
        <div v-if="tags.length" class="c-project-card__tags o-font-display-caption">
          <span v-for="tag in tags" :key="tag" class="c-project-card__tag">{{ tag }}</span>
        </div>
      </div>
    </component>
  </article>
</template>

<style lang="scss" scoped>
  .c-project-card {
    height: 100%;

    &__link {
      display: flex;
      flex-direction: column;
      height: 100%;
      border: 1px solid var(--color-border-subtle);
      border-radius: 12px;
      overflow: hidden;
      background: var(--color-primary-dark);
      text-decoration: none;
      color: inherit;
      transition:
        border-color 0.25s ease,
        box-shadow 0.25s ease,
        transform 0.25s ease;

      &:hover {
        border-color: var(--color-primary);
        box-shadow: 0 8px 32px rgba(0, 107, 223, 0.15);
        transform: translateY(-4px);

        .c-project-card__image {
          transform: scale(1.05);
        }

        .c-project-card__overlay {
          opacity: 1;
        }
      }
    }

    &__image-wrap {
      position: relative;
      aspect-ratio: 16 / 9;
      overflow: hidden;
    }

    &__image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.5s ease;
    }

    &__overlay {
      position: absolute;
      inset: 0;
      background: rgba(8, 8, 10, 0.65);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s ease;
      backdrop-filter: blur(3px);
    }

    &__overlay-label {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #ffffff;
      font-size: 0.9375rem;
      font-weight: 600;
      letter-spacing: 0.02em;
      padding: 10px 22px;
      border: 1.5px solid rgba(255, 255, 255, 0.45);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(4px);
      transition:
        border-color 0.2s,
        background 0.2s;

      &:hover {
        border-color: rgba(255, 255, 255, 0.85);
        background: rgba(255, 255, 255, 0.18);
      }
    }

    &__body {
      padding: 1.25rem;
      display: flex;
      flex-direction: column;
      flex: 1;
      gap: 0.5rem;
    }

    &__category {
      display: inline-block;
      align-self: flex-start;
      font-size: 0.6875rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--color-primary);
      background: rgba(0, 107, 223, 0.1);
      border: 1px solid rgba(0, 107, 223, 0.25);
      border-radius: 4px;
      padding: 3px 10px;
    }

    &__title {
      color: var(--color-white);
      margin-top: 0.25rem;
    }

    &__description {
      color: var(--color-gray-dark);
      flex: 1;
    }

    &__tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.375rem;
      margin-top: 0.5rem;
    }

    &__tag {
      color: var(--color-primary);
      background: var(--color-primary-dark);
      border: 1px solid var(--color-primary);
      padding: 2px 10px;
      border-radius: 6px;
    }

    &__badge-soon {
      position: absolute;
      top: 12px;
      right: 12px;
      font-size: 0.6875rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      color: #ffffff;
      background: var(--color-gradient);
      padding: 4px 12px;
      border-radius: 20px;
      pointer-events: none;
    }

    // Sin link: sin hover interactivo
    &--coming-soon {
      .c-project-card__link {
        cursor: default;

        &:hover {
          transform: none;
          border-color: var(--color-border-subtle);
          box-shadow: none;

          .c-project-card__image {
            transform: none;
          }
        }
      }
    }
  }
</style>
