<script setup lang="ts">
  import { useDevice } from '@/composables/useDevice'
  import { useHeroLogoState } from '@/composables/useHeroLogoState'
  import { useTheme } from '@/composables/useTheme'
  import Logo from '@assets/logos/logo.svg'
  import { router } from '@inertiajs/vue3'
  import { onMounted, onUnmounted, ref, watch } from 'vue'

  const { isMobile } = useDevice()
  const { theme, toggle } = useTheme()
  const { heroManagesLogo } = useHeroLogoState()

  const menuOpen     = ref(false)
  const scrolled     = ref(false)
  const softwareOpen = ref(false)   // dropdown desktop
  const softwareMobileOpen = ref(false) // desplegable móvil

  // Cerrar dropdown al hacer click fuera
  const onClickOutside = (e: MouseEvent) => {
    const el = document.querySelector('.c-header__dropdown')
    if (el && !el.contains(e.target as Node)) softwareOpen.value = false
  }

  watch(isMobile, (val) => {
    if (!val) menuOpen.value = false
  })

  const onScroll = () => {
    scrolled.value = window.scrollY > 20
  }

  onMounted(() => {
    window.addEventListener('scroll', onScroll)
    document.addEventListener('click', onClickOutside)
  })
  onUnmounted(() => {
    window.removeEventListener('scroll', onScroll)
    document.removeEventListener('click', onClickOutside)
  })

  const navItems = [
    { label: 'Inicio', anchor: '#inicio' },
    { label: 'Servicios', anchor: '#servicios' },
    { label: 'Productos', anchor: '#productos' },
    { label: 'Desarrollo', anchor: '#desarrollo' },
    { label: 'Contacto', anchor: '#contacto' }
  ]

  const scrollTo = (anchor: string) => {
    if (anchor === '#inicio') {
      window.scrollTo({ top: 0, behavior: 'smooth' })
      return
    }
    const el = document.querySelector(anchor)
    if (el) el.scrollIntoView({ behavior: 'smooth' })
  }

  const handleNav = (anchor: string) => {
    if (window.location.pathname === '/') {
      scrollTo(anchor)
    } else {
      router.visit('/', {
        onSuccess: () => setTimeout(() => scrollTo(anchor), 80)
      })
    }
  }
</script>

<template>
  <header class="c-header" :class="{ 'c-header--scrolled': scrolled }">
    <div class="c-header__inner o-container">
      <!-- Burger móvil (izquierda) -->
      <button
        v-if="isMobile"
        class="c-header__burger"
        :class="{ 'c-header__burger--open': menuOpen }"
        @click="menuOpen = !menuOpen"
        :aria-label="menuOpen ? 'Cerrar menú' : 'Abrir menú'"
        :aria-expanded="menuOpen"
      >
        <span></span>
        <span></span>
        <span></span>
      </button>

      <!-- Logo desktop (izquierda) -->
      <!-- visibility:hidden (not display:none) so CHero can still measure its rect -->
      <a
        v-if="!isMobile"
        class="c-header__logo"
        :class="{ 'c-header__logo--hero-managed': heroManagesLogo }"
        @click.prevent="handleNav('#inicio')"
        href="#inicio"
      >
        <component class="c-header__svg" :is="Logo" />
      </a>

      <!-- Logo mobile (centrado absoluto) -->
      <a
        v-if="isMobile"
        class="c-header__logo c-header__logo--mobile"
        :class="{ 'c-header__logo--hero-managed': heroManagesLogo }"
        @click.prevent="handleNav('#inicio')"
        href="#inicio"
      >
        <component class="c-header__svg" :is="Logo" />
      </a>

      <!-- Nav desktop -->
      <nav v-if="!isMobile" class="c-header__nav">
        <a
          v-for="item in navItems"
          :key="item.label"
          :href="item.anchor"
          class="c-header__nav-link"
          @click.prevent="handleNav(item.anchor)"
        >
          {{ item.label }}
        </a>

        <!-- Dropdown Software -->
        <div class="c-header__dropdown" :class="{ 'is-open': softwareOpen }">
          <button
            class="c-header__nav-link c-header__nav-link--dropdown"
            :aria-expanded="softwareOpen"
            aria-haspopup="true"
            @click.stop="softwareOpen = !softwareOpen"
          >
            Software
            <svg class="c-header__chevron" viewBox="0 0 16 16" fill="none" aria-hidden="true">
              <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6"
                    stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>

          <Transition name="dropdown">
            <div v-if="softwareOpen" class="c-header__dropdown-panel">
              <a href="/sat-it" class="c-header__dropdown-item" @click.prevent="router.visit('/sat-it'); softwareOpen = false">
                <span>
                  <strong>SAT IT</strong>
                  <span class="c-header__dropdown-item-desc">Gestión de talleres SAT</span>
                </span>
              </a>
            </div>
          </Transition>
        </div>
      </nav>

      <!-- Theme toggle (derecha) -->
      <button
        class="c-theme-toggle"
        :class="`c-theme-toggle--${theme}`"
        @click="toggle"
        :aria-label="theme === 'dark' ? 'Activar modo claro' : 'Activar modo oscuro'"
      >
        <span class="c-theme-toggle__track">
          <span class="c-theme-toggle__thumb">
            <Transition name="theme-icon" mode="out-in">
              <svg
                v-if="theme === 'dark'"
                key="moon"
                class="c-theme-toggle__icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
              </svg>
              <svg
                v-else
                key="sun"
                class="c-theme-toggle__icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <circle cx="12" cy="12" r="5" />
                <line x1="12" y1="1" x2="12" y2="3" />
                <line x1="12" y1="21" x2="12" y2="23" />
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                <line x1="1" y1="12" x2="3" y2="12" />
                <line x1="21" y1="12" x2="23" y2="12" />
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
              </svg>
            </Transition>
          </span>
        </span>
      </button>

      <!-- Menú móvil -->
      <Transition name="menu-slide">
        <div v-if="isMobile && menuOpen" class="c-header__mobile-menu">
          <a
            v-for="item in navItems"
            :key="item.label"
            :href="item.anchor"
            class="c-header__mobile-link"
            @click.prevent="
              () => {
                handleNav(item.anchor)
                menuOpen = false
              }
            "
          >
            {{ item.label }}
          </a>
          <!-- Software desplegable móvil -->
          <div class="c-header__mobile-section">
            <button
              class="c-header__mobile-link c-header__mobile-link--section"
              @click="softwareMobileOpen = !softwareMobileOpen"
            >
              Software
              <svg class="c-header__chevron" :class="{ 'is-open': softwareMobileOpen }"
                   viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6"
                      stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
            <Transition name="dropdown">
              <div v-if="softwareMobileOpen" class="c-header__mobile-sub">
                <a
                  href="/sat-it"
                  class="c-header__mobile-sub-link"
                  @click.prevent="router.visit('/sat-it'); menuOpen = false; softwareMobileOpen = false"
                >
                  SAT IT
                </a>
              </div>
            </Transition>
          </div>

          <a
            href="#contacto"
            class="c-header__cta c-header__cta--mobile"
            @click.prevent="
              () => {
                handleNav('#contacto')
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
    top: var(--banner-h, 0px); // se desplaza cuando el banner está activo
    left: 0;
    z-index: 100;
    border-bottom: 1px solid transparent;
    background: var(--color-black);
    transition:
      top 0.35s ease,
      background 0.3s ease,
      border-color 0.3s ease;

    &__inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 64px;
      position: relative;
      background-color: var(--color-black);
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
      color: var(--color-gray);
      text-decoration: none;
      transition: color 0.2s;
      cursor: pointer;

      &:hover {
        color: var(--color-secondary);
      }

      &--dropdown {
        display: flex;
        align-items: center;
        gap: 4px;
        background: none;
        border: none;
        padding: 0;
        font-family: inherit;
      }
    }

    // ── Dropdown ──────────────────────────────────────────
    &__chevron {
      width: 14px;
      height: 14px;
      flex-shrink: 0;
      transition: transform 0.25s ease;

      .is-open & { transform: rotate(180deg); }
      &.is-open  { transform: rotate(180deg); }
    }

    &__dropdown {
      position: relative;

      &.is-open .c-header__nav-link--dropdown {
        color: var(--color-white);
      }
    }

    &__dropdown-panel {
      position: absolute;
      top: calc(100% + 16px);
      left: 50%;
      transform: translateX(-50%);
      min-width: 220px;
      background: var(--color-background);
      border: 1px solid var(--color-border-subtle);
      border-radius: 12px;
      padding: 8px;
      box-shadow: 0 16px 48px rgba(0, 0, 0, 0.18);
      z-index: 200;

      // Triángulo apuntador
      &::before {
        content: '';
        position: absolute;
        top: -6px;
        left: 50%;
        transform: translateX(-50%);
        width: 10px;
        height: 10px;
        background: var(--color-background);
        border-left: 1px solid var(--color-border-subtle);
        border-top: 1px solid var(--color-border-subtle);
        rotate: 45deg;
      }
    }

    &__dropdown-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 12px;
      border-radius: 8px;
      text-decoration: none;
      color: var(--color-text, var(--color-white));
      transition: background 0.2s ease;

      &:hover {
        background: var(--color-primary-dark);
      }

      strong {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
      }
    }

    &__dropdown-item-icon {
      font-size: 1.2rem;
      flex-shrink: 0;
    }

    &__dropdown-item-desc {
      display: block;
      font-size: 0.75rem;
      color: var(--color-gray-dark);
      margin-top: 1px;
    }

    // ── Móvil: sección desplegable ────────────────────────
    &__mobile-section {
      display: flex;
      flex-direction: column;
    }

    &__mobile-link--section {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: none;
      border: none;
      padding: 0;
      font-family: inherit;
      font-size: 1rem;
      color: var(--color-text-muted);
      cursor: pointer;
      width: 100%;
      text-align: left;

      &:hover { color: var(--color-white); }
    }

    &__mobile-sub {
      padding-left: 16px;
      margin-top: 8px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      border-left: 2px solid var(--color-primary);
    }

    &__mobile-sub-link {
      font-size: 0.9rem;
      color: var(--color-gray-dark);
      text-decoration: none;
      transition: color 0.2s;

      &:hover { color: var(--color-white); }
    }

    &__cta {
      display: inline-flex;
      align-items: center;
      padding: 0.5rem 1.25rem;
      border-radius: 6px;
      font-size: 0.9375rem;
      font-weight: 600;
      color: var(--color-white);
      background: var(--color-primary);
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
      justify-content: center;
      gap: 5px;
      background: none;
      border: none;
      cursor: pointer;
      padding: 4px;
      width: 32px;
      height: 32px;

      span {
        display: block;
        width: 24px;
        height: 2px;
        background: var(--color-white);
        border-radius: 2px;
        transition:
          transform 0.3s ease,
          opacity 0.3s ease;
        transform-origin: center;
      }

      // ── Estado abierto → cruz ──────────────────────────────
      &--open {
        span:nth-child(1) {
          transform: translateY(7px) rotate(45deg);
        }
        span:nth-child(2) {
          opacity: 0;
          transform: scaleX(0);
        }
        span:nth-child(3) {
          transform: translateY(-7px) rotate(-45deg);
        }
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
      border-top: 1px solid var(--color-border-subtle);
      gap: 1rem;
      transform-origin: top;
    }
  }

  // ── Pill toggle ───────────────────────────────────────────────
  .c-theme-toggle {
    background: none;
    border: none;
    cursor: pointer;
    padding: 2px;
    flex-shrink: 0;
    outline: none;

    &:focus-visible .c-theme-toggle__track {
      outline: 2px solid var(--color-secondary);
      outline-offset: 2px;
    }

    &__track {
      position: relative;
      display: block;
      width: 52px;
      height: 28px;
      border-radius: 14px;
      border: 1px solid var(--color-border-subtle);
      background: rgba(255, 255, 255, 0.06);
      transition:
        background 0.35s ease,
        border-color 0.35s ease;
    }

    &__thumb {
      position: absolute;
      top: 3px;
      left: 3px;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
      color: #1e1b4b;
    }

    &__icon {
      width: 11px;
      height: 11px;
      display: block;
    }

    &--dark {
      .c-theme-toggle__thumb {
        transform: translateX(24px);
        color: #1e1b4b;
      }
    }

    &--light {
      .c-theme-toggle__track {
        background: rgba(245, 158, 11, 0.15);
        border-color: rgba(245, 158, 11, 0.5);
      }

      .c-theme-toggle__thumb {
        transform: translateX(0);
        color: #f59e0b;
      }
    }

    &:hover .c-theme-toggle__track {
      border-color: var(--color-secondary, rgba(255, 255, 255, 0.3));
    }
  }

  .theme-icon-enter-active,
  .theme-icon-leave-active {
    transition:
      opacity 0.18s ease,
      transform 0.18s ease;
  }

  .theme-icon-enter-from {
    opacity: 0;
    transform: rotate(45deg) scale(0.4);
  }

  .theme-icon-leave-to {
    opacity: 0;
    transform: rotate(-45deg) scale(0.4);
  }

  // ──────────────────────────────────────────────────────────────
  // ── Transición dropdown ───────────────────────────────────────
  .dropdown-enter-active,
  .dropdown-leave-active {
    transition:
      opacity   0.2s ease,
      transform 0.2s ease;
  }
  .dropdown-enter-from,
  .dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px);
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
      color: var(--color-text-muted);
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

      &--mobile {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        pointer-events: auto;
      }

      // Hidden while the hero's fixed logo manages this slot.
      // visibility:hidden keeps the layout box so getBoundingClientRect still returns a valid rect.
      &--hero-managed {
        visibility: hidden;
        pointer-events: none;
      }
    }
  }
</style>
