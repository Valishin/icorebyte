<script setup lang="ts">
  import CBannerFix from '@/Components/CBannerFix.vue'
  import CCookieBanner from '@/Components/CCookieBanner.vue'
  import CFooter from '@/Components/CFooter.vue'
  import CHeader from '@/Components/CHeader.vue'
  import CPageLoader from '@/Components/CPageLoader.vue'
  import CToast from '@/Components/CToast.vue'
  import { useTheme } from '@/composables/useTheme'
  import { usePage } from '@inertiajs/vue3'
  import { computed, onMounted } from 'vue'

  const { init } = useTheme()
  onMounted(() => init())

  interface BannerProps {
    title      : string
    description?: string
    image?     : string
    cta_label? : string
    cta_link?  : string
    date_start?: string
    date_end?  : string
  }

  const banner = computed(() => (usePage().props.banner as BannerProps | null) ?? null)
</script>

<template>
  <div class="l-public">
    <CPageLoader />

    <CBannerFix
      v-if="banner"
      :title="banner.title"
      :description="banner.description"
      :image="banner.image"
      :cta-label="banner.cta_label"
      :cta-link="banner.cta_link"
      :date-start="banner.date_start"
      :date-end="banner.date_end"
    />

    <CHeader />
    <main class="l-public__main">
      <slot />
    </main>
    <CFooter />
    <CToast />
    <CCookieBanner />
  </div>
</template>

<style lang="scss" scoped>
  .l-public {
    display: flex;
    flex-direction: column;
    min-height: 100vh;

    &__main {
      flex: 1;
      // padding-top = header + banner (si existe)
      padding-top: calc(36px + var(--banner-h, 0px));

      @include from-md {
        padding-top: calc(64px + var(--banner-h, 0px));
      }
    }
  }
</style>
