<script setup lang="ts">
  import { useDevice } from '@/composables/useDevice'
  import Logo from '@assets/logos/logo.svg'
  import { onMounted, onUnmounted, ref, watch } from 'vue'

  const { isMobile } = useDevice()

  const menuOpen = ref(false)
  const scrolled = ref(false)

  watch(isMobile, (val) => {
    if (!val) menuOpen.value = false
  })

  const onScroll = () => {
    scrolled.value = window.scrollY > 20
  }

  onMounted(() => window.addEventListener('scroll', onScroll))
  onUnmounted(() => window.removeEventListener('scroll', onScroll))

  const navItems = [
    { label: 'Inicio', anchor: '#inicio' },
    { label: 'Servicios', anchor: '#servicios' },
    { label: 'Productos', anchor: '#productos' },
    { label: 'Desarrollo', anchor: '#desarrollo' },
    { label: 'Contacto', anchor: '#contacto' }
  ]

  const scrollTo = (anchor: string) => {
    const el = document.querySelector(anchor)
    if (el) el.scrollIntoView({ behavior: 'smooth' })
  }
</script>

<template>
  <header class="c-header" :class="{ 'c-header--scrolled': scrolled }">
    <div class="c-header__inner o-container">
      <!-- Logo -->
      <a
        v-if="!isMobile"
        class="c-header__logo"
        @click.prevent="scrollTo('#inicio')"
        href="#inicio"
      >
        <component class="c-header__svg" :is="Logo" />
      </a>

      <!-- Nav -->
      <nav v-if="!isMobile" class="c-header__nav">
        <a
          v-for="item in navItems"
          :key="item.label"
          :href="item.anchor"
          class="c-header__nav-link"
          @click.prevent="scrollTo(item.anchor)"
        >
          {{ item.label }}
        </a>
      </nav>

      <!-- Burger móvil -->
      <button
        v-if="isMobile"
        class="c-header__burger"
        @click="menuOpen = !menuOpen"
        aria-label="Menú"
      >
        <span></span>
        <span></span>
        <span></span>
      </button>
      <!-- Menú móvil -->
      <Transition name="menu-slide">
      <div
        v-if="isMobile && menuOpen"
        class="c-header__mobile-menu"
      >
        <a
          v-for="item in navItems"
          :key="item.label"
          :href="item.anchor"
          class="c-header__mobile-link"
          @click.prevent="
            () => {
              scrollTo(item.anchor)
              menuOpen = false
            }
          "
        >
          {{ item.label }}
        </a>
        <a
          href="#contacto"
          class="c-header__cta c-header__cta--mobile"
          @click.prevent="
            () => {
              scrollTo('#contacto')
              menuOpen = false
            }
          "
        >
          Solicitar Presupuesto
        </a>
      </div>
      </Transition>
    </div>
  </header>
</template>

<style lang="scss" scoped>
  .c-header {
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 100;
    border-bottom: 1px solid transparent;
    background: transparent;
    transition:
      background 0.3s ease,
      border-color 0.3s ease;

    &--scrolled {
      background: var(--color-black);
      border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    &__inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 64px;
    }

    &__logo {
      display: flex;
      align-items: center;
      gap: 0.625rem;
      text-decoration: none;
      cursor: pointer;
    }

    &__logo-text {
      font-size: 1.125rem;
      color: var(--color-white);
      letter-spacing: -0.01em;

      strong {
        font-weight: 700;
        color: var(--color-white);
      }
    }

    &__nav {
      display: flex;
      align-items: center;
      gap: 2rem;
    }

    &__nav-link {
      font-size: 0.9375rem;
      color: rgba(255, 255, 255, 0.75);
      text-decoration: none;
      transition: color 0.2s;
      cursor: pointer;

      &:hover {
        color: var(--color-white);
      }
    }

    &__cta {
      display: inline-flex;
      align-items: center;
      padding: 0.5rem 1.25rem;
      border-radius: 6px;
      font-size: 0.9375rem;
      font-weight: 600;
      color: var(--color-white);
      background: var(--color-gradient);
      text-decoration: none;
      cursor: pointer;
      transition: opacity 0.2s;

      &:hover {
        opacity: 0.85;
      }

      &--mobile {
        margin-top: 0.5rem;
        justify-content: center;
      }
    }

    &__burger {
      display: flex;
      flex-direction: column;
      gap: 5px;
      background: none;
      border: none;
      cursor: pointer;
      padding: 4px;

      span {
        display: block;
        width: 24px;
        height: 2px;
        background: var(--color-white);
        border-radius: 2px;
        transition: all 0.3s;
      }
    }

    &__mobile-menu {
      display: flex;
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      width: 100%;
      flex-direction: column;
      padding: 1rem 1.5rem 1.5rem;
      background: var(--color-black);
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      gap: 1rem;
      transform-origin: top;
    }
  }

  .menu-slide-enter-active,
  .menu-slide-leave-active {
    transition:
      transform 0.28s ease,
      opacity 0.28s ease;
  }

  .menu-slide-enter-from,
  .menu-slide-leave-to {
    transform: scaleY(0);
    opacity: 0;
  }

  .menu-slide-enter-to,
  .menu-slide-leave-from {
    transform: scaleY(1);
    opacity: 1;
  }

  .c-header {
    &__mobile-link {
      font-size: 1rem;
      color: rgba(255, 255, 255, 0.75);
      text-decoration: none;
      transition: color 0.2s;

      &:hover {
        color: var(--color-white);
      }
    }

    &__logo {
      display: flex;
      align-items: center;
      gap: 0.625rem;
      text-decoration: none;
      cursor: pointer;
      width: 140px;
    }
  }
</style>
