<template>
  <input
    ref="inputRef"
    type="text"
    v-currency="options"
    v-model="modelValue"
    class="form-control"
  />
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: Number,
  options: Object
})

const emit = defineEmits(['update:modelValue'])
const inputRef = ref()

// sinkronisasi value
watch(
  () => props.modelValue,
  (val) => {
    if (inputRef.value && inputRef.value.value !== val) {
      emit('update:modelValue', val)
    }
  }
)
</script>
