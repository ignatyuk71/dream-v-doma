import { ref } from 'vue'

export const message = ref('')
export const type = ref('success')
export const visible = ref(false)

let timeout = null

export function showToast(msg, msgType = 'success') {
  message.value = msg
  type.value = msgType
  visible.value = true

  if (timeout) clearTimeout(timeout)
  timeout = setTimeout(() => {
    visible.value = false
  }, 8000)
}

export function hideToast() {
  visible.value = false
  clearTimeout(timeout)
}

window.showToast = showToast
