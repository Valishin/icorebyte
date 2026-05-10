<script setup lang="ts">
  import CFooter from '@/Components/CFooter.vue'
  import CHeader from '@/Components/CHeader.vue'
  import CToast from '@/Components/CToast.vue'
  import { useTheme } from '@/composables/useTheme'
  import { onMounted } from 'vue'

  const { init } = useTheme()
  onMounted(() => init())
</script>

<template>
  <div class="l-public">
    <CHeader />
    <main class="l-public__main">
      <slot />
    </main>
    <CFooter />
    <CToast />
  </div>
</template>

<style lang="scss" scoped>
  .l-public {
    display: flex;
    flex-direction: column;
    min-height: 100vh;

    &__main {
      flex: 1;
      padding-top: 36px;

      @include from-md {
        padding-top: 64px;
      }
    }
  }
</style>
