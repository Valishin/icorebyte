// Singleton compartido para todos los efectos parallax de la web.
// N scroll listeners + N RAF loops  →  1 scroll listener + 1 RAF loop.

export interface ParallaxEntry {
  updateTarget(): void   // se llama en cada evento scroll
  lerp():        boolean // se llama cada frame; devuelve true mientras anima
}

const entries = new Set<ParallaxEntry>()
let   rafId   = 0
let   busy    = false

function frame() {
  let any = false
  entries.forEach(e => { if (e.lerp()) any = true })
  if (any) {
    rafId = requestAnimationFrame(frame)
  } else {
    busy = false
  }
}

function onScroll() {
  entries.forEach(e => e.updateTarget())
  if (!busy) {
    busy  = true
    rafId = requestAnimationFrame(frame)
  }
}

/** Registra una entrada parallax. Devuelve la función para desregistrarla. */
export function registerParallax(entry: ParallaxEntry): () => void {
  if (!entries.size) window.addEventListener('scroll', onScroll, { passive: true })
  entries.add(entry)

  return () => {
    entries.delete(entry)
    if (!entries.size) {
      window.removeEventListener('scroll', onScroll)
      cancelAnimationFrame(rafId)
      busy = false
    }
  }
}
