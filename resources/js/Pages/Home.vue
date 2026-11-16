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

  interface BgIcon { d: string; x: number; y: number; size: number; opacity: number; delay: number; dur: number }

  const bgIcons: BgIcon[] = [
    { d: 'M4 5h16v10H4V5zm-1 10h18v2H3v-2z',                                                                                                        x:  2, y:  2, size: 56, opacity: 0.30, delay: 0.0, dur:  8 },
    { d: 'M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm5 16v1',                                                              x: 90, y:  5, size: 42, opacity: 0.26, delay: 1.4, dur: 11 },
    { d: 'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91A6 6 0 0114.7 6.3z', x: 20, y: 14, size: 46, opacity: 0.28, delay: 3.1, dur:  7 },
    { d: 'M2 4h20v13H2V4zm0 13 3 4h14l3-4M9 20h6',                                                                                                  x: 72, y: 20, size: 50, opacity: 0.26, delay: 0.7, dur: 13 },
    { d: 'M9 3h6v3h3v3h3v6h-3v3h-3v3H9v-3H6v-3H3V9h3V6h3V3zm0 6h6v6H9V9z',                                                                        x:  4, y: 30, size: 62, opacity: 0.22, delay: 2.5, dur:  9 },
    { d: 'M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z',                                                                                               x: 88, y: 36, size: 44, opacity: 0.26, delay: 4.2, dur: 12 },
    { d: 'M12 20h.01M2 8.82a15 15 0 0120 0M5 12.86A10 10 0 0119 12.86M8 16.82a5 5 0 018 0',                                                        x: 36, y: 42, size: 40, opacity: 0.24, delay: 1.0, dur:  6 },
    { d: 'M16 18l6-6-6-6M8 6L2 12l6 6',                                                                                                             x: 60, y: 48, size: 46, opacity: 0.28, delay: 5.3, dur: 10 },
    { d: 'M4 5h16v10H4V5zm-1 10h18v2H3v-2z',                                                                                                        x:  6, y: 56, size: 48, opacity: 0.24, delay: 0.3, dur: 14 },
    { d: 'M12 2a7 7 0 00-7 7v5a7 7 0 0014 0V9a7 7 0 00-7-7zm0 0v7',                                                                                x: 82, y: 62, size: 52, opacity: 0.28, delay: 3.7, dur:  8 },
    { d: 'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91A6 6 0 0114.7 6.3z', x: 24, y: 70, size: 36, opacity: 0.22, delay: 1.9, dur: 11 },
    { d: 'M9 3h6v3h3v3h3v6h-3v3h-3v3H9v-3H6v-3H3V9h3V6h3V3zm0 6h6v6H9V9z',                                                                        x: 68, y: 76, size: 50, opacity: 0.26, delay: 6.0, dur:  7 },
    { d: 'M2 4h20v13H2V4zm0 13 3 4h14l3-4M9 20h6',                                                                                                  x: 44, y: 82, size: 44, opacity: 0.24, delay: 2.2, dur: 15 },
    { d: 'M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm5 16v1',                                                              x: 54, y: 88, size: 38, opacity: 0.26, delay: 4.8, dur:  9 },
    { d: 'M16 18l6-6-6-6M8 6L2 12l6 6',                                                                                                             x: 13, y: 92, size: 42, opacity: 0.22, delay: 0.6, dur: 13 },
    { d: 'M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z',                                                                                               x: 85, y: 94, size: 46, opacity: 0.26, delay: 2.9, dur:  6 },
  ]
</script>

<template>
  <PublicLayout>
    <div class="home-page">

      <!-- ── Iconos de fondo flotantes ──────────────────────── -->
      <div class="home-bg" aria-hidden="true">
        <svg
          v-for="(icon, i) in bgIcons" :key="i"
          class="home-bg__icon"
          :style="{
            left: icon.x + '%',
            top: icon.y + '%',
            width: icon.size + 'px',
            height: icon.size + 'px',
            opacity: icon.opacity,
            animationDelay: icon.delay + 's',
            animationDuration: icon.dur + 's'
          }"
          viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"
        ><path :d="icon.d" /></svg>
      </div>

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
    </div>
  </PublicLayout>
</template>

<style lang="scss" scoped>
  @keyframes home-float {
    0%   { transform: translate(0px,   0px)  rotate(0deg);    }
    20%  { transform: translate(5px,  -12px) rotate(2deg);    }
    45%  { transform: translate(-4px,  -6px) rotate(-1.5deg); }
    65%  { transform: translate(6px,   8px)  rotate(2.5deg);  }
    85%  { transform: translate(-3px,  3px)  rotate(-1deg);   }
    100% { transform: translate(0px,   0px)  rotate(0deg);    }
  }

  .home-page {
    position: relative;
    overflow: hidden;
  }

  .home-bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;

    &__icon {
      position: absolute;
      color: #89c2ff;
      filter: drop-shadow(0 0 10px rgba(89, 160, 255, 0.55));
      animation: home-float 7s ease-in-out infinite;

      @media (prefers-reduced-motion: reduce) {
        animation: none;
      }
    }
  }

  // Sections painted after .home-bg (z-index:0) in document order — no z-index
  // needed, and setting one would create a stacking context that traps the
  // fixed logo-morph element below the header.
  :deep(.c-service-list),
  :deep(.c-shop),
  :deep(.c-video),
  :deep(.c-development),
  :deep(.c-projects),
  :deep(.c-contact) {
    position: relative;
  }
</style>
