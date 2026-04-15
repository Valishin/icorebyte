<script setup lang="ts">
  import { images } from '@/constants/images'
  import Logo from '@assets/logos/logo.svg'
  import { useScroll } from '../composables/useScroll'
  import CCard from './CCard.vue'

  const { scrollToNextSection } = useScroll()
</script>
<template>
  <div class="c-hero">
    <div class="c-hero__inner">
      <div class="c-hero__container o-container">
        <div class="c-hero__col o-col-8@md o-col-push-2@md o-col-8@sm o-col-4@xs">
          <div class="c-hero__overline">
            <p class="c-hero__overline-text o-font-display-caption">
              Servicios IT profesionales en tu zona
            </p>
          </div>
          <div class="c-hero__title">
            <h1 class="c-hero__title">
              <span class="sr-only">iCoreByte</span>
              <component class="c-hero__svg" :is="Logo" />
            </h1>
          </div>
          <div class="c-hero__subtitle">
            <p class="c-hero__subtitle-text o-font-display-3">
              Tu centro integral de soluciones informáticas. Reparación, venta y desarrollo
              tecnológico al más alto nivel.
            </p>
          </div>
        </div>
        <div class="c-hero__wrapper-cards">
          <CCard
            :image="images.imagenRepara"
            title="Reparación de equipos"
            content="Reparamos todo tipo de dispositivos electrónicos con eficiencia."
          />
          <CCard
            :image="images.imagenTienda"
            title="Nuestra tienda"
            content="Ofrecemos una amplia gama de productos de alta calidad."
          />
          <CCard
            :image="images.imagenDesarrollo"
            title="Desarrollo"
            content="Desarrollamos soluciones tecnológicas innovadoras para tu negocio."
          />
        </div>
        <button
          class="c-hero__explore"
          type="button"
          @click="scrollToNextSection('.c-hero')"
          aria-label="Explorar y hacer scroll"
        >
          <span class="c-hero__explore-text">Explorar</span>
          <span class="c-hero__explore-arrow" aria-hidden="true">↓</span>
        </button>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .c-hero {
    &__inner {
      height: 100vh;
      text-align: center;
      // padding-top: 50px;
    }
    &__overline {
      padding-bottom: 40px;
    }
    &__overline-text {
      display: inline-block;
      color: var(--color-white);
      margin-bottom: 1rem;
      border: 1px solid var(--color-gray-dark);
      padding: 5px 10px;
      border-radius: 9999px;
      animation: c-hero-overline-float 3.2s ease-in-out infinite;
    }
    &__title {
      padding-bottom: 20px;
    }
    &__title-text {
      color: var(--color-white);
      margin-bottom: 1rem;
    }
    &__subtitle {
      padding-bottom: 24px;
    }
    &__subtitle-text {
      color: var(--color-gray-dark);
    }
    &__explore {
      margin-top: 24px;
      display: inline-flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      border: 0;
      background: transparent;
      color: var(--color-white);
      cursor: pointer;
      opacity: 0.85;
      transition: opacity 0.3s ease;

      &:hover {
        opacity: 1;
      }
    }
    &__explore-text {
      font-size: 0.9rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
    &__explore-arrow {
      font-size: 1rem;
      line-height: 1;
      animation: c-hero-arrow-bounce 1.4s ease-in-out infinite;
    }
    &__svg {
      position: relative;
      left: 50%;
      transform: translateX(-50%);
      animation: c-hero-logo-enter 0.9s ease-out both;
    }
    &__wrapper-cards {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      justify-content: center;
      margin-top: 40px;
    }
  }

  @keyframes c-hero-arrow-bounce {
    0%,
    100% {
      transform: translateY(-2px);
    }
    50% {
      transform: translateY(4px);
    }
  }

  @keyframes c-hero-overline-float {
    0%,
    100% {
      transform: translateY(-1px);
    }
    50% {
      transform: translateY(2px);
    }
  }

  @keyframes c-hero-logo-enter {
    0% {
      opacity: 0;
      transform: translateX(calc(-50% - 10px));
    }
    100% {
      opacity: 1;
      transform: translateX(-50%);
    }
  }
</style>
