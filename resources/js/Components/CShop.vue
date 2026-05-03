<script setup lang="ts">
  import { images } from '@/constants/images'
  import CButton from './CButton.vue'
  import CCategoryGrid from './CCategoryGrid.vue'
  import CIcon from './CIcon.vue'
  import CPlaceCard from './CPlaceCard.vue'
  import CTitle from './CTitle.vue'
</script>
<template>
  <div class="c-shop">
    <div class="c-shop__inner">
      <CTitle
        :title="'Nuestra tienda de informática'"
        :description="'Ofrecemos una amplia gama de productos de informática y tecnología.'"
        :color="'secondary'"
      />
      <div class="c-shop__container o-container">
        <div class="c-shop__col o-col-12@md o-col-8@sm o-col-4@xs">
          <div class="c-shop__wrapper">
            <div class="c-shop__box-image">
              <img
                class="c-shop__image"
                :src="images.imageShop"
                alt="Imagen de la tienda de informática"
              />
            </div>
            <div class="c-shop__box-content">
              <div class="c-shop__wrapper-overline">
                <CIcon :icon="'IconShop'" :color="'secondary'" />
                <p class="c-shop__overline o-font-display-caption">Nuestra tienda</p>
              </div>
              <div class="c-shop__wrapper-title">
                <h2 class="c-shop__title o-font-display-3">
                  Visítanos en <span>nuestra tienda</span>
                </h2>
              </div>
              <div class="c-shop__wrapper-description">
                <p class="c-shop__description o-font-display-body">
                  Descubre nuestra amplia gama de productos de alta calidad. Desde componentes
                  informáticos hasta accesorios tecnológicos, tenemos todo lo que necesitas para
                  potenciar tu experiencia.
                </p>
              </div>
              <div class="c-shop__wrapper-info">
                <CPlaceCard
                  :icon="'IconLocation'"
                  :title="'Ubicación'"
                  :description="'Calle Falsa 123, Ciudad, País'"
                  :iconColor="'primary'"
                  :iconSize="'24px'"
                />
                <CPlaceCard
                  :icon="'IconTime'"
                  :title="'Horario de atención'"
                  :description="'Lunes a viernes: 9:00 - 18:00, Sábados: 10:00 - 14:00'"
                  :iconColor="'primary'"
                />
                <CPlaceCard
                  :icon="'IconPhone'"
                  :title="'+34 912 345 678'"
                  :description="'También por WhatsApp'"
                  :iconColor="'primary'"
                />
              </div>
              <CCategoryGrid
                title="Categorías de productos"
                :items="[
                  { icon: 'IconLaptop', title: 'Portátiles', enlace: '#' },
                  { icon: 'IconDesktop', title: 'Sobremesa', enlace: '#' },
                  { icon: 'IconElectronic', title: 'Componentes', enlace: '#' },
                  { icon: 'IconCable', title: 'Accesorios', enlace: '#' },
                  { icon: 'IconPrinter', title: 'Impresoras', enlace: '#' },
                  { icon: 'IconCartridge', title: 'Consumibles', enlace: '#' },
                  { icon: 'IconHarddisk', title: 'Almacenamiento', enlace: '#' },
                  { icon: 'IconSoftware', title: 'Software', enlace: '#' }
                ]"
              />
              <div class="c-shop__wrapper-button">
                <CButton title="Ir a la tienda" :icon="'IconShop'" :color="'gradient'" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .c-shop {
    &__inner {
      padding: 4rem 0;
    }
    &__wrapper {
      border: 1px solid var(--color-gray-dark);
      border-radius: 8px;
      display: flex;
      align-items: center;
    }
    &__box-content {
      padding: 3rem;
      width: 50%;
    }
    &__box-image {
      width: 50%;
      overflow: hidden;
      height: 100%;
    }
    &__image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 8px;
    }
    &__wrapper-info {
      margin-top: 1.5rem;
      padding-bottom: 34px;
      &::after {
        content: '';
        position: relative;
        display: block;
        width: 100%;
        height: 1px;
        bottom: -15px;
        background-color: var(--color-gray-dark);
      }
    }
    &__wrapper-overline {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding-bottom: 10px;
    }
    &__overline {
      color: var(--color-secondary);
    }
    &__title {
      margin-bottom: 1rem;
      color: var(--color-gray);
      span {
        color: var(--color-primary);
      }
    }
    &__description {
      color: var(--color-gray-dark);
    }
    &__wrapper-button {
      margin-top: 2rem;
      display: flex;
      justify-content: flex-end;
      align-items: center;
    }
  }
</style>
