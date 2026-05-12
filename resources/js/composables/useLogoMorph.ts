import { clamp, easeOutQuart, lerp } from '@/utils/math'
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useHeroLogoState } from './useHeroLogoState'
import { useLoaderState } from './useLoaderState'


/**
 * Gestiona el logo fijo que viaja desde la posición del hero hasta el header al hacer scroll.
 *
 * Uso:
 *   const { heroRef, fixedLogoStyle, handleLogoClick } = useLogoMorph()
 *
 * - heroRef        → poner en el elemento raíz del hero (para calcular triggerDist)
 * - fixedLogoStyle → :style del <a> con el logo fijo
 * - handleLogoClick → @click del mismo <a>
 */
export function useLogoMorph() {
  const { heroManagesLogo, loaderMorphedToHero, loaderIsActive } = useHeroLogoState()
  const { loaderReady } = useLoaderState()

  const heroRef       = ref<HTMLElement>()
  const fixedLogoStyle = ref<Record<string, string>>({ opacity: '0', pointerEvents: 'none' })

  // Estado mutable interno (no reactivo, solo para cálculos de scroll)
  let initRect     = { left: 0, top: 0, width: 0, height: 0 }
  let headRect     = { left: 0, top: 0, width: 0 }
  let triggerDist  = 350
  let measured     = false
  let entranceDone = false
  let rafId        = 0

  // ── Aplicar estilos al logo fijo ──────────────────────────
  const applyStyle = (eased: number, opacity = 1, transition = '') => {
    const style: Record<string, string> = {
      position        : 'fixed',
      top             : '0',
      left            : '0',
      width           : `${initRect.width}px`,
      transformOrigin : 'top left',
      willChange      : 'transform',
      pointerEvents   : 'auto',
      zIndex          : '101',
      cursor          : 'pointer',
      transform       : `translate(${lerp(initRect.left, headRect.left, eased)}px, ${lerp(initRect.top, headRect.top, eased)}px) scale(${lerp(1, headRect.width / initRect.width, eased)})`,
      opacity         : String(opacity),
    }
    if (transition) style.transition = transition
    fixedLogoStyle.value = style
  }

  // ── Medir posiciones y arrancar la animación de entrada ───
  const measurePositions = (skipEntrance = false) => {
    // [data-hero-logo] apunta al SVG real → bounds exactos aunque sea visibility:hidden
    const placeholder = document.querySelector('[data-hero-logo]') as HTMLElement | null
    // .c-header__logo--hero-managed mantiene layout (visibility:hidden) → getBoundingClientRect ok
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
      top   : pRect.top + scrollY, // posición absoluta en página (scroll=0 al arrancar)
      width : pRect.width,
      height: pRect.height,
    }
    headRect = {
      left : hRect.left,
      top  : hRect.top,            // viewport (header es fixed, no se mueve)
      width: hRect.width,
    }
    triggerDist = heroRef.value
      ? Math.min(500, heroRef.value.offsetHeight * 0.5)
      : 350

    measured = true

    const eased = easeOutQuart(clamp(scrollY / triggerDist, 0, 1))

    if (skipEntrance) {
      // El loader ya hizo el morph — aparecemos al instante, sin fade
      applyStyle(eased, 1)
      entranceDone = true
    } else {
      // Navegación SPA — fade-in suave
      applyStyle(eased, 0)
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          applyStyle(eased, 1, 'opacity 0.5s ease-out')
          setTimeout(() => { entranceDone = true }, 600)
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
      const opacity = entranceDone ? 1 : parseFloat(fixedLogoStyle.value.opacity ?? '1')
      applyStyle(eased, opacity)
    })
  }

  // ── Lifecycle ─────────────────────────────────────────────
  onMounted(() => {
    heroManagesLogo.value = true // header logo oculto mientras el hero gestiona el logo

    if (loaderIsActive.value) {
      // El overlay del loader está activo (carga inicial o re-mount del layout en navegación).
      // Esperamos a que CPageLoader termine el morph y señale con loaderMorphedToHero=true.
      // El hero logo permanece invisible (opacity:0) hasta ese momento.
      const stop = watch(loaderMorphedToHero, (morphed) => {
        if (morphed) { measurePositions(true); stop() }
      })
    } else if (loaderReady.value) {
      // No hay overlay activo y la página ya está lista (layout persistente, SPA sin loader).
      // Fade-in suave.
      setTimeout(() => measurePositions(false), 50)
    } else {
      // Carga inicial en página no-home: esperamos a loaderReady.
      const stop = watch(loaderReady, (ready) => {
        if (ready) { setTimeout(() => measurePositions(false), 50); stop() }
      })
    }

    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', () => measurePositions(false))
  })

  onUnmounted(() => {
    heroManagesLogo.value     = false // restaurar logo del header en otras páginas
    loaderMorphedToHero.value = false // próxima visita al home: esperar al loader de nuevo
    window.removeEventListener('scroll', onScroll)
    window.removeEventListener('resize', () => measurePositions(false))
    cancelAnimationFrame(rafId)
  })

  // ── Click — vuelve al inicio ──────────────────────────────
  const handleLogoClick = () =>
    document.querySelector('#inicio')?.scrollIntoView({ behavior: 'smooth' })

  return { heroRef, fixedLogoStyle, handleLogoClick }
}
