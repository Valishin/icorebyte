<script setup lang="ts">
  import CTitle from './CTitle.vue'

  withDefaults(
    defineProps<{
      /** Ruta al archivo .mp4 (relativa a /public o URL absoluta) */
      src: string
      /** Imagen de portada mientras no se reproduce */
      poster?: string
      /** Título descriptivo — aparece como caption debajo del vídeo */
      title?: string
      /** Reproducción automática (fuerza muted) */
      autoplay?: boolean
      /** Repetir en bucle */
      loop?: boolean
      /** Silenciado */
      muted?: boolean
      /** Mostrar controles nativos del navegador */
      controls?: boolean
      /** Relación de aspecto en desktop (ej. '16/9', '4/3', '1/1') */
      ratio?: string
      /** Relación de aspecto en móvil — si se omite usa ratio */
      ratioMobile?: string
    }>(),
    {
      poster: undefined,
      title: undefined,
      autoplay: false,
      loop: false,
      muted: false,
      controls: true,
      ratio: '16/9',
      ratioMobile: undefined
    }
  )
</script>

<template>
  <figure class="c-video">
    <div class="c-video__container o-container">
      <div class="o-col-12@md o-col-8@sm o-col-4@xs">
        <CTitle
          :title="'Cambio de pantalla y batería para iPhone'"
          :description="'Ofrecemos servicio de reparación para iPhone. Cambio de pantalla y batería calidad-precio insuperable.'"
          :color="'primary'"
          :button="{
            title: '¡Consúltanos!',
            color: 'gradient',
            href: '#contacto'
          }"
        />
      </div>
    </div>
    <div
      class="c-video__wrap"
      :style="{
        '--ratio': ratio,
        '--ratio-mobile': ratioMobile ?? ratio
      }"
    >
      <video
        class="c-video__player"
        :src="src"
        :poster="poster"
        :title="title"
        :autoplay="autoplay"
        :loop="loop"
        :muted="muted || autoplay"
        :controls="controls"
        playsinline
        preload="metadata"
      />
    </div>
    <figcaption v-if="title" class="c-video__caption o-font-display-caption">
      {{ title }}
    </figcaption>
  </figure>
</template>

<style lang="scss" scoped>
  .c-video {
    margin: 0;
    width: 100%;

    &__wrap {
      position: relative;
      width: 100%;
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid var(--color-border-subtle);
      background: var(--color-primary-dark);

      // Móvil: usa --ratio-mobile (fallback a --ratio si no se pasa)
      aspect-ratio: var(--ratio-mobile);

      @include from-sm {
        // A partir de sm usa siempre --ratio
        aspect-ratio: var(--ratio);
      }
    }

    &__player {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    &__caption {
      margin-top: 0.75rem;
      color: var(--color-gray-dark);
      text-align: center;
    }
  }
</style>
