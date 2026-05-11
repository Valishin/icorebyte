import { ref } from 'vue'

// Singleton compartido — false hasta que el loader inicial desaparece
const loaderReady = ref(false)

export function useLoaderState() {
  const markReady = () => {
    loaderReady.value = true
  }
  return { loaderReady, markReady }
}
