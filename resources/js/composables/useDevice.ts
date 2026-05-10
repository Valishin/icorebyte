import { BREAKPOINTS } from '@/constants/breakpoints'
import { computed, onMounted, onUnmounted, ref } from 'vue'

export function useDevice() {
  const width = ref(window.innerWidth)

  const onResize = () => {
    width.value = window.innerWidth
  }

  onMounted(() => window.addEventListener('resize', onResize))
  onUnmounted(() => window.removeEventListener('resize', onResize))

  const isMobile  = computed(() => width.value < BREAKPOINTS.mobile)
  const isTablet  = computed(() => width.value >= BREAKPOINTS.mobile && width.value < BREAKPOINTS.tablet)
  const isDesktop = computed(() => width.value >= BREAKPOINTS.tablet)

  return { isMobile, isTablet, isDesktop }
}
