<script setup lang="ts">
  import CFooter from '@/Components/CFooter.vue'
  import CHeader from '@/Components/CHeader.vue'
</script>

<template>
  <div class="l-public">
    <CHeader />
    <main class="l-public__main">
      <slot />
    </main>
    <CFooter />
  </div>
</template>

<style lang="scss" scoped>
  .l-public {
    display: flex;
    flex-direction: column;
    min-height: 100vh;

    &__main {
      flex: 1;
      padding-top: 64px; // altura del header
    }
  }
</style>
