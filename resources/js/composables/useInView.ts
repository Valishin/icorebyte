import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useLoaderState } from './useLoaderState'

export function useInView(threshold = 0.15) {
  const el = ref<HTMLElement | null>(null)
  const isVisible = ref(false)
  const { loaderReady } = useLoaderState()

  let observer: IntersectionObserver | null = null

  const startObserving = () => {
    if (!el.value || isVisible.value) return
    observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          isVisible.value = true
          observer?.disconnect()
        }
      },
      { threshold }
    )
    observer.observe(el.value)
  }

  onMounted(() => {
    if (loaderReady.value) {
      // Navegaciones posteriores: loader ya terminó, observar de inmediato
      startObserving()
    } else {
      // Primera carga: esperar a que el loader termine
      const stop = watch(loaderReady, (ready) => {
        if (ready) {
          startObserving()
          stop()
        }
      })
    }
  })

  onUnmounted(() => observer?.disconnect())

  return { el, isVisible }
}
