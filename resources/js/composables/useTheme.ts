import { ref } from 'vue'

type Theme = 'dark' | 'light'
const STORAGE_KEY = 'icorebyte-theme'
const theme = ref<Theme>('dark')

export function useTheme() {
  const applyTheme = (t: Theme) => {
    theme.value = t
    document.documentElement.setAttribute('data-theme', t)
    document.body.classList.remove('theme-dark', 'theme-light')
    document.body.classList.add(`theme-${t}`)
    localStorage.setItem(STORAGE_KEY, t)
  }

  const toggle = () => applyTheme(theme.value === 'dark' ? 'light' : 'dark')

  const init = () => {
    const saved = localStorage.getItem(STORAGE_KEY) as Theme | null
    const systemPrefers = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark'
    applyTheme(saved ?? systemPrefers)
  }

  return { theme, toggle, init }
}
