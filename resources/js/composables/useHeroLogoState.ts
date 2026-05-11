import { ref } from 'vue'

// true mientras el Hero está montado y gestiona él mismo el logo
const heroManagesLogo = ref(false)

export function useHeroLogoState() {
  return { heroManagesLogo }
}
