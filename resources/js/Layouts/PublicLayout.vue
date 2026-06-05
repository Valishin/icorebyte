<script setup lang="ts">
  import CBannerFix from '@/Components/CBannerFix.vue'
  import CCookieBanner from '@/Components/CCookieBanner.vue'
  import CFooter from '@/Components/CFooter.vue'
  import CHeader from '@/Components/CHeader.vue'
  import CPageLoader from '@/Components/CPageLoader.vue'
  import CToast from '@/Components/CToast.vue'
  import { useTheme } from '@/composables/useTheme'
  import { onMounted } from 'vue'

  const { init } = useTheme()
  onMounted(() => init())
</script>

<template>
  <div class="l-public">
    <CPageLoader />

    <!--
      Banner promocional — edita las props para personalizar:
        title      → texto principal
        description→ subtexto (se oculta en móvil)
        image      → ruta a imagen o thumbnail (opcional)
        cta-label  → texto del botón
        cta-link   → ancla (#contacto) o URL externa
        date-start → 'YYYY-MM-DD'  (el banner no aparece antes de esta fecha)
        date-end   → 'YYYY-MM-DD'  (el banner desaparece después de esta fecha)
    -->
    <CBannerFix
      icon="IconMobile"
      title="20% dto. en cambio de pantalla para iPhone"
      description="iPhone 11, 12, 13, 14, 15 · Solo este mes"
      cta-label="Pedir cita"
      cta-link="#contacto"
      whatsapp-label="WhatsApp"
      date-start="2026-06-01"
      date-end="2026-06-30"
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
