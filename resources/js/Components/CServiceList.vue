<script setup lang="ts">
  import { images } from '@/constants/images'
  import { icons } from '@assets/icons'
  import type { Swiper as SwiperType } from 'swiper'
  import 'swiper/css'
  import 'swiper/css/pagination'
  import { Autoplay, Pagination } from 'swiper/modules'
  import { Swiper, SwiperSlide } from 'swiper/vue'
  import { ref } from 'vue'
  import CCard from './CCard.vue'
  import CTitle from './CTitle.vue'

  type IconKey = keyof typeof icons

  const modules = [Autoplay, Pagination]

  const swiperRef = ref<SwiperType | null>(null)

  const onSwiper = (swiper: SwiperType) => {
    swiperRef.value = swiper
  }

  const prev = () => swiperRef.value?.slidePrev()
  const next = () => swiperRef.value?.slideNext()

  const cards: { image: string; icon: IconKey; title: string; content: string }[] = [
    {
      image: images.imagePcRepair,
      icon: 'IconDesktop',
      title: 'PC de sobremesa',
      content: 'Diagnóstico, reparación y montaje de equipos de sobremesa.'
    },
    {
      image: images.imageLaptopRepair,
      icon: 'IconLaptop',
      title: 'Portátiles',
      content: 'Reparación, mantenimiento y venta de portátiles de todas las marcas.'
    },
    {
      image: images.imagePhoneRepair,
      icon: 'IconMobile',
      title: 'Móviles',
      content: 'Reparamos y optimizamos dispositivos móviles de todas las marcas.'
    },
    {
      image: images.imageIpad,
      icon: 'IconTablet',
      title: 'Tablets',
      content: 'Reparamos y optimizamos tablets de todas las marcas.'
    },
    {
      image: images.imagenRepara,
      icon: 'IconElectronic',
      title: 'Electrónica en placa',
      content:
        'Reparación a nivel de componente en placas base. Microsoldadura y diagnóstico avanzado con osciloscopio.'
    },
    {
      image: images.imagePrinterRepair,
      icon: 'IconPrinter',
      title: 'Impresoras',
      content:
        'Reparación, mantenimiento y configuración de impresoras láser, inyección de tinta y multifunción.'
    },
    {
      image: images.imageItMaintance,
      icon: 'IconMaintenance',
      title: 'Mantenimiento IT',
      content:
        'Servicios de mantenimiento informático preventivo y correctivo para particulares y empresas.'
    },
    {
      image: images.imageSupportIt,
      icon: 'IconSupport',
      title: 'Soporte Empresarial',
      content:
        'Planes de mantenimiento a medida para empresas. Soporte técnico remoto y presencial con SLA garantizado.'
    }
  ]
</script>

<template>
  <div class="c-service-list">
    <div class="c-service-list__inner">
      <div class="c-service-list__container o-container">
        <div class="c-service-list__header">
          <div class="o-col-12@md o-col-8@sm o-col-4@xs">
            <CTitle
              overline="Servicios de reparación"
              title="Laboratorio de Reparación"
              description="Reparamos cualquier dispositivo con herramientas de última generación."
              :color="'primary'"
            />
          </div>
        </div>
        <Swiper
          class="c-service-list__slider"
          :modules="modules"
          :slides-per-view="1.2"
          :space-between="16"
          :loop="true"
          :autoplay="{ delay: 3500, disableOnInteraction: false }"
          :pagination="{ clickable: true }"
          :breakpoints="{
            640: { slidesPerView: 2.2 },
            1024: { slidesPerView: 3.2 },
            1280: { slidesPerView: 4.2 }
          }"
          @swiper="onSwiper"
        >
          <div class="c-service-list__nav">
            <button class="c-service-list__nav-btn" @click="prev" aria-label="Anterior">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 19l-7-7 7-7"
                />
              </svg>
            </button>
            <button class="c-service-list__nav-btn" @click="next" aria-label="Siguiente">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </button>
          </div>
          <SwiperSlide v-for="card in cards" :key="card.title">
            <CCard
              :image="card.image"
              :iconInImage="card.icon"
              :title="card.title"
              :content="card.content"
            />
          </SwiperSlide>
        </Swiper>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .c-service-list {
    padding-top: 40px;

    &__header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      margin-bottom: 1.5rem;
    }

    &__nav {
      display: flex;
      gap: 0.5rem;
      padding-bottom: 0.5rem; // alinea con la base del título
      position: relative;
      justify-content: space-between;
      width: 100%;
      bottom: -30px;
    }

    &__nav-btn {
      width: 2.5rem;
      height: 2.5rem;
      border-radius: 50%;
      border: 1px solid var(--color-primary);
      background: transparent;
      color: var(--color-primary);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition:
        background 0.2s,
        color 0.2s;

      svg {
        width: 1.1rem;
        height: 1.1rem;
      }

      &:hover {
        background: var(--color-primary);
        color: var(--color-white);
      }
    }

    &__slider {
      width: 100%;
      padding-bottom: 2.5rem;
      position: relative;

      :deep(.swiper-slide) {
        height: auto;
        display: flex;

        .c-card {
          width: 100%;

          &__inner {
            display: flex;
            flex-direction: column;
            height: 100%;
          }

          &__wrapper-content {
            flex: 1;
          }
        }
      }

      :deep(.swiper-pagination-bullet) {
        background: var(--color-gray-dark);
        opacity: 1;
      }

      :deep(.swiper-pagination-bullet-active) {
        background: var(--color-primary);
      }
    }
  }
</style>
