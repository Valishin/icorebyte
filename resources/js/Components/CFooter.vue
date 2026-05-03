<script setup lang="ts">
  import Logo from '@assets/logos/logo.svg'
  const currentYear = new Date().getFullYear()

  const services = [
    'Reparación de Ordenadores',
    'Reparación de Móviles',
    'Reparación de Tablets',
    'Reparación de Impresoras',
    'Electrónica en Placa'
  ]

  const products = [
    'Ordenadores a Medida',
    'Portátiles',
    'Tintas y Tóner',
    'Componentes',
    'Accesorios'
  ]

  const development = [
    'Páginas Web',
    'Aplicaciones Web',
    'E-Commerce',
    'Mantenimiento Web',
    'SEO & Marketing'
  ]
</script>

<template>
  <footer class="c-footer">
    <div class="c-footer__inner o-container">
      <!-- Col izquierda -->
      <div class="c-footer__brand">
        <div class="c-footer__logo">
          <component class="c-header__svg" :is="Logo" />
        </div>
        <p class="c-footer__description">
          Tu centro integral de soluciones informáticas. Reparación, venta de productos y desarrollo
          web con la máxima calidad y profesionalidad.
        </p>
      </div>

      <!-- Servicios -->
      <div class="c-footer__col">
        <h4 class="c-footer__col-title">Servicios</h4>
        <ul class="c-footer__list">
          <li v-for="item in services" :key="item">
            <a href="#servicios" class="c-footer__link">{{ item }}</a>
          </li>
        </ul>
      </div>

      <!-- Productos -->
      <div class="c-footer__col">
        <h4 class="c-footer__col-title">Productos</h4>
        <ul class="c-footer__list">
          <li v-for="item in products" :key="item">
            <a href="#productos" class="c-footer__link">{{ item }}</a>
          </li>
        </ul>
      </div>

      <!-- Desarrollo -->
      <div class="c-footer__col">
        <h4 class="c-footer__col-title">Desarrollo</h4>
        <ul class="c-footer__list">
          <li v-for="item in development" :key="item">
            <a href="#desarrollo" class="c-footer__link">{{ item }}</a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Bottom bar -->
    <div class="c-footer__bottom">
      <div class="c-footer__bottom-inner o-container">
        <p class="c-footer__copy">© {{ currentYear }} iCoreByte. Todos los derechos reservados.</p>
        <div class="c-footer__legal">
          <a href="#" class="c-footer__legal-link">Política de Privacidad</a>
          <a href="#" class="c-footer__legal-link">Aviso Legal</a>
          <a href="#" class="c-footer__legal-link">Cookies</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<style lang="scss" scoped>
  .c-footer {
    background: var(--color-black);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    margin-top: 4rem;

    &__inner {
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr 1fr;
      gap: 3rem;
      padding-top: 3rem;
      padding-bottom: 3rem;

      @media (max-width: 1024px) {
        grid-template-columns: 1fr 1fr;
      }

      @media (max-width: 640px) {
        grid-template-columns: 1fr;
      }
    }

    &__brand {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    &__logo {
      display: flex;
      align-items: center;
      gap: 0.625rem;
    }

    &__logo-text {
      font-size: 1.125rem;
      color: var(--color-white);

      strong {
        font-weight: 700;
      }
    }

    &__description {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.5);
      line-height: 1.6;
      max-width: 280px;
    }

    &__col {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    &__col-title {
      font-size: 0.9375rem;
      font-weight: 700;
      color: var(--color-white);
      margin: 0;
    }

    &__list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    &__link {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.5);
      text-decoration: none;
      transition: color 0.2s;

      &:hover {
        color: var(--color-white);
      }
    }

    &__bottom {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    &__bottom-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 1.25rem;
      padding-bottom: 1.25rem;

      @media (max-width: 640px) {
        flex-direction: column;
        gap: 0.75rem;
        text-align: center;
      }
    }

    &__copy {
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.4);
      margin: 0;
      font-family: monospace;
    }

    &__legal {
      display: flex;
      gap: 1.5rem;
    }

    &__legal-link {
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.4);
      text-decoration: none;
      transition: color 0.2s;

      &:hover {
        color: var(--color-white);
      }
    }
  }
</style>
