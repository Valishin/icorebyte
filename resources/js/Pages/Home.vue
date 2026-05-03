<script setup lang="ts">
  import CContact from '@/Components/CContact.vue'
  import CDevelopment from '@/Components/CDevelopment.vue'
  import CHero from '@/Components/CHero.vue'
  import CServiceList from '@/Components/CServiceList.vue'
  import CShop from '@/Components/CShop.vue'
  import PublicLayout from '@/Layouts/PublicLayout.vue'
</script>

<template>
  <PublicLayout>
    <CHero id="inicio" />
    <CServiceList id="servicios" />
    <CShop id="productos" />
    <CDevelopment id="desarrollo" />
    <CContact id="contacto" />
  </PublicLayout>
</template>
