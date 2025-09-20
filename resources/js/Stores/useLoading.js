// resources/js/Stores/useLoading.js
import { ref } from 'vue'

export const isLoading = ref(false)

export const startLoading = () => { isLoading.value = true }
export const stopLoading = () => { isLoading.value = false }
