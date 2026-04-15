<script setup lang="ts">
  import { images } from '@/constants/images'
  import CCard from './CCard.vue'
  import CTitle from './CTitle.vue'
</script>
<template>
  <div class="c-service-list">
    <div class="c-service-list__inner">
      <div class="c-service-list__container o-container">
        <div class="c-service-list__col o-col-12@md o-col-8@sm o-col-4@xs">
          <CTitle
            overline="Servicios de reparación"
            title="Laboratorio de Reparación"
            description="Reparamos cualquier dispositivo con herramientas de última generación."
            :color="'gradient'"
          />
        </div>
        <div class="c-service-list__list">
          <CCard
            :image="images.imagenRepara"
            title="Reparación de equipos"
            content="Reparamos todo tipo de dispositivos electrónicos con eficiencia."
          />

          <CCard
            :image="images.imagenTienda"
            title="Venta de productos"
            content="Ofrecemos una amplia gama de productos de alta calidad."
          />
          <CCard
            :image="images.imagenDesarrollo"
            title="Desarrollo de software"
            content="Desarrollamos soluciones tecnológicas innovadoras para tu negocio."
          />
          <CCard
            :image="images.imagenSoporte"
            title="Soporte técnico"
            content="Brindamos soporte técnico especializado para tus dispositivos."
          />
          <CCard
            :image="images.imagenSoporte"
            title="Soporte técnico"
            content="Brindamos soporte técnico especializado para tus dispositivos."
          />
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .c-service-list {
    padding-top: 40px;

    &__title {
      margin-bottom: 2rem;
      text-align: center;
    }

    &__list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1rem;
    }

    &__item {
      background-color: #f0f0f0;
      padding: 1rem 2rem;
      border-radius: 8px;
      font-size: 1.25rem;
      transition: background-color 0.3s ease;

      &:hover {
        background-color: #e0e0e0;
      }
    }
  }
</style>
