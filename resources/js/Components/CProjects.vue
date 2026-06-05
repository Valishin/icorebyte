<script setup lang="ts">
  import { useInView } from '@/composables/useInView'
  import { images } from '@/constants/images'
  import CProjectCard from './CProjectCard.vue'
  import CTitle from './CTitle.vue'

  defineProps<{ id?: string }>()

  const { el: gridRef, isVisible: gridVisible } = useInView(0.05)

  const projects = [
    {
      title: 'Maquinaria y hostelería directa',
      description:
        'Tienda online de maquinaria y equipamiento para hostelería con catálogo de productos, carrito de compra y pasarela de pago.',
      image: images.imageMhdProject,
      category: 'E-commerce',
      tags: ['SASS', 'BEMIT', 'Woocommerce', 'WordPress', 'PHP'],
      link: 'https://vd-mhd.validev.es'
    },
    {
      title: 'Validev',
      description:
        'Portfolio propio para mostrar servicios de desarrollo web, proyectos realizados y formulario de contacto.',
      image: images.validevProject,
      category: 'Portfolio',
      tags: ['Vue.js', 'BEMIT', 'SASS', 'Vite'],
      link: 'https://validev.es'
    },
    {
      title: 'APP SAT',
      description:
        'CRM para gestión de servicios técnicos, con panel de administración, gestión de clientes, partes de trabajo y facturación.',
      image: images.appsatProject,
      category: 'CRM',
      tags: ['Vue.js', 'Laravel', 'SASS', 'Vite', 'API REST'],
      link: '#'
    },
    {
      title: 'Makecoded',
      description:
        'Empresa de desarrollo de software a medida. El proyecto incluye diseño personalizado, catálogo de servicios, sección de proyectos realizados y formulario de contacto.',
      image: images.makecoded,
      category: 'Desarrollo Web',
      tags: ['Wordpress', 'PHP', 'SASS', 'BEMIT'],
      link: 'https://makecoded.com/'
    },
    {
      title: 'VD Beauty',
      description:
        'Página sobre productos de belleza e información sobre tratamientos estéticos. El proyecto incluye diseño personalizado, catálogo de productos, blog y formulario de contacto.',
      image: images.vdBeauty,
      category: 'E-commerce',
      tags: ['SASS', 'BEMIT', 'Wordpress', 'PHP'],
      link: 'https://vd-beauty.validev.es'
    }
  ]
</script>

<template>
  <section class="c-projects" :id="id">
    <div class="o-container">
      <CTitle
        title="Proyectos Realizados"
        description="Una selección de proyectos web desarrollados para nuestros clientes. Cada proyecto es una solución a medida, diseñada para impulsar su presencia digital."
        color="primary"
      />

      <div ref="gridRef" class="c-projects__grid" :class="{ 'is-visible': gridVisible }">
        <CProjectCard
          v-for="(project, i) in projects"
          :key="project.title"
          v-bind="project"
          :style="{ '--i': i }"
        />
      </div>
    </div>
  </section>
</template>

<style lang="scss" scoped>
  .c-projects {
    padding-bottom: 80px;

    @include from-sm {
      padding-bottom: 120px;
    }

    &__grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.25rem;

      @include from-sm {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
      }

      @include from-md {
        gap: 2rem;
      }

      // Animación de entrada staggered
      :deep(.c-project-card) {
        opacity: 0;
        transform: translateY(28px);
        transition:
          opacity 0.5s ease,
          transform 0.5s ease;
        transition-delay: calc(var(--i, 0) * 0.1s);

        @media (prefers-reduced-motion: reduce) {
          opacity: 1;
          transform: none;
          transition: none;
        }
      }

      &.is-visible :deep(.c-project-card) {
        opacity: 1;
        transform: none;
      }
    }
  }
</style>
