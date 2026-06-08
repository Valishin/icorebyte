<script setup lang="ts">
  import CContact from '@/Components/CContact.vue'
  import CDevelopment from '@/Components/CDevelopment.vue'
  import CHero from '@/Components/CHero.vue'
  import CProjects from '@/Components/CProjects.vue'
  import CServiceList from '@/Components/CServiceList.vue'
  import CShop from '@/Components/CShop.vue'
  import CVideo from '@/Components/CVideo.vue'
  import PublicLayout from '@/Layouts/PublicLayout.vue'
  import { images } from '@/constants/images'
  import { videos } from '@/constants/videos'
</script>

<template>
  <PublicLayout>
    <CHero
      id="inicio"
      :bg-images="[
        images.heroSlide1,
        images.heroSlide2,
        images.heroSlide3,
        images.heroSlide4,
        images.heroSlide5
      ]"
    />
    <CServiceList id="servicios" />
    <CShop id="productos" />
    <CVideo
      :src="videos.icorebyteBoxFone"
      :controls="false"
      :loop="true"
      :autoplay="true"
      ratio="24/9"
      ratio-mobile="16/10"
    />
    <CDevelopment id="desarrollo" />
    <CProjects id="proyectos" />
    <CContact id="contacto" />
  </PublicLayout>
</template>
