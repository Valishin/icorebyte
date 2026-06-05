<script setup lang="ts">
  import { WHATSAPP_URL } from '@/constants/contact'
  import { icons } from '@assets/icons'
  import { computed, onMounted, onUnmounted, ref } from 'vue'

  const props = withDefaults(
    defineProps<{
      title          : string
      description?   : string
      image?         : string
      /** Icono del sistema (keyof icons) que aparece antes del título */
      icon?          : keyof typeof icons
      /** Texto del botón CTA principal */
      ctaLabel?      : string
      /** Enlace del CTA principal (ancla o URL) */
      ctaLink?       : string
      /** Texto del botón de WhatsApp — si se omite no se muestra */
      whatsappLabel? : string
      /** Fecha de inicio (YYYY-MM-DD) — no aparece antes de esta fecha */
      dateStart?     : string
      /** Fecha de fin (YYYY-MM-DD) — desaparece automáticamente */
      dateEnd?       : string
    }>(),
    {
      description   : undefined,
      image         : undefined,
      icon          : undefined,
      ctaLabel      : undefined,
      ctaLink       : undefined,
      whatsappLabel : undefined,
      dateStart     : undefined,
      dateEnd       : undefined,
    }
  )

  const BANNER_H = 52
  const visible  = ref(false)

  // ── Rango de fechas ───────────────────────────────────────
  const isWithinRange = (): boolean => {
    const now = new Date()
    if (props.dateStart && new Date(props.dateStart) > now) return false
    if (props.dateEnd) {
      const end = new Date(props.dateEnd)
      end.setHours(23, 59, 59, 999)
      if (end < now) return false
    }
    return true
  }

  // ── Días restantes ────────────────────────────────────────
  const daysLeft = computed<number | null>(() => {
    if (!props.dateEnd) return null
    const end = new Date(props.dateEnd)
    end.setHours(23, 59, 59, 999)
    const diff = Math.ceil((end.getTime() - Date.now()) / 86_400_000)
    return diff >= 0 ? diff : null
  })

  const daysLabel = computed(() => {
    const d = daysLeft.value
    if (d === null) return null
    if (d === 0) return '¡Hoy es el último día!'
    if (d === 1) return '¡Último día!'
    return `Quedan ${d} días`
  })

  const formatDate = (iso: string) =>
    new Date(iso).toLocaleDateString('es-ES', { day: 'numeric', month: 'short' })

  // ── CSS var ───────────────────────────────────────────────
  const setVar = (h: number) =>
    document.documentElement.style.setProperty('--banner-h', `${h}px`)

  onMounted(() => {
    if (isWithinRange()) {
      visible.value = true
      setVar(BANNER_H)
    }
  })

  onUnmounted(() => setVar(0))
</script>

<template>
  <Transition name="banner-fix">
    <div v-if="visible" class="c-banner-fix" role="banner" aria-live="polite">
      <div class="c-banner-fix__inner o-container">

        <!-- Imagen opcional -->
        <img
          v-if="image"
          :src="image"
          alt=""
          aria-hidden="true"
          class="c-banner-fix__image"
        />

        <!-- Texto principal -->
        <div class="c-banner-fix__content">
          <component
            v-if="icon"
            :is="icons[icon]"
            class="c-banner-fix__icon"
            aria-hidden="true"
          />
          <span class="c-banner-fix__title">{{ title }}</span>
          <span v-if="description" class="c-banner-fix__description">
            &nbsp;—&nbsp;{{ description }}
          </span>
        </div>

        <!-- Badges de fecha -->
        <div v-if="dateEnd || daysLabel" class="c-banner-fix__dates">
          <span v-if="daysLabel" class="c-banner-fix__badge c-banner-fix__badge--days">
            {{ daysLabel }}
          </span>
          <span v-if="dateEnd" class="c-banner-fix__badge c-banner-fix__badge--until">
            <svg viewBox="0 0 16 16" fill="none" width="11" height="11">
              <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.4"/>
              <path d="M8 4.5V8l2.5 1.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            Hasta el {{ formatDate(dateEnd) }}
          </span>
        </div>

        <!-- CTA principal -->
        <a
          v-if="ctaLink && ctaLabel"
          :href="ctaLink"
          class="c-banner-fix__cta"
          :target="ctaLink.startsWith('http') ? '_blank' : undefined"
          :rel="ctaLink.startsWith('http') ? 'noopener noreferrer' : undefined"
        >
          {{ ctaLabel }}
        </a>

        <!-- WhatsApp -->
        <a
          v-if="whatsappLabel"
          :href="WHATSAPP_URL"
          target="_blank"
          rel="noopener noreferrer"
          class="c-banner-fix__cta c-banner-fix__cta--whatsapp"
          :aria-label="`Contactar por WhatsApp: ${whatsappLabel}`"
        >
          <svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15" aria-hidden="true">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
          </svg>
          {{ whatsappLabel }}
        </a>

      </div>
    </div>
  </Transition>
</template>

<style lang="scss" scoped>
  .c-banner-fix {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 101;
    height: 52px;
    background: var(--color-gradient);
    overflow: hidden;

    &__inner {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      height: 100%;

      @media (max-width: 480px) {
        gap: 0.375rem;
      }
    }

    &__image {
      width: 28px;
      height: 28px;
      object-fit: cover;
      border-radius: 6px;
      flex-shrink: 0;

      @media (max-width: 480px) {
        display: none;
      }
    }

    &__content {
      flex: 1;
      display: flex;
      align-items: center;
      min-width: 0;
      overflow: hidden;
    }

    &__icon {
      width: 15px;
      height: 15px;
      flex-shrink: 0;
      color: #ffffff;
      margin-right: 5px;

      @media (max-width: 380px) {
        display: none;
      }
    }

    &__title {
      font-size: 0.8125rem;
      font-weight: 700;
      color: #ffffff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      min-width: 0;

      @media (max-width: 480px) {
        font-size: 0.75rem;
      }
    }

    &__description {
      font-size: 0.8125rem;
      color: rgba(255, 255, 255, 0.85);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;

      @media (max-width: 640px) {
        display: none;
      }
    }

    &__dates {
      display: flex;
      align-items: center;
      gap: 0.375rem;
      flex-shrink: 0;

      @media (max-width: 768px) {
        display: none;
      }
    }

    &__badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 0.6875rem;
      font-weight: 600;
      white-space: nowrap;
      border-radius: 20px;
      padding: 3px 10px;

      &--days {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
      }

      &--until {
        background: rgba(0, 0, 0, 0.2);
        color: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.2);
      }
    }

    &__cta {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.8125rem;
      font-weight: 600;
      color: #ffffff;
      text-decoration: none;
      background: rgba(255, 255, 255, 0.18);
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 6px;
      padding: 5px 14px;
      white-space: nowrap;
      flex-shrink: 0;
      transition:
        background 0.2s,
        border-color 0.2s;

      @media (max-width: 600px) {
        &:not(.c-banner-fix__cta--whatsapp) {
          display: none;
        }
      }

      &:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.7);
      }

      &--whatsapp {
        background: rgba(37, 211, 102, 0.25);
        border-color: rgba(37, 211, 102, 0.6);

        @media (max-width: 400px) {
          font-size: 0;
          gap: 0;
          padding: 6px 8px;

          svg { width: 16px; height: 16px; }
        }

        &:hover {
          background: rgba(37, 211, 102, 0.45);
          border-color: rgba(37, 211, 102, 0.9);
        }
      }
    }
  }

  .banner-fix-enter-active,
  .banner-fix-leave-active {
    transition:
      transform 0.35s ease,
      opacity 0.35s ease;
  }

  .banner-fix-enter-from,
  .banner-fix-leave-to {
    transform: translateY(-100%);
    opacity: 0;
  }
</style>
