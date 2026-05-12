<script setup lang="ts">
  import CIcon from './CIcon.vue'

  const props = withDefaults(
    defineProps<{
      title       : string
      description : string
      image       : string
      alt?        : string       // texto alternativo de la imagen (default: title)
      items?      : string[]     // etiquetas/tags — si no se pasa no se renderiza la sección
      link?       : string       // URL — si se pasa la card es un <a> y muestra el icono enlace
    }>(),
    {
      alt  : undefined,
      items: undefined,
      link : undefined,
    }
  )
</script>

<template>
  <div class="c-service-card">
    <div class="c-service-card__inner">
      <div class="c-service-card__wrapper-content">
        <!--
          Si hay link la card es un <a> con hover/cursor de enlace.
          Si no, un <div> normal sin semántica de enlace.
        -->
        <component
          :is="props.link ? 'a' : 'div'"
          :href="props.link ?? undefined"
          class="c-service-card__wrapper-card"
          :class="{ 'c-service-card__wrapper-card--link': props.link }"
        >
          <div class="c-service-card__box-image">
            <img
              class="c-service-card__image"
              :src="props.image"
              :alt="props.alt ?? props.title"
            />
          </div>

          <div class="c-service-card__box-content">
            <div class="c-service-card__wrapper-title">
              <h3 class="c-service-card__title o-font-display-3">{{ props.title }}</h3>
              <!-- Icono de enlace solo cuando la card tiene link -->
              <CIcon v-if="props.link" :icon="'IconLink'" :color="'gray-dark'" />
            </div>

            <div class="c-service-card__wrapper-description">
              <p class="c-service-card__description o-font-display-body">
                {{ props.description }}
              </p>
            </div>

            <!-- Tags/items — solo se renderizan si se proporcionan -->
            <div v-if="props.items?.length" class="c-service-card__wrapper-items o-font-display-caption">
              <div v-for="value in props.items" :key="value" class="c-service-card__item">
                <p>{{ value }}</p>
              </div>
            </div>
          </div>
        </component>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-service-card {
    height: 100%;

    &__inner {
      height: 100%;
    }

    &__wrapper-content {
      height: 100%;
    }

    &__wrapper-card {
      background-color: var(--color-black);
      border: 2px solid var(--color-primary-dark);
      border-radius: 0.5rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
      height: 100%;
      text-decoration: none;     // por si es <a>
      color: inherit;

      // Cursor y hover de enlace solo cuando tiene link
      &--link {
        cursor: pointer;

        &:hover .c-service-card__image {
          transform: scale(1.06);
        }
      }

      // Sin link: hover también (pero sin cursor pointer)
      &:not(&--link):hover .c-service-card__image {
        transform: scale(1.06);
      }
    }

    &__box-image {
      margin-bottom: 1.5rem;
      position: relative;
      overflow: hidden;
      border-radius: 8px 8px 0 0;
      width: 100%;
    }

    &__image {
      border-radius: 8px 8px 0 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.5s ease;
    }

    &__box-content {
      display: flex;
      flex-direction: column;
      flex: 1;
      color: var(--color-white);
      padding: 0 15px 15px;
      width: 100%;
    }

    &__wrapper-title {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    &__description {
      color: var(--color-gray-dark);
    }

    &__wrapper-items {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-top: 1.5rem;
    }

    &__item {
      background-color: var(--color-primary-dark);
      color: var(--color-primary);
      padding: 0.25rem 0.75rem;
      border-radius: 8px;
      border: 1px solid var(--color-primary);
      transition:
        background-color 0.3s ease,
        color 0.3s ease;

      &:hover {
        background-color: var(--color-primary);
        color: var(--color-white);

        .theme-light & { color: var(--color-black); }
      }
    }
  }
</style>
