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
  import { Head } from '@inertiajs/vue3'
</script>

<template>
  <Head>
    <title>iCoreByte | Tienda Informática y Reparación iPhone · Sant Feliu de Guíxols</title>

    <!-- Descripción principal (~155 chars) -->
    <meta
      name="description"
      content="Tienda de informática en Sant Feliu de Guíxols. Reparamos iPhone (SE al 15 Pro Max), ordenadores, tablets y portátiles. Venta de equipos. Desarrollo web. ☎ +34 678 758 210"
    />

    <!-- Keywords: tienda + reparación + modelos iPhone + dispositivos -->
    <meta
      name="keywords"
      content="tienda informática Sant Feliu de Guíxols, reparación iPhone Sant Feliu, reparación iPhone 15, reparación iPhone 15 Pro, reparación iPhone 15 Pro Max, reparación iPhone 14, reparación iPhone 14 Pro, reparación iPhone 14 Pro Max, reparación iPhone 13, reparación iPhone 13 Pro, reparación iPhone 12, reparación iPhone 11, reparación iPhone X, reparación iPhone XR, reparación iPhone XS, reparación iPhone 8, reparación iPhone 7, reparación iPhone SE, reparación ordenadores Girona, reparación portátiles Sant Feliu, reparación laptops, reparación tablets, servicio técnico informático, tienda informática Girona, informática Costa Brava, desarrollo web Sant Feliu, iCoreByte"
    />

    <!-- Open Graph -->
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="es_ES" />
    <meta property="og:site_name" content="iCoreByte" />
    <meta
      property="og:title"
      content="iCoreByte | Tienda Informática y Reparación iPhone · Sant Feliu de Guíxols"
    />
    <meta
      property="og:description"
      content="Tienda de informática en Sant Feliu de Guíxols. Reparamos iPhone (SE al 15 Pro Max), ordenadores, tablets y portátiles. Desarrollo web profesional."
    />
    <meta property="og:url" content="https://icorebyte.com" />
    <meta property="og:image" content="https://icorebyte.com/og-image.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:alt" content="iCoreByte — Tienda Informática Sant Feliu de Guíxols" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta
      name="twitter:title"
      content="iCoreByte | Tienda Informática y Reparación iPhone · Sant Feliu de Guíxols"
    />
    <meta
      name="twitter:description"
      content="Tienda de informática en Sant Feliu de Guíxols. Reparamos iPhone (SE al 15 Pro Max), ordenadores, tablets y portátiles."
    />
    <meta name="twitter:image" content="https://icorebyte.com/og-image.jpg" />
  </Head>

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
