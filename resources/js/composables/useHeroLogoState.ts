import { ref } from 'vue'

// true mientras el Hero está montado y gestiona él mismo el logo
const heroManagesLogo = ref(false)

// true cuando CPageLoader terminó el morph al hero — CHero aparece al instante sin fade-in
const loaderMorphedToHero = ref(false)

// true mientras el overlay del loader está activo — useLogoMorph espera antes de aparecer
const loaderIsActive = ref(false)

export function useHeroLogoState() {
  return { heroManagesLogo, loaderMorphedToHero, loaderIsActive }
}
