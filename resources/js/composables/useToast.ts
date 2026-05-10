import { ref } from 'vue'

type ToastType = 'success' | 'error' | 'info'

const visible = ref(false)
const type    = ref<ToastType>('info')
const message = ref('')
const barKey  = ref(0)
let timer: ReturnType<typeof setTimeout>

export function useToast() {
  const show = (t: ToastType, msg: string, duration = 3000) => {
    clearTimeout(timer)
    type.value    = t
    message.value = msg
    visible.value = true
    barKey.value++
    timer = setTimeout(() => { visible.value = false }, duration)
  }

  const close = () => {
    clearTimeout(timer)
    visible.value = false
  }

  return { visible, type, message, barKey, show, close }
}
