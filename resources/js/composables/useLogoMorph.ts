import { clamp, easeOutQuart, lerp } from '@/utils/math'
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useHeroLogoState } from './useHeroLogoState'
import { useLoaderState } from './useLoaderState'


/**
 * Gestiona el logo fijo que viaja desde la posición del hero hasta el header al hacer scroll.
 *
 * Uso:
 *   const { heroRef, logoFixedEl, handleLogoClick } = useLogoMorph()
 *
 * - heroRef      → poner en el elemento raíz del hero
 * - logoFixedEl  → poner en el <a> del logo fijo (ref de template, sin :style)
 * - handleLogoClick → @click del mismo <a>
 *
 * Optimización: los estilos de scroll (transform + opacity) se aplican directamente
 * al elemento DOM, sin pasar por la reactividad de Vue, para evitar VDOM diff en cada frame.
 */
export function useLogoMorph() {
  const { heroManagesLogo, loaderMorphedToHero, loaderIsActive } = useHeroLogoState()
  const { loaderReady } = useLoaderState()

  const heroRef    = ref<HTMLElement>()
  const logoFixedEl = ref<HTMLElement>()  // ref directo al elemento DOM del logo fijo

  // Estado mutable interno (no reactivo, solo para cálculos de scroll)
  let initRect     = { left: 0, top: 0, width: 0, height: 0 }
  let headRect     = { left: 0, top: 0, width: 0 }
  let triggerDist  = 350
  let measured     = false
  let entranceDone = false
  let rafId        = 0

  // ── Estilos fijos (se aplican una vez al medir) ───────────
  const setupElement = () => {
    const el = logoFixedEl.value
    if (!el) return
    el.style.position       = 'fixed'
    el.style.top            = '0'
    el.style.left           = '0'
    el.style.width          = `${initRect.width}px`
    el.style.transformOrigin = 'top left'
    el.style.willChange     = 'transform, opacity'
    el.style.pointerEvents  = 'auto'
    el.style.zIndex         = '101'
    el.style.cursor         = 'pointer'
  }

  // ── Solo transform + opacity en cada frame (compositor-only) ─
  const applyTransform = (eased: number, opacity = 1, transition = '') => {
    const el = logoFixedEl.value
    if (!el) return
    const x = lerp(initRect.left, headRect.left, eased)
    const y = lerp(initRect.top,  headRect.top,  eased)
    const s = lerp(1, headRect.width / initRect.width, eased)
    el.style.transform  = `translate(${x}px, ${y}px) scale(${s})`
    el.style.opacity    = String(opacity)
    el.style.transition = transition
  }

  // ── Medir posiciones y arrancar la animación de entrada ───
  const measurePositions = (skipEntrance = false) => {
    const placeholder = document.querySelector('[data-hero-logo]') as HTMLElement | null
    const headerEl = (
      document.querySelector('.c-header__logo--mobile') ??
      document.querySelector('.c-header__logo')
    ) as HTMLElement | null

    if (!placeholder || !headerEl) return

    const pRect   = placeholder.getBoundingClientRect()
    const hRect   = headerEl.getBoundingClientRect()
    const scrollY = window.scrollY

    initRect = {
      left  : pRect.left,
      top   : pRect.top + scrollY,
      width : pRect.width,
      height: pRect.height,
    }
    headRect = {
      left : hRect.left,
      top  : hRect.top,
      width: hRect.width,
    }
    triggerDist = heroRef.value
      ? Math.min(500, heroRef.value.offsetHeight * 0.5)
      : 350

    measured = true
    setupElement()

    const eased = easeOutQuart(clamp(scrollY / triggerDist, 0, 1))

    if (skipEntrance) {
      applyTransform(eased, 1)
      entranceDone = true
    } else {
      applyTransform(eased, 0)
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          applyTransform(eased, 1, 'opacity 0.5s ease-out')
          setTimeout(() => {
            applyTransform(eased, 1, '') // quitar transition para no interferir con scroll
            entranceDone = true
          }, 600)
        })
      })
    }
  }

  // ── Scroll handler ────────────────────────────────────────
  const onScroll = () => {
    cancelAnimationFrame(rafId)
    rafId = requestAnimationFrame(() => {
      if (!measured) return
      const scrollY = window.scrollY
      const eased   = easeOutQuart(clamp(scrollY / triggerDist, 0, 1))
      const el      = logoFixedEl.value
      const opacity = entranceDone ? 1 : parseFloat(el?.style.opacity ?? '1')
      applyTransform(eased, opacity)
    })
  }

  // ── Lifecycle ─────────────────────────────────────────────
  onMounted(() => {
    heroManagesLogo.value = true

    // Ocultar el logo hasta que measurePositions lo posicione
    if (logoFixedEl.value) {
      logoFixedEl.value.style.opacity       = '0'
      logoFixedEl.value.style.pointerEvents = 'none'
    }

    if (loaderIsActive.value) {
      const stop = watch(loaderMorphedToHero, (morphed) => {
        if (morphed) { measurePositions(true); stop() }
      })
    } else if (loaderReady.value) {
      setTimeout(() => measurePositions(false), 50)
    } else {
      const stop = watch(loaderReady, (ready) => {
        if (ready) { setTimeout(() => measurePositions(false), 50); stop() }
      })
    }

    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', () => measurePositions(false))
  })

  onUnmounted(() => {
    heroManagesLogo.value     = false
    loaderMorphedToHero.value = false
    window.removeEventListener('scroll', onScroll)
    window.removeEventListener('resize', () => measurePositions(false))
    cancelAnimationFrame(rafId)
  })

  // ── Click — vuelve al inicio ──────────────────────────────
  const handleLogoClick = () =>
    document.querySelector('#inicio')?.scrollIntoView({ behavior: 'smooth' })

  return { heroRef, logoFixedEl, handleLogoClick }
}
