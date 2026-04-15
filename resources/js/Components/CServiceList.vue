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
            :color="'primary'"
          />
        </div>
        <div class="c-service-list__list">
          <CCard
            :image="images.imagenRepara"
            :iconInImage="'IconMaintenance'"
            title="PC de sobremesa"
            content="Diagnóstico, reparación y montaje de equipos de sobremesa."
          />

          <CCard
            :image="images.imagenTienda"
            title="Portátiles"
            content="Reparación, mantenimiento y venta de portátiles de todas las marcas."
          />
          <CCard
            :image="images.imagenDesarrollo"
            title="Móviles"
            content="Reparamos y optimizamos dispositivos móviles de todas las marcas."
          />
          <CCard
            :image="images.imagenSoporte"
            title="Tablets"
            content="Reparamos y optimizamos tablets de todas las marcas."
          />
          <CCard
            :image="images.imagenSoporte"
            title="Electrónica en placa"
            content="Reparación a nivel de componente en placas base. Microsoldadura y diagnóstico avanzado con osciloscopio."
          />
          <CCard
            :image="images.imagenSoporte"
            title="Impresoras"
            content="Reparación, mantenimiento y configuración de impresoras láser, inyección de tinta y multifunción."
          />
          <CCard
            :image="images.imagenSoporte"
            title="Mantenimiento IT"
            content="Servicios de mantenimiento informático preventivo y correctivo para particulares y empresas."
          />
          <CCard
            :image="images.imagenSoporte"
            title="Soporte Empresarial"
            content="Planes de mantenimiento a medida para empresas. Soporte técnico remoto y presencial con SLA garantizado."
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
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      text-align: center;
    }
  }
</style>
