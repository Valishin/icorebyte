<script setup lang="ts">
  import CIcon from './CIcon.vue'

  const props = defineProps<{
    title: string
    description: string
    items: string[]
    image: string
  }>()
</script>
<template>
  <div class="c-service-card">
    <div class="c-service-card__inner">
      <div class="c-service-card__wrapper-content">
        <div class="c-service-card__wrapper-card">
          <div class="c-service-card__box-image">
            <img
              class="c-service-card__image"
              :src="props.image"
              alt="Imagen del servicio de reparación de ordenadores"
            />
          </div>
          <div class="c-service-card__box-content">
            <div class="c-service-card__wrapper-title">
              <h3 class="c-service-card__title o-font-display-3">{{ props.title }}</h3>
              <CIcon :icon="'IconLink'" :color="'gray-dark'" />
            </div>
            <div class="c-service-card__wrapper-description">
              <p class="c-service-card__description o-font-display-body">
                {{ props.description }}
              </p>
            </div>
            <div class="c-service-card__wrapper-items o-font-display-caption">
              <div v-for="value in props.items" :key="value">
                <div class="c-service-card__item">
                  <p>{{ value }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .c-service-card {
    &__wrapper-content {
    }
    &__wrapper-card {
      background-color: var(--color-black);
      border: 2px solid var(--color-primary-dark);
      border-radius: 0.5rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    &__box-image {
      margin-bottom: 1.5rem;
      position: relative;
    }
    &__image {
      border-radius: 8px 8px 0 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      overflow: hidden;
    }
    &__box-content {
      display: flex;
      flex-direction: column;
      color: var(--color-white);
      padding: 0px 15px 15px 15px;
    }
    &__wrapper-title {
      display: flex;
      justify-content: space-between;
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
    }
  }
</style>
