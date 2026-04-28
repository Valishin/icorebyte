<script setup lang="ts">
  import { images } from '@/constants/images'
  import CServiceCard from './CServiceCard.vue'
  import CTitle from './CTitle.vue'
</script>

<template>
  <div class="c-development">
    <div class="c-development__inner o-container">
      <div class="c-development__col o-col-6@md o-col-push-3@md o-col-6@sm o-col-4@xs">
        <CTitle
          :overline="'Desarrollo webs & apps'"
          :title="'Estudio de Desarrollo'"
          :description="'En iCorebyte, ofrecemos servicios de desarrollo de software a medida para satisfacer las necesidades específicas de tu negocio. Nuestro equipo de expertos en desarrollo trabaja estrechamente contigo para crear soluciones personalizadas que impulsen tu éxito.'"
          :color="'gradient'"
        />
      </div>
      <div class="c-development__col o-col-12@md o-col-6@sm o-col-4@xs">
        <div class="c-development__wrapper-services">
          <CServiceCard
            :title="'Plataforma E-Commerce'"
            :description="'Nuestra plataforma de e-commerce te permite vender tus productos online de forma sencilla y eficiente.'"
            :items="['Bagisto', 'Vue.js', 'Laravel', 'php', 'Woocommerce']"
            :image="images.imageEcommerce"
          />
          <CServiceCard
            :title="'Dashboard empresarial'"
            :description="'Nuestro dashboard empresarial te proporciona una visión completa de tus operaciones, permitiéndote tomar decisiones informadas y optimizar tu negocio.'"
            :items="['MySQL', 'Vue.js', 'Laravel', 'php', 'Node.js']"
            :image="images.imageDevelopment"
          />
          <CServiceCard
            :title="'Páginas web'"
            :description="'Ofrecemos servicios de creación de páginas web personalizadas para ayudarte a establecer una presencia online sólida y atractiva.'"
            :items="['Wordpress', 'Vue.js', 'Laravel', 'php', 'Rest API']"
            :image="images.imageAppMobile"
          />
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .c-development {
    &__wrapper-services {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
    }
  }
</style>
